<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ar_marker', function (Blueprint $table) {
            $table->string('path_audio', 255)->nullable()->after('path_model');
        });
    }

    public function down(): void
    {
        Schema::table('ar_marker', function (Blueprint $table) {
            $table->dropColumn('path_audio');
        });
    }
};
