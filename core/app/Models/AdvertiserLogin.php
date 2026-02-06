<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdvertiserLogin extends Model
{
    public function advertiser()
    {
        return $this->belongsTo(Advertiser::class);
    }
}
