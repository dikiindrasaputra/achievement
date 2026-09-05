<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Target extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'year',
        'week_number',
        'start_date',
        'end_date',
        'monthly_target',
        'apply_all_days',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'monthly_target' => 'integer',
        'year' => 'integer',
        'week_number' => 'integer',
        'apply_all_days' => 'boolean',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(TargetItem::class);
    }

    /**
     * Auto-calculate start_date from year + week_number (ISO 8601)
     */
    public static function calcStartDate(int $year, int $weekNumber): Carbon
    {
        // 4 Januari selalu di ISO Week 1, mundur ke Senin
        $yearStart = Carbon::parse("{$year}-01-04")->startOfWeek(Carbon::MONDAY);
        return $yearStart->copy()->addWeeks($weekNumber - 1);
    }

    /**
     * Auto-calculate end_date from year + week_number (ISO 8601)
     */
    public static function calcEndDate(int $year, int $weekNumber): Carbon
    {
        return self::calcStartDate($year, $weekNumber)->copy()->endOfWeek(Carbon::SUNDAY);
    }

    /**
     * Check if target is active for a given date
     */
    public function isActive($date = null): bool
    {
        $date = $date ? Carbon::parse($date) : Carbon::now();

        return $date->between($this->start_date, $this->end_date);
    }

    /**
     * Get all manpower that have targets in this target
     */
    public function manpower()
    {
        return $this->belongsToMany(Manpower::class, 'target_items')
                    ->withPivot(['daily_target', 'weekly_target']);
    }
}
