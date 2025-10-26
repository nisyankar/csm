<?php

namespace App\Http\Controllers;

use App\Models\Quantity;
use App\Models\Project;
use App\Models\WorkItem;
use App\Models\ProjectStructure;
use App\Models\ProjectFloor;
use App\Models\ProjectUnit;
use App\Services\QuantityService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class QuantityController extends Controller
{
    protected QuantityService $quantityService;

    public function __construct(QuantityService $quantityService)
    {
        $this->quantityService = $quantityService;
    }

    /**
     * Dashboard - Keşif & Metraj özet istatistikleri
     */
    public function dashboard(): Response
    {
        // Tüm projeler için özet istatistikler
        $allQuantities = Quantity::with(['project', 'workItem'])->get();

        $summary = [
            'total_measurements' => $allQuantities->count(),
            'total_planned_quantity' => $allQuantities->sum('planned_quantity'),
            'total_completed_quantity' => $allQuantities->sum('completed_quantity'),
            'total_remaining_quantity' => $allQuantities->sum('remaining_quantity'),
            'verified_count' => $allQuantities->filter->isVerified()->count(),
            'approved_count' => $allQuantities->filter->isApproved()->count(),
            'pending_verification' => $allQuantities->filter(fn($q) => !$q->isVerified())->count(),
            'completion_percentage' => $allQuantities->sum('planned_quantity') > 0
                ? round(($allQuantities->sum('completed_quantity') / $allQuantities->sum('planned_quantity')) * 100, 2)
                : 0,
        ];

        // Proje bazında istatistikler
        $byProject = Project::withCount('quantities')
            ->with('quantities')
            ->get()
            ->map(function ($project) {
                $quantities = $project->quantities;
                $totalPlanned = $quantities->sum('planned_quantity');
                $totalCompleted = $quantities->sum('completed_quantity');

                return [
                    'id' => $project->id,
                    'name' => $project->name,
                    'measurement_count' => $project->quantities_count,
                    'total_planned' => $totalPlanned,
                    'total_completed' => $totalCompleted,
                    'completion_percentage' => $totalPlanned > 0
                        ? round(($totalCompleted / $totalPlanned) * 100, 2)
                        : 0,
                ];
            })
            ->filter(fn($p) => $p['measurement_count'] > 0)
            ->values()
            ->toArray();

        // Son ölçümler
        $recentMeasurements = Quantity::with([
            'project',
            'workItem',
            'projectStructure',
            'projectFloor',
            'verifiedBy'
        ])
            ->orderBy('measurement_date', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($quantity) {
                return [
                    'id' => $quantity->id,
                    'project' => [
                        'id' => $quantity->project->id,
                        'name' => $quantity->project->name,
                    ],
                    'work_item' => [
                        'id' => $quantity->workItem->id,
                        'name' => $quantity->workItem->name,
                        'code' => $quantity->workItem->code,
                    ],
                    'location' => $this->getLocationLabel($quantity),
                    'planned_quantity' => $quantity->planned_quantity,
                    'completed_quantity' => $quantity->completed_quantity,
                    'remaining_quantity' => $quantity->remaining_quantity,
                    'unit' => $quantity->unit,
                    'completion_percentage' => $quantity->completion_percentage,
                    'measurement_date' => $quantity->measurement_date?->format('d.m.Y'),
                    'is_verified' => $quantity->isVerified(),
                    'is_approved' => $quantity->isApproved(),
                    'verified_by' => $quantity->verifiedBy?->name,
                ];
            });

        // Doğrulama bekleyenler
        $pendingVerification = Quantity::with([
            'project',
            'workItem'
        ])
            ->whereNull('verified_by')
            ->orderBy('measurement_date', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($quantity) {
                return [
                    'id' => $quantity->id,
                    'project' => [
                        'id' => $quantity->project->id,
                        'name' => $quantity->project->name,
                    ],
                    'work_item' => [
                        'id' => $quantity->workItem->id,
                        'name' => $quantity->workItem->name,
                    ],
                    'location' => $this->getLocationLabel($quantity),
                    'completed_quantity' => $quantity->completed_quantity,
                    'unit' => $quantity->unit,
                    'measurement_date' => $quantity->measurement_date?->format('d.m.Y'),
                ];
            });

        return Inertia::render('Quantities/Dashboard', [
            'summary' => $summary,
            'byProject' => $byProject,
            'recentMeasurements' => $recentMeasurements,
            'pendingVerification' => $pendingVerification,
        ]);
    }

    /**
     * Display a listing of quantities
     */
    public function index(Request $request): Response
    {
        $query = Quantity::with([
            'project',
            'workItem',
            'projectStructure',
            'projectFloor',
            'projectUnit',
            'verifiedBy',
            'approvedBy'
        ]);

        // Filtreler
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('work_item_id')) {
            $query->where('work_item_id', $request->work_item_id);
        }

        if ($request->filled('structure_id')) {
            $query->where('project_structure_id', $request->structure_id);
        }

        if ($request->filled('floor_id')) {
            $query->where('project_floor_id', $request->floor_id);
        }

        if ($request->filled('status')) {
            switch ($request->status) {
                case 'pending':
                    $query->whereNull('verified_by');
                    break;
                case 'verified':
                    $query->whereNotNull('verified_by')->whereNull('approved_by');
                    break;
                case 'approved':
                    $query->whereNotNull('approved_by');
                    break;
                case 'completed':
                    $query->whereRaw('completed_quantity >= planned_quantity');
                    break;
            }
        }

        // Arama
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('project', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })->orWhereHas('workItem', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $quantities = $query->orderBy('measurement_date', 'desc')
            ->paginate(15)
            ->withQueryString()
            ->through(fn($quantity) => [
                'id' => $quantity->id,
                'project' => [
                    'id' => $quantity->project->id,
                    'name' => $quantity->project->name,
                ],
                'work_item' => [
                    'id' => $quantity->workItem->id,
                    'name' => $quantity->workItem->name,
                    'code' => $quantity->workItem->code,
                ],
                'location' => $this->getLocationLabel($quantity),
                'planned_quantity' => $quantity->planned_quantity,
                'completed_quantity' => $quantity->completed_quantity,
                'remaining_quantity' => $quantity->remaining_quantity,
                'unit' => $quantity->unit,
                'completion_percentage' => $quantity->completion_percentage,
                'measurement_date' => $quantity->measurement_date?->format('d.m.Y'),
                'measurement_method' => $quantity->measurement_method,
                'is_verified' => $quantity->isVerified(),
                'is_approved' => $quantity->isApproved(),
                'verified_by' => $quantity->verifiedBy?->name,
                'approved_by' => $quantity->approvedBy?->name,
            ]);

        // İstatistikler
        $stats = [
            'total' => Quantity::count(),
            'pending' => Quantity::whereNull('verified_by')->count(),
            'verified' => Quantity::whereNotNull('verified_by')->whereNull('approved_by')->count(),
            'approved' => Quantity::whereNotNull('approved_by')->count(),
            'completed' => Quantity::whereRaw('completed_quantity >= planned_quantity')->count(),
        ];

        return Inertia::render('Quantities/Index', [
            'quantities' => $quantities,
            'stats' => $stats,
            'filters' => $request->only(['project_id', 'work_item_id', 'structure_id', 'floor_id', 'status', 'search']),
            'projects' => Project::select('id', 'name')->orderBy('name')->get(),
            'workItems' => WorkItem::select('id', 'name', 'code', 'unit')->orderBy('name')->get(),
        ]);
    }

    /**
     * Show the form for creating a new quantity
     */
    public function create(): Response
    {
        return Inertia::render('Quantities/Create', [
            'projects' => Project::select('id', 'name')->orderBy('name')->get(),
            'workItems' => WorkItem::select('id', 'name', 'code', 'unit')->orderBy('name')->get(),
            'structures' => ProjectStructure::select('id', 'project_id', 'name')->orderBy('name')->get(),
            'floors' => ProjectFloor::select('id', 'structure_id as project_structure_id', 'name')->orderBy('floor_number')->get(),
            'units' => ProjectUnit::select('id', 'floor_id', 'unit_code')->get()
                ->map(function ($unit) {
                    return [
                        'id' => $unit->id,
                        'project_floor_id' => $unit->floor_id,
                        'name' => $unit->name, // accessor'dan gelir
                    ];
                }),
        ]);
    }

    /**
     * Store a newly created quantity
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'work_item_id' => 'required|exists:work_items,id',
            'project_structure_id' => 'nullable|exists:project_structures,id',
            'project_floor_id' => 'nullable|exists:project_floors,id',
            'project_unit_id' => 'nullable|exists:project_units,id',
            'planned_quantity' => 'required|numeric|min:0',
            'completed_quantity' => 'required|numeric|min:0',
            'unit' => 'required|string|max:50',
            'measurement_date' => 'nullable|date',
            'measurement_method' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $validated['measurement_date'] = $validated['measurement_date'] ?? now();

        Quantity::create($validated);

        return redirect()->route('quantities.index')
            ->with('success', 'Metraj kaydı başarıyla oluşturuldu.');
    }

    /**
     * Display the specified quantity
     */
    public function show(Quantity $quantity): Response
    {
        $quantity->load([
            'project',
            'workItem',
            'projectStructure',
            'projectFloor',
            'projectUnit',
            'verifiedBy',
            'approvedBy',
            'progressPayments.subcontractor'
        ]);

        // İlişkili hakediş kayıtlarını formatla
        $relatedPayments = $quantity->progressPayments->map(function ($payment) {
            return [
                'id' => $payment->id,
                'subcontractor' => $payment->subcontractor->company_name,
                'completed_quantity' => $payment->completed_quantity,
                'unit_price' => $payment->unit_price,
                'total_amount' => $payment->total_amount,
                'status' => $payment->status,
                'payment_date' => $payment->payment_date?->format('d.m.Y'),
                'period' => $payment->period_month . '/' . $payment->period_year,
            ];
        });

        return Inertia::render('Quantities/Show', [
            'quantity' => [
                'id' => $quantity->id,
                'project' => [
                    'id' => $quantity->project->id,
                    'name' => $quantity->project->name,
                ],
                'work_item' => [
                    'id' => $quantity->workItem->id,
                    'name' => $quantity->workItem->name,
                    'code' => $quantity->workItem->code,
                ],
                'structure' => $quantity->projectStructure ? [
                    'id' => $quantity->projectStructure->id,
                    'name' => $quantity->projectStructure->name,
                ] : null,
                'floor' => $quantity->projectFloor ? [
                    'id' => $quantity->projectFloor->id,
                    'name' => $quantity->projectFloor->name,
                ] : null,
                'unit_info' => $quantity->projectUnit ? [
                    'id' => $quantity->projectUnit->id,
                    'name' => $quantity->projectUnit->name,
                ] : null,
                'planned_quantity' => $quantity->planned_quantity,
                'completed_quantity' => $quantity->completed_quantity,
                'remaining_quantity' => $quantity->remaining_quantity,
                'unit' => $quantity->unit,
                'completion_percentage' => $quantity->completion_percentage,
                'measurement_date' => $quantity->measurement_date?->format('d.m.Y'),
                'measurement_method' => $quantity->measurement_method,
                'is_verified' => $quantity->isVerified(),
                'is_approved' => $quantity->isApproved(),
                'verified_by' => $quantity->verifiedBy?->name,
                'verified_at' => $quantity->verified_at?->format('d.m.Y H:i'),
                'approved_by' => $quantity->approvedBy?->name,
                'approved_at' => $quantity->approved_at?->format('d.m.Y H:i'),
                'notes' => $quantity->notes,
                'created_at' => $quantity->created_at->format('d.m.Y H:i'),
                'updated_at' => $quantity->updated_at->format('d.m.Y H:i'),
            ],
            'relatedPayments' => $relatedPayments,
        ]);
    }

    /**
     * Show the form for editing the specified quantity
     */
    public function edit(Quantity $quantity): Response
    {
        return Inertia::render('Quantities/Edit', [
            'quantity' => [
                'id' => $quantity->id,
                'project_id' => $quantity->project_id,
                'work_item_id' => $quantity->work_item_id,
                'project_structure_id' => $quantity->project_structure_id,
                'project_floor_id' => $quantity->project_floor_id,
                'project_unit_id' => $quantity->project_unit_id,
                'planned_quantity' => $quantity->planned_quantity,
                'completed_quantity' => $quantity->completed_quantity,
                'unit' => $quantity->unit,
                'measurement_date' => $quantity->measurement_date?->format('Y-m-d'),
                'measurement_method' => $quantity->measurement_method,
                'notes' => $quantity->notes,
            ],
            'projects' => Project::select('id', 'name')->orderBy('name')->get(),
            'workItems' => WorkItem::select('id', 'name', 'code', 'unit')->orderBy('name')->get(),
            'structures' => ProjectStructure::select('id', 'project_id', 'name')->orderBy('name')->get(),
            'floors' => ProjectFloor::select('id', 'structure_id as project_structure_id', 'name')->orderBy('floor_number')->get(),
            'units' => ProjectUnit::select('id', 'floor_id', 'unit_code')->get()
                ->map(function ($unit) {
                    return [
                        'id' => $unit->id,
                        'project_floor_id' => $unit->floor_id,
                        'name' => $unit->name, // accessor'dan gelir
                    ];
                }),
        ]);
    }

    /**
     * Update the specified quantity
     */
    public function update(Request $request, Quantity $quantity): RedirectResponse
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'work_item_id' => 'required|exists:work_items,id',
            'project_structure_id' => 'nullable|exists:project_structures,id',
            'project_floor_id' => 'nullable|exists:project_floors,id',
            'project_unit_id' => 'nullable|exists:project_units,id',
            'planned_quantity' => 'required|numeric|min:0',
            'completed_quantity' => 'required|numeric|min:0',
            'unit' => 'required|string|max:50',
            'measurement_date' => 'nullable|date',
            'measurement_method' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $quantity->update($validated);

        return redirect()->route('quantities.index')
            ->with('success', 'Metraj kaydı başarıyla güncellendi.');
    }

    /**
     * Remove the specified quantity
     */
    public function destroy(Quantity $quantity): RedirectResponse
    {
        $quantity->delete();

        return redirect()->route('quantities.index')
            ->with('success', 'Metraj kaydı başarıyla silindi.');
    }

    /**
     * Metraj doğrulama
     */
    public function verify(Quantity $quantity): RedirectResponse
    {
        if ($quantity->isVerified()) {
            return back()->with('error', 'Bu metraj zaten doğrulanmış.');
        }

        $quantity->verify(Auth::id());

        return back()->with('success', 'Metraj başarıyla doğrulandı.');
    }

    /**
     * Metraj onaylama
     */
    public function approve(Quantity $quantity): RedirectResponse
    {
        if (!$quantity->isVerified()) {
            return back()->with('error', 'Metraj önce doğrulanmalıdır.');
        }

        if ($quantity->isApproved()) {
            return back()->with('error', 'Bu metraj zaten onaylanmış.');
        }

        $quantity->approve(Auth::id());

        return back()->with('success', 'Metraj başarıyla onaylandı.');
    }

    /**
     * Hakediş için metraj ara (API endpoint)
     */
    public function search(Request $request)
    {
        $query = Quantity::query();

        // Lokasyon filtreleme
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('structure_id')) {
            $query->where('project_structure_id', $request->structure_id);
        }

        if ($request->filled('floor_id')) {
            $query->where('project_floor_id', $request->floor_id);
        }

        if ($request->filled('unit_id')) {
            $query->where('project_unit_id', $request->unit_id);
        }

        // İş kalemi filtreleme
        if ($request->filled('work_item_id')) {
            $query->where('work_item_id', $request->work_item_id);
        }

        // İlk eşleşen metraj kaydını bul
        $quantity = $query->with('progressPayments')->first();

        if (!$quantity) {
            return response()->json([
                'found' => false,
                'message' => 'Bu lokasyon ve iş kalemi için metraj kaydı bulunamadı.'
            ], 404);
        }

        // Daha önce hakediş yapılan toplam miktarı hesapla
        $totalInvoiced = $quantity->progressPayments()
            ->whereIn('status', ['completed', 'approved', 'paid'])
            ->sum('completed_quantity');

        // Hakediş yapılabilir kalan miktar
        $availableToInvoice = max(0, $quantity->completed_quantity - $totalInvoiced);

        return response()->json([
            'found' => true,
            'quantity' => [
                'id' => $quantity->id,
                'project_id' => $quantity->project_id,
                'work_item_id' => $quantity->work_item_id,
                'planned_quantity' => $quantity->planned_quantity,
                'completed_quantity' => $quantity->completed_quantity,
                'remaining_quantity' => $quantity->remaining_quantity,
                'unit' => $quantity->unit,
                'completion_percentage' => $quantity->completion_percentage,
                'total_invoiced' => $totalInvoiced,
                'available_to_invoice' => $availableToInvoice,
                'is_verified' => $quantity->isVerified(),
                'is_approved' => $quantity->isApproved(),
            ]
        ]);
    }

    /**
     * Lokasyon etiketi oluştur
     */
    private function getLocationLabel(Quantity $quantity): string
    {
        $parts = [];

        if ($quantity->projectStructure) {
            $parts[] = $quantity->projectStructure->name;
        }

        if ($quantity->projectFloor) {
            $parts[] = $quantity->projectFloor->name;
        }

        if ($quantity->projectUnit) {
            $parts[] = $quantity->projectUnit->name;
        }

        return empty($parts) ? '-' : implode(' / ', $parts);
    }
}
