<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('kpi_values', function (Blueprint $table) {
            // Drop existing foreign key with cascade delete
            $table->dropForeign(['kpi_id']);

            // Add new foreign key with restrict delete
            // This prevents deleting a KPI if it has associated kpi_values
            $table->foreign('kpi_id')
                ->references('id')
                ->on('kpis')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('kpi_values', function (Blueprint $table) {
            // Revert back to cascade delete
            $table->dropForeign(['kpi_id']);

            $table->foreign('kpi_id')
                ->references('id')
                ->on('kpis')
                ->cascadeOnDelete();
        });
    }
};
