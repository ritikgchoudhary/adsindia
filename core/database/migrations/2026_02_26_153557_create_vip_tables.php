<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('vip_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('price', 18, 8);
            $table->integer('months')->default(1);
            $table->decimal('withdrawal_fee_discount', 5, 2)->default(100.00); // 100% discount by default
            $table->boolean('priority_payout')->default(true);
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });

        Schema::create('vip_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('plan_id');
            $table->timestamp('expires_at');
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->index('user_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('vip_subscriptions');
        Schema::dropIfExists('vip_plans');
    }
};
