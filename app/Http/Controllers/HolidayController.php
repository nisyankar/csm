<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class HolidayController extends Controller
{
    /**
     * Resmi tatillerin listesi (API endpoint)
     */
    public function index(Request $request)
    {
        $year = $request->get('year', now()->year);

        $holidays = Holiday::forYear($year)
            ->orderBy('date')
            ->get();

        return response()->json([
            'holidays' => $holidays,
            'year' => $year,
        ]);
    }

    /**
     * Yeni resmi tatil ekle
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'type' => 'required|in:national,religious,other',
            'is_half_day' => 'boolean',
            'half_day_start' => 'nullable|date_format:H:i',
            'description' => 'nullable|string|max:1000',
            'is_paid' => 'boolean',
            'is_active' => 'boolean',
        ]);

        // Yıl bilgisini tarihten çıkar
        $validated['year'] = Carbon::parse($validated['date'])->year;

        // Yarım gün değilse half_day_start'ı null yap
        if (empty($validated['is_half_day'])) {
            $validated['half_day_start'] = null;
        }

        $holiday = Holiday::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Resmi tatil başarıyla eklendi.',
            'holiday' => $holiday,
        ]);
    }

    /**
     * Resmi tatil güncelle
     */
    public function update(Request $request, Holiday $holiday)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'type' => 'required|in:national,religious,other',
            'is_half_day' => 'boolean',
            'half_day_start' => 'nullable|date_format:H:i',
            'description' => 'nullable|string|max:1000',
            'is_paid' => 'boolean',
            'is_active' => 'boolean',
        ]);

        // Yıl bilgisini tarihten çıkar
        $validated['year'] = Carbon::parse($validated['date'])->year;

        // Yarım gün değilse half_day_start'ı null yap
        if (empty($validated['is_half_day'])) {
            $validated['half_day_start'] = null;
        }

        $holiday->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Resmi tatil başarıyla güncellendi.',
            'holiday' => $holiday->fresh(),
        ]);
    }

    /**
     * Resmi tatil sil
     */
    public function destroy(Holiday $holiday)
    {
        $holiday->delete();

        return response()->json([
            'success' => true,
            'message' => 'Resmi tatil silindi.',
        ]);
    }

    /**
     * Belirli bir tarih aralığındaki tatilleri getir
     */
    public function getHolidaysInRange(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $holidays = Holiday::active()
            ->betweenDates($validated['start_date'], $validated['end_date'])
            ->orderBy('date')
            ->get();

        return response()->json([
            'holidays' => $holidays,
            'count' => $holidays->count(),
        ]);
    }

    /**
     * 2025 yılı Türkiye resmi tatillerini otomatik ekle
     */
    public function seed2025Holidays()
    {
        $holidays = [
            // Ulusal Bayramlar
            ['name' => 'Yılbaşı', 'date' => '2025-01-01', 'type' => 'national', 'is_half_day' => false],
            ['name' => '23 Nisan Ulusal Egemenlik ve Çocuk Bayramı', 'date' => '2025-04-23', 'type' => 'national', 'is_half_day' => false],
            ['name' => '1 Mayıs Emek ve Dayanışma Günü', 'date' => '2025-05-01', 'type' => 'national', 'is_half_day' => false],
            ['name' => '19 Mayıs Atatürk\'ü Anma, Gençlik ve Spor Bayramı', 'date' => '2025-05-19', 'type' => 'national', 'is_half_day' => false],
            ['name' => '15 Temmuz Demokrasi ve Milli Birlik Günü', 'date' => '2025-07-15', 'type' => 'national', 'is_half_day' => false],
            ['name' => '30 Ağustos Zafer Bayramı', 'date' => '2025-08-30', 'type' => 'national', 'is_half_day' => false],
            ['name' => '29 Ekim Cumhuriyet Bayramı', 'date' => '2025-10-28', 'type' => 'national', 'is_half_day' => false], // Çarşamba
            ['name' => '29 Ekim Cumhuriyet Bayramı', 'date' => '2025-10-29', 'type' => 'national', 'is_half_day' => false],

            // Dini Bayramlar 2025 (Tahmini - kesin tarihler açıklanınca güncellenecek)
            ['name' => 'Ramazan Bayramı Arefesi', 'date' => '2025-03-30', 'type' => 'religious', 'is_half_day' => true, 'half_day_start' => '13:00'],
            ['name' => 'Ramazan Bayramı 1. Gün', 'date' => '2025-03-31', 'type' => 'religious', 'is_half_day' => false],
            ['name' => 'Ramazan Bayramı 2. Gün', 'date' => '2025-04-01', 'type' => 'religious', 'is_half_day' => false],
            ['name' => 'Ramazan Bayramı 3. Gün', 'date' => '2025-04-02', 'type' => 'religious', 'is_half_day' => false],

            ['name' => 'Kurban Bayramı Arefesi', 'date' => '2025-06-06', 'type' => 'religious', 'is_half_day' => true, 'half_day_start' => '13:00'],
            ['name' => 'Kurban Bayramı 1. Gün', 'date' => '2025-06-07', 'type' => 'religious', 'is_half_day' => false],
            ['name' => 'Kurban Bayramı 2. Gün', 'date' => '2025-06-08', 'type' => 'religious', 'is_half_day' => false],
            ['name' => 'Kurban Bayramı 3. Gün', 'date' => '2025-06-09', 'type' => 'religious', 'is_half_day' => false],
            ['name' => 'Kurban Bayramı 4. Gün', 'date' => '2025-06-10', 'type' => 'religious', 'is_half_day' => false],
        ];

        $created = 0;
        foreach ($holidays as $holidayData) {
            $holidayData['year'] = 2025;
            $holidayData['is_paid'] = true;
            $holidayData['is_active'] = true;

            // Eğer yarım gün değilse half_day_start null olsun
            if (!isset($holidayData['is_half_day']) || !$holidayData['is_half_day']) {
                $holidayData['half_day_start'] = null;
            }

            Holiday::firstOrCreate(
                ['date' => $holidayData['date'], 'name' => $holidayData['name']],
                $holidayData
            );
            $created++;
        }

        return response()->json([
            'success' => true,
            'message' => "2025 yılı {$created} resmi tatil eklendi.",
            'created' => $created,
        ]);
    }
}
