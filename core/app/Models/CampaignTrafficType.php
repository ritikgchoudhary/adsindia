<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignTrafficType extends Model {
    public function campaignName() {
        return $this->belongsTo(Campaign::class, 'campaign_id');
    }
}
