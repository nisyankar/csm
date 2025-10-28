<?php

namespace App\Http\Controllers;

use App\Models\Inspection;
use App\Models\InspectionCompany;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class InspectionController extends Controller
{
    /**
     * Dashboard - İstatistikler
     */
    public function dashboard(): Response
    {
        $totalInspections = Inspection::count();
        $scheduledInspections = Inspection::where('status', 'scheduled')->count();
        $pendingActions = Inspection::where('status', 'pending_action')->count();

        // Kritik uygunsuzluklar
        $criticalNonConformities = Inspection::whereNotNull('non_conformities')
            ->get()
            ->sum(fn($i) => $i->critical_non_conformities_count);

        // Yaklaşan denetimler (7 gün içinde)
        $upcomingInspections = Inspection::whereNotNull('next_inspection_date')
            ->whereDate('next_inspection_date', '>=', now())
            ->whereDate('next_inspection_date', '<=', now()->addDays(7))
            ->with(['project', 'inspectionCompany'])
            ->orderBy('next_inspection_date')
            ->take(5)
            ->get();

        // Gecikmiş denetimler
        $overdueInspections = Inspection::whereNotNull('next_inspection_date')
            ->whereDate('next_inspection_date', '<', now())
            ->where('status', '!=', 'closed')
            ->count();

        // Son denetimler
        $recentInspections = Inspection::with(['project', 'inspectionCompany'])
            ->orderBy('inspection_date', 'desc')
            ->take(5)
            ->get();

        // Denetim türü dağılımı
        $inspectionsByType = Inspection::selectRaw('inspection_type, count(*) as count')
            ->groupBy('inspection_type')
            ->get();

        return Inertia::render('Inspections/Dashboard', [
            'stats' => [
                'total' => $totalInspections,
                'scheduled' => $scheduledInspections,
                'pending_actions' => $pendingActions,
                'critical_non_conformities' => $criticalNonConformities,
                'overdue' => $overdueInspections,
            ],
            'upcomingInspections' => $upcomingInspections,
            'recentInspections' => $recentInspections,
            'inspectionsByType' => $inspectionsByType,
        ]);
    }

    /**
     * Denetim listesi
     */
    public function index(Request $request): Response
    {
        $inspections = Inspection::query()
            ->with(['project', 'inspectionCompany'])
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('inspection_number', 'like', "%{$search}%")
                        ->orWhere('inspector_name', 'like', "%{$search}%")
                        ->orWhereHas('project', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->project_id, function ($query, $projectId) {
                $query->where('project_id', $projectId);
            })
            ->when($request->inspection_company_id, function ($query, $companyId) {
                $query->where('inspection_company_id', $companyId);
            })
            ->when($request->inspection_type, function ($query, $type) {
                $query->where('inspection_type', $type);
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->date_from, function ($query, $dateFrom) {
                $query->whereDate('inspection_date', '>=', $dateFrom);
            })
            ->when($request->date_to, function ($query, $dateTo) {
                $query->whereDate('inspection_date', '<=', $dateTo);
            })
            ->orderBy('inspection_date', 'desc')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Inspections/Index', [
            'inspections' => $inspections,
            'filters' => $request->only(['search', 'project_id', 'inspection_company_id', 'inspection_type', 'status', 'date_from', 'date_to']),
            'projects' => Project::select('id', 'name', 'project_code')->orderBy('name')->get(),
            'inspectionCompanies' => InspectionCompany::where('is_active', true)->orderBy('company_name')->get(),
        ]);
    }

    /**
     * Denetim detayı
     */
    public function show(Inspection $inspection): Response
    {
        $inspection->load(['project', 'inspectionCompany']);

        return Inertia::render('Inspections/Show', [
            'inspection' => $inspection,
        ]);
    }

    /**
     * Yeni denetim formu
     */
    public function create(): Response
    {
        return Inertia::render('Inspections/Create', [
            'projects' => Project::select('id', 'name', 'project_code')->orderBy('name')->get(),
            'inspectionCompanies' => InspectionCompany::where('is_active', true)->orderBy('company_name')->get(),
        ]);
    }

    /**
     * Denetim kaydet
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'inspection_company_id' => 'required|exists:inspection_companies,id',
            'inspector_name' => 'required|string|max:255',
            'inspection_date' => 'required|date',
            'inspection_type' => 'required|in:periodic,special,final',
            'status' => 'required|in:scheduled,completed,pending_action,closed',
            'findings' => 'nullable|string',
            'next_inspection_date' => 'nullable|date|after:inspection_date',
            'notes' => 'nullable|string',
        ]);

        // Otomatik denetim numarası oluştur
        $validated['inspection_number'] = Inspection::generateInspectionNumber(
            $validated['project_id'],
            $validated['inspection_type']
        );

        $inspection = Inspection::create($validated);

        return redirect()->route('inspections.show', $inspection)
            ->with('success', 'Denetim başarıyla oluşturuldu.');
    }

    /**
     * Denetim düzenleme formu
     */
    public function edit(Inspection $inspection): Response
    {
        return Inertia::render('Inspections/Edit', [
            'inspection' => $inspection,
            'projects' => Project::select('id', 'name', 'project_code')->orderBy('name')->get(),
            'inspectionCompanies' => InspectionCompany::where('is_active', true)->orderBy('company_name')->get(),
        ]);
    }

    /**
     * Denetim güncelle
     */
    public function update(Request $request, Inspection $inspection): RedirectResponse
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'inspection_company_id' => 'required|exists:inspection_companies,id',
            'inspector_name' => 'required|string|max:255',
            'inspection_date' => 'required|date',
            'inspection_type' => 'required|in:periodic,special,final',
            'status' => 'required|in:scheduled,completed,pending_action,closed',
            'findings' => 'nullable|string',
            'next_inspection_date' => 'nullable|date|after:inspection_date',
            'notes' => 'nullable|string',
        ]);

        $inspection->update($validated);

        return redirect()->route('inspections.show', $inspection)
            ->with('success', 'Denetim başarıyla güncellendi.');
    }

    /**
     * Denetim sil
     */
    public function destroy(Inspection $inspection): RedirectResponse
    {
        // Dosyaları sil
        if ($inspection->report_path) {
            Storage::delete($inspection->report_path);
        }

        if (!empty($inspection->attachments)) {
            foreach ($inspection->attachments as $attachment) {
                Storage::delete($attachment['path'] ?? '');
            }
        }

        $inspection->delete();

        return redirect()->route('inspections.index')
            ->with('success', 'Denetim başarıyla silindi.');
    }

    /**
     * Rapor dosyası yükle
     */
    public function uploadReport(Request $request, Inspection $inspection): RedirectResponse
    {
        $request->validate([
            'report' => 'required|file|mimes:pdf|max:10240', // 10MB
        ]);

        // Eski raporu sil
        if ($inspection->report_path) {
            Storage::delete($inspection->report_path);
        }

        // Yeni raporu kaydet
        $path = $request->file('report')->store('inspection_reports', 'public');
        $inspection->update(['report_path' => $path]);

        return back()->with('success', 'Denetim raporu başarıyla yüklendi.');
    }

    /**
     * Rapor dosyası sil
     */
    public function deleteReport(Inspection $inspection): RedirectResponse
    {
        if ($inspection->report_path) {
            Storage::delete($inspection->report_path);
            $inspection->update(['report_path' => null]);
        }

        return back()->with('success', 'Denetim raporu başarıyla silindi.');
    }

    /**
     * Ek dosya yükle
     */
    public function uploadAttachment(Request $request, Inspection $inspection): RedirectResponse
    {
        $request->validate([
            'attachment' => 'required|file|max:10240', // 10MB
            'name' => 'nullable|string|max:255',
        ]);

        $file = $request->file('attachment');
        $path = $file->store('inspection_attachments', 'public');

        $attachments = $inspection->attachments ?? [];
        $attachments[] = [
            'name' => $request->name ?? $file->getClientOriginalName(),
            'path' => $path,
            'type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
            'uploaded_at' => now()->toDateTimeString(),
        ];

        $inspection->update(['attachments' => $attachments]);

        return back()->with('success', 'Ek dosya başarıyla yüklendi.');
    }

    /**
     * Ek dosya sil
     */
    public function deleteAttachment(Request $request, Inspection $inspection): RedirectResponse
    {
        $request->validate([
            'index' => 'required|integer|min:0',
        ]);

        $attachments = $inspection->attachments ?? [];
        $index = $request->index;

        if (isset($attachments[$index])) {
            // Dosyayı sil
            Storage::delete($attachments[$index]['path'] ?? '');

            // Array'den kaldır
            unset($attachments[$index]);
            $attachments = array_values($attachments); // Re-index

            $inspection->update(['attachments' => $attachments]);

            return back()->with('success', 'Ek dosya başarıyla silindi.');
        }

        return back()->with('error', 'Ek dosya bulunamadı.');
    }

    /**
     * Uygunsuzluk ekle/güncelle
     */
    public function updateNonConformities(Request $request, Inspection $inspection): JsonResponse
    {
        $validated = $request->validate([
            'non_conformities' => 'required|array',
            'non_conformities.*.description' => 'required|string',
            'non_conformities.*.severity' => 'required|in:minor,major,critical',
            'non_conformities.*.deadline' => 'nullable|date',
            'non_conformities.*.photo' => 'nullable|string',
        ]);

        $inspection->update([
            'non_conformities' => $validated['non_conformities'],
            'status' => 'pending_action', // Uygunsuzluk varsa durum değiştir
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Uygunsuzluklar başarıyla güncellendi.',
        ]);
    }

    /**
     * Düzeltici faaliyet ekle/güncelle
     */
    public function updateCorrectiveActions(Request $request, Inspection $inspection): JsonResponse
    {
        $validated = $request->validate([
            'corrective_actions' => 'required|array',
            'corrective_actions.*.action' => 'required|string',
            'corrective_actions.*.responsible' => 'required|string',
            'corrective_actions.*.deadline' => 'nullable|date',
            'corrective_actions.*.status' => 'required|in:pending,in_progress,completed',
            'corrective_actions.*.completion_date' => 'nullable|date',
        ]);

        $inspection->update([
            'corrective_actions' => $validated['corrective_actions'],
        ]);

        // Tüm faaliyetler tamamlandıysa durumu güncelle
        $allCompleted = collect($validated['corrective_actions'])
            ->every(fn($action) => $action['status'] === 'completed');

        if ($allCompleted) {
            $inspection->update(['status' => 'completed']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Düzeltici faaliyetler başarıyla güncellendi.',
        ]);
    }
}
