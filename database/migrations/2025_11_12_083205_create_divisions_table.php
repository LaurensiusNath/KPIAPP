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
        Schema::create('divisions', function (Blueprint $table) {
            $table->id();

            // SAME as PostgreSQL: NOT NULL + UNIQUE
            $table->string('name')->unique();

            // SAME as PostgreSQL: NOT NULL + foreign key restricted
            $table->foreignId('leader_id')
                ->constrained(
                    table: 'users',
                    indexName: 'divisions_leader_id',
                    column: 'id'
                )
                ->restrictOnDelete();

            // SAME as PostgreSQL: timestamps nullable (Laravel default)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('divisions');
    }
};
