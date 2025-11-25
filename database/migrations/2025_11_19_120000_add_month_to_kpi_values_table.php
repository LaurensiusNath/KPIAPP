<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('kpi_values', function (Blueprint $table) {
            // Add month column (1..12) after period_id
            $table->unsignedTinyInteger('month')->nullable()->after('period_id');
            // Optional helpful index for lookups
            $table->index(['user_id', 'period_id', 'month']);
        });
    }

    public function down(): void
    {
        Schema::table('kpi_values', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'period_id', 'month']);
            $table->dropColumn('month');
        });
    }
};
