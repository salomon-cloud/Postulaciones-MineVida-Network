<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_interviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('interviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('scheduled_at')->nullable()->index();
            $table->string('location')->nullable();
            $table->string('status')->default('scheduled')->index();
            $table->text('notes')->nullable();
            $table->text('result_notes')->nullable();
            $table->timestamp('completed_at')->nullable()->index();
            $table->timestamps();

            $table->index(['application_id', 'status']);
            $table->index(['interviewer_id', 'scheduled_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_interviews');
    }
};
