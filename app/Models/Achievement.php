<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Achievement extends Model
{
    use HasFactory;

    protected $fillable = [
        'manpower_id',
        'date',
        'achievement',
        'carryover',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
        'achievement' => 'integer',
        'carryover' => 'integer',
    ];

    public function manpower(): BelongsTo
    {
        return $this->belongsTo(Manpower::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Calculate carryover for next day
     * 
     * Logika:
     * - Jika achievement < effectiveTarget → carryover POSITIF (next day target naik)
     * - Jika achievement > effectiveTarget → carryover NEGATIF (next day target turun)
     * - Jika achievement = effectiveTarget → carryover 0
     * - Jika hari ini whitelist → carryover 0
     */
    public function calculateNextDayCarryover(): int
    {
        // Cek apakah hari ini ada whitelist
        $isWhitelisted = $this->manpower->whitelists()
            ->whereDate('date', $this->date)
            ->exists();

        if ($isWhitelisted) {
            return 0;
        }

        $dailyTarget = $this->manpower->getActiveDailyTarget($this->date);
        $effectiveTarget = $dailyTarget + $this->carryover;

        // Hitung selisih: effectiveTarget - achievement
        // Positif = kurang dari target (next day naik)
        // Negatif = lebih dari target (next day turun)
        return $effectiveTarget - $this->achievement;
    }

    /**
     * Get effective target for this day (including carryover)
     * Jika hari ini whitelist, return 0
     */
    public function getEffectiveTarget(): int
    {
        // Cek apakah hari ini ada whitelist
        $isWhitelisted = $this->manpower->whitelists()
            ->whereDate('date', $this->date)
            ->exists();

        if ($isWhitelisted) {
            return 0;
        }

        $dailyTarget = $this->manpower->getActiveDailyTarget($this->date);
        return $dailyTarget + $this->carryover;
    }

    /**
     * Get achievement percentage
     */
    public function getPercentage(): float
    {
        $effectiveTarget = $this->getEffectiveTarget();
        if ($effectiveTarget <= 0) return 0;

        return ($this->achievement / $effectiveTarget) * 100;
    }

    /**
     * Get status based on percentage
     */
    public function getStatus(): string
    {
        $percentage = $this->getPercentage();

        if ($percentage >= 100) {
            return 'achieved';
        } elseif ($percentage >= 50) {
            return 'partial';
        } else {
            return 'low';
        }
    }

    /**
     * Save achievement with auto carryover calculation
     * Jika hari ini whitelist, carryover = 0
     */
    public static function saveWithCarryover($manpowerId, $date, $achievement, $notes = null, $createdBy = null): self
    {
        $date = Carbon::parse($date);
        $manpower = Manpower::findOrFail($manpowerId);

        // Day 1 = awal minggu, carryover selalu 0
        if ($manpower->getDayInWeek($date) === 1) {
            $carryover = 0;
        } else {
            // Cek apakah hari ini ada whitelist
            $isWhitelisted = $manpower->whitelists()
                ->where('date', $date->format('Y-m-d'))
                ->exists();

            if ($isWhitelisted) {
                $carryover = 0;
            } else {
                $prevDate = $date->copy()->subDay();
                $prevAchievement = self::where('manpower_id', $manpowerId)
                    ->where('date', $prevDate)
                    ->first();

                $carryover = $prevAchievement ? $prevAchievement->calculateNextDayCarryover() : 0;
            }
        }

        return self::updateOrCreate(
            [
                'manpower_id' => $manpowerId,
                'date' => $date,
            ],
            [
                'achievement' => $achievement,
                'carryover' => $carryover,
                'notes' => $notes,
                'created_by' => $createdBy,
            ]
        );
    }

    /**
     * Get weekly productivity accumulation
     */
    public static function getWeeklyAccumulation($manpowerId, $date = null): array
    {
        $manpower = Manpower::findOrFail($manpowerId);
        $date = $date ? Carbon::parse($date) : Carbon::now();

        $weekStart = $manpower->getWeekStartDate($date);
        $weekEnd = $manpower->getWeekEndDate($date);
        $today = Carbon::now();

        $achievements = self::where('manpower_id', $manpowerId)
            ->whereBetween('date', [$weekStart, $today])
            ->orderBy('date')
            ->get();

        $totalAchievement = $achievements->sum('achievement');
        $totalCarryover = $achievements->last()->carryover ?? 0;

        $dailyTarget = $manpower->getActiveDailyTarget($date);
        $weeklyTarget = $manpower->getActiveWeeklyTarget($date);

        $remainingDays = max(0, $today->diffInDays($weekEnd));
        $remainingTarget = $weeklyTarget - $totalAchievement;

        $suggestedDaily = $remainingDays > 0 ? ceil($remainingTarget / $remainingDays) : 0;

        return [
            'week_start' => $weekStart,
            'week_end' => $weekEnd,
            'total_achievement' => $totalAchievement,
            'total_carryover' => $totalCarryover,
            'daily_target' => $dailyTarget,
            'weekly_target' => $weeklyTarget,
            'remaining_days' => $remainingDays,
            'remaining_target' => $remainingTarget,
            'suggested_daily' => $suggestedDaily,
        ];
    }
}
