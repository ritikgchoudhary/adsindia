<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('influencer_program_settings')) {
            return;
        }

        Schema::create('influencer_program_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('apk_path', 255)->nullable();
            $table->string('apk_url', 500)->nullable();
            $table->string('apk_version', 50)->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('influencer_program_settings');
    }
};

