<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    // 🔹 Relationship with user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 🔹 Relationship with advertiser
    public function advertiser()
    {
        return $this->belongsTo(Advertiser::class);
    }
}
