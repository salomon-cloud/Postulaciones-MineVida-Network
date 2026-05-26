<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('application_categories', 'image_path')) {
            return;
        }

        Schema::table('application_categories', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('accent_color');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('application_categories', 'image_path')) {
            return;
        }

        Schema::table('application_categories', function (Blueprint $table) {
            $table->dropColumn('image_path');
        });
    }
};
