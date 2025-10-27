<?php

namespace App\Http\Controllers;

use App\Models\SalePayment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SalePaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = SalePayment::with([
            'unitSale.project',
            'unitSale.projectUnit',
            'customer'
        ]);

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_type')) {
            $query->byPaymentType($request->payment_type);
        }

        if ($request->filled('overdue_only') && $request->overdue_only) {
            $query->overdue();
        }

        if ($request->filled('due_month')) {
            $query->whereYear('due_date', substr($request->due_month, 0, 4))
                ->whereMonth('due_date', substr($request->due_month, 5, 2));
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('payment_number', 'like', "%{$request->search}%")
                    ->orWhereHas('customer', function ($q2) use ($request) {
                        $q2->where('first_name', 'like', "%{$request->search}%")
                            ->orWhere('last_name', 'like', "%{$request->search}%");
                    });
            });
        }

        $salePayments = $query->orderBy('due_date', 'asc')->paginate(15)->withQueryString();

        return Inertia::render('Sales/Payments/Index', [
            'salePayments' => $salePayments,
            'filters' => $request->only(['status', 'payment_type', 'due_month', 'overdue_only', 'search'])
        ]);
    }

    public function create()
    {
        return Inertia::render('Sales/Payments/Create');
    }

    public function store(Request $request)
    {
        // TODO: Implement store logic
    }

    public function show(SalePayment $payment)
    {
        return Inertia::render('Sales/Payments/Show', [
            'payment' => $payment->load(['unitSale.project', 'unitSale.projectUnit', 'customer'])
        ]);
    }

    public function edit(SalePayment $payment)
    {
        return Inertia::render('Sales/Payments/Edit', [
            'payment' => $payment
        ]);
    }

    public function update(Request $request, SalePayment $payment)
    {
        // TODO: Implement update logic
    }

    public function destroy(SalePayment $payment)
    {
        // TODO: Implement destroy logic
    }

    public function markAsPaid(Request $request, SalePayment $payment)
    {
        // TODO: Implement mark as paid logic
    }
}
