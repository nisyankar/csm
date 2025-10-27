<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    /**
     * Display a listing of customers
     */
    public function index(Request $request)
    {
        $query = Customer::with(['unitSales', 'salePayments']);

        // Filters
        if ($request->filled('customer_type')) {
            $query->where('customer_type', $request->customer_type);
        }

        if ($request->filled('customer_status')) {
            $query->where('customer_status', $request->customer_status);
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Sorting
        $sortField = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $customers = $query->paginate($request->get('per_page', 15));

        // Add computed fields
        $customers->getCollection()->transform(function ($customer) {
            $customer->total_purchases = $customer->getTotalPurchaseAmount();
            $customer->total_paid = $customer->getTotalPaidAmount();
            $customer->total_outstanding = $customer->getTotalOutstandingAmount();
            return $customer;
        });

        return response()->json([
            'success' => true,
            'data' => $customers->items(),
            'meta' => [
                'current_page' => $customers->currentPage(),
                'last_page' => $customers->lastPage(),
                'per_page' => $customers->perPage(),
                'total' => $customers->total(),
            ],
            'links' => [
                'first' => $customers->url(1),
                'last' => $customers->url($customers->lastPage()),
                'prev' => $customers->previousPageUrl(),
                'next' => $customers->nextPageUrl(),
            ],
        ]);
    }

    /**
     * Store a new customer
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email',
            'phone' => 'required|string|max:20',
            'customer_type' => 'required|in:individual,corporate',
            'tc_number' => 'nullable|string|size:11|unique:customers,tc_number',
            'company_name' => 'required_if:customer_type,corporate',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $customer = Customer::create(array_merge(
            $request->all(),
            ['created_by' => Auth::id()]
        ));

        return response()->json([
            'success' => true,
            'message' => 'Müşteri başarıyla oluşturuldu.',
            'data' => $customer,
        ], 201);
    }

    /**
     * Display the specified customer
     */
    public function show(Customer $customer)
    {
        $customer->load([
            'unitSales.project',
            'unitSales.projectUnit',
            'salePayments' => function ($query) {
                $query->orderBy('due_date', 'desc');
            }
        ]);

        $customer->total_purchases = $customer->getTotalPurchaseAmount();
        $customer->total_paid = $customer->getTotalPaidAmount();
        $customer->total_outstanding = $customer->getTotalOutstandingAmount();
        $customer->has_overdue_payments = $customer->hasOverduePayments();

        return response()->json([
            'success' => true,
            'data' => $customer,
        ]);
    }

    /**
     * Update the specified customer
     */
    public function update(Request $request, Customer $customer)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'sometimes|required|string|max:255',
            'last_name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:customers,email,' . $customer->id,
            'phone' => 'sometimes|required|string|max:20',
            'tc_number' => 'nullable|string|size:11|unique:customers,tc_number,' . $customer->id,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $customer->update(array_merge(
            $request->all(),
            ['updated_by' => Auth::id()]
        ));

        return response()->json([
            'success' => true,
            'message' => 'Müşteri başarıyla güncellendi.',
            'data' => $customer,
        ]);
    }

    /**
     * Remove the specified customer
     */
    public function destroy(Customer $customer)
    {
        // Check if customer has active sales
        if ($customer->unitSales()->whereNotIn('status', ['cancelled'])->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Aktif satışı olan müşteri silinemez.',
            ], 422);
        }

        $customer->delete();

        return response()->json([
            'success' => true,
            'message' => 'Müşteri başarıyla silindi.',
        ]);
    }

    /**
     * Get customer statistics
     */
    public function stats()
    {
        $stats = [
            'total_customers' => Customer::count(),
            'active_customers' => Customer::active()->count(),
            'potential_customers' => Customer::potential()->count(),
            'interested_customers' => Customer::interested()->count(),
            'individual_customers' => Customer::individual()->count(),
            'corporate_customers' => Customer::corporate()->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Activate customer
     */
    public function activate(Customer $customer)
    {
        $customer->activate();

        return response()->json([
            'success' => true,
            'message' => 'Müşteri aktif hale getirildi.',
            'data' => $customer,
        ]);
    }

    /**
     * Deactivate customer
     */
    public function deactivate(Customer $customer)
    {
        $customer->deactivate();

        return response()->json([
            'success' => true,
            'message' => 'Müşteri pasif hale getirildi.',
            'data' => $customer,
        ]);
    }

    /**
     * Blacklist customer
     */
    public function blacklist(Customer $customer)
    {
        $customer->blacklist();

        return response()->json([
            'success' => true,
            'message' => 'Müşteri kara listeye eklendi.',
            'data' => $customer,
        ]);
    }
}
