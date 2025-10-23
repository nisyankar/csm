<?php

namespace App\Http\Controllers;

use App\Models\LeaveParameter;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class LeaveParameterController extends Controller
{
    /**
     * Display a listing of leave parameters
     */
    public function index(Request $request): Response
    {
        $query = LeaveParameter::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('parameter_key', 'like', "%{$search}%");
            });
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by category (parameter_group in database)
        if ($request->filled('category')) {
            $query->where('parameter_group', $request->category);
        }

        $parameters = $query->orderBy('parameter_group')
            ->orderBy('parameter_name')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('LeaveManagement/Parameters/Index', [
            'parameters' => $parameters,
            'filters' => $request->only(['search', 'type', 'status', 'category']),
            'stats' => $this->getParameterStats(),
            'categories' => $this->getParameterCategories(),
            'types' => $this->getParameterTypes(),
        ]);
    }

    /**
     * Show the form for creating a new parameter
     */
    public function create(): Response
    {
        return Inertia::render('LeaveManagement/Parameters/Create', [
            'categories' => $this->getParameterCategories(),
            'types' => $this->getParameterTypes(),
        ]);
    }

    /**
     * Store a newly created parameter
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parameter_key' => 'required|string|max:100|unique:leave_parameters',
            'description' => 'nullable|string',
            'type' => 'required|in:integer,decimal,boolean,string,date,json',
            'category' => 'required|in:annual_leave,sick_leave,maternity_leave,paternity_leave,unpaid_leave,calculation,eligibility,restrictions',
            'default_value' => 'nullable|string',
            'min_value' => 'nullable|numeric',
            'max_value' => 'nullable|numeric',
            'validation_rules' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'is_system' => 'boolean',
            'is_editable' => 'boolean',
            'applies_to_all' => 'boolean',
            'employee_categories' => 'nullable|array',
            'employee_categories.*' => 'string|in:worker,foreman,engineer,manager,system_admin',
        ]);

        $validated['created_by'] = Auth::id();

        // Validate JSON if type is json
        if ($validated['type'] === 'json' && $validated['default_value']) {
            $decoded = json_decode($validated['default_value']);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return back()->withErrors(['default_value' => 'Geçersiz JSON formatı.']);
            }
        }

        // Validate number ranges
        if (in_array($validated['type'], ['integer', 'decimal'])) {
            if (isset($validated['min_value'], $validated['max_value'])) {
                if ($validated['min_value'] > $validated['max_value']) {
                    return back()->withErrors(['max_value' => 'Maksimum değer minimum değerden büyük olmalıdır.']);
                }
            }
        }

        LeaveParameter::create($validated);

        return redirect()->route('leave-management.parameters.index')
            ->with('success', 'İzin parametresi başarıyla oluşturuldu.');
    }

    /**
     * Display the specified parameter
     */
    public function show(LeaveParameter $parameter): Response
    {
        return Inertia::render('LeaveManagement/Parameters/Show', [
            'parameter' => $parameter->load('creator'),
            'usage_stats' => $this->getParameterUsageStats($parameter),
            'audit_logs' => $this->getParameterAuditLogs($parameter),
        ]);
    }

    /**
     * Show the form for editing the parameter
     */
    public function edit(LeaveParameter $parameter): Response
    {
        // Check if parameter is editable
        if (!$parameter->is_editable) {
            return redirect()->route('leave-management.parameters.show', $parameter)
                ->with('error', 'Bu parametre düzenlenemez.');
        }

        return Inertia::render('LeaveManagement/Parameters/Edit', [
            'parameter' => $parameter,
            'categories' => $this->getParameterCategories(),
            'types' => $this->getParameterTypes(),
        ]);
    }

    /**
     * Update the specified parameter
     */
    public function update(Request $request, LeaveParameter $parameter): RedirectResponse
    {
        // Check if parameter is editable
        if (!$parameter->is_editable) {
            return back()->with('error', 'Bu parametre düzenlenemez.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'default_value' => 'nullable|string',
            'min_value' => 'nullable|numeric',
            'max_value' => 'nullable|numeric',
            'validation_rules' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'applies_to_all' => 'boolean',
            'employee_categories' => 'nullable|array',
            'employee_categories.*' => 'string|in:worker,foreman,engineer,manager,system_admin',
        ]);

        // Don't allow changing system parameters key or type
        if ($parameter->is_system) {
            unset($validated['parameter_key'], $validated['type'], $validated['category']);
        }

        // Validate JSON if type is json
        if ($parameter->type === 'json' && $validated['default_value']) {
            $decoded = json_decode($validated['default_value']);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return back()->withErrors(['default_value' => 'Geçersiz JSON formatı.']);
            }
        }

        // Validate number ranges
        if (in_array($parameter->type, ['integer', 'decimal'])) {
            if (isset($validated['min_value'], $validated['max_value'])) {
                if ($validated['min_value'] > $validated['max_value']) {
                    return back()->withErrors(['max_value' => 'Maksimum değer minimum değerden büyük olmalıdır.']);
                }
            }
        }

        $parameter->update($validated);

        return redirect()->route('leave-management.parameters.show', $parameter)
            ->with('success', 'İzin parametresi başarıyla güncellendi.');
    }

    /**
     * Remove the specified parameter
     */
    public function destroy(LeaveParameter $parameter): RedirectResponse
    {
        // Prevent deletion of system parameters
        if ($parameter->is_system) {
            return back()->with('error', 'Sistem parametreleri silinemez.');
        }

        // Check if parameter is in use
        if ($this->isParameterInUse($parameter)) {
            return back()->with('error', 'Bu parametre kullanımda olduğu için silinemez.');
        }

        $parameter->delete();

        return redirect()->route('leave-management.parameters.index')
            ->with('success', 'İzin parametresi başarıyla silindi.');
    }

    /**
     * Get parameter statistics
     */
    private function getParameterStats(): array
    {
        return [
            'total' => LeaveParameter::count(),
            'active' => LeaveParameter::where('is_active', true)->count(),
            'inactive' => LeaveParameter::where('is_active', false)->count(),
            'system' => LeaveParameter::where('is_system_parameter', true)->count(),
            'custom' => LeaveParameter::where('is_system_parameter', false)->count(),
            'by_category' => LeaveParameter::selectRaw('parameter_group, COUNT(*) as count')
                ->groupBy('parameter_group')
                ->pluck('count', 'parameter_group')
                ->toArray(),
        ];
    }

    /**
     * Get available parameter categories
     */
    private function getParameterCategories(): array
    {
        return [
            'basic' => 'Temel İzin Süreleri',
            'special' => 'Özel Durumlar',
            'business' => 'İş Kuralları',
            'calculation' => 'Hesaplama Parametreleri',
        ];
    }

    /**
     * Get available parameter types
     */
    private function getParameterTypes(): array
    {
        return [
            'integer' => 'Tam Sayı',
            'decimal' => 'Ondalık Sayı',
            'boolean' => 'Evet/Hayır',
            'string' => 'Metin',
            'date' => 'Tarih',
            'json' => 'JSON Veri',
        ];
    }

    /**
     * Get parameter usage statistics
     */
    private function getParameterUsageStats(LeaveParameter $parameter): array
    {
        // This would track where and how often the parameter is used
        // For now, return placeholder data
        return [
            'calculations_using' => 0,
            'last_used_at' => null,
            'usage_frequency' => 'low',
        ];
    }

    /**
     * Get parameter audit logs
     */
    private function getParameterAuditLogs(LeaveParameter $parameter): array
    {
        // This would return audit trail for parameter changes
        // For now, return placeholder data
        return [];
    }

    /**
     * Check if parameter is currently in use
     */
    private function isParameterInUse(LeaveParameter $parameter): bool
    {
        // This would check if parameter is referenced in calculations, rules, etc.
        // For now, return false
        return false;
    }
}