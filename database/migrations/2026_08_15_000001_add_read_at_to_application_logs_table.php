<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('application_logs', 'read_at')) {
            return;
        }

        Schema::table('application_logs', function (Blueprint $table) {
            $table->timestamp('read_at')->nullable()->after('user_agent');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('application_logs', 'read_at')) {
            return;
        }

        Schema::table('application_logs', function (Blueprint $table) {
            $table->dropColumn('read_at');
        });
    }
};
