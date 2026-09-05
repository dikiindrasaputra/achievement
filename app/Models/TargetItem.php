<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TargetItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'target_id',
        'manpower_id',
        'daily_target',
        'weekly_target',
        'day_1',
        'day_2',
        'day_3',
        'day_4',
        'day_5',
        'day_6',
    ];

    protected $casts = [
        'daily_target' => 'integer',
        'weekly_target' => 'integer',
        'day_1' => 'integer',
        'day_2' => 'integer',
        'day_3' => 'integer',
        'day_4' => 'integer',
        'day_5' => 'integer',
        'day_6' => 'integer',
    ];

    public function target(): BelongsTo
    {
        return $this->belongsTo(Target::class);
    }

    public function manpower(): BelongsTo
    {
        return $this->belongsTo(Manpower::class);
    }

    /**
     * Get target for specific day
     */
    public function getDayTarget(int $day): ?int
    {
        $value = match($day) {
            1 => $this->day_1,
            2 => $this->day_2,
            3 => $this->day_3,
            4 => $this->day_4,
            5 => $this->day_5,
            6 => $this->day_6,
            default => null,
        };

        return $value > 0 ? $value : null;
    }
}
