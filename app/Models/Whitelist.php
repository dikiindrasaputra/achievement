<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Whitelist extends Model
{
    use HasFactory;

    protected $fillable = [
        'manpower_id',
        'date',
        'reason',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
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
     * Check if whitelist is available for manpower in current week
     * Only "Libur" counts toward quota (1 for dedicated, 3 for mitra)
     */
    public static function isAvailable($manpowerId, $date = null): bool
    {
        $manpower = Manpower::find($manpowerId);
        if (!$manpower) return false;

        $date = $date ? \Carbon\Carbon::parse($date) : \Carbon\Carbon::now();
        $weekStart = $manpower->getWeekStartDate($date);
        $weekEnd = $manpower->getWeekEndDate($date);

        $whitelistCount = self::where('manpower_id', $manpowerId)
            ->whereBetween('date', [$weekStart, $weekEnd])
            ->where('reason', 'Libur')
            ->count();

        if ($manpower->contract_type === 'dedicated') {
            return $whitelistCount < 1;
        }

        return $whitelistCount < 3;
    }

    /**
     * Get remaining whitelist quota for current week
     * Only "Libur" counts toward quota
     */
    public static function getRemainingQuota($manpowerId, $date = null): int
    {
        $manpower = Manpower::find($manpowerId);
        if (!$manpower) return 0;

        $date = $date ? \Carbon\Carbon::parse($date) : \Carbon\Carbon::now();
        $weekStart = $manpower->getWeekStartDate($date);
        $weekEnd = $manpower->getWeekEndDate($date);

        $whitelistCount = self::where('manpower_id', $manpowerId)
            ->whereBetween('date', [$weekStart, $weekEnd])
            ->where('reason', 'Libur')
            ->count();

        if ($manpower->contract_type === 'dedicated') {
            return max(0, 1 - $whitelistCount);
        }

        return max(0, 3 - $whitelistCount);
    }
}
