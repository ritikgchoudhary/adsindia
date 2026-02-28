<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('influencer_applications')) {
            return;
        }

        Schema::table('influencer_applications', function (Blueprint $table) {
            if (!Schema::hasColumn('influencer_applications', 'lead_key')) {
                $table->string('lead_key', 64)->nullable()->unique();
            }
            if (!Schema::hasColumn('influencer_applications', 'is_draft')) {
                $table->boolean('is_draft')->default(false)->index();
            }
            if (!Schema::hasColumn('influencer_applications', 'last_step')) {
                $table->unsignedTinyInteger('last_step')->default(0)->index();
            }
            if (!Schema::hasColumn('influencer_applications', 'submitted_at')) {
                $table->timestamp('submitted_at')->nullable()->index();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('influencer_applications')) {
            return;
        }

        Schema::table('influencer_applications', function (Blueprint $table) {
            if (Schema::hasColumn('influencer_applications', 'submitted_at')) {
                $table->dropColumn('submitted_at');
            }
            if (Schema::hasColumn('influencer_applications', 'last_step')) {
                $table->dropColumn('last_step');
            }
            if (Schema::hasColumn('influencer_applications', 'is_draft')) {
                $table->dropColumn('is_draft');
            }
            if (Schema::hasColumn('influencer_applications', 'lead_key')) {
                $table->dropUnique(['lead_key']);
                $table->dropColumn('lead_key');
            }
        });
    }
};

