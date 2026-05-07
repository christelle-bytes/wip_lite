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
        Schema::create('timesheet_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('timesheet_id')
                  ->constrained()
                  ->onDelete('cascade');
            $table->date('date');
            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();
            $table->integer('break_duration')->default(0); // minutes
            $table->decimal('total_hours', 5, 2)->nullable();
            $table->decimal('planned_hours', 5, 2)->nullable();
            $table->decimal('overtime_hours', 5, 2)->nullable();
            $table->string('absence_type')->nullable();
            $table->text('comment')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['timesheet_id']);
// $table->check('(check_out > check_in OR (check_in IS NULL AND check_out IS NULL))', 'valid_check_times');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timesheet_entries');
    }
};
