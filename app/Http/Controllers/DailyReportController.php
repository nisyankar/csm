<?php

namespace App\Http\Controllers;

use App\Models\DailyReport;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class DailyReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $query = DailyReport::with(['project', 'reporter', 'approver'])
            ->orderBy('report_date', 'desc');

        // Proje filtresi
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        // Durum filtresi
        if ($request->filled('status')) {
            $query->where('approval_status', $request->status);
        }

        // Tarih aralığı filtresi
        if ($request->filled('start_date')) {
            $query->where('report_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('report_date', '<=', $request->end_date);
        }

        $reports = $query->paginate(20);
        $projects = Project::orderBy('name')->get();

        return Inertia::render('DailyReports/Index', [
            'reports' => $reports,
            'projects' => $projects,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): Response
    {
        $projects = Project::where('status', 'active')->orderBy('name')->get();
        $selectedProjectId = $request->get('project_id');

        return Inertia::render('DailyReports/Create', [
            'projects' => $projects,
            'selectedProjectId' => $selectedProjectId,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        // Debug için request verisini loglayalım
        Log::info('Daily Report Store Request', [
            'all_data' => $request->all(),
            'has_photos' => $request->hasFile('photos'),
        ]);

        try {
            $validated = $request->validate([
                'project_id' => 'required|exists:projects,id',
                'report_date' => 'required|date',
                'weather_condition' => 'required|in:sunny,cloudy,rainy,snowy,windy,stormy',
                'temperature' => 'nullable|numeric|between:-50,60',
                'weather_notes' => 'nullable|string|max:500',
                'total_workers' => 'nullable|integer|min:0',
                'subcontractor_workers' => 'nullable|integer|min:0',
                'internal_workers' => 'nullable|integer|min:0',
                'work_summary' => 'required|string',
                'completed_works' => 'nullable|array',
                'completed_works.*' => 'nullable|string|max:500',
                'ongoing_works' => 'nullable|array',
                'ongoing_works.*' => 'nullable|string|max:500',
                'planned_works' => 'nullable|array',
                'planned_works.*' => 'nullable|string|max:500',
                'has_delays' => 'nullable|boolean',
                'delay_reasons' => 'nullable|array',
                'delay_reasons.*' => 'nullable|string|max:500',
                'has_accidents' => 'nullable|boolean',
                'accident_details' => 'nullable|array',
                'accident_details.*' => 'nullable|string|max:500',
                'has_material_shortage' => 'nullable|boolean',
                'material_shortage_details' => 'nullable|array',
                'material_shortage_details.*' => 'nullable|string|max:500',
                'visitors' => 'nullable|array',
                'visitors.*' => 'nullable|string|max:200',
                'equipment_usage' => 'nullable|array',
                'equipment_usage.*.name' => 'nullable|string|max:200',
                'equipment_usage.*.hours' => 'nullable|numeric|min:0',
                'photos' => 'nullable|array',
                'photos.*' => 'nullable|image|max:5120', // 5MB max
                'notes' => 'nullable|string',
                'should_submit' => 'nullable|boolean', // Gönder butonu için
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Daily Report Validation Error', [
                'errors' => $e->errors(),
                'request_data' => $request->except('photos')
            ]);
            throw $e;
        }

        // Boolean değerleri düzelt
        $validated['has_delays'] = $request->boolean('has_delays');
        $validated['has_accidents'] = $request->boolean('has_accidents');
        $validated['has_material_shortage'] = $request->boolean('has_material_shortage');

        // Fotoğraf yükleme
        $photosPaths = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('daily-reports', 'public');
                $photosPaths[] = $path;
            }
        }
        $validated['photos'] = $photosPaths;

        // Rapor oluşturan kişi
        $validated['reported_by'] = Auth::id();

        // Durum belirleme - "Gönder" butonu kullanıldıysa submitted, yoksa draft
        $validated['approval_status'] = $request->boolean('should_submit') ? 'submitted' : 'draft';

        $report = DailyReport::create($validated);

        $message = $request->boolean('should_submit')
            ? 'Günlük rapor başarıyla oluşturuldu ve gönderildi.'
            : 'Günlük rapor taslak olarak kaydedildi.';

        return redirect()
            ->route('daily-reports.show', $report)
            ->with('success', $message);
    }

    /**
     * Display the specified resource.
     */
    public function show(DailyReport $dailyReport): Response
    {
        $dailyReport->load(['project', 'reporter', 'approver']);

        return Inertia::render('DailyReports/Show', [
            'dailyReport' => $dailyReport,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DailyReport $dailyReport): Response|RedirectResponse
    {
        // Sadece taslak ve reddedilen raporlar düzenlenebilir
        if (!$dailyReport->canEdit()) {
            return redirect()
                ->route('daily-reports.show', $dailyReport)
                ->with('error', 'Bu rapor düzenlenemez.');
        }

        $projects = Project::where('status', 'active')->orderBy('name')->get();

        return Inertia::render('DailyReports/Edit', [
            'dailyReport' => $dailyReport,
            'projects' => $projects,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DailyReport $dailyReport): RedirectResponse
    {
        // Sadece taslak ve reddedilen raporlar düzenlenebilir
        if (!$dailyReport->canEdit()) {
            return redirect()
                ->route('daily-reports.show', $dailyReport)
                ->with('error', 'Bu rapor düzenlenemez.');
        }

        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'report_date' => 'required|date',
            'weather_condition' => 'required|in:sunny,cloudy,rainy,snowy,windy,stormy',
            'temperature' => 'nullable|numeric|between:-50,60',
            'weather_notes' => 'nullable|string|max:500',
            'total_workers' => 'required|integer|min:0',
            'subcontractor_workers' => 'required|integer|min:0',
            'internal_workers' => 'required|integer|min:0',
            'work_summary' => 'required|string',
            'completed_works' => 'nullable|array',
            'completed_works.*' => 'nullable|string|max:500',
            'ongoing_works' => 'nullable|array',
            'ongoing_works.*' => 'nullable|string|max:500',
            'planned_works' => 'nullable|array',
            'planned_works.*' => 'nullable|string|max:500',
            'has_delays' => 'nullable|boolean',
            'delay_reasons' => 'nullable|array',
            'delay_reasons.*' => 'nullable|string|max:500',
            'has_accidents' => 'nullable|boolean',
            'accident_details' => 'nullable|array',
            'accident_details.*' => 'nullable|string|max:500',
            'has_material_shortage' => 'nullable|boolean',
            'material_shortage_details' => 'nullable|array',
            'material_shortage_details.*' => 'nullable|string|max:500',
            'visitors' => 'nullable|array',
            'visitors.*' => 'nullable|string|max:200',
            'equipment_usage' => 'nullable|array',
            'equipment_usage.*.name' => 'nullable|string|max:200',
            'equipment_usage.*.hours' => 'nullable|numeric|min:0',
            'new_photos' => 'nullable|array',
            'new_photos.*' => 'nullable|image|max:5120',
            'remove_photos' => 'nullable|array',
            'notes' => 'nullable|string',
            'should_submit' => 'nullable|boolean',
        ]);

        // Boolean değerleri düzelt
        $validated['has_delays'] = $request->boolean('has_delays');
        $validated['has_accidents'] = $request->boolean('has_accidents');
        $validated['has_material_shortage'] = $request->boolean('has_material_shortage');

        // Mevcut fotoğraflardan silinecekleri çıkar
        $photos = $dailyReport->photos ?? [];
        if ($request->has('remove_photos')) {
            foreach ($request->remove_photos as $photoToRemove) {
                // Dosyayı sil
                Storage::disk('public')->delete($photoToRemove);
                // Arrayden çıkar
                $photos = array_diff($photos, [$photoToRemove]);
            }
        }

        // Yeni fotoğrafları ekle
        if ($request->hasFile('new_photos')) {
            foreach ($request->file('new_photos') as $photo) {
                $path = $photo->store('daily-reports', 'public');
                $photos[] = $path;
            }
        }
        $validated['photos'] = array_values($photos);

        // Durum güncelleme - "Gönder" butonu kullanıldıysa submitted yap
        if ($request->boolean('should_submit')) {
            $validated['approval_status'] = 'submitted';
        }

        $dailyReport->update($validated);

        $message = $request->boolean('should_submit')
            ? 'Günlük rapor güncellendi ve gönderildi.'
            : 'Günlük rapor güncellendi.';

        return redirect()
            ->route('daily-reports.show', $dailyReport)
            ->with('success', $message);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DailyReport $dailyReport): RedirectResponse
    {
        // Sadece taslak raporlar silinebilir
        if ($dailyReport->approval_status !== 'draft') {
            return redirect()
                ->route('daily-reports.index')
                ->with('error', 'Sadece taslak raporlar silinebilir.');
        }

        // Fotoğrafları sil
        if ($dailyReport->photos) {
            foreach ($dailyReport->photos as $photo) {
                Storage::disk('public')->delete($photo);
            }
        }

        $dailyReport->delete();

        return redirect()
            ->route('daily-reports.index')
            ->with('success', 'Günlük rapor silindi.');
    }

    /**
     * Raporu gönder (submit)
     */
    public function submit(DailyReport $dailyReport): RedirectResponse
    {
        if (!$dailyReport->canSubmit()) {
            return redirect()
                ->route('daily-reports.show', $dailyReport)
                ->with('error', 'Bu rapor gönderilemez.');
        }

        $dailyReport->submit();

        return redirect()
            ->route('daily-reports.show', $dailyReport)
            ->with('success', 'Rapor başarıyla gönderildi.');
    }

    /**
     * Raporu onayla (approve)
     */
    public function approve(DailyReport $dailyReport): RedirectResponse
    {
        if (!$dailyReport->canApprove()) {
            return redirect()
                ->route('daily-reports.show', $dailyReport)
                ->with('error', 'Bu rapor onaylanamaz.');
        }

        $dailyReport->approve(Auth::id());

        return redirect()
            ->route('daily-reports.show', $dailyReport)
            ->with('success', 'Rapor başarıyla onaylandı.');
    }

    /**
     * Raporu reddet (reject)
     */
    public function reject(Request $request, DailyReport $dailyReport): RedirectResponse
    {
        if (!$dailyReport->canApprove()) {
            return redirect()
                ->route('daily-reports.show', $dailyReport)
                ->with('error', 'Bu rapor reddedilemez.');
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $dailyReport->reject(Auth::id(), $validated['rejection_reason']);

        return redirect()
            ->route('daily-reports.show', $dailyReport)
            ->with('success', 'Rapor reddedildi.');
    }
}
