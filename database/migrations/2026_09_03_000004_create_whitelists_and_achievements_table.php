<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whitelists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manpower_id')->constrained('manpower')->cascadeOnDelete();
            $table->date('date');
            $table->string('reason')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->unique(['manpower_id', 'date']);
            $table->index('date');
        });

        Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manpower_id')->constrained('manpower')->cascadeOnDelete();
            $table->date('date');
            $table->integer('achievement')->default(0);
            $table->integer('carryover')->default(0);
            $table->string('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->unique(['manpower_id', 'date']);
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('achievements');
        Schema::dropIfExists('whitelists');
    }
};
