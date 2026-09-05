<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    public function up(): void
    {
        $targets = DB::table('targets')->select('id', 'year', 'week_number')->get();

        foreach ($targets as $target) {
            // ISO 8601: January 4 is always in Week 1
            $yearStart = Carbon::parse("{$target->year}-01-04")->startOfWeek(Carbon::MONDAY);
            $startDate = $yearStart->copy()->addWeeks($target->week_number - 1);
            $endDate = $startDate->copy()->endOfWeek(Carbon::SUNDAY);

            DB::table('targets')
                ->where('id', $target->id)
                ->update([
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                ]);
        }
    }

    public function down(): void
    {
        // This migration fixes data, reverse would need old system logic
        // but we don't want to go back to old system
    }
};
