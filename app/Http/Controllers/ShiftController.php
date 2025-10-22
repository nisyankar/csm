<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ShiftController extends Controller
{
    public function index()
    {
        $shifts = Shift::orderBy('sort_order')->paginate(50);

        return Inertia::render('Shifts/Index', [
            'shifts' => $shifts,
        ]);
    }

    public function create()
    {
        return Inertia::render('Shifts/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:10|unique:shifts,code',
            'shift_type' => 'required|string|max:50',
            'daily_hours' => 'required|numeric|min:0|max:24',
            'overtime_multiplier' => 'required|numeric|min:0|max:10',
            'is_paid' => 'required|boolean',
            'counts_as_work_day' => 'required|boolean',
            'is_active' => 'required|boolean',
            'sort_order' => 'nullable|integer',
            'description' => 'nullable|string|max:500',
        ]);

        Shift::create($validated);

        return redirect()
            ->route('shifts.index')
            ->with('success', 'Vardiya oluşturuldu.');
    }

    public function show(Shift $shift)
    {
        return Inertia::render('Shifts/Show', [
            'shift' => $shift,
        ]);
    }

    public function edit(Shift $shift)
    {
        return Inertia::render('Shifts/Edit', [
            'shift' => $shift,
        ]);
    }

    public function update(Request $request, Shift $shift)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:10|unique:shifts,code,' . $shift->id,
            'shift_type' => 'required|string|max:50',
            'daily_hours' => 'required|numeric|min:0|max:24',
            'overtime_multiplier' => 'required|numeric|min:0|max:10',
            'is_paid' => 'required|boolean',
            'counts_as_work_day' => 'required|boolean',
            'is_active' => 'required|boolean',
            'sort_order' => 'nullable|integer',
            'description' => 'nullable|string|max:500',
        ]);

        $shift->update($validated);

        return redirect()
            ->route('shifts.index')
            ->with('success', 'Vardiya güncellendi.');
    }

    public function destroy(Shift $shift)
    {
        $shift->delete();

        return redirect()
            ->route('shifts.index')
            ->with('success', 'Vardiya silindi.');
    }

    /**
     * Aktif vardiyaları getir (API)
     */
    public function getActive()
    {
        return Shift::active()->orderBy('sort_order')->get();
    }
}
