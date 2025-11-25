<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('kpis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignId('period_id')
                ->constrained('periods')
                ->cascadeOnDelete();
            $table->string('title', 255);
            $table->decimal('weight', 5, 2); // e.g., 25.00
            $table->json('criteria_scale');
            $table->timestamps();

            $table->index(['user_id', 'period_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpis');
    }
};
