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
        Schema::create('timesheets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade')
                  ->after('id'); // employee_id
            $table->date('period_start');
            $table->date('period_end');
            $table->enum('status', ['draft', 'submitted', 'validated'])->default('draft');
            $table->foreignId('validated_by')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();

            // Indexes and constraints
            $table->unique(['user_id', 'period_start']);
            $table->index('status');
// $table->check('period_end >= period_start', 'period_end_after_start');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timesheets');
    }
};
