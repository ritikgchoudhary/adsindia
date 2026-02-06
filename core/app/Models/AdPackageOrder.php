<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdPackageOrder extends Model {
    protected $fillable = [
        'user_id',
        'package_id',
        'deposit_id',
        'amount',
        'status',
        'admin_feedback',
        'expires_at',
    ];

    protected $casts = [
        'amount' => 'decimal:8',
        'status' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function package() {
        return $this->belongsTo(AdPackage::class, 'package_id');
    }

    public function deposit() {
        return $this->belongsTo(Deposit::class);
    }

    public function adViews() {
        return $this->hasMany(AdView::class, 'user_package_id');
    }

    public function scopeActive($query) {
        return $query->where('status', 1)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });
    }

    public function scopePending($query) {
        return $query->where('status', 0);
    }

    public function scopeApproved($query) {
        return $query->where('status', 1);
    }
}
