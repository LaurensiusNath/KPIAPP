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
        Schema::create('appraisals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('evaluator_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('division_id')->constrained('divisions')->cascadeOnDelete();
            $table->foreignId('period_id')->constrained('periods')->cascadeOnDelete();
            $table->decimal('final_score', 5, 2)->nullable();
            $table->text('comment_teamleader')->nullable();
            $table->text('comment_hrd')->nullable();
            $table->boolean('is_finalized')->default(false);
            $table->timestamp('teamleader_submitted_at')->nullable();
            $table->timestamp('hrd_submitted_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'period_id'], 'appraisals_user_period_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appraisals');
    }
};
