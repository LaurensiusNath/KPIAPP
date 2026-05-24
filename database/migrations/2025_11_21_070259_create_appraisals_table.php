<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
            $table->foreignId('team_leader_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('division_id')->nullable()->constrained('divisions')->nullOnDelete();
            $table->foreignId('period_id')->constrained('periods')->cascadeOnDelete();
            $table->decimal('final_score', 5, 2)->nullable();
            $table->text('comment_teamleader')->nullable();
            $table->text('comment_hrd')->nullable();
            $table->enum('status', [
                'pending_teamleader',
                'pending_hrd',
                'finalized'
            ])->default('pending_teamleader');
            $table->timestamp('teamleader_submitted_at')->nullable();
            $table->timestamp('hrd_submitted_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'period_id'], 'appraisals_user_period_unique');

            // Indexing
            $table->index(['period_id', 'status']);
            $table->index(['division_id', 'period_id', 'status']);
            $table->index(['period_id', 'teamleader_submitted_at']);
            $table->index(['period_id', 'hrd_submitted_at']);
        });

        DB::statement("
            ALTER TABLE appraisals ADD CONSTRAINT appraisals_status_state_check
            CHECK (
                (status = 'pending_teamleader' AND teamleader_submitted_at IS NULL AND hrd_submitted_at IS NULL) OR
                (status = 'pending_hrd' AND teamleader_submitted_at IS NOT NULL AND hrd_submitted_at IS NULL) OR
                (status = 'finalized' AND teamleader_submitted_at IS NOT NULL AND hrd_submitted_at IS NOT NULL)
            )
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appraisals');
    }
};
