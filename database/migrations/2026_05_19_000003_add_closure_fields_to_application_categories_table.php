<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('application_categories', function (Blueprint $table) {
            $table->timestamp('closed_until')->nullable()->after('is_open');
            $table->string('closed_message', 500)->nullable()->after('closed_until');
        });
    }

    public function down(): void
    {
        Schema::table('application_categories', function (Blueprint $table) {
            $table->dropColumn(['closed_until', 'closed_message']);
        });
    }
};
