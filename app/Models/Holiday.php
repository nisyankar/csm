<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

class Holiday extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'date',
        'year',
        'type',
        'is_half_day',
        'half_day_start',
        'description',
        'is_paid',
        'is_active',
    ];

    protected $casts = [
        'date' => 'date',
        'half_day_start' => 'datetime:H:i',
        'is_half_day' => 'boolean',
        'is_paid' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Scope: Aktif tatiller
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Belirli bir yıldaki tatiller
     */
    public function scopeForYear($query, int $year)
    {
        return $query->where('year', $year);
    }

    /**
     * Scope: Belirli bir tarih aralığındaki tatiller
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope: Ücretli tatiller
     */
    public function scopePaid($query)
    {
        return $query->where('is_paid', true);
    }

    /**
     * Scope: Türe göre filtrele
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Belirli bir tarihin tatil olup olmadığını kontrol et
     */
    public static function isHoliday($date): bool
    {
        return self::active()
            ->whereDate('date', $date)
            ->exists();
    }

    /**
     * Belirli bir tarih aralığındaki tatil günlerini say
     */
    public static function countHolidaysInRange($startDate, $endDate): int
    {
        return self::active()
            ->betweenDates($startDate, $endDate)
            ->count();
    }

    /**
     * Türkçe tatil türü
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'national' => 'Ulusal Bayram',
            'religious' => 'Dini Bayram',
            'other' => 'Diğer',
            default => $this->type,
        };
    }

    /**
     * Formatlı tarih
     */
    public function getFormattedDateAttribute(): string
    {
        return $this->date->format('d.m.Y');
    }

    /**
     * Gün adı
     */
    public function getDayNameAttribute(): string
    {
        return $this->date->locale('tr')->dayName;
    }

    /**
     * Yarım gün tatil bilgisi (Arefe günleri için)
     */
    public function getHalfDayInfoAttribute(): ?string
    {
        if (!$this->is_half_day) {
            return null;
        }

        $startTime = $this->half_day_start ? Carbon::parse($this->half_day_start)->format('H:i') : '13:00';
        return "Saat {$startTime}'den sonra tatil";
    }

    /**
     * Tam gün tatil mi yoksa yarım gün mü?
     */
    public function isFullDay(): bool
    {
        return !$this->is_half_day;
    }

    /**
     * Çalışma saatlerini hesapla (yarım gün tatil için)
     */
    public function getWorkingHours(): float
    {
        if (!$this->is_half_day) {
            return 0; // Tam gün tatil, çalışma yok
        }

        // Yarım gün tatil - varsayılan 13:00'a kadar çalışılır (8 saatlik mesaide 5 saat)
        $startTime = $this->half_day_start ? Carbon::parse($this->half_day_start) : Carbon::parse('13:00');

        // 08:00 - 13:00 arası 5 saat (1 saat öğle molası çıkarılırsa 4 saat net çalışma)
        // Ancak genelde yarım gün 5 saat olarak kabul edilir
        return 5.0;
    }
}