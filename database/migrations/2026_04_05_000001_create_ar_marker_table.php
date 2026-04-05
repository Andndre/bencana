<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ar_marker', function (Blueprint $table) {
            $table->id('marker_id');
            $table->unsignedBigInteger('disaster_id')->nullable();
            $table->string('nama', 255)->nullable();
            $table->string('path_gambar_marker', 255)->nullable();
            $table->string('path_patt', 255)->nullable();
            $table->string('path_model', 255)->nullable();
            $table->timestamps();

            $table->foreign('disaster_id')->references('id')
                ->on('disasters')->onDelete('cascade');
            $table->index('disaster_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ar_marker');
    }
};
