<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('kpi_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kpi_id')->constrained('kpis')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('evaluator_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('division_id')->constrained('divisions')->cascadeOnDelete();
            $table->foreignId('period_id')->constrained('periods')->cascadeOnDelete();
            $table->unsignedTinyInteger('month')->nullable();
            $table->unsignedTinyInteger('score');
            $table->text('note')->nullable();
            $table->boolean('is_submitted')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'period_id', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpi_values');
    }
};
