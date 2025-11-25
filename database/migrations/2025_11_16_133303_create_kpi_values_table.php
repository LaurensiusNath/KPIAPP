<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('kpi_values', function (Blueprint $table) {
            $table->id();

            $table->foreignId('kpi_id')
                ->constrained('kpis')
                ->cascadeOnDelete();

            $table->foreignId('user_id') // user yang dinilai
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('evaluator_id') // team leader
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('division_id')
                ->constrained('divisions')
                ->cascadeOnDelete();

            $table->foreignId('period_id')
                ->constrained('periods')
                ->cascadeOnDelete();

            $table->unsignedTinyInteger('score'); // 1–5
            $table->text('note')->nullable();

            $table->boolean('is_submitted')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpi_values');
    }
};
