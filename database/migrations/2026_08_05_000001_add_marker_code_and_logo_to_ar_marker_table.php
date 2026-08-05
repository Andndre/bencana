<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ar_marker', function (Blueprint $table) {
            $table->string('marker_code', 64)->nullable()->unique()->after('nama');
            $table->string('path_logo_tengah', 255)->nullable()->after('path_gambar_marker');
        });
    }

    public function down(): void
    {
        Schema::table('ar_marker', function (Blueprint $table) {
            $table->dropUnique(['marker_code']);
            $table->dropColumn(['marker_code', 'path_logo_tengah']);
        });
    }
};
