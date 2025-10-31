<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessDwgFile;
use App\Jobs\ApplyDwgImportMappings;
use App\Models\DwgImport;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class DwgImportController extends Controller
{
    /**
     * Display a listing of DWG imports
     */
    public function index(Request $request)
    {
        $query = DwgImport::with(['project', 'uploader'])
            ->orderBy('created_at', 'desc');

        // Filter by status if provided
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by project if provided
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        $imports = $query->paginate(15)->through(function ($import) {
            return [
                'id' => $import->id,
                'project' => [
                    'id' => $import->project->id,
                    'name' => $import->project->name,
                ],
                'uploader' => [
                    'id' => $import->uploader->id,
                    'name' => $import->uploader->name,
                ],
                'original_filename' => $import->original_filename,
                'file_size_human' => $import->file_size_human,
                'import_type' => $import->import_type,
                'import_type_label' => $import->import_type_label,
                'status' => $import->status,
                'status_label' => $import->status_label,
                'structures_count' => $import->structures_count,
                'floors_count' => $import->floors_count,
                'units_count' => $import->units_count,
                'total_created_count' => $import->total_created_count,
                'created_at' => $import->created_at->format('d.m.Y H:i'),
                'completed_at' => $import->completed_at?->format('d.m.Y H:i'),
                'error_message' => $import->error_message,
            ];
        });

        $projects = Project::select('id', 'name')->orderBy('name')->get();

        return Inertia::render('DwgImports/Index', [
            'imports' => $imports,
            'projects' => $projects,
            'filters' => $request->only(['status', 'project_id']),
        ]);
    }

    /**
     * Show the form for creating a new DWG import
     */
    public function create()
    {
        $projects = Project::select('id', 'name')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return Inertia::render('DwgImports/Create', [
            'projects' => $projects,
        ]);
    }

    /**
     * Store a newly uploaded DWG file and start processing
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'import_type' => 'required|in:comprehensive,structures_only,floors_only,units_only',
            'dwg_file' => 'required|file|mimes:dwg,dxf|max:51200', // Max 50MB
            'notes' => 'nullable|string|max:1000',
        ], [
            'project_id.required' => 'Proje seçimi zorunludur.',
            'import_type.required' => 'İçe aktarım tipi seçimi zorunludur.',
            'dwg_file.required' => 'DWG/DXF dosyası seçimi zorunludur.',
            'dwg_file.mimes' => 'Sadece DWG veya DXF dosyaları yüklenebilir.',
            'dwg_file.max' => 'Dosya boyutu maksimum 50MB olabilir.',
        ]);

        $file = $request->file('dwg_file');
        $originalFilename = $file->getClientOriginalName();
        $storedFilename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $filePath = $file->storeAs('dwg_imports', $storedFilename, 'local');

        // Create import record
        $import = DwgImport::create([
            'project_id' => $validated['project_id'],
            'uploaded_by' => Auth::id(),
            'original_filename' => $originalFilename,
            'stored_filename' => $storedFilename,
            'file_path' => $filePath,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'import_type' => $validated['import_type'],
            'status' => 'pending',
            'notes' => $validated['notes'] ?? null,
        ]);

        // Dispatch processing job
        ProcessDwgFile::dispatch($import);

        return redirect()->route('dwg-imports.show', $import->id)
            ->with('success', 'DWG dosyası yüklendi ve işleme alındı. Lütfen bekleyin...');
    }

    /**
     * Display the specified DWG import
     */
    public function show($id)
    {
        $import = DwgImport::with(['project', 'uploader'])->findOrFail($id);

        // Get existing structures/floors from project for mapping
        $projectStructures = [];
        $projectFloors = [];

        if ($import->project) {
            $projectStructures = $import->project->structures()
                ->select('id', 'project_id', 'name', 'description')
                ->orderBy('order')
                ->get();

            // Get all floors grouped by structure
            foreach ($projectStructures as $structure) {
                $projectFloors[$structure->id] = $structure->floors()
                    ->select('id', 'structure_id', 'name', 'floor_number')
                    ->orderBy('floor_number')
                    ->get();
            }
        }

        return Inertia::render('DwgImports/Show', [
            'import' => [
                'id' => $import->id,
                'project' => [
                    'id' => $import->project->id,
                    'name' => $import->project->name,
                ],
                'uploader' => [
                    'id' => $import->uploader->id,
                    'name' => $import->uploader->name,
                ],
                'original_filename' => $import->original_filename,
                'file_size_human' => $import->file_size_human,
                'import_type' => $import->import_type,
                'import_type_label' => $import->import_type_label,
                'status' => $import->status,
                'status_label' => $import->status_label,
                'parsed_data' => $import->parsed_data,
                'detected_layers' => $import->detected_layers,
                'layer_mappings' => $import->layer_mappings,
                'created_structures' => $import->created_structures,
                'structures_count' => $import->structures_count,
                'floors_count' => $import->floors_count,
                'units_count' => $import->units_count,
                'total_created_count' => $import->total_created_count,
                'error_message' => $import->error_message,
                'error_details' => $import->error_details,
                'notes' => $import->notes,
                'created_at' => $import->created_at->format('d.m.Y H:i'),
                'started_at' => $import->started_at?->format('d.m.Y H:i'),
                'completed_at' => $import->completed_at?->format('d.m.Y H:i'),
                'processing_duration_seconds' => $import->processing_duration_seconds,
                'is_ready_for_review' => $import->is_ready_for_review,
                'is_completed' => $import->is_completed,
                'is_failed' => $import->is_failed,
                'is_processing' => $import->is_processing,
            ],
            'project_structures' => $projectStructures,
            'project_floors' => $projectFloors,
        ]);
    }

    /**
     * Update layer mappings
     */
    public function updateMappings(Request $request, $id)
    {
        $import = DwgImport::findOrFail($id);

        // Only allow updating mappings if status is ready_for_review
        if ($import->status !== 'ready_for_review') {
            return back()->with('error', 'Bu import için mapping güncellemesi yapılamaz.');
        }

        $validated = $request->validate([
            'layer_mappings' => 'required|array',
        ]);

        $import->update([
            'layer_mappings' => $validated['layer_mappings'],
        ]);

        return back()->with('success', 'Layer eşleştirmeleri kaydedildi.');
    }

    /**
     * Approve and apply mappings to create records
     */
    public function approve(Request $request, $id)
    {
        $import = DwgImport::findOrFail($id);

        // Only allow approving if status is ready_for_review
        if ($import->status !== 'ready_for_review') {
            return back()->with('error', 'Bu import onaylanamaz. Durum: ' . $import->status_label);
        }

        // Save layer_mappings if provided in request
        if ($request->has('layer_mappings')) {
            $import->update([
                'layer_mappings' => $request->layer_mappings,
            ]);
        }

        // Validate that layer_mappings exist
        if (empty($import->layer_mappings)) {
            return back()->with('error', 'Lütfen önce layer eşleştirmelerini yapın.');
        }

        // Dispatch job to apply mappings
        ApplyDwgImportMappings::dispatch($import);

        return back()->with('success', 'Import onaylandı ve kayıtlar oluşturuluyor...');
    }

    /**
     * Remove the specified DWG import
     */
    public function destroy($id)
    {
        $import = DwgImport::findOrFail($id);

        // Don't allow deletion if currently processing
        if ($import->status === 'processing') {
            return back()->with('error', 'İşlenen import silinemez.');
        }

        // Delete the file from storage
        if (Storage::disk('local')->exists($import->file_path)) {
            Storage::disk('local')->delete($import->file_path);
        }

        $import->delete();

        return redirect()->route('dwg-imports.index')
            ->with('success', 'DWG import kaydı silindi.');
    }
}
