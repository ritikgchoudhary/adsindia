<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('special_discount_links', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('package_id'); // 1..5
            $table->unsignedInteger('discount')->default(0); // ₹ amount
            $table->string('sig', 128);
            $table->boolean('is_global')->default(true);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable(); // admin id (if available)
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();

            $table->index(['is_active', 'is_global', 'package_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('special_discount_links');
    }
};

