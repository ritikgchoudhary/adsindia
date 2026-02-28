<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('influencer_applications')) {
            return;
        }

        Schema::create('influencer_applications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 255);
            $table->string('email', 255)->nullable()->index();
            $table->string('phone', 50)->nullable()->index();
            $table->json('platforms')->nullable();
            $table->json('data')->nullable();

            // 0=pending, 1=contacted, 2=approved, 3=rejected
            $table->unsignedTinyInteger('status')->default(0)->index();
            $table->text('admin_notes')->nullable();

            $table->string('source_url', 500)->nullable();
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('influencer_applications');
    }
};

