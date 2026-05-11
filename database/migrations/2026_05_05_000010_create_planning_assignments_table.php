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
        Schema::create('planning_assignments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('planning_model_id')
                  ->constrained('planning_models')
                  ->cascadeOnDelete();

            $table->foreignId('employee_id')
                  ->constrained('employees')
                  ->cascadeOnDelete();

            $table->date('start_date');
            $table->date('end_date')->nullable();

            $table->enum('status', ['en attente', 'validé', 'suspendu', 'terminé'])
                  ->default('en attente');

            $table->foreignId('validated_by')
                  ->nullable()
                  ->constrained('employees')
                  ->nullOnDelete();

            $table->timestamp('validated_at')->nullable();

            $table->timestamps();
        });
    
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planning_assignments');
    }
};
