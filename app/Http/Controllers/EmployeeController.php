<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Department;
use App\Models\Project;
use App\Models\EmployeeSalaryHistory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Http\RedirectResponse;

class EmployeeController extends Controller
{
    /**
     * Display a listing of employees
     */
    public function index(Request $request): Response
    {
        try {
            $employeesData = $this->getEmployeesWithRawSQL($request);

            return Inertia::render('Employees/Index', [
                'employees' => $employeesData,
                'departments' => $this->getDepartmentsRaw(),
                'projects' => $this->getProjectsRaw(),
                'positions' => $this->getPositionsRaw(),
                'filters' => $this->getFilters($request)
            ]);
        } catch (\Exception $e) {
            \Log::error('Employee index error: ' . $e->getMessage());
            return $this->getFallbackResponse();
        }
    }

    /**
     * Show employee detail
     */
    public function show(Employee $employee): Response|RedirectResponse
    {
        try {
            $employeeData = DB::select("
                SELECT e.*, d.name as department_name, p.name as project_name
                FROM employees e
                LEFT JOIN departments d ON e.department_id = d.id
                LEFT JOIN projects p ON e.current_project_id = p.id
                WHERE e.id = ?
            ", [$employee->id]);

            if (empty($employeeData)) {
                return redirect()->route('employees.index')->with('error', 'Çalışan bulunamadı.');
            }

            $transformedEmployee = $this->transformDetailedEmployeeRow($employeeData[0]);

            return Inertia::render('Employees/Show', [
                'employee' => $transformedEmployee,
                'recentTimesheets' => [],
                'leaveRequests' => [],
                'documents' => [],
                'performanceMetrics' => [],
            ]);
        } catch (\Exception $e) {
            \Log::error('Employee show error: ' . $e->getMessage());
            return redirect()->route('employees.index')->with('error', 'Çalışan bilgileri yüklenemedi.');
        }
    }

    /**
     * Show create form
     */
    public function create(): Response
    {
        return Inertia::render('Employees/Create', [
            'departments' => $this->getDepartmentsRaw(),
            'projects' => $this->getProjectsRaw(),
            'managers' => $this->getManagersRaw(),
            'positions' => $this->getPositionsRaw(),
            'wageTypes' => [
                ['value' => 'daily', 'label' => 'Günlük'],
                ['value' => 'hourly', 'label' => 'Saatlik'],
                ['value' => 'monthly', 'label' => 'Aylık'],
            ],
            'categories' => [
                ['value' => 'worker', 'label' => 'İşçi'],
                ['value' => 'foreman', 'label' => 'Ustabaşı'],
                ['value' => 'technician', 'label' => 'Tekniker'],
                ['value' => 'engineer', 'label' => 'Mühendis'],
                ['value' => 'manager', 'label' => 'Yönetici'],
            ],
            'statuses' => [
                ['value' => 'active', 'label' => 'Aktif'],
                ['value' => 'inactive', 'label' => 'Pasif'],
                ['value' => 'suspended', 'label' => 'Askıya Alınmış'],
            ],
        ]);
    }

    /**
     * Store new employee
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            $validated = $request->validate([
                'employee_code' => 'required|string|max:50|unique:employees',
                'first_name' => 'required|string|max:100',
                'last_name' => 'required|string|max:100',
                'tc_number' => 'required|string|size:11|unique:employees',
                'birth_date' => 'required|date|before:today',
                'gender' => 'nullable|in:male,female',
                'email' => 'nullable|email|unique:employees',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string',
                'position' => 'required|string|max:100',
                'category' => 'required|in:worker,foreman,technician,engineer,manager',
                'status' => 'required|in:active,inactive,suspended',
                'start_date' => 'required|date',
                'end_date' => 'nullable|date|after:start_date',
                'department_id' => 'nullable|exists:departments,id',
                'current_project_id' => 'nullable|exists:projects,id',
                'manager_id' => 'nullable|exists:employees,id',
                'annual_leave_days' => 'required|integer|min:14|max:30',
                'used_leave_days' => 'nullable|integer|min:0',
                'wage_type' => 'required|in:daily,hourly,monthly',
                'daily_wage' => 'nullable|numeric|min:0',
                'hourly_wage' => 'nullable|numeric|min:0',
                'monthly_salary' => 'nullable|numeric|min:0',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
            ]);

            // Clean string data
            foreach (['employee_code', 'first_name', 'last_name', 'email', 'phone', 'position', 'tc_number', 'address'] as $field) {
                if (isset($validated[$field])) {
                    $validated[$field] = $this->cleanString($validated[$field]);
                }
            }

            // Handle photo upload
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('employee_photos', 'public');
                $validated['photo_path'] = $photoPath;
            }

            $employee = Employee::create($validated);

            return redirect()->route('employees.show', $employee)->with('success', 'Çalışan başarıyla oluşturuldu.');
        } catch (\Exception $e) {
            \Log::error('Employee store error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Çalışan oluşturulamadı.')->withInput();
        }
    }

    /**
     * Show edit form
     */
    public function edit(Employee $employee): Response
    {
        try {
            $employeeData = DB::select("
                SELECT e.*, d.name as department_name, p.name as project_name
                FROM employees e
                LEFT JOIN departments d ON e.department_id = d.id
                LEFT JOIN projects p ON e.current_project_id = p.id
                WHERE e.id = ?
            ", [$employee->id]);

            $transformedEmployee = !empty($employeeData)
                ? $this->transformDetailedEmployeeRow($employeeData[0])
                : $this->getDefaultEmployeeData($employee);

            // Get salary history
            $salaryHistory = $employee->salaryHistory()
                ->with('changedBy')
                ->orderBy('effective_date', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($history) {
                    return [
                        'id' => $history->id,
                        'change_type' => $history->change_type,
                        'change_type_label' => $history->change_type_label,
                        'change_amount' => $history->change_amount,
                        'formatted_change_amount' => $history->formatted_change_amount,
                        'change_percentage' => $history->change_percentage,
                        'reason' => $history->reason,
                        'effective_date' => $history->effective_date->format('Y-m-d'),
                        'old_salary_formatted' => $history->old_salary_formatted,
                        'new_salary_formatted' => $history->new_salary_formatted,
                        'changed_by_name' => $history->changedBy->name ?? 'Sistem',
                        'created_at' => $history->created_at->format('d.m.Y H:i'),
                    ];
                });

            $transformedEmployee['salary_history'] = $salaryHistory;

            return Inertia::render('Employees/Edit', [
                'employee' => $transformedEmployee,
                'departments' => $this->getDepartmentsRaw(),
                'projects' => $this->getProjectsRaw(),
                'managers' => $this->getManagersRaw($employee->id),
                'positions' => $this->getPositionsRaw(),
                'wageTypes' => [
                    ['value' => 'daily', 'label' => 'Günlük'],
                    ['value' => 'hourly', 'label' => 'Saatlik'],
                    ['value' => 'monthly', 'label' => 'Aylık'],
                ],
                'categories' => [
                    ['value' => 'worker', 'label' => 'İşçi'],
                    ['value' => 'foreman', 'label' => 'Ustabaşı'],
                    ['value' => 'technician', 'label' => 'Tekniker'],
                    ['value' => 'engineer', 'label' => 'Mühendis'],
                    ['value' => 'manager', 'label' => 'Yönetici'],
                ],
                'statuses' => [
                    ['value' => 'active', 'label' => 'Aktif'],
                    ['value' => 'inactive', 'label' => 'Pasif'],
                    ['value' => 'suspended', 'label' => 'Askıya Alınmış'],
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Employee edit error: ' . $e->getMessage());
            return Inertia::render('Employees/Edit', [
                'employee' => $this->getDefaultEmployeeData($employee),
                'departments' => [],
                'projects' => [],
                'managers' => [],
                'positions' => [],
                'wageTypes' => [],
                'categories' => [],
                'statuses' => [],
                'error' => 'Form yüklenirken hata oluştu.'
            ]);
        }
    }

    /**
     * Update employee
     */
    public function update(Request $request, Employee $employee): RedirectResponse
    {

        \Log::info('Update method called', [
            'has_photo' => $request->hasFile('photo'),
            'all_files' => $request->allFiles(),
            'request_data' => $request->all()
        ]);
        
        try {
            $validated = $request->validate([
                'employee_code' => [
                    'required',
                    'string',
                    'max:50',
                    Rule::unique('employees')->ignore($employee->id)
                ],
                'first_name' => 'required|string|max:100',
                'last_name' => 'required|string|max:100',
                'tc_number' => [
                    'required',
                    'string',
                    'size:11',
                    Rule::unique('employees')->ignore($employee->id)
                ],
                'birth_date' => 'required|date|before:today',
                'gender' => 'nullable|in:male,female',
                'email' => [
                    'nullable',
                    'email',
                    Rule::unique('employees')->ignore($employee->id)
                ],
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string',
                'position' => 'required|string|max:100',
                'category' => 'required|in:worker,foreman,technician,engineer,manager',
                'status' => 'required|in:active,inactive,suspended',
                'start_date' => 'required|date',
                'end_date' => 'nullable|date|after:start_date',
                'department_id' => 'nullable|exists:departments,id',
                'current_project_id' => 'nullable|exists:projects,id',
                'manager_id' => 'nullable|exists:employees,id',
                'annual_leave_days' => 'required|integer|min:14|max:30',
                'used_leave_days' => 'nullable|integer|min:0',
                'wage_type' => 'required|in:daily,hourly,monthly',
                'daily_wage' => 'nullable|numeric|min:0',
                'hourly_wage' => 'nullable|numeric|min:0',
                'monthly_salary' => 'nullable|numeric|min:0',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
            ]);

            // Clean string data
            foreach (['employee_code', 'first_name', 'last_name', 'email', 'phone', 'position', 'tc_number', 'address'] as $field) {
                if (isset($validated[$field])) {
                    $validated[$field] = $this->cleanString($validated[$field]);
                }
            }

            // Track salary changes before update
            $oldSalaryData = [
                'wage_type' => $employee->wage_type,
                'daily_wage' => $employee->daily_wage,
                'hourly_wage' => $employee->hourly_wage,
                'monthly_salary' => $employee->monthly_salary,
            ];

            // Handle photo upload
            if ($request->hasFile('photo')) {
                \Log::info('Photo upload detected', [
                    'file_size' => $request->file('photo')->getSize(),
                    'file_type' => $request->file('photo')->getMimeType(),
                    'original_name' => $request->file('photo')->getClientOriginalName()
                ]);

                // Delete old photo if exists
                if ($employee->photo_path && Storage::disk('public')->exists($employee->photo_path)) {
                    Storage::disk('public')->delete($employee->photo_path);
                    \Log::info('Old photo deleted', ['path' => $employee->photo_path]);
                }

                $photoPath = $request->file('photo')->store('employee_photos', 'public');
                $validated['photo_path'] = $photoPath;

                \Log::info('New photo stored', ['path' => $photoPath]);
            }

            $employee->update($validated);

            // Create salary history if wages changed
            $this->createSalaryHistoryIfChanged($employee, $oldSalaryData, $validated);

            return redirect()->route('employees.show', $employee)->with('success', 'Çalışan başarıyla güncellendi.');
        } catch (\Exception $e) {
            \Log::error('Employee update error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Çalışan güncellenirken hata oluştu.')->withInput();
        }
    }

    /**
     * Delete employee
     */
    public function destroy(Employee $employee): RedirectResponse
    {
        try {
            if ($employee->photo_path) {
                Storage::disk('public')->delete($employee->photo_path);
            }
            $employee->delete();
            return redirect()->route('employees.index')->with('success', 'Çalışan başarıyla silindi.');
        } catch (\Exception $e) {
            \Log::error('Employee delete error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Çalışan silinemedi.');
        }
    }

    // PRIVATE HELPER METHODS

    /**
     * Get employees with raw SQL
     */
    private function getEmployeesWithRawSQL(Request $request): array
    {
        $baseQuery = "
        SELECT 
            e.id, COALESCE(e.employee_code, '') as employee_code,
            COALESCE(e.first_name, '') as first_name, COALESCE(e.last_name, '') as last_name,
            COALESCE(e.email, '') as email, COALESCE(e.phone, '') as phone,
            COALESCE(e.position, '') as position, COALESCE(e.status, 'active') as status,
            e.start_date, e.department_id, e.current_project_id, e.created_at, e.updated_at,
            d.name as department_name, p.name as project_name
        FROM employees e
        LEFT JOIN departments d ON e.department_id = d.id
        LEFT JOIN projects p ON e.current_project_id = p.id
        WHERE e.deleted_at IS NULL
        ";

        $params = [];
        $conditions = [];

        // Filters
        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $conditions[] = "(e.first_name LIKE ? OR e.last_name LIKE ? OR e.employee_code LIKE ? OR e.email LIKE ?)";
            $params = array_merge($params, [$search, $search, $search, $search]);
        }

        if ($request->filled('department')) {
            $conditions[] = "e.department_id = ?";
            $params[] = $request->department;
        }

        if ($request->filled('status')) {
            $conditions[] = "e.status = ?";
            $params[] = $request->status;
        }

        if (!empty($conditions)) {
            $baseQuery .= " AND " . implode(" AND ", $conditions);
        }

        // Sorting
        $sortField = $request->get('sort', 'first_name');
        $sortDirection = $request->get('direction', 'asc');
        $allowedSorts = ['first_name', 'last_name', 'employee_code', 'position', 'status', 'start_date'];
        if (!in_array($sortField, $allowedSorts)) {
            $sortField = 'first_name';
        }
        $baseQuery .= " ORDER BY e.{$sortField} {$sortDirection}";

        // Pagination
        $perPage = min($request->get('per_page', 10), 100);
        $page = $request->get('page', 1);
        $offset = ($page - 1) * $perPage;

        // Get total count
        $countQuery = "SELECT COUNT(*) as total FROM employees e WHERE e.deleted_at IS NULL";
        if (!empty($conditions)) {
            $countQuery .= " AND " . implode(" AND ", $conditions);
        }

        $totalResult = DB::select($countQuery, $params);
        $total = $totalResult[0]->total ?? 0;

        // Get paginated results
        $baseQuery .= " LIMIT {$perPage} OFFSET {$offset}";
        $employees = DB::select($baseQuery, $params);

        $transformedEmployees = array_map([$this, 'transformEmployeeRow'], $employees);

        return [
            'data' => $transformedEmployees,
            'total' => (int) $total,
            'per_page' => (int) $perPage,
            'current_page' => (int) $page,
            'last_page' => (int) ceil($total / $perPage),
            'from' => $total > 0 ? $offset + 1 : 0,
            'to' => min($offset + $perPage, $total),
            'path' => $request->url(),
            'links' => $this->generatePaginationLinks($page, ceil($total / $perPage), $request)
        ];
    }

    /**
     * Transform employee row for listing
     */
    private function transformEmployeeRow($row): array
    {
        $firstName = $this->cleanString($row->first_name ?? '');
        $lastName = $this->cleanString($row->last_name ?? '');

        return [
            'id' => (int) $row->id,
            'employee_code' => $this->cleanString($row->employee_code ?? ''),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'full_name' => trim($firstName . ' ' . $lastName),
            'email' => $this->cleanString($row->email ?? ''),
            'phone' => $this->cleanString($row->phone ?? ''),
            'position' => $this->cleanString($row->position ?? ''),
            'status' => $row->status ?? 'active',
            'hire_date' => $row->start_date ?? null,
            'avatar' => null,
            'initials' => $this->getInitials($firstName, $lastName),
            'department' => $row->department_name ? [
                'id' => (int) $row->department_id,
                'name' => $this->cleanString($row->department_name)
            ] : null,
            'project' => $row->project_name ? [
                'id' => (int) $row->current_project_id,
                'name' => $this->cleanString($row->project_name)
            ] : null,
            'created_at' => $row->created_at ?? null,
            'updated_at' => $row->updated_at ?? null,
        ];
    }

    /**
     * Transform detailed employee row for show/edit
     */
    private function transformDetailedEmployeeRow($row): array
    {
        $firstName = $this->cleanString($row->first_name ?? '');
        $lastName = $this->cleanString($row->last_name ?? '');

        return [
            'id' => (int) $row->id,
            'employee_code' => $this->cleanString($row->employee_code ?? ''),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'full_name' => trim($firstName . ' ' . $lastName),
            'tc_number' => $this->cleanString($row->tc_number ?? ''),
            'birth_date' => $row->birth_date ?? null,
            'gender' => $row->gender ?? '',
            'email' => $this->cleanString($row->email ?? ''),
            'phone' => $this->cleanString($row->phone ?? ''),
            'address' => $this->cleanString($row->address ?? ''),
            'position' => $this->cleanString($row->position ?? ''),
            'category' => $row->category ?? '',
            'status' => $row->status ?? 'active',
            'start_date' => $row->start_date ?? null,
            'end_date' => $row->end_date ?? null,
            'department_id' => $row->department_id ?? null,
            'current_project_id' => $row->current_project_id ?? null,
            'manager_id' => $row->manager_id ?? null,
            'annual_leave_days' => (int) ($row->annual_leave_days ?? 14),
            'used_leave_days' => (int) ($row->used_leave_days ?? 0),
            'wage_type' => $row->wage_type ?? '',
            'daily_wage' => $row->daily_wage ?? null,
            'hourly_wage' => $row->hourly_wage ?? null,
            'monthly_salary' => $row->monthly_salary ?? null,
            'photo_path' => $row->photo_path ?? null,
            'hire_date' => $row->start_date ?? null,
            'avatar' => null,
            'initials' => $this->getInitials($firstName, $lastName),
            'department' => $row->department_name ? [
                'id' => (int) $row->department_id,
                'name' => $this->cleanString($row->department_name)
            ] : null,
            'project' => $row->project_name ? [
                'id' => (int) $row->current_project_id,
                'name' => $this->cleanString($row->project_name)
            ] : null,
            'created_at' => $row->created_at ?? null,
            'updated_at' => $row->updated_at ?? null,
        ];
    }

    /**
     * Get default employee data
     */
    private function getDefaultEmployeeData(Employee $employee): array
    {
        return [
            'id' => $employee->id,
            'employee_code' => $this->cleanString($employee->employee_code ?? ''),
            'first_name' => $this->cleanString($employee->first_name ?? ''),
            'last_name' => $this->cleanString($employee->last_name ?? ''),
            'full_name' => $this->cleanString(trim(($employee->first_name ?? '') . ' ' . ($employee->last_name ?? ''))),
            'tc_number' => $this->cleanString($employee->tc_number ?? ''),
            'birth_date' => $employee->birth_date ? $employee->birth_date->format('Y-m-d') : null,
            'gender' => $employee->gender ?? '',
            'email' => $this->cleanString($employee->email ?? ''),
            'phone' => $this->cleanString($employee->phone ?? ''),
            'address' => $this->cleanString($employee->address ?? ''),
            'position' => $this->cleanString($employee->position ?? ''),
            'category' => $employee->category ?? '',
            'status' => $employee->status ?? 'active',
            'start_date' => $employee->start_date ? $employee->start_date->format('Y-m-d') : null,
            'end_date' => $employee->end_date ? $employee->end_date->format('Y-m-d') : null,
            'department_id' => $employee->department_id ?? null,
            'current_project_id' => $employee->current_project_id ?? null,
            'manager_id' => $employee->manager_id ?? null,
            'annual_leave_days' => $employee->annual_leave_days ?? 14,
            'used_leave_days' => $employee->used_leave_days ?? 0,
            'wage_type' => $employee->wage_type ?? '',
            'daily_wage' => $employee->daily_wage ?? null,
            'hourly_wage' => $employee->hourly_wage ?? null,
            'monthly_salary' => $employee->monthly_salary ?? null,
            'photo_path' => $employee->photo_path ?? null,
            'hire_date' => $employee->start_date ?? null,
            'avatar' => null,
            'initials' => $this->getInitials($employee->first_name ?? '', $employee->last_name ?? ''),
            'department' => null,
            'project' => null,
            'created_at' => $employee->created_at ? $employee->created_at->toISOString() : null,
            'updated_at' => $employee->updated_at ? $employee->updated_at->toISOString() : null,
        ];
    }

    /**
     * Get managers list
     */
    private function getManagersRaw(?int $excludeId = null): array
    {
        try {
            $sql = "
                SELECT id, COALESCE(first_name, '') as first_name, COALESCE(last_name, '') as last_name
                FROM employees 
                WHERE deleted_at IS NULL AND status = 'active'
            ";

            $params = [];
            if ($excludeId) {
                $sql .= " AND id != ?";
                $params[] = $excludeId;
            }

            $sql .= " ORDER BY first_name LIMIT 100";

            $managers = DB::select($sql, $params);

            return array_map(function ($manager) {
                return [
                    'id' => (int) $manager->id,
                    'name' => $this->cleanString(trim($manager->first_name . ' ' . $manager->last_name))
                ];
            }, $managers);
        } catch (\Exception $e) {
            \Log::warning('Managers raw SQL error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get positions list
     */
    private function getPositionsRaw(): array
    {
        try {
            $positions = DB::select("
                SELECT DISTINCT position 
                FROM employees 
                WHERE position IS NOT NULL AND position != '' AND deleted_at IS NULL 
                ORDER BY position LIMIT 50
            ");

            return array_map(function ($pos) {
                return $this->cleanStringGentle($pos->position);
            }, $positions);
        } catch (\Exception $e) {
            \Log::warning('Positions raw SQL error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get projects list
     */
    private function getProjectsRaw(): array
    {
        try {
            $projects = DB::select("
                SELECT id, COALESCE(name, '') as name 
                FROM projects 
                WHERE deleted_at IS NULL 
                ORDER BY name LIMIT 100
            ");

            return array_map(function ($project) {
                return [
                    'id' => (int) $project->id,
                    'name' => $this->cleanStringGentle($project->name)
                ];
            }, $projects);
        } catch (\Exception $e) {
            \Log::warning('Projects raw SQL error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get departments list
     */
    private function getDepartmentsRaw(): array
    {
        try {
            $departments = DB::select("
                SELECT id, COALESCE(name, '') as name 
                FROM departments 
                WHERE deleted_at IS NULL 
                ORDER BY name LIMIT 100
            ");

            return array_map(function ($dept) {
                return [
                    'id' => (int) $dept->id,
                    'name' => $this->cleanStringGentle($dept->name)
                ];
            }, $departments);
        } catch (\Exception $e) {
            \Log::warning('Departments raw SQL error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Generate pagination links
     */
    private function generatePaginationLinks(int $currentPage, int $lastPage, Request $request): array
    {
        $links = [];

        if ($currentPage > 1) {
            $links[] = [
                'url' => $request->fullUrlWithQuery(['page' => $currentPage - 1]),
                'label' => '&laquo; Previous',
                'active' => false
            ];
        }

        for ($i = max(1, $currentPage - 2); $i <= min($lastPage, $currentPage + 2); $i++) {
            $links[] = [
                'url' => $request->fullUrlWithQuery(['page' => $i]),
                'label' => (string) $i,
                'active' => $i === $currentPage
            ];
        }

        if ($currentPage < $lastPage) {
            $links[] = [
                'url' => $request->fullUrlWithQuery(['page' => $currentPage + 1]),
                'label' => 'Next &raquo;',
                'active' => false
            ];
        }

        return $links;
    }

    /**
     * Get request filters
     */
    private function getFilters(Request $request): array
    {
        return [
            'search' => $request->get('search', ''),
            'department' => $request->get('department', ''),
            'status' => $request->get('status', ''),
            'project' => $request->get('project', ''),
            'position' => $request->get('position', ''),
            'hire_date_from' => $request->get('hire_date_from', ''),
            'sort' => $request->get('sort', 'first_name'),
            'direction' => $request->get('direction', 'asc')
        ];
    }

    /**
     * Fallback response
     */
    private function getFallbackResponse(): Response
    {
        return Inertia::render('Employees/Index', [
            'employees' => [
                'data' => [],
                'total' => 0,
                'per_page' => 10,
                'current_page' => 1,
                'last_page' => 1,
                'from' => 0,
                'to' => 0,
                'links' => []
            ],
            'departments' => [],
            'projects' => [],
            'positions' => [],
            'filters' => []
        ]);
    }

    /**
     * String cleaning - gentle for Turkish
     */
    private function cleanStringGentle(?string $input): string
    {
        if (empty($input)) {
            return '';
        }

        // Only remove truly problematic characters
        // Keep Turkish characters: ç, ğ, ı, ö, ş, ü, Ç, Ğ, İ, Ö, Ş, Ü
        $clean = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $input);

        // Ensure UTF-8 but don't be aggressive
        if (!mb_check_encoding($clean, 'UTF-8')) {
            // Try to fix encoding
            $clean = mb_convert_encoding($clean, 'UTF-8', ['UTF-8', 'ISO-8859-9', 'Windows-1254']);
        }

        return trim($clean);
    }

    /**
     * String cleaning - strict for critical data
     */
    private function cleanString(?string $input): string
    {
        if (empty($input)) {
            return '';
        }

        // More aggressive cleaning for employee codes, emails etc.
        $clean = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $input);

        // Ensure UTF-8
        if (!mb_check_encoding($clean, 'UTF-8')) {
            $clean = mb_convert_encoding($clean, 'UTF-8', 'UTF-8');
        }

        return trim($clean);
    }

    /**
     * Get initials safely
     */
    private function getInitials(string $firstName, string $lastName): string
    {
        $first = $firstName ? mb_strtoupper(mb_substr($firstName, 0, 1, 'UTF-8'), 'UTF-8') : '';
        $last = $lastName ? mb_strtoupper(mb_substr($lastName, 0, 1, 'UTF-8'), 'UTF-8') : '';
        return $first . $last ?: 'NN';
    }

    /**
     * Create salary history entry if wages changed
     */
    private function createSalaryHistoryIfChanged(Employee $employee, array $oldData, array $newData): void
    {
        // Check if any wage-related field changed
        $wageFields = ['wage_type', 'daily_wage', 'hourly_wage', 'monthly_salary'];
        $hasWageChange = false;

        foreach ($wageFields as $field) {
            if (($oldData[$field] ?? null) != ($newData[$field] ?? null)) {
                $hasWageChange = true;
                break;
            }
        }

        if (!$hasWageChange) {
            return;
        }

        // Calculate change amount and type
        $oldAmount = $this->calculateTotalWage($oldData);
        $newAmount = $this->calculateTotalWage($newData);
        $changeAmount = $newAmount - $oldAmount;

        $changeType = 'adjustment';
        if ($changeAmount > 0) {
            $changeType = 'increase';
        } elseif ($changeAmount < 0) {
            $changeType = 'decrease';
        }

        $changePercentage = $oldAmount > 0 ? ($changeAmount / $oldAmount) * 100 : 0;

        // Create history record
        EmployeeSalaryHistory::create([
            'employee_id' => $employee->id,
            'old_wage_type' => $oldData['wage_type'],
            'old_daily_wage' => $oldData['daily_wage'],
            'old_hourly_wage' => $oldData['hourly_wage'],
            'old_monthly_salary' => $oldData['monthly_salary'],
            'new_wage_type' => $newData['wage_type'],
            'new_daily_wage' => $newData['daily_wage'],
            'new_hourly_wage' => $newData['hourly_wage'],
            'new_monthly_salary' => $newData['monthly_salary'],
            'change_type' => $changeType,
            'change_amount' => abs($changeAmount),
            'change_percentage' => round($changePercentage, 2),
            'reason' => 'Çalışan bilgileri güncelleme',
            'changed_by' => auth()->id(),
            'effective_date' => now()->toDateString(),
        ]);

        \Log::info('Salary history created', [
            'employee_id' => $employee->id,
            'change_type' => $changeType,
            'change_amount' => $changeAmount,
        ]);
    }

    /**
     * Calculate total wage amount for comparison
     */
    private function calculateTotalWage(array $wageData): float
    {
        switch ($wageData['wage_type']) {
            case 'daily':
                return (float) ($wageData['daily_wage'] ?? 0);
            case 'hourly':
                return (float) ($wageData['hourly_wage'] ?? 0) * 8; // 8 hours per day
            case 'monthly':
                return (float) ($wageData['monthly_salary'] ?? 0) / 30; // Daily equivalent
            default:
                return 0;
        }
    }

    /**
     * Personel kategorilerini listele (API için)
     */
    public function getCategories()
    {
        $categories = [
            'worker' => [
                'code' => 'worker',
                'name' => 'İşçi',
                'color' => 'blue',
                'icon' => 'user',
                'permissions' => ['basic']
            ],
            'foreman' => [
                'code' => 'foreman',
                'name' => 'Forman',
                'color' => 'green',
                'icon' => 'user-group',
                'permissions' => ['basic', 'team_management']
            ],
            'engineer' => [
                'code' => 'engineer',
                'name' => 'Mühendis',
                'color' => 'purple',
                'icon' => 'academic-cap',
                'permissions' => ['basic', 'technical']
            ],
            'manager' => [
                'code' => 'manager',
                'name' => 'Proje Yöneticisi',
                'color' => 'orange',
                'icon' => 'briefcase',
                'permissions' => ['basic', 'team_management', 'project_management']
            ],
            'system_admin' => [
                'code' => 'system_admin',
                'name' => 'Sistem Yöneticisi',
                'color' => 'red',
                'icon' => 'cog-6-tooth',
                'permissions' => ['all']
            ]
        ];

        return response()->json($categories);
    }

    /**
     * Sistem yöneticilerini listele
     */
    public function systemAdmins(Request $request)
    {
        $this->authorize('viewAny', Employee::class);

        $systemAdmins = Employee::systemAdmins()
            ->with(['user.roles', 'currentProject'])
            ->active()
            ->paginate(15);

        if ($request->wantsJson()) {
            return response()->json($systemAdmins);
        }

        return inertia('Employees/SystemAdmins', [
            'systemAdmins' => $systemAdmins,
        ]);
    }

    /**
     * Personel kategorisini güncelle
     */
    public function updateCategory(Request $request, Employee $employee)
    {
        $this->authorize('update', $employee);

        $request->validate([
            'category' => 'required|in:worker,foreman,engineer,manager,system_admin',
            'reason' => 'required|string|max:500'
        ]);

        $oldCategory = $employee->category;
        $newCategory = $request->category;

        // Sistem yöneticisi değişikliği özel yetki gerektirir
        if ($newCategory === 'system_admin' && !auth()->user()->hasRole(['admin', 'system_admin'])) {
            abort(403, 'Sistem yöneticisi atama yetkiniz bulunmamaktadır.');
        }

        $employee->update([
            'category' => $newCategory,
            'position' => $this->getPositionByCategory($newCategory)
        ]);

        // User rollerini güncelle
        if ($employee->user) {
            $this->updateUserRoles($employee->user, $newCategory);
        }

        // Log kaydet
        activity()
            ->performedOn($employee)
            ->causedBy(auth()->user())
            ->withProperties([
                'old_category' => $oldCategory,
                'new_category' => $newCategory,
                'reason' => $request->reason
            ])
            ->log('Personel kategorisi güncellendi');

        return back()->with('success', 'Personel kategorisi başarıyla güncellendi.');
    }

    /**
     * Kategoriye göre pozisyon belirle
     */
    private function getPositionByCategory(string $category): string
    {
        $positions = [
            'worker' => 'İşçi',
            'foreman' => 'Forman',
            'engineer' => 'Mühendis',
            'manager' => 'Proje Yöneticisi',
            'system_admin' => 'Sistem Yöneticisi',
        ];

        return $positions[$category] ?? 'İşçi';
    }

    /**
     * Kategoriye göre user rollerini güncelle
     */
    private function updateUserRoles(User $user, string $category): void
    {
        // Mevcut rolleri temizle (admin hariç)
        $user->roles()->detach($user->roles()->where('name', '!=', 'admin')->pluck('id'));

        // Kategoriye göre rol ata
        $roleMap = [
            'worker' => 'employee',
            'foreman' => 'foreman',
            'engineer' => 'employee',
            'manager' => 'project_manager',
            'system_admin' => 'system_admin',
        ];

        if (isset($roleMap[$category])) {
            $user->assignRole($roleMap[$category]);
        }

        // Sistem yöneticisine admin rolü de ver
        if ($category === 'system_admin') {
            $user->assignRole('admin');
        }
    }
}
