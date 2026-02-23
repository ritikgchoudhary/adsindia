<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('course_affiliate_commissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('affiliate_id')->comment('User who earned commission');
            $table->unsignedBigInteger('course_order_id');
            $table->unsignedBigInteger('course_id');
            $table->unsignedBigInteger('purchaser_id')->comment('User who purchased the course');
            $table->decimal('commission_amount', 28, 8);
            $table->integer('status')->default(0)->comment('0=Pending, 1=Paid, 2=Cancelled');
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->foreign('affiliate_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('course_order_id')->references('id')->on('course_orders')->onDelete('cascade');
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            $table->foreign('purchaser_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_affiliate_commissions');
    }
};
