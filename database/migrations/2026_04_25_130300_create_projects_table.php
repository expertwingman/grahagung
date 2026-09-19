<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('lead_id')->nullable()->constrained('leads')->nullOnDelete();
            $table->enum('status', [
                'planning',
                'in_progress', 
                'on_hold',
                'completed',
                'cancelled'
            ])->default('planning');
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->decimal('value', 15, 2)->nullable();
            $table->decimal('progress', 5, 2)->default(0); // 0-100 persen
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();
            $table->date('completed_date')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};