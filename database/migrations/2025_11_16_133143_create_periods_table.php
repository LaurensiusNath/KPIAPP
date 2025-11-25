<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('periods', function (Blueprint $table) {
            $table->id();
            $table->integer('year');
            $table->smallInteger('semester'); // 1 or 2
            $table->boolean('is_active')->default(false);
            $table->timestamps();

            $table->unique(['year', 'semester'], 'periods_year_semester_unique');
        });

        // Postgres check constraint to ensure semester is only 1 or 2
        // Safe for PostgreSQL; if another DB is used, wrap in condition or remove.
        DB::statement("ALTER TABLE periods ADD CONSTRAINT periods_semester_check CHECK (semester IN (1, 2));");
    }

    public function down(): void
    {
        Schema::dropIfExists('periods');
    }
};
