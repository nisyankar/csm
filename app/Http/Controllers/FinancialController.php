<?php

namespace App\Http\Controllers;

use App\Models\FinancialTransaction;
use App\Models\IncomeCategory;
use App\Models\ExpenseCategory;
use App\Models\Project;
use App\Services\Financial\FinancialTransactionService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FinancialController extends Controller
{
    protected FinancialTransactionService $service;

    public function __construct(FinancialTransactionService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of financial transactions
     */
    public function index(Request $request): Response
    {
        $query = FinancialTransaction::with([
            'project:id,name',
            'incomeCategory:id,name,code',
            'expenseCategory:id,name,code',
            'createdBy:id,name',
            'approvedBy:id,name'
        ]);

        // Filters
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('transaction_type')) {
            $query->where('transaction_type', $request->transaction_type);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('transaction_date', [
                $request->start_date,
                $request->end_date
            ]);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('invoice_number', 'like', "%{$search}%")
                  ->orWhere('accounting_code', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'transaction_date');
        $sortDir = $request->get('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        $transactions = $query->paginate(15)->withQueryString();

        // Get filter options
        $projects = Project::select('id', 'name')->where('status', 'active')->get();
        $incomeCategories = IncomeCategory::select('id', 'name', 'code')->where('is_active', true)->get();
        $expenseCategories = ExpenseCategory::select('id', 'name', 'code')->where('is_active', true)->get();

        return Inertia::render('Financial/Index', [
            'transactions' => $transactions,
            'filters' => $request->only(['project_id', 'transaction_type', 'category_id', 'payment_status', 'start_date', 'end_date', 'search']),
            'projects' => $projects,
            'incomeCategories' => $incomeCategories,
            'expenseCategories' => $expenseCategories,
        ]);
    }

    /**
     * Show the form for creating a new transaction
     */
    public function create(): Response
    {
        $projects = Project::select('id', 'name')->where('status', 'active')->get();
        $incomeCategories = IncomeCategory::where('is_active', true)->get();
        $expenseCategories = ExpenseCategory::where('is_active', true)->get();

        return Inertia::render('Financial/Create', [
            'projects' => $projects,
            'incomeCategories' => $incomeCategories,
            'expenseCategories' => $expenseCategories,
        ]);
    }

    /**
     * Display the specified transaction
     */
    public function show(FinancialTransaction $transaction): Response
    {
        $transaction->load([
            'project',
            'incomeCategory',
            'expenseCategory',
            'createdBy',
            'approvedBy'
        ]);

        return Inertia::render('Financial/Show', [
            'transaction' => $transaction,
        ]);
    }

    /**
     * Show the form for editing the specified transaction
     */
    public function edit(FinancialTransaction $transaction): Response
    {
        $transaction->load([
            'project',
            'incomeCategory',
            'expenseCategory'
        ]);

        $projects = Project::select('id', 'name')->where('status', 'active')->get();
        $incomeCategories = IncomeCategory::where('is_active', true)->get();
        $expenseCategories = ExpenseCategory::where('is_active', true)->get();

        return Inertia::render('Financial/Edit', [
            'transaction' => $transaction,
            'projects' => $projects,
            'incomeCategories' => $incomeCategories,
            'expenseCategories' => $expenseCategories,
        ]);
    }

    /**
     * Display profit/loss report
     */
    public function profitLoss(Request $request): Response
    {
        $projectId = $request->get('project_id');
        $year = $request->get('year', now()->year);
        $month = $request->get('month');

        $summary = $this->service->getProfitLossSummary($projectId, $year, $month);
        $categoryBreakdown = $this->service->getCategoryBreakdown($projectId, $year, $month);

        $projects = Project::select('id', 'name')->where('status', 'active')->get();

        return Inertia::render('Financial/ProfitLoss', [
            'summary' => $summary,
            'categoryBreakdown' => $categoryBreakdown,
            'projects' => $projects,
            'filters' => compact('projectId', 'year', 'month'),
        ]);
    }

    /**
     * Display financial dashboard
     */
    public function dashboard(Request $request): Response
    {
        $projectId = $request->get('project_id');
        $year = now()->year;

        // Get summary for current year
        $summary = $this->service->getProfitLossSummary($projectId, $year);

        // Get monthly data for charts
        $monthlyData = [];
        for ($month = 1; $month <= 12; $month++) {
            $monthlyData[] = $this->service->getProfitLossSummary($projectId, $year, $month);
        }

        // Get recent transactions
        $recentTransactions = FinancialTransaction::with([
            'project:id,name',
            'incomeCategory:id,name',
            'expenseCategory:id,name'
        ])
            ->when($projectId, fn($q) => $q->where('project_id', $projectId))
            ->orderBy('transaction_date', 'desc')
            ->limit(10)
            ->get();

        // Get pending payments
        $pendingPayments = FinancialTransaction::with([
            'project:id,name',
            'expenseCategory:id,name'
        ])
            ->when($projectId, fn($q) => $q->where('project_id', $projectId))
            ->where('payment_status', '!=', 'paid')
            ->where('transaction_type', 'expense')
            ->orderBy('transaction_date', 'asc')
            ->limit(10)
            ->get();

        $projects = Project::select('id', 'name')->where('status', 'active')->get();

        return Inertia::render('Financial/Dashboard', [
            'summary' => $summary,
            'monthlyData' => $monthlyData,
            'recentTransactions' => $recentTransactions,
            'pendingPayments' => $pendingPayments,
            'projects' => $projects,
            'selectedProjectId' => $projectId,
        ]);
    }
}
