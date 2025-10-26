<?php

namespace App\Http\Controllers;

use App\Models\ProgressPayment;
use App\Models\Project;
use App\Models\Subcontractor;
use App\Models\WorkItem;
use App\Models\ProjectStructure;
use App\Models\ProjectFloor;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProgressPaymentController extends Controller
{
    /**
     * Display dashboard with summary statistics
     */
    public function dashboard(): Response
    {
        // Calculate totals manually to avoid computed column issues
        $allPayments = ProgressPayment::all();
        $totalAmount = $allPayments->sum(function($payment) {
            return ($payment->completed_quantity ?? 0) * ($payment->unit_price ?? 0);
        });

        $paidPayments = ProgressPayment::where('status', 'paid')->get();
        $paidAmount = $paidPayments->sum(function($payment) {
            return ($payment->completed_quantity ?? 0) * ($payment->unit_price ?? 0);
        });

        $pendingPayments = ProgressPayment::whereIn('status', ['planned', 'in_progress', 'completed', 'approved'])->get();
        $pendingAmount = $pendingPayments->sum(function($payment) {
            return ($payment->completed_quantity ?? 0) * ($payment->unit_price ?? 0);
        });

        // Summary statistics
        $summary = [
            'total_payments' => ProgressPayment::count(),
            'completed_payments' => ProgressPayment::whereIn('status', ['completed', 'approved', 'paid'])->count(),
            'total_amount' => round($totalAmount, 2),
            'paid_amount' => round($paidAmount, 2),
            'pending_amount' => round($pendingAmount, 2),

            // By status
            'by_status' => ProgressPayment::selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray(),

            // By project
            'by_project' => Project::withCount('progressPayments')
                ->with('progressPayments')
                ->get()
                ->map(function ($project) {
                    $payments = $project->progressPayments;
                    $avgProgress = $payments->isEmpty() ? 0 : round($payments->avg('completion_percentage'), 2);

                    return [
                        'id' => $project->id,
                        'name' => $project->name,
                        'payment_count' => $project->progress_payments_count,
                        'progress' => $avgProgress
                    ];
                })
                ->filter(fn($p) => $p['payment_count'] > 0)
                ->values()
                ->toArray(),

            // By subcontractor
            'by_subcontractor' => Subcontractor::withCount('progressPayments')
                ->with('progressPayments')
                ->get()
                ->map(function ($subcontractor) {
                    $payments = $subcontractor->progressPayments;
                    $avgProgress = $payments->isEmpty() ? 0 : round($payments->avg('completion_percentage'), 2);

                    // Calculate total amount manually from completed_quantity * unit_price
                    $totalAmount = $payments->sum(function($payment) {
                        return ($payment->completed_quantity ?? 0) * ($payment->unit_price ?? 0);
                    });

                    return [
                        'id' => $subcontractor->id,
                        'company_name' => $subcontractor->company_name,
                        'payment_count' => $subcontractor->progress_payments_count,
                        'total_amount' => $totalAmount,
                        'avg_progress' => $avgProgress
                    ];
                })
                ->filter(fn($s) => $s['payment_count'] > 0)
                ->values()
                ->toArray()
        ];

        // Recent payments
        $recentPayments = ProgressPayment::with([
            'project',
            'subcontractor',
            'workItem'
        ])
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'project' => [
                        'id' => $payment->project->id,
                        'name' => $payment->project->name
                    ],
                    'subcontractor' => [
                        'id' => $payment->subcontractor->id,
                        'company_name' => $payment->subcontractor->company_name
                    ],
                    'total_amount' => $payment->total_amount,
                    'status' => $payment->status,
                    'status_label' => $this->getStatusLabel($payment->status),
                    'completion_percentage' => $payment->completion_percentage
                ];
            });

        // Pending approvals
        $pendingApprovals = ProgressPayment::with([
            'project',
            'subcontractor'
        ])
            ->where('status', 'completed')
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'project' => [
                        'id' => $payment->project->id,
                        'name' => $payment->project->name
                    ],
                    'subcontractor' => [
                        'id' => $payment->subcontractor->id,
                        'company_name' => $payment->subcontractor->company_name
                    ],
                    'total_amount' => $payment->total_amount,
                    'status' => $payment->status,
                    'status_label' => $this->getStatusLabel($payment->status),
                    'completion_percentage' => $payment->completion_percentage
                ];
            });

        return Inertia::render('ProgressPayments/Dashboard', [
            'summary' => $summary,
            'recentPayments' => $recentPayments,
            'pendingApprovals' => $pendingApprovals
        ]);
    }

    /**
     * Display a listing of progress payments
     */
    public function index(Request $request): Response
    {
        $query = ProgressPayment::with([
            'project',
            'subcontractor',
            'workItem',
            'projectStructure',
            'projectFloor',
            'approvedBy'
        ]);

        // Filters
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('subcontractor_id')) {
            $query->where('subcontractor_id', $request->subcontractor_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('year')) {
            $query->where('period_year', $request->year);
        }

        if ($request->filled('month')) {
            $query->where('period_month', $request->month);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('project', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('subcontractor', function ($q) use ($search) {
                    $q->where('company_name', 'like', "%{$search}%");
                })
                ->orWhereHas('workItem', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            });
        }

        $progressPayments = $query->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString()
            ->through(fn ($payment) => [
                'id' => $payment->id,
                'project_name' => $payment->project->name,
                'subcontractor_name' => $payment->subcontractor->company_name,
                'work_item_name' => $payment->workItem->name,
                'structure_name' => $payment->projectStructure?->name,
                'floor_display' => $payment->projectFloor?->floor_display,
                'planned_quantity' => $payment->planned_quantity,
                'completed_quantity' => $payment->completed_quantity,
                'unit' => $payment->unit,
                'completion_percentage' => $payment->completion_percentage,
                'unit_price' => $payment->unit_price,
                'total_amount' => $payment->total_amount,
                'status' => $payment->status,
                'status_label' => $this->getStatusLabel($payment->status),
                'period' => $payment->period_year && $payment->period_month
                    ? "{$payment->period_month}/{$payment->period_year}"
                    : null,
                'payment_date' => $payment->payment_date?->format('d.m.Y'),
            ]);

        return Inertia::render('ProgressPayments/Index', [
            'progressPayments' => $progressPayments,
            'filters' => $request->only(['project_id', 'subcontractor_id', 'status', 'year', 'month', 'search']),
            'projects' => Project::active()->get(['id', 'name']),
            'subcontractors' => Subcontractor::active()->get(['id', 'company_name']),
        ]);
    }

    /**
     * Show the form for creating a new progress payment
     */
    public function create(): Response
    {
        return Inertia::render('ProgressPayments/Create', [
            'projects' => Project::active()->with(['structures.floors.units', 'subcontractors'])->get(),
            'subcontractors' => Subcontractor::active()->get(['id', 'company_name']),
            'workItems' => WorkItem::active()->get(['id', 'name', 'unit']),
        ]);
    }

    /**
     * Store a newly created progress payment
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'subcontractor_id' => 'required|exists:subcontractors,id',
            'work_item_id' => 'required|exists:work_items,id',
            'project_structure_id' => 'nullable|exists:project_structures,id',
            'project_floor_id' => 'nullable|exists:project_floors,id',
            'project_unit_id' => 'nullable|exists:project_units,id',
            'planned_quantity' => 'required|numeric|min:0',
            'completed_quantity' => 'required|numeric|min:0',
            'unit' => 'required|string|max:50',
            'unit_price' => 'required|numeric|min:0',
            'payment_date' => 'nullable|date',
            'status' => 'required|in:planned,in_progress,completed,approved,paid',
            'period_year' => 'nullable|integer|min:2000|max:2100',
            'period_month' => 'nullable|integer|min:1|max:12',
            'notes' => 'nullable|string',
        ]);

        // Otomatik dönem ataması
        if (!isset($validated['period_year'])) {
            $validated['period_year'] = now()->year;
            $validated['period_month'] = now()->month;
        }

        $progressPayment = ProgressPayment::create($validated);

        return redirect()->route('progress-payments.show', $progressPayment)
            ->with('success', 'Hakediş kaydı başarıyla oluşturuldu.');
    }

    /**
     * Display the specified progress payment
     */
    public function show(ProgressPayment $progressPayment): Response
    {
        $progressPayment->load([
            'project',
            'subcontractor',
            'workItem',
            'projectStructure',
            'projectFloor',
            'projectUnit',
            'approvedBy'
        ]);

        return Inertia::render('ProgressPayments/Show', [
            'progressPayment' => [
                'id' => $progressPayment->id,
                'project' => [
                    'id' => $progressPayment->project->id,
                    'name' => $progressPayment->project->name,
                ],
                'subcontractor' => [
                    'id' => $progressPayment->subcontractor->id,
                    'company_name' => $progressPayment->subcontractor->company_name,
                ],
                'work_item' => [
                    'id' => $progressPayment->workItem->id,
                    'name' => $progressPayment->workItem->name,
                ],
                'structure' => $progressPayment->projectStructure ? [
                    'id' => $progressPayment->projectStructure->id,
                    'name' => $progressPayment->projectStructure->name,
                ] : null,
                'floor' => $progressPayment->projectFloor ? [
                    'id' => $progressPayment->projectFloor->id,
                    'floor_display' => $progressPayment->projectFloor->floor_display,
                ] : null,
                'project_unit' => $progressPayment->projectUnit ? [
                    'id' => $progressPayment->projectUnit->id,
                    'name' => $progressPayment->projectUnit->name,
                ] : null,
                'planned_quantity' => $progressPayment->planned_quantity,
                'completed_quantity' => $progressPayment->completed_quantity,
                'unit' => $progressPayment->unit,
                'unit_price' => $progressPayment->unit_price,
                'total_amount' => $progressPayment->total_amount,
                'completion_percentage' => $progressPayment->completion_percentage,
                'payment_date' => $progressPayment->payment_date?->format('d.m.Y'),
                'status' => $progressPayment->status,
                'status_label' => $this->getStatusLabel($progressPayment->status),
                'period_year' => $progressPayment->period_year,
                'period_month' => $progressPayment->period_month,
                'notes' => $progressPayment->notes,
                'approved_by' => $progressPayment->approvedBy ? [
                    'id' => $progressPayment->approvedBy->id,
                    'name' => $progressPayment->approvedBy->name,
                ] : null,
                'approved_at' => $progressPayment->approved_at?->format('d.m.Y H:i'),
                'created_at' => $progressPayment->created_at->format('d.m.Y H:i'),
                'updated_at' => $progressPayment->updated_at->format('d.m.Y H:i'),
            ],
        ]);
    }

    /**
     * Show the form for editing the specified progress payment
     */
    public function edit(ProgressPayment $progressPayment): Response
    {
        return Inertia::render('ProgressPayments/Edit', [
            'progressPayment' => $progressPayment->load([
                'project',
                'subcontractor',
                'workItem',
                'projectStructure.floors',
                'projectFloor',
                'projectUnit'
            ]),
            'projects' => Project::active()->with(['structures.floors.units', 'subcontractors'])->get(),
            'subcontractors' => Subcontractor::active()->get(['id', 'company_name']),
            'workItems' => WorkItem::active()->get(['id', 'name', 'unit']),
        ]);
    }

    /**
     * Update the specified progress payment
     */
    public function update(Request $request, ProgressPayment $progressPayment)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'subcontractor_id' => 'required|exists:subcontractors,id',
            'work_item_id' => 'required|exists:work_items,id',
            'project_structure_id' => 'nullable|exists:project_structures,id',
            'project_floor_id' => 'nullable|exists:project_floors,id',
            'project_unit_id' => 'nullable|exists:project_units,id',
            'planned_quantity' => 'required|numeric|min:0',
            'completed_quantity' => 'required|numeric|min:0',
            'unit' => 'required|string|max:50',
            'unit_price' => 'required|numeric|min:0',
            'payment_date' => 'nullable|date',
            'status' => 'required|in:planned,in_progress,completed,approved,paid',
            'period_year' => 'nullable|integer|min:2000|max:2100',
            'period_month' => 'nullable|integer|min:1|max:12',
            'notes' => 'nullable|string',
        ]);

        $progressPayment->update($validated);

        // İlerleme güncelleme metodunu çağır (otomatik statü kontrolü)
        if ($validated['completed_quantity'] != $progressPayment->getOriginal('completed_quantity')) {
            $progressPayment->updateProgress((float) $validated['completed_quantity']);
        }

        return redirect()->route('progress-payments.show', $progressPayment)
            ->with('success', 'Hakediş kaydı başarıyla güncellendi.');
    }

    /**
     * Remove the specified progress payment
     */
    public function destroy(ProgressPayment $progressPayment)
    {
        $progressPayment->delete();

        return redirect()->route('progress-payments.index')
            ->with('success', 'Hakediş kaydı başarıyla silindi.');
    }

    /**
     * Approve progress payment
     */
    public function approve(ProgressPayment $progressPayment)
    {
        if ($progressPayment->status !== 'completed') {
            return back()->with('error', 'Sadece tamamlanmış hakediş kayıtları onaylanabilir.');
        }

        $progressPayment->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Hakediş kaydı onaylandı.');
    }

    /**
     * Mark as paid
     */
    public function markAsPaid(Request $request, ProgressPayment $progressPayment)
    {
        if ($progressPayment->status !== 'approved') {
            return back()->with('error', 'Sadece onaylanmış hakediş kayıtları ödendi olarak işaretlenebilir.');
        }

        $validated = $request->validate([
            'payment_date' => 'required|date',
        ]);

        $progressPayment->update([
            'status' => 'paid',
            'payment_date' => $validated['payment_date'],
        ]);

        // Fire event to create financial transaction
        event(new \App\Events\ProgressPaymentPaidEvent($progressPayment));

        return back()->with('success', 'Hakediş kaydı ödendi olarak işaretlendi.');
    }

    /**
     * Get progress summary for a project
     */
    public function projectProgress(Project $project)
    {
        return response()->json($project->getProgressSummary());
    }

    /**
     * Get progress summary for a structure
     */
    public function structureProgress(ProjectStructure $structure)
    {
        return response()->json($structure->getProgressSummary());
    }

    /**
     * Get progress summary for a floor
     */
    public function floorProgress(ProjectFloor $floor)
    {
        return response()->json($floor->getProgressSummary());
    }

    /**
     * Helper method to get status label
     */
    private function getStatusLabel(string $status): string
    {
        return match($status) {
            'planned' => 'Planlandı',
            'in_progress' => 'Devam Ediyor',
            'completed' => 'Tamamlandı',
            'approved' => 'Onaylandı',
            'paid' => 'Ödendi',
            default => $status,
        };
    }
}
