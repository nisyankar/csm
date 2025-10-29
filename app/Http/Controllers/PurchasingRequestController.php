<?php

namespace App\Http\Controllers;

use App\Models\PurchasingRequest;
use App\Models\Project;
use App\Models\Department;
use App\Models\Warehouse;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class PurchasingRequestController extends Controller
{
    /**
     * Satınalma taleplerini listele
     */
    public function index(Request $request): Response
    {
        $query = PurchasingRequest::with([
            'requestedBy:id,name,email',
            'project:id,name',
            'department:id,name',
            'items',
            'supervisorApproval:id,name',
            'managerApproval:id,name'
        ]);

        // Filtreleme
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        if ($request->has('urgency') && $request->urgency !== '') {
            $query->where('urgency', $request->urgency);
        }

        if ($request->has('category') && $request->category !== '') {
            $query->where('category', $request->category);
        }

        if ($request->has('project_id') && $request->project_id !== '') {
            $query->where('project_id', $request->project_id);
        }

        if ($request->has('department_id') && $request->department_id !== '') {
            $query->where('department_id', $request->department_id);
        }

        // Arama
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('request_code', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sıralama
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $requests = $query->paginate(15)->withQueryString();

        return Inertia::render('PurchasingRequests/Index', [
            'requests' => $requests,
            'filters' => $request->only(['status', 'urgency', 'category', 'project_id', 'department_id', 'search']),
            'projects' => Project::select('id', 'name')->get(),
            'departments' => Department::select('id', 'name')->get(),
        ]);
    }

    /**
     * Yeni talep oluşturma formu
     */
    public function create(): Response
    {
        return Inertia::render('PurchasingRequests/Create', [
            'projects' => Project::select('id', 'name')->get(),
            'departments' => Department::select('id', 'name')->get(),
            'materials' => \App\Models\Material::active()->orderBy('name')->get(),
        ]);
    }

    /**
     * Yeni satınalma talebi kaydet
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'project_id' => 'required|exists:projects,id',
            'department_id' => 'nullable|exists:departments,id',
            'urgency' => 'required|in:low,normal,high,urgent',
            'category' => 'required|in:concrete,steel,general,equipment,service,other',
            'required_date' => 'required|date|after:today',
            'items' => 'required|array|min:1',
            'items.*.material_id' => 'nullable|exists:materials,id',
            'items.*.item_name' => 'required|string|max:255',
            'items.*.description' => 'nullable|string',
            'items.*.specification' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit' => 'required|string|max:50',
            'items.*.estimated_unit_price' => 'required|numeric|min:0',
            'items.*.category' => 'nullable|string',
            'items.*.required_date' => 'nullable|date',
            'items.*.delivery_location' => 'nullable|string',
            'items.*.notes' => 'nullable|string',
            'items.*.priority' => 'nullable|integer|min:1|max:5',
            // Beton özel alanları
            'items.*.concrete_class' => 'nullable|string',
            'items.*.concrete_slump' => 'nullable|string',
            'items.*.concrete_aggregate_size' => 'nullable|string',
            // Demir özel alanları
            'items.*.steel_diameter' => 'nullable|string',
            'items.*.steel_quality' => 'nullable|string',
            'items.*.steel_length' => 'nullable|numeric',
        ]);

        DB::beginTransaction();
        try {
            // Talep oluştur
            $purchasingRequest = PurchasingRequest::create([
                'request_code' => PurchasingRequest::generateRequestCode(),
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'requested_by' => Auth::id(),
                'project_id' => $validated['project_id'],
                'department_id' => $validated['department_id'] ?? null,
                'status' => 'draft',
                'urgency' => $validated['urgency'],
                'category' => $validated['category'],
                'required_date' => $validated['required_date'],
            ]);

            // Kalemleri oluştur
            foreach ($validated['items'] as $item) {
                $purchasingRequest->items()->create($item);
            }

            // Tahmini toplamı hesapla ve güncelle
            $estimatedTotal = $purchasingRequest->calculateEstimatedTotal();
            $purchasingRequest->update(['estimated_total' => $estimatedTotal]);

            DB::commit();

            return redirect()
                ->route('purchasing-requests.show', $purchasingRequest)
                ->with('success', 'Satınalma talebi başarıyla oluşturuldu.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Satınalma talebi oluşturulamadı: ' . $e->getMessage());
        }
    }

    /**
     * Satınalma talebini görüntüle
     */
    public function show(PurchasingRequest $purchasingRequest): Response
    {
        $purchasingRequest->load([
            'requestedBy:id,name,email',
            'project:id,name',
            'department:id,name',
            'items',
            'supervisorApproval:id,name',
            'managerApproval:id,name'
        ]);

        return Inertia::render('PurchasingRequests/Show', [
            'purchasingRequest' => $purchasingRequest,
        ]);
    }

    /**
     * Talep düzenleme formu
     */
    public function edit(PurchasingRequest $purchasingRequest): Response|RedirectResponse
    {
        // Sadece draft durumundaki talepler düzenlenebilir
        if ($purchasingRequest->status !== 'draft') {
            return redirect()
                ->route('purchasing-requests.show', $purchasingRequest)
                ->with('error', 'Sadece taslak durumundaki talepler düzenlenebilir.');
        }

        $purchasingRequest->load(['items.material']);

        return Inertia::render('PurchasingRequests/Edit', [
            'purchasingRequest' => $purchasingRequest,
            'projects' => Project::select('id', 'name')->get(),
            'departments' => Department::select('id', 'name')->get(),
            'materials' => \App\Models\Material::active()->orderBy('name')->get(),
        ]);
    }

    /**
     * Satınalma talebini güncelle
     */
    public function update(Request $request, PurchasingRequest $purchasingRequest): RedirectResponse
    {
        // Sadece draft durumundaki talepler güncellenebilir
        if ($purchasingRequest->status !== 'draft') {
            return redirect()
                ->route('purchasing-requests.show', $purchasingRequest)
                ->with('error', 'Sadece taslak durumundaki talepler güncellenebilir.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'project_id' => 'required|exists:projects,id',
            'department_id' => 'nullable|exists:departments,id',
            'urgency' => 'required|in:low,normal,high,urgent',
            'category' => 'required|in:concrete,steel,general,equipment,service,other',
            'required_date' => 'required|date|after:today',
            'items' => 'required|array|min:1',
            'items.*.material_id' => 'nullable|exists:materials,id',
            'items.*.item_name' => 'required|string|max:255',
            'items.*.description' => 'nullable|string',
            'items.*.specification' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit' => 'required|string|max:50',
            'items.*.estimated_unit_price' => 'required|numeric|min:0',
            'items.*.category' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $purchasingRequest->update([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'project_id' => $validated['project_id'],
                'department_id' => $validated['department_id'] ?? null,
                'urgency' => $validated['urgency'],
                'category' => $validated['category'],
                'required_date' => $validated['required_date'],
            ]);

            // Mevcut items'ı sil ve yenilerini ekle
            $purchasingRequest->items()->delete();
            foreach ($validated['items'] as $item) {
                $purchasingRequest->items()->create($item);
            }

            // Tahmini toplamı yeniden hesapla
            $estimatedTotal = $purchasingRequest->calculateEstimatedTotal();
            $purchasingRequest->update(['estimated_total' => $estimatedTotal]);

            DB::commit();

            return redirect()
                ->route('purchasing-requests.show', $purchasingRequest)
                ->with('success', 'Satınalma talebi başarıyla güncellendi.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Satınalma talebi güncellenemedi: ' . $e->getMessage());
        }
    }

    /**
     * Satınalma talebini sil
     */
    public function destroy(PurchasingRequest $purchasingRequest): RedirectResponse
    {
        // Sadece draft veya rejected durumundaki talepler silinebilir
        if (!in_array($purchasingRequest->status, ['draft', 'rejected'])) {
            return redirect()
                ->back()
                ->with('error', 'Sadece taslak veya reddedilmiş talepler silinebilir.');
        }

        try {
            $purchasingRequest->delete();

            return redirect()
                ->route('purchasing-requests.index')
                ->with('success', 'Satınalma talebi başarıyla silindi.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Satınalma talebi silinemedi: ' . $e->getMessage());
        }
    }

    /**
     * Talebi onaya gönder
     */
    public function submit(PurchasingRequest $purchasingRequest): RedirectResponse
    {
        if ($purchasingRequest->status !== 'draft') {
            return redirect()
                ->back()
                ->with('error', 'Sadece taslak durumundaki talepler onaya gönderilebilir.');
        }

        if ($purchasingRequest->items()->count() === 0) {
            return redirect()
                ->back()
                ->with('error', 'Talebin en az bir kalemi olmalıdır.');
        }

        try {
            $purchasingRequest->submit();

            return redirect()
                ->route('purchasing-requests.show', $purchasingRequest)
                ->with('success', 'Talep başarıyla onaya gönderildi.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Talep onaya gönderilemedi: ' . $e->getMessage());
        }
    }

    /**
     * Şef onayı
     */
    public function approveBySupervisor(Request $request, PurchasingRequest $purchasingRequest): RedirectResponse
    {
        if ($purchasingRequest->status !== 'pending') {
            return redirect()
                ->back()
                ->with('error', 'Sadece beklemedeki talepler onaylanabilir.');
        }

        $validated = $request->validate([
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            $purchasingRequest->approveBySupervisor(Auth::id(), $validated['notes'] ?? null);

            return redirect()
                ->route('purchasing-requests.show', $purchasingRequest)
                ->with('success', 'Talep şef tarafından onaylandı.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Talep onaylanamadı: ' . $e->getMessage());
        }
    }

    /**
     * Yönetici onayı
     */
    public function approveByManager(Request $request, PurchasingRequest $purchasingRequest): RedirectResponse
    {
        if ($purchasingRequest->status !== 'pending') {
            return redirect()
                ->back()
                ->with('error', 'Sadece beklemedeki talepler onaylanabilir.');
        }

        $validated = $request->validate([
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            $purchasingRequest->approveByManager(Auth::id(), $validated['notes'] ?? null);

            return redirect()
                ->route('purchasing-requests.show', $purchasingRequest)
                ->with('success', 'Talep yönetici tarafından onaylandı.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Talep onaylanamadı: ' . $e->getMessage());
        }
    }

    /**
     * Talebi reddet
     */
    public function reject(Request $request, PurchasingRequest $purchasingRequest): RedirectResponse
    {
        if ($purchasingRequest->status !== 'pending') {
            return redirect()
                ->back()
                ->with('error', 'Sadece beklemedeki talepler reddedilebilir.');
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500'
        ]);

        try {
            $purchasingRequest->reject($validated['rejection_reason']);

            return redirect()
                ->route('purchasing-requests.show', $purchasingRequest)
                ->with('success', 'Talep reddedildi.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Talep reddedilemedi: ' . $e->getMessage());
        }
    }

    /**
     * Talebi iptal et
     */
    public function cancel(PurchasingRequest $purchasingRequest): RedirectResponse
    {
        // İptal edilebilecek durumlar
        $cancellableStatuses = ['draft', 'pending', 'approved'];

        if (!in_array($purchasingRequest->status, $cancellableStatuses)) {
            return redirect()
                ->back()
                ->with('error', 'Bu durumdaki talep iptal edilemez.');
        }

        try {
            $purchasingRequest->cancel();

            return redirect()
                ->route('purchasing-requests.show', $purchasingRequest)
                ->with('success', 'Talep iptal edildi.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Talep iptal edilemedi: ' . $e->getMessage());
        }
    }

    /**
     * Teslimat onayı ve otomatik stok girişi
     */
    public function markAsDelivered(PurchasingRequest $purchasingRequest): RedirectResponse
    {
        if ($purchasingRequest->status !== 'ordered') {
            return redirect()
                ->back()
                ->with('error', 'Sadece sipariş durumundaki talepler teslim alınabilir.');
        }

        DB::beginTransaction();
        try {
            // Talebi teslim edildi olarak işaretle
            $purchasingRequest->markAsDelivered();

            // Otomatik stok hareketleri oluştur
            $this->createStockMovementsFromDelivery($purchasingRequest);

            DB::commit();

            return redirect()
                ->route('purchasing-requests.show', $purchasingRequest)
                ->with('success', 'Teslimat onaylandı ve stok kayıtları oluşturuldu.');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Satınalma teslimat hatası: ' . $e->getMessage(), [
                'purchasing_request_id' => $purchasingRequest->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->back()
                ->with('error', 'Teslimat onaylanamadı: ' . $e->getMessage());
        }
    }

    /**
     * Satınalma teslimatından otomatik stok hareketleri oluştur
     */
    private function createStockMovementsFromDelivery(PurchasingRequest $purchasingRequest): void
    {
        // Projenin deposunu bul veya oluştur
        $warehouse = Warehouse::where('project_id', $purchasingRequest->project_id)
            ->where('is_active', true)
            ->first();

        // Depo yoksa otomatik oluştur
        if (!$warehouse) {
            $warehouse = Warehouse::create([
                'project_id' => $purchasingRequest->project_id,
                'name' => 'Ana Depo',
                'location' => $purchasingRequest->project->location ?? 'Proje Sahası',
                'responsible_user_id' => $purchasingRequest->requested_by,
                'description' => 'Otomatik oluşturulan ana depo',
                'is_active' => true,
            ]);

            Log::info("Proje için otomatik depo oluşturuldu", [
                'project_id' => $purchasingRequest->project_id,
                'warehouse_id' => $warehouse->id,
            ]);
        }

        // Her kalem için stok hareketi oluştur
        $purchasingRequest->load('items.material');
        $createdMovements = 0;

        foreach ($purchasingRequest->items as $item) {
            // Sadece material_id'si olan kalemler için stok hareketi oluştur
            if (!$item->material_id) {
                Log::info("Kalem için material_id yok, stok hareketi atlanıyor", [
                    'item_id' => $item->id,
                    'item_name' => $item->item_name,
                ]);
                continue;
            }

            try {
                // Stok hareketi oluştur
                $movement = StockMovement::create([
                    'warehouse_id' => $warehouse->id,
                    'material_id' => $item->material_id,
                    'movement_type' => 'in',
                    'quantity' => $item->quantity,
                    'unit_price' => $item->actual_unit_price ?? $item->estimated_unit_price ?? 0,
                    'reference_type' => 'purchasing_request',
                    'reference_id' => $purchasingRequest->id,
                    'performed_by' => Auth::id() ?? 1,
                    'movement_date' => now(),
                    'notes' => "Satınalma teslim alındı: {$purchasingRequest->request_code} - {$item->item_name}",
                ]);

                // Material stokunu artır
                if ($item->material) {
                    $item->material->increment('current_stock', $item->quantity);

                    Log::info("Stok artırıldı", [
                        'material_id' => $item->material_id,
                        'material_name' => $item->material->name,
                        'quantity' => $item->quantity,
                        'new_stock' => $item->material->fresh()->current_stock,
                    ]);
                }

                $createdMovements++;
            } catch (\Exception $e) {
                Log::error("Stok hareketi oluşturulamadı", [
                    'item_id' => $item->id,
                    'material_id' => $item->material_id,
                    'error' => $e->getMessage(),
                ]);

                // Hata olsa bile devam et, diğer kalemleri işle
                continue;
            }
        }

        Log::info("Satınalma teslimatı için stok hareketleri oluşturuldu", [
            'purchasing_request_id' => $purchasingRequest->id,
            'request_code' => $purchasingRequest->request_code,
            'warehouse_id' => $warehouse->id,
            'total_items' => $purchasingRequest->items->count(),
            'created_movements' => $createdMovements,
        ]);
    }
}
