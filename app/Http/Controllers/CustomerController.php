<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::query();

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

        $customers = $query->latest()->paginate(15)->withQueryString();

        return Inertia::render('Sales/Customers/Index', [
            'customers' => $customers,
            'filters' => $request->only(['customer_type', 'customer_status', 'search'])
        ]);
    }

    public function create()
    {
        return Inertia::render('Sales/Customers/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_type' => 'required|in:individual,corporate',
            'first_name' => 'required_if:customer_type,individual|nullable|string|max:255',
            'last_name' => 'required_if:customer_type,individual|nullable|string|max:255',
            'tc_number' => 'nullable|string|max:11',
            'passport_number' => 'nullable|string|max:50',
            'company_name' => 'required_if:customer_type,corporate|nullable|string|max:255',
            'tax_office' => 'nullable|string|max:255',
            'tax_number' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'mobile_phone' => 'nullable|string|max:20',
            'work_phone' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'postal_code' => 'nullable|string|max:10',
            'country' => 'nullable|string|max:100',
            'birth_date' => 'nullable|date',
            'birth_place' => 'nullable|string|max:100',
            'gender' => 'nullable|in:male,female,other',
            'marital_status' => 'nullable|in:single,married,divorced,widowed',
            'nationality' => 'nullable|string|max:100',
            'reference_source' => 'nullable|string|max:255',
            'reference_person' => 'nullable|string|max:255',
            'customer_status' => 'required|in:potential,interested,active,inactive,blacklisted',
            'satisfaction_score' => 'nullable|integer|min:1|max:10',
            'notes' => 'nullable|string',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['updated_by'] = auth()->id();

        $customer = Customer::create($validated);

        return redirect()->route('customers.show', $customer)
            ->with('success', 'Müşteri başarıyla oluşturuldu.');
    }

    public function show(Customer $customer)
    {
        return Inertia::render('Sales/Customers/Show', [
            'customer' => $customer->load(['unitSales', 'salePayments'])
        ]);
    }

    public function edit(Customer $customer)
    {
        return Inertia::render('Sales/Customers/Edit', [
            'customer' => $customer
        ]);
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'customer_type' => 'required|in:individual,corporate',
            'first_name' => 'required_if:customer_type,individual|nullable|string|max:255',
            'last_name' => 'required_if:customer_type,individual|nullable|string|max:255',
            'tc_number' => 'nullable|string|max:11',
            'passport_number' => 'nullable|string|max:50',
            'company_name' => 'required_if:customer_type,corporate|nullable|string|max:255',
            'tax_office' => 'nullable|string|max:255',
            'tax_number' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'mobile_phone' => 'nullable|string|max:20',
            'work_phone' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'postal_code' => 'nullable|string|max:10',
            'country' => 'nullable|string|max:100',
            'birth_date' => 'nullable|date',
            'birth_place' => 'nullable|string|max:100',
            'gender' => 'nullable|in:male,female,other',
            'marital_status' => 'nullable|in:single,married,divorced,widowed',
            'nationality' => 'nullable|string|max:100',
            'reference_source' => 'nullable|string|max:255',
            'reference_person' => 'nullable|string|max:255',
            'customer_status' => 'required|in:potential,interested,active,inactive,blacklisted',
            'satisfaction_score' => 'nullable|integer|min:1|max:10',
            'notes' => 'nullable|string',
        ]);

        $validated['updated_by'] = auth()->id();

        $customer->update($validated);

        return redirect()->route('customers.show', $customer)
            ->with('success', 'Müşteri başarıyla güncellendi.');
    }

    public function destroy(Customer $customer)
    {
        // Müşteriye ait aktif satışlar varsa silmeyi engelle
        if ($customer->unitSales()->whereNotIn('status', ['cancelled'])->exists()) {
            return redirect()->back()
                ->with('error', 'Bu müşteriye ait aktif satışlar bulunmaktadır. Önce satışları iptal etmelisiniz.');
        }

        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', 'Müşteri başarıyla silindi.');
    }
}
