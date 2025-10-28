<?php

namespace App\Http\Controllers;

use App\Models\ConstructionPermit;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ConstructionPermitController extends Controller
{
    /**
     * Display dashboard
     */
    public function dashboard()
    {
        $stats = [
            'total' => ConstructionPermit::count(),
            'pending' => ConstructionPermit::where('status', 'pending')->count(),
            'approved' => ConstructionPermit::where('status', 'approved')->count(),
            'expired' => ConstructionPermit::expired()->count(),
            'expiring_soon' => ConstructionPermit::expiringSoon(30)->count(),
        ];

        // Permit type breakdown
        $permitTypes = ConstructionPermit::selectRaw('permit_type, COUNT(*) as count')
            ->groupBy('permit_type')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->permit_type => $item->count];
            });

        // Expiring soon permits
        $expiringSoon = ConstructionPermit::with('project:id,name,project_code')
            ->expiringSoon(30)
            ->take(10)
            ->get()
            ->map(function ($permit) {
                return [
                    'id' => $permit->id,
                    'permit_number' => $permit->permit_number,
                    'permit_type' => $permit->permit_type,
                    'permit_type_label' => $permit->permit_type_label,
                    'project' => $permit->project,
                    'expiry_date' => $permit->expiry_date,
                    'days_until_expiry' => $permit->days_until_expiry,
                    'status_badge' => $permit->status_badge,
                ];
            });

        // Recent permits
        $recentPermits = ConstructionPermit::with('project:id,name,project_code')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->map(function ($permit) {
                return [
                    'id' => $permit->id,
                    'permit_number' => $permit->permit_number,
                    'permit_type_label' => $permit->permit_type_label,
                    'project' => $permit->project,
                    'status_badge' => $permit->status_badge,
                    'issuing_authority' => $permit->issuing_authority,
                    'approval_date' => $permit->approval_date,
                    'created_at' => $permit->created_at,
                ];
            });

        return Inertia::render('ConstructionPermits/Dashboard', [
            'stats' => $stats,
            'permitTypes' => $permitTypes,
            'expiringSoon' => $expiringSoon,
            'recentPermits' => $recentPermits,
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ConstructionPermit::with(['project:id,name,project_code', 'projectUnit:id,unit_code,unit_type']);

        // Filters
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('project_unit_id')) {
            $query->where('project_unit_id', $request->project_unit_id);
        }

        if ($request->filled('permit_type')) {
            $query->where('permit_type', $request->permit_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('issuing_authority')) {
            $query->where('issuing_authority', 'like', '%' . $request->issuing_authority . '%');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('permit_number', 'like', "%{$search}%")
                    ->orWhere('issuing_authority', 'like', "%{$search}%")
                    ->orWhere('zoning_status', 'like', "%{$search}%");
            });
        }

        // Date filters
        if ($request->filled('expiry_date_from')) {
            $query->where('expiry_date', '>=', $request->expiry_date_from);
        }

        if ($request->filled('expiry_date_to')) {
            $query->where('expiry_date', '<=', $request->expiry_date_to);
        }

        // Special filters
        if ($request->filled('filter')) {
            switch ($request->filter) {
                case 'expiring_soon':
                    $query->expiringSoon(30);
                    break;
                case 'expired':
                    $query->expired();
                    break;
                case 'active':
                    $query->active();
                    break;
            }
        }

        // Sort
        $sortField = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $permits = $query->paginate(15)->withQueryString();

        // Transform data
        $permits->getCollection()->transform(function ($permit) {
            return [
                'id' => $permit->id,
                'permit_number' => $permit->permit_number,
                'permit_type' => $permit->permit_type,
                'permit_type_label' => $permit->permit_type_label,
                'project' => $permit->project,
                'project_unit' => $permit->projectUnit,
                'status' => $permit->status,
                'status_badge' => $permit->status_badge,
                'issuing_authority' => $permit->issuing_authority,
                'application_date' => $permit->application_date,
                'approval_date' => $permit->approval_date,
                'expiry_date' => $permit->expiry_date,
                'is_expiring_soon' => $permit->is_expiring_soon,
                'is_expired' => $permit->is_expired,
                'days_until_expiry' => $permit->days_until_expiry,
                'documents_count' => is_array($permit->documents) ? count($permit->documents) : 0,
                'created_at' => $permit->created_at,
            ];
        });

        // Projects for filter
        $projects = Project::select('id', 'name', 'project_code')
            ->orderBy('name')
            ->get();

        return Inertia::render('ConstructionPermits/Index', [
            'permits' => $permits,
            'projects' => $projects,
            'filters' => $request->only([
                'project_id',
                'permit_type',
                'status',
                'issuing_authority',
                'search',
                'expiry_date_from',
                'expiry_date_to',
                'filter',
                'sort_by',
                'sort_direction'
            ]),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $projects = Project::select('id', 'name', 'project_code')
            ->with(['structures.floors.units' => function ($query) {
                $query->select('id', 'floor_id', 'unit_code', 'unit_type', 'gross_area')
                    ->orderBy('unit_code');
            }])
            ->orderBy('name')
            ->get()
            ->map(function ($project) {
                // Flatten units for easier access
                $project->units = $project->structures
                    ->flatMap(fn($s) => $s->floors->flatMap(fn($f) => $f->units))
                    ->values();
                unset($project->structures); // Remove to reduce data size
                return $project;
            });

        return Inertia::render('ConstructionPermits/Create', [
            'projects' => $projects,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'project_unit_id' => 'nullable|exists:project_units,id',
            'permit_type' => 'required|in:building,demolition,occupancy,usage',
            'permit_number' => 'nullable|string|max:255|unique:construction_permits,permit_number',
            'application_date' => 'nullable|date',
            'approval_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'status' => 'required|in:pending,approved,rejected,expired,renewed',
            'issuing_authority' => 'nullable|string|max:255',
            'zoning_status' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        // Auto-generate permit number if not provided
        if (empty($validated['permit_number'])) {
            $project = Project::findOrFail($validated['project_id']);
            $validated['permit_number'] = ConstructionPermit::generatePermitNumber(
                $project->project_code,
                $validated['permit_type']
            );
        }

        $validated['created_by'] = auth()->id();
        $validated['updated_by'] = auth()->id();

        $permit = ConstructionPermit::create($validated);

        return redirect()
            ->route('construction-permits.show', $permit->id)
            ->with('success', 'Ruhsat başarıyla oluşturuldu.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ConstructionPermit $constructionPermit)
    {
        $constructionPermit->load([
            'project:id,name,project_code,location,city',
            'projectUnit:id,unit_code,unit_type,gross_area',
            'creator:id,name',
            'updater:id,name'
        ]);

        return Inertia::render('ConstructionPermits/Show', [
            'permit' => [
                'id' => $constructionPermit->id,
                'permit_number' => $constructionPermit->permit_number,
                'permit_type' => $constructionPermit->permit_type,
                'permit_type_label' => $constructionPermit->permit_type_label,
                'project' => $constructionPermit->project,
                'project_unit' => $constructionPermit->projectUnit,
                'status' => $constructionPermit->status,
                'status_badge' => $constructionPermit->status_badge,
                'application_date' => $constructionPermit->application_date,
                'approval_date' => $constructionPermit->approval_date,
                'expiry_date' => $constructionPermit->expiry_date,
                'issuing_authority' => $constructionPermit->issuing_authority,
                'zoning_status' => $constructionPermit->zoning_status,
                'documents' => $constructionPermit->documents,
                'notes' => $constructionPermit->notes,
                'is_expiring_soon' => $constructionPermit->is_expiring_soon,
                'is_expired' => $constructionPermit->is_expired,
                'days_until_expiry' => $constructionPermit->days_until_expiry,
                'creator' => $constructionPermit->creator,
                'updater' => $constructionPermit->updater,
                'created_at' => $constructionPermit->created_at,
                'updated_at' => $constructionPermit->updated_at,
            ],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ConstructionPermit $constructionPermit)
    {
        $constructionPermit->load('project:id,name,project_code');

        $projects = Project::select('id', 'name', 'project_code')
            ->orderBy('name')
            ->get();

        return Inertia::render('ConstructionPermits/Edit', [
            'permit' => [
                'id' => $constructionPermit->id,
                'project_id' => $constructionPermit->project_id,
                'project_unit_id' => $constructionPermit->project_unit_id,
                'permit_type' => $constructionPermit->permit_type,
                'permit_number' => $constructionPermit->permit_number,
                'application_date' => $constructionPermit->application_date,
                'approval_date' => $constructionPermit->approval_date,
                'expiry_date' => $constructionPermit->expiry_date,
                'status' => $constructionPermit->status,
                'issuing_authority' => $constructionPermit->issuing_authority,
                'zoning_status' => $constructionPermit->zoning_status,
                'notes' => $constructionPermit->notes,
                'project' => $constructionPermit->project,
            ],
            'projects' => $projects,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ConstructionPermit $constructionPermit)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'project_unit_id' => 'nullable|exists:project_units,id',
            'permit_type' => 'required|in:building,demolition,occupancy,usage',
            'permit_number' => 'required|string|max:255|unique:construction_permits,permit_number,' . $constructionPermit->id,
            'application_date' => 'nullable|date',
            'approval_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'status' => 'required|in:pending,approved,rejected,expired,renewed',
            'issuing_authority' => 'nullable|string|max:255',
            'zoning_status' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $validated['updated_by'] = auth()->id();

        $constructionPermit->update($validated);

        return redirect()
            ->route('construction-permits.show', $constructionPermit->id)
            ->with('success', 'Ruhsat başarıyla güncellendi.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ConstructionPermit $constructionPermit)
    {
        // Delete associated documents
        if (is_array($constructionPermit->documents)) {
            foreach ($constructionPermit->documents as $document) {
                if (isset($document['path']) && Storage::disk('public')->exists($document['path'])) {
                    Storage::disk('public')->delete($document['path']);
                }
            }
        }

        $constructionPermit->delete();

        return redirect()
            ->route('construction-permits.index')
            ->with('success', 'Ruhsat başarıyla silindi.');
    }

    /**
     * Upload document for permit
     */
    public function uploadDocument(Request $request, ConstructionPermit $constructionPermit)
    {
        $request->validate([
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240', // 10MB max
            'document_name' => 'nullable|string|max:255',
        ]);

        $file = $request->file('document');
        $documentName = $request->input('document_name', $file->getClientOriginalName());

        // Store file
        $path = $file->store('permit_documents', 'public');

        // Add to documents array
        $documents = $constructionPermit->documents ?? [];
        $documents[] = [
            'name' => $documentName,
            'original_name' => $file->getClientOriginalName(),
            'path' => $path,
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'uploaded_at' => now()->toDateTimeString(),
            'uploaded_by' => auth()->id(),
        ];

        $constructionPermit->update([
            'documents' => $documents,
            'updated_by' => auth()->id(),
        ]);

        return redirect()
            ->back()
            ->with('success', 'Belge başarıyla yüklendi.');
    }

    /**
     * Delete document from permit
     */
    public function deleteDocument(ConstructionPermit $constructionPermit, $index)
    {
        $documents = $constructionPermit->documents ?? [];

        if (isset($documents[$index])) {
            // Delete file from storage
            if (isset($documents[$index]['path']) && Storage::disk('public')->exists($documents[$index]['path'])) {
                Storage::disk('public')->delete($documents[$index]['path']);
            }

            // Remove from array
            unset($documents[$index]);
            $documents = array_values($documents); // Re-index array

            $constructionPermit->update([
                'documents' => $documents,
                'updated_by' => auth()->id(),
            ]);

            return redirect()
                ->back()
                ->with('success', 'Belge başarıyla silindi.');
        }

        return redirect()
            ->back()
            ->with('error', 'Belge bulunamadı.');
    }
}
