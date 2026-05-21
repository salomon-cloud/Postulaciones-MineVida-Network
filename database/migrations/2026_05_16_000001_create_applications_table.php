<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type')->index();
            $table->string('status')->default('pending')->index();
            $table->string('minecraft_nick', 32);
            $table->unsignedTinyInteger('age');
            $table->string('country', 100);
            $table->string('timezone', 80)->nullable();
            $table->text('available_schedule')->nullable();
            $table->text('admin_response')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('cooldown_until')->nullable();
            $table->boolean('correction_requested')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'type', 'status']);
            $table->index(['created_at', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
