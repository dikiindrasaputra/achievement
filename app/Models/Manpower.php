<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Manpower extends Model
{
    use HasFactory;

    protected $table = 'manpower';

    protected $fillable = [
        'nip',
        'full_name',
        'vehicle_type',
        'contract_type',
        'start_date',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function targets(): HasMany
    {
        return $this->hasMany(TargetItem::class);
    }

    public function whitelists(): HasMany
    {
        return $this->hasMany(Whitelist::class);
    }

    public function achievements(): HasMany
    {
        return $this->hasMany(Achievement::class);
    }

    /**
     * Mendapatkan expected carryover untuk tanggal tertentu
     * Selalu dihitung real-time dari pencapaian hari sebelumnya
     */
    public function getExpectedCarryover($date = null): int
    {
        $date = $date ? Carbon::parse($date) : Carbon::now();

        // Day 1 = awal minggu, carryover selalu 0
        if ($this->getDayInWeek($date) === 1) {
            return 0;
        }

        // Cek apakah hari ini ada whitelist
        $isWhitelisted = $this->whitelists()
            ->whereDate('date', $date)
            ->exists();

        if ($isWhitelisted) {
            return 0;
        }

        // Selalu hitung dari pencapaian hari sebelumnya (real-time)
        $prevDate = $date->copy()->subDay();
        $prevAchievement = $this->achievements()
            ->whereDate('date', $prevDate)
            ->first();

        if (!$prevAchievement) {
            return 0;
        }

        // Hitung carryover dari hari sebelumnya
        $dailyTarget = $this->getActiveDailyTarget($prevDate);
        
        // Jika tidak ada target, carryover = 0
        if ($dailyTarget <= 0) {
            return 0;
        }
        
        $prevCarryover = $prevAchievement->carryover;
        $effectiveTarget = $dailyTarget + $prevCarryover;

        return $effectiveTarget - $prevAchievement->achievement;
    }

    /**
     * Mendapatkan tanggal awal tahun ISO (Senin minggu pertama)
     * ISO 8601: Week 1 = minggu yang berisi Kamis pertama tahun tersebut
     * Equivalently: 4 Januari selalu ada di ISO Week 1
     */
    public static function getYearStart($date = null): Carbon
    {
        $date = $date ? Carbon::parse($date) : Carbon::now();
        $year = $date->year;
        // 4 Januari selalu di ISO Week 1, mundur ke Senin
        return Carbon::parse("{$year}-01-04")->startOfWeek(Carbon::MONDAY);
    }

    /**
     * Mendapatkan ISO week number (Senin = awal minggu)
     */
    public function getWeekNumber($date = null): int
    {
        $date = $date ? Carbon::parse($date) : Carbon::now();
        return $date->weekOfYear;
    }

    /**
     * Mendapatkan hari keberapa dalam minggu ISO (1=Senin, 7=Minggu)
     */
    public function getDayInWeek($date = null): int
    {
        $date = $date ? Carbon::parse($date) : Carbon::now();
        // Carbon dayOfWeek: 0=Sun, 1=Mon, ..., 6=Sat
        // ISO 8601: 1=Mon, 2=Tue, ..., 7=Sun
        return $date->dayOfWeek === 0 ? 7 : $date->dayOfWeek;
    }

    /**
     * Mendapatkan tanggal awal ISO week (Senin)
     */
    public function getWeekStartDate($date = null): Carbon
    {
        $date = $date ? Carbon::parse($date) : Carbon::now();
        return $date->copy()->startOfWeek(Carbon::MONDAY);
    }

    /**
     * Mendapatkan tanggal akhir ISO week (Minggu)
     */
    public function getWeekEndDate($date = null): Carbon
    {
        return $this->getWeekStartDate($date)->copy()->endOfWeek(Carbon::SUNDAY);
    }

    /**
     * Mendapatkan effective days dalam minggu (exclude whitelist)
     */
    public function getEffectiveDaysInWeek($date = null): int
    {
        $weekStart = $this->getWeekStartDate($date);
        $weekEnd = $this->getWeekEndDate($date);

        $whitelistCount = $this->whitelists()
            ->whereBetween('date', [$weekStart, $weekEnd])
            ->count();

        if ($this->contract_type === 'dedicated') {
            return 7 - 1;
        }

        return 7 - $whitelistCount;
    }

    /**
     * Mendapatkan jumlah hari masuk dalam minggu
     */
    public function getAttendanceDaysInWeek($date = null): int
    {
        $weekStart = $this->getWeekStartDate($date);
        $weekEnd = $this->getWeekEndDate($date);

        return $this->achievements()
            ->whereBetween('date', [$weekStart, $weekEnd])
            ->where('achievement', '>', 0)
            ->count();
    }

    /**
     * Mendapatkan total achievement dalam minggu
     */
    public function getWeeklyAchievement($date = null): int
    {
        $weekStart = $this->getWeekStartDate($date);
        $weekEnd = $this->getWeekEndDate($date);

        return $this->achievements()
            ->whereBetween('date', [$weekStart, $weekEnd])
            ->sum('achievement');
    }

    /**
     * Mendapatkan carryover terakhir dalam minggu
     */
    public function getLastCarryoverInWeek($date = null): int
    {
        $weekStart = $this->getWeekStartDate($date);
        $weekEnd = $this->getWeekEndDate($date);

        $lastAchievement = $this->achievements()
            ->whereBetween('date', [$weekStart, $weekEnd])
            ->orderByDesc('date')
            ->first();

        return $lastAchievement ? $lastAchievement->carryover : 0;
    }

    /**
     * Mendapatkan daily target yang berlaku
     * Return 0 jika hari itu ada whitelist, atau jika hari sebelum start_date
     */
    public function getActiveDailyTarget($date = null): int
    {
        $date = $date ? Carbon::parse($date) : Carbon::now();

        // Cek apakah hari ini ada whitelist
        $isWhitelisted = $this->whitelists()
            ->whereDate('date', $date)
            ->exists();

        if ($isWhitelisted) {
            return 0;
        }

        // Cek apakah sebelum start_date
        if ($this->start_date && $date->lt(Carbon::parse($this->start_date))) {
            return 0;
        }

        $targetItem = $this->targets()
            ->whereHas('target', function ($query) use ($date) {
                $query->where('start_date', '<=', $date)
                      ->where('end_date', '>=', $date);
            })
            ->with('target')
            ->first();

        if (!$targetItem) {
            return 0;
        }

        // Gunakan day-specific target jika available
        $dayInWeek = $this->getDayInWeek($date);
        if ($dayInWeek >= 1 && $dayInWeek <= 6) {
            $dayTarget = $targetItem->getDayTarget($dayInWeek);
            if ($dayTarget !== null) {
                return $dayTarget;
            }
        }

        return $targetItem->daily_target;
    }

    /**
     * Mendapatkan weekly target yang berlaku
     */
    public function getActiveWeeklyTarget($date = null): int
    {
        $date = $date ? Carbon::parse($date) : Carbon::now();

        $targetItem = $this->targets()
            ->whereHas('target', function ($query) use ($date) {
                $query->where('start_date', '<=', $date)
                      ->where('end_date', '>=', $date);
            })
            ->with('target')
            ->first();

        return $targetItem ? $targetItem->weekly_target : 0;
    }

    /**
     * Scope: active manpower
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: by contract type
     */
    public function scopeByContractType($query, $type)
    {
        return $query->where('contract_type', $type);
    }

    /**
     * Scope: by vehicle type
     */
    public function scopeByVehicleType($query, $type)
    {
        return $query->where('vehicle_type', $type);
    }

    /**
     * Scope: search by name or nip
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('full_name', 'like', "%{$search}%")
              ->orWhere('nip', 'like', "%{$search}%");
        });
    }
}
