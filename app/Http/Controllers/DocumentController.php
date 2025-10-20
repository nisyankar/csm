<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Employee;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class DocumentController extends Controller
{
    /**
     * Display a listing of documents
     */
    public function index(Request $request): Response
    {
        $query = Document::with(['uploader', 'employee', 'project', 'verifier']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('original_filename', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        // Filter by document type
        if ($request->filled('document_type')) {
            $query->where('document_type', $request->document_type);
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by employee
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        // Filter by project
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        // Filter by date range
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('created_at', [
                Carbon::parse($request->date_from)->startOfDay(),
                Carbon::parse($request->date_to)->endOfDay(),
            ]);
        }

        // Authorization filters
        $user = Auth::user();
        if (!$user->hasRole(['admin', 'project_manager'])) {
            if ($user->hasRole('site_manager')) {
                // Site managers can see documents from their projects
                $managedProjects = Project::where('site_manager_id', $user->employee->id)->pluck('id');
                $query->where(function ($q) use ($managedProjects, $user) {
                    $q->whereIn('project_id', $managedProjects)
                      ->orWhere('uploaded_by', $user->id)
                      ->orWhere('is_public', true);
                });
            } else {
                // Regular employees can only see their own documents and public ones
                $query->where(function ($q) use ($user) {
                    $q->where('uploaded_by', $user->id)
                      ->orWhere('employee_id', $user->employee->id)
                      ->orWhere('is_public', true);
                });
            }
        }

        $documents = $query->orderBy('created_at', 'desc')->paginate(20);

        // Get filter options
        $filterOptions = $this->getFilterOptions($user);

        return Inertia::render('Documents/Index', [
            'documents' => $documents,
            'filters' => $request->only([
                'search', 'document_type', 'category', 'status', 
                'employee_id', 'project_id', 'date_from', 'date_to'
            ]),
            'filterOptions' => $filterOptions,
        ]);
    }

    /**
     * Show the form for creating a new document
     */
    public function create(): Response
    {
        $employees = Employee::select('id', 'first_name', 'last_name', 'employee_code')->get();
        $projects = Project::select('id', 'name', 'project_code')->get();

        return Inertia::render('Documents/Create', [
            'employees' => $employees,
            'projects' => $projects,
            'documentTypes' => $this->getDocumentTypes(),
            'categories' => $this->getCategories(),
        ]);
    }

    /**
     * Store a newly created document
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'document_type' => 'required|in:contract,certificate,permit,report,invoice,other',
            'category' => 'nullable|string|max:100',
            'employee_id' => 'nullable|exists:employees,id',
            'project_id' => 'nullable|exists:projects,id',
            'is_mandatory' => 'boolean',
            'is_public' => 'boolean',
            'expiry_date' => 'nullable|date|after:today',
            'file' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png,xls,xlsx|max:10240',
        ]);

        // Handle file upload
        $file = $request->file('file');
        $filename = time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('documents', $filename, 'public');

        // Create document
        $document = Document::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'document_type' => $validated['document_type'],
            'category' => $validated['category'],
            'employee_id' => $validated['employee_id'],
            'project_id' => $validated['project_id'],
            'filename' => $filename,
            'original_filename' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'file_extension' => $file->getClientOriginalExtension(),
            'file_hash' => hash_file('sha256', $file->path()),
            'is_mandatory' => $validated['is_mandatory'] ?? false,
            'is_public' => $validated['is_public'] ?? false,
            'expiry_date' => $validated['expiry_date'],
            'uploaded_by' => Auth::id(),
            'status' => 'pending',
            'version' => 1,
            'is_latest_version' => true,
        ]);

        return redirect()->route('documents.show', $document)
            ->with('success', 'Belge başarıyla yüklendi.');
    }

    /**
     * Display the specified document
     */
    public function show(Document $document): Response
    {
        $this->authorizeDocumentAccess($document);

        $document->load([
            'uploader',
            'employee',
            'project',
            'verifier',
            'archiver',
            'versions' => function ($query) {
                $query->orderBy('version', 'desc');
            }
        ]);

        // Get document history
        $history = $this->getDocumentHistory($document);

        return Inertia::render('Documents/Show', [
            'document' => $document,
            'history' => $history,
            'canEdit' => $this->canEditDocument($document),
            'canVerify' => $this->canVerifyDocument($document),
            'canArchive' => $this->canArchiveDocument($document),
        ]);
    }

    /**
     * Show the form for editing the document
     */
    public function edit(Document $document): Response
    {
        $this->authorizeDocumentEdit($document);

        $employees = Employee::select('id', 'first_name', 'last_name', 'employee_code')->get();
        $projects = Project::select('id', 'name', 'project_code')->get();

        return Inertia::render('Documents/Edit', [
            'document' => $document,
            'employees' => $employees,
            'projects' => $projects,
            'documentTypes' => $this->getDocumentTypes(),
            'categories' => $this->getCategories(),
        ]);
    }

    /**
     * Update the specified document
     */
    public function update(Request $request, Document $document): RedirectResponse
    {
        $this->authorizeDocumentEdit($document);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'document_type' => 'required|in:contract,certificate,permit,report,invoice,other',
            'category' => 'nullable|string|max:100',
            'employee_id' => 'nullable|exists:employees,id',
            'project_id' => 'nullable|exists:projects,id',
            'is_mandatory' => 'boolean',
            'is_public' => 'boolean',
            'expiry_date' => 'nullable|date|after:today',
            'file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png,xls,xlsx|max:10240',
        ]);

        // Handle file upload if new file provided
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            
            // Create new version
            $newVersion = $document->createNewVersion([
                'file' => $file,
                'updated_by' => Auth::id(),
                'change_reason' => 'Dosya güncellendi',
            ]);

            $validated['version'] = $newVersion->version;
            $validated['filename'] = $newVersion->filename;
            $validated['original_filename'] = $file->getClientOriginalName();
            $validated['file_path'] = $newVersion->file_path;
            $validated['file_size'] = $file->getSize();
            $validated['mime_type'] = $file->getMimeType();
            $validated['file_extension'] = $file->getClientOriginalExtension();
            $validated['file_hash'] = hash_file('sha256', $file->path());
        }

        $document->update($validated);

        return redirect()->route('documents.show', $document)
            ->with('success', 'Belge başarıyla güncellendi.');
    }

    /**
     * Remove the specified document
     */
    public function destroy(Document $document): RedirectResponse
    {
        $this->authorizeDocumentEdit($document);

        // Check if document can be deleted
        if ($document->status === 'verified' && !Auth::user()->hasRole('admin')) {
            return back()->with('error', 'Onaylanmış belgeler silinemez.');
        }

        // Delete physical file
        if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        // Delete all versions
        foreach ($document->versions as $version) {
            if (Storage::disk('public')->exists($version->file_path)) {
                Storage::disk('public')->delete($version->file_path);
            }
            $version->delete();
        }

        $document->delete();

        return redirect()->route('documents.index')
            ->with('success', 'Belge başarıyla silindi.');
    }

    /**
     * Download document
     */
    public function download(Document $document)
    {
        $this->authorizeDocumentAccess($document);

        if (!Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'Dosya bulunamadı.');
        }

        // Update download statistics
        $document->increment('download_count');
        $document->update([
            'last_downloaded_at' => now(),
            'last_downloaded_by' => Auth::id(),
        ]);

        return Storage::disk('public')->download(
            $document->file_path,
            $document->original_filename
        );
    }

    /**
     * Verify document
     */
    public function verify(Request $request, Document $document): RedirectResponse
    {
        if (!$this->canVerifyDocument($document)) {
            return back()->with('error', 'Bu belgeyi doğrulama yetkiniz yok.');
        }

        $validated = $request->validate([
            'verification_notes' => 'nullable|string|max:1000',
        ]);

        $document->verify(Auth::user(), $validated['verification_notes']);

        return back()->with('success', 'Belge başarıyla doğrulandı.');
    }

    /**
     * Reject document
     */
    public function reject(Request $request, Document $document): RedirectResponse
    {
        if (!$this->canVerifyDocument($document)) {
            return back()->with('error', 'Bu belgeyi reddetme yetkiniz yok.');
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $document->reject(Auth::user(), $validated['rejection_reason']);

        return back()->with('success', 'Belge reddedildi.');
    }

    /**
     * Archive document
     */
    public function archive(Request $request, Document $document): RedirectResponse
    {
        if (!$this->canArchiveDocument($document)) {
            return back()->with('error', 'Bu belgeyi arşivleme yetkiniz yok.');
        }

        $validated = $request->validate([
            'archive_reason' => 'nullable|string|max:500',
        ]);

        $document->archive(Auth::user(), $validated['archive_reason']);

        return back()->with('success', 'Belge arşivlendi.');
    }

    /**
     * Restore archived document
     */
    public function restore(Document $document): RedirectResponse
    {
        if (!Auth::user()->hasAnyRole(['admin', 'project_manager'])) {
            return back()->with('error', 'Belge geri yükleme yetkiniz yok.');
        }

        $document->update([
            'status' => 'pending',
            'is_archived' => false,
            'archived_by' => null,
            'archived_at' => null,
            'archive_reason' => null,
        ]);

        return back()->with('success', 'Belge arşivden geri yüklendi.');
    }

    /**
     * Get documents expiring soon
     */
    public function expiring(Request $request): JsonResponse
    {
        $days = $request->input('days', 30);

        $documents = Document::where('expiry_date', '<=', now()->addDays($days))
            ->where('expiry_date', '>', now())
            ->where('status', '!=', 'archived')
            ->with(['employee', 'project'])
            ->orderBy('expiry_date', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'documents' => $documents,
            'count' => $documents->count(),
        ]);
    }

    /**
     * Bulk operations
     */
    public function bulkAction(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'action' => 'required|in:verify,reject,archive,delete',
            'document_ids' => 'required|array|min:1',
            'document_ids.*' => 'required|exists:documents,id',
            'reason' => 'nullable|string|max:1000',
        ]);

        $user = Auth::user();
        $documents = Document::whereIn('id', $validated['document_ids'])->get();
        
        $successful = 0;
        $failed = 0;
        $errors = [];

        foreach ($documents as $document) {
            try {
                switch ($validated['action']) {
                    case 'verify':
                        if ($this->canVerifyDocument($document)) {
                            $document->verify($user, $validated['reason']);
                            $successful++;
                        } else {
                            $failed++;
                            $errors[] = "Belge #{$document->id} doğrulama yetkisi yok";
                        }
                        break;

                    case 'reject':
                        if ($this->canVerifyDocument($document)) {
                            $document->reject($user, $validated['reason'] ?? 'Toplu reddetme');
                            $successful++;
                        } else {
                            $failed++;
                            $errors[] = "Belge #{$document->id} reddetme yetkisi yok";
                        }
                        break;

                    case 'archive':
                        if ($this->canArchiveDocument($document)) {
                            $document->archive($user, $validated['reason']);
                            $successful++;
                        } else {
                            $failed++;
                            $errors[] = "Belge #{$document->id} arşivleme yetkisi yok";
                        }
                        break;

                    case 'delete':
                        if ($this->canEditDocument($document)) {
                            if (Storage::disk('public')->exists($document->file_path)) {
                                Storage::disk('public')->delete($document->file_path);
                            }
                            $document->delete();
                            $successful++;
                        } else {
                            $failed++;
                            $errors[] = "Belge #{$document->id} silme yetkisi yok";
                        }
                        break;
                }
            } catch (\Exception $e) {
                $failed++;
                $errors[] = "Belge #{$document->id}: " . $e->getMessage();
            }
        }

        return response()->json([
            'success' => true,
            'message' => "{$successful} belge işlendi" . ($failed > 0 ? ", {$failed} başarısız." : "."),
            'successful' => $successful,
            'failed' => $failed,
            'errors' => $errors,
        ]);
    }

    // Private helper methods

    private function authorizeDocumentAccess(Document $document): void
    {
        $user = Auth::user();

        // Public documents are accessible to everyone
        if ($document->is_public) {
            return;
        }

        // Admin and project managers can access all documents
        if ($user->hasAnyRole(['admin', 'project_manager'])) {
            return;
        }

        // Document uploader can always access
        if ($document->uploaded_by === $user->id) {
            return;
        }

        // Employee can access their own documents
        if ($document->employee_id === $user->employee->id) {
            return;
        }

        // Site managers can access documents from their projects
        if ($user->hasRole('site_manager') && $document->project_id) {
            $project = Project::find($document->project_id);
            if ($project && $project->site_manager_id === $user->employee->id) {
                return;
            }
        }

        abort(403, 'Bu belgeye erişim yetkiniz yok.');
    }

    private function authorizeDocumentEdit(Document $document): void
    {
        $user = Auth::user();

        // Admin can edit all documents
        if ($user->hasRole('admin')) {
            return;
        }

        // Document uploader can edit their own documents
        if ($document->uploaded_by === $user->id && $document->status !== 'verified') {
            return;
        }

        // Project managers can edit documents from their projects
        if ($user->hasRole('project_manager') && $document->project_id) {
            return;
        }

        abort(403, 'Bu belgeyi düzenleme yetkiniz yok.');
    }

    private function canEditDocument(Document $document): bool
    {
        $user = Auth::user();

        if ($user->hasRole('admin')) {
            return true;
        }

        if ($document->uploaded_by === $user->id && $document->status !== 'verified') {
            return true;
        }

        if ($user->hasRole('project_manager') && $document->project_id) {
            return true;
        }

        return false;
    }

    private function canVerifyDocument(Document $document): bool
    {
        $user = Auth::user();

        if ($document->status !== 'pending') {
            return false;
        }

        if ($user->hasAnyRole(['admin', 'project_manager'])) {
            return true;
        }

        if ($user->hasRole('site_manager') && $document->project_id) {
            $project = Project::find($document->project_id);
            return $project && $project->site_manager_id === $user->employee->id;
        }

        return false;
    }

    private function canArchiveDocument(Document $document): bool
    {
        return Auth::user()->hasAnyRole(['admin', 'project_manager']);
    }

    private function getFilterOptions(User $user): array
    {
        $options = [
            'documentTypes' => $this->getDocumentTypes(),
            'categories' => $this->getCategories(),
            'statuses' => [
                ['value' => 'pending', 'label' => 'Beklemede'],
                ['value' => 'verified', 'label' => 'Doğrulandı'],
                ['value' => 'rejected', 'label' => 'Reddedildi'],
                ['value' => 'archived', 'label' => 'Arşivlendi'],
            ],
        ];

        // Get employees and projects based on user role
        if ($user->hasAnyRole(['admin', 'project_manager'])) {
            $options['employees'] = Employee::select('id', 'first_name', 'last_name', 'employee_code')->get();
            $options['projects'] = Project::select('id', 'name', 'project_code')->get();
        } else {
            $options['employees'] = collect([]);
            $options['projects'] = collect([]);
        }

        return $options;
    }

    private function getDocumentTypes(): array
    {
        return [
            ['value' => 'contract', 'label' => 'Sözleşme'],
            ['value' => 'certificate', 'label' => 'Sertifika'],
            ['value' => 'permit', 'label' => 'İzin/Ruhsat'],
            ['value' => 'report', 'label' => 'Rapor'],
            ['value' => 'invoice', 'label' => 'Fatura'],
            ['value' => 'other', 'label' => 'Diğer'],
        ];
    }

    private function getCategories(): array
    {
        return [
            'İK Belgeleri',
            'Proje Belgeleri',
            'Mali Belgeler',
            'Teknik Belgeler',
            'Yasal Belgeler',
            'Güvenlik Belgeleri',
            'Kalite Belgeleri',
            'Diğer',
        ];
    }

    private function getDocumentHistory(Document $document): array
    {
        $history = [];

        // Add creation event
        $history[] = [
            'action' => 'created',
            'user' => $document->uploader,
            'date' => $document->created_at,
            'details' => 'Belge yüklendi',
        ];

        // Add verification events
        if ($document->verified_at) {
            $history[] = [
                'action' => 'verified',
                'user' => $document->verifier,
                'date' => $document->verified_at,
                'details' => $document->verification_notes ?? 'Belge doğrulandı',
            ];
        }

        // Add rejection events
        if ($document->status === 'rejected') {
            $history[] = [
                'action' => 'rejected',
                'user' => $document->verifier,
                'date' => $document->verified_at,
                'details' => $document->rejection_reason ?? 'Belge reddedildi',
            ];
        }

        // Add archive events
        if ($document->archived_at) {
            $history[] = [
                'action' => 'archived',
                'user' => $document->archiver,
                'date' => $document->archived_at,
                'details' => $document->archive_reason ?? 'Belge arşivlendi',
            ];
        }

        return array_reverse($history);
    }
}