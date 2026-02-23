<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdPackage extends Model {
    protected $fillable = [
        'name',
        'slug',
        'description',
        'daily_ad_limit',
        'price',
        'reward_per_ad',
        'duration_seconds',
        'is_recommended',
        'status',
        'sort_order',
    ];

    protected $casts = [
        'daily_ad_limit' => 'integer',
        'price' => 'decimal:8',
        'reward_per_ad' => 'decimal:8',
        'duration_seconds' => 'integer',
        'is_recommended' => 'boolean',
        'status' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function orders() {
        return $this->hasMany(AdPackageOrder::class, 'package_id');
    }

    public function activeOrders() {
        return $this->hasMany(AdPackageOrder::class, 'package_id')->where('status', 1);
    }
}
