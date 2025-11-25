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

            // User yang dinilai
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // evaluator_id = siapa yang memberikan nilai (admin atau TL)
            $table->foreignId('evaluator_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Divisi pada saat appraisal terjadi
            $table->foreignId('division_id')
                ->constrained('divisions')
                ->cascadeOnDelete();

            // Periode semester (S1 atau S2)
            $table->foreignId('period_id')
                ->constrained('periods')
                ->cascadeOnDelete();

            // Nilai akhir (rata-rata dari KPI bulanan)
            $table->decimal('final_score', 5, 2)->nullable();

            // Komentar dari TL
            $table->text('comment_teamleader')->nullable();

            // Komentar dari HRD/Admin
            $table->text('comment_hrd')->nullable();

            // Seal/lock appraisal setelah finalisasi opsional
            $table->boolean('is_finalized')->default(false);

            $table->timestamp('teamleader_submitted_at')->nullable();
            $table->timestamp('hrd_submitted_at')->nullable();
            $table->timestamps();



            // Prevent duplicate appraisal per user per semester
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
