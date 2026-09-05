<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('manpower', function (Blueprint $table) {
            $table->dropIndex('manpower_nip_unique');
            $table->string('nip', 10)->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('manpower', function (Blueprint $table) {
            $table->dropIndex('manpower_nip_unique');
            $table->string('nip', 7)->unique()->change();
        });
    }
};
