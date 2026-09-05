<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Check if year column exists
        $hasYear = Schema::hasColumn('targets', 'year');

        if (!$hasYear) {
            Schema::table('targets', function (Blueprint $table) {
                $table->integer('year')->after('name')->nullable();
            });
        }

        // Update existing data using PHP (SQLite compatible)
        $targets = DB::table('targets')->whereNull('year')->get();
        foreach ($targets as $target) {
            $year = (int) date('Y', strtotime($target->start_date));
            DB::table('targets')->where('id', $target->id)->update(['year' => $year]);
        }

        // Make NOT NULL if not already
        if ($hasYear) {
            // Check if there are still null values
            $nullCount = DB::table('targets')->whereNull('year')->count();
            if ($nullCount > 0) {
                // Can't make NOT NULL, leave as nullable
                return;
            }
        }

        Schema::table('targets', function (Blueprint $table) {
            $table->integer('year')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('targets', function (Blueprint $table) {
            $table->dropColumn('year');
        });
    }
};
