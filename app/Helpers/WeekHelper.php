<?php

namespace App\Helpers;

use Carbon\Carbon;

class WeekHelper
{
    /**
     * Mendapatkan ISO week number
     */
    public static function getWeekNumber(string $startDate, ?string $date = null): int
    {
        $date = $date ? Carbon::parse($date) : Carbon::now();
        return $date->weekOfYear;
    }

    /**
     * Mendapatkan hari keberapa dalam minggu ISO (1=Senin, 7=Minggu)
     */
    public static function getDayInWeek(string $startDate, ?string $date = null): int
    {
        $date = $date ? Carbon::parse($date) : Carbon::now();
        return $date->dayOfWeek === 0 ? 7 : $date->dayOfWeek;
    }

    /**
     * Mendapatkan tanggal awal ISO week (Senin)
     */
    public static function getWeekStartDate(string $startDate, int $weekNumber): Carbon
    {
        $year = Carbon::parse($startDate)->year;
        // 4 Januari selalu di ISO Week 1, mundur ke Senin
        $yearStart = Carbon::parse("{$year}-01-04")->startOfWeek(Carbon::MONDAY);
        return $yearStart->copy()->addWeeks($weekNumber - 1);
    }

    /**
     * Mendapatkan tanggal akhir ISO week (Minggu)
     */
    public static function getWeekEndDate(string $startDate, int $weekNumber): Carbon
    {
        return self::getWeekStartDate($startDate, $weekNumber)->copy()->endOfWeek(Carbon::SUNDAY);
    }

    /**
     * Mendapatkan sisa hari di minggu terakhir tahun
     */
    public static function getRemainingDaysInYear(int $year): int
    {
        $lastDay = Carbon::parse("$year-12-31");
        $yearStart = Carbon::parse("{$year}-01-04")->startOfWeek(Carbon::MONDAY);
        $currentWeekStart = $lastDay->copy()->startOfWeek(Carbon::MONDAY);

        if ($currentWeekStart->year < $year) {
            return 7;
        }

        return $lastDay->diffInDays($currentWeekStart) + 1;
    }

    /**
     * Mendapatkan total minggu dalam setahun (ISO 8601)
     */
    public static function getTotalWeeksInYear(int $year): int
    {
        $dec28 = Carbon::parse("{$year}-12-28");
        return $dec28->weekOfYear;
    }

    /**
     * Check apakah tanggal adalah tahun baru
     */
    public static function isNewYearTransition(string $date1, string $date2): bool
    {
        $d1 = Carbon::parse($date1);
        $d2 = Carbon::parse($date2);

        return $d1->year !== $d2->year;
    }

    /**
     * Mendapatkan sisa hari di minggu saat pergantian tahun
     */
    public static function getYearEndRemainingDays(string $startDate): int
    {
        $start = Carbon::parse($startDate);
        $yearEnd = Carbon::parse($start->year . '-12-31');

        if ($start->diffInDays($yearEnd) < 7) {
            return 7 - $start->diffInDays($yearEnd);
        }

        return 0;
    }
}
