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
         Schema::create('planning_models', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->text('description')->nullable();

            $table->decimal('monday_hours', 5, 2)->default(0);
            $table->decimal('tuesday_hours', 5, 2)->default(0);
            $table->decimal('wednesday_hours', 5, 2)->default(0);
            $table->decimal('thursday_hours', 5, 2)->default(0);
            $table->decimal('friday_hours', 5, 2)->default(0);
            $table->decimal('saturday_hours', 5, 2)->default(0);
            $table->decimal('sunday_hours', 5, 2)->default(0);

            $table->decimal('total_hours', 6, 2)->default(0);

            $table->foreignId('created_by')
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planning_models');
    }
};
