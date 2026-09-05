<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('targets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('week_number');
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('monthly_target')->default(0);
            $table->boolean('apply_all_days')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->index('start_date');
            $table->index('end_date');
        });

        Schema::create('target_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('target_id')->constrained()->cascadeOnDelete();
            $table->foreignId('manpower_id')->constrained('manpower')->cascadeOnDelete();
            $table->integer('daily_target')->default(0);
            $table->integer('weekly_target')->default(0);
            $table->integer('day_1')->default(0);
            $table->integer('day_2')->default(0);
            $table->integer('day_3')->default(0);
            $table->integer('day_4')->default(0);
            $table->integer('day_5')->default(0);
            $table->integer('day_6')->default(0);
            $table->timestamps();

            $table->unique(['target_id', 'manpower_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('target_items');
        Schema::dropIfExists('targets');
    }
};
