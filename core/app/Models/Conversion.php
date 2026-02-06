<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversion extends Model
{
    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeByTrackingType($query, $type)
    {
        return $query->where('tracking_type', $type);
    }

    public function scopeByAffiliate($query, $affiliateId)
    {
        return $query->where('affiliate_id', $affiliateId);
    }
}
