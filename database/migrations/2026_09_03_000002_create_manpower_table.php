<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manpower', function (Blueprint $table) {
            $table->id();
            $table->string('nip', 10)->unique();
            $table->string('full_name');
            $table->enum('vehicle_type', ['2wh', '4wh']);
            $table->enum('contract_type', ['dedicated', 'mitra']);
            $table->date('start_date');
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->index('contract_type');
            $table->index('vehicle_type');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manpower');
    }
};
