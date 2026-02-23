<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('special_link_commission_settings')) {
            Schema::create('special_link_commission_settings', function (Blueprint $table) {
                $table->id();
                // Commission per-package: matches course package IDs 1-5
                $table->unsignedTinyInteger('package_id')->unique();
                $table->boolean('enabled')->default(false);
                $table->enum('mode', ['percent', 'fixed'])->default('fixed');
                $table->decimal('value', 28, 8)->default(0);
                $table->timestamps();
            });

            // Seed with default rows for all 5 packages
            $packages = [
                ['id' => 1, 'price' => 1499],
                ['id' => 2, 'price' => 2999],
                ['id' => 3, 'price' => 5999],
                ['id' => 4, 'price' => 9999],
                ['id' => 5, 'price' => 15999],
            ];

            $now = now();
            foreach ($packages as $p) {
                DB::table('special_link_commission_settings')->insert([
                    'package_id' => $p['id'],
                    'enabled' => false,
                    'mode' => 'fixed',
                    'value' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('special_link_commission_settings');
    }
};
