<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdView extends Model {
    protected $fillable = [
        'user_id',
        'user_package_id',
        'ad_url',
        'reward_amount',
        'watch_duration',
        'is_completed',
        'viewed_at',
    ];

    protected $casts = [
        'reward_amount' => 'decimal:8',
        'watch_duration' => 'integer',
        'is_completed' => 'boolean',
        'viewed_at' => 'datetime',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function adPackageOrder() {
        return $this->belongsTo(AdPackageOrder::class, 'user_package_id');
    }
}
