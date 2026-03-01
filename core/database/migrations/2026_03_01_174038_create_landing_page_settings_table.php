<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('landing_page_settings', function (Blueprint $table) {
            $table->id();
            
            // Core Settings
            $table->string('video_url')->nullable()->comment('YouTube or Vimeo URL');
            $table->integer('active_package_id')->default(2)->comment('Default 2 = AdsPro');
            $table->decimal('discounted_price', 10, 2)->nullable()->comment('Override price (e.g. 499)');
            $table->integer('timer_hours')->default(2)->comment('FOMO Timer in hours');
            
            // Text Overrides (Optional)
            $table->text('whatsapp_heading')->nullable();
            $table->text('marketing_heading')->nullable();
            $table->text('marketing_subheading')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('landing_page_settings');
    }
};
