<?php

namespace App\Http\Controllers;

use App\Models\InspectionCompany;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class InspectionCompanyController extends Controller
{
    /**
     * Denetim kuruluşları listesi
     */
    public function index(Request $request): Response
    {
        $companies = InspectionCompany::query()
            ->withCount('inspections')
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('company_name', 'like', "%{$search}%")
                        ->orWhere('license_number', 'like', "%{$search}%")
                        ->orWhere('contact_person', 'like', "%{$search}%");
                });
            })
            ->when($request->is_active !== null, function ($query) use ($request) {
                $query->where('is_active', $request->is_active);
            })
            ->orderBy('company_name')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('InspectionCompanies/Index', [
            'companies' => $companies,
            'filters' => $request->only(['search', 'is_active']),
        ]);
    }

    /**
     * Yeni denetim kuruluşu formu
     */
    public function create(): Response
    {
        return Inertia::render('InspectionCompanies/Create');
    }

    /**
     * Denetim kuruluşu kaydet
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'license_number' => 'required|string|max:255|unique:inspection_companies,license_number',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        InspectionCompany::create($validated);

        return redirect()->route('inspection-companies.index')
            ->with('success', 'Denetim kuruluşu başarıyla oluşturuldu.');
    }

    /**
     * Denetim kuruluşu düzenleme formu
     */
    public function edit(InspectionCompany $inspectionCompany): Response
    {
        return Inertia::render('InspectionCompanies/Edit', [
            'inspectionCompany' => $inspectionCompany,
        ]);
    }

    /**
     * Denetim kuruluşu güncelle
     */
    public function update(Request $request, InspectionCompany $inspectionCompany): RedirectResponse
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'license_number' => 'required|string|max:255|unique:inspection_companies,license_number,' . $inspectionCompany->id,
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $inspectionCompany->update($validated);

        return redirect()->route('inspection-companies.index')
            ->with('success', 'Denetim kuruluşu başarıyla güncellendi.');
    }

    /**
     * Denetim kuruluşu sil
     */
    public function destroy(InspectionCompany $inspectionCompany): RedirectResponse
    {
        // İlişkili denetim var mı kontrol et
        if ($inspectionCompany->inspections()->count() > 0) {
            return back()->with('error', 'Bu denetim kuruluşuna ait denetimler olduğu için silinemez.');
        }

        $inspectionCompany->delete();

        return redirect()->route('inspection-companies.index')
            ->with('success', 'Denetim kuruluşu başarıyla silindi.');
    }
}
