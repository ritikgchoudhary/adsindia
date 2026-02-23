<?php

namespace App\Models;

use App\Constants\Status;
use App\Traits\GlobalStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model {
    use GlobalStatus;

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at'   => 'datetime',
    ];

    public function scopeActive($query) {
        return $query->approved()
            ->where('is_paused', 0)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            });
    }

    public function advertiser() {
        return $this->belongsTo(Advertiser::class, 'advertiser_id');
    }

    public function category() {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function conversions() {
        return $this->hasMany(Conversion::class);
    }

    public function traffic_types() {
        return $this->belongsToMany(TrafficType::class, 'campaign_traffic_types');
    }

    protected function trafficTypeId(): Attribute {
        return new Attribute(
            get: fn() => $this->traffic_types->pluck('id')->toArray()
        );
    }

    protected function trafficTypeName(): Attribute {
        return new Attribute(
            get: fn() => $this->traffic_types->pluck('name')->toArray()
        );
    }

    public function statusBadge(): Attribute {
        return new Attribute(function () {
            $html = '';
            if ($this->status == Status::CAMPAIGN_PENDING) {
                $html = '<span class="badge badge--warning">' . trans('Pending') . '</span>';
            } else if ($this->status == Status::CAMPAIGN_APPROVED) {
                $html = '<span class="badge badge--success">' . trans('Approved') . '</span>';
            } else if ($this->status == Status::CAMPAIGN_REJECTED) {
                $html = '<span class="badge badge--danger">' . trans('Rejected') . '</span>';
            } else if ($this->status == Status::CAMPAIGN_COMPLETED) {
                $html = '<span class="badge badge--info">' . trans('Completed') . '</span>';
            } else {
                $html = '<span class="badge badge--secondary">' . trans('Unknown') . '</span>';
            }
            return $html;
        });
    }

    public function paymentStatusBadge(): Attribute {
        return new Attribute(function () {
            $html = '';

            if ($this->payment_status) {
                $html = '<span class="badge badge--success">' . trans('Paid') . '</span>';
            } else {
                $html = '<span class="badge badge--danger">' . trans('Unpaid') . '</span>';
            }

            return $html;
        });
    }

    public function pauseBadge(): Attribute {
        return new Attribute(function () {
            $html = '';

            if ($this->is_paused) {
                $html = '<span class="badge badge--warning">' . trans('Paused') . '</span>';
            } else {
                $html = '<span class="badge badge--success">' . trans('Running') . '</span>';
            }

            return $html;
        });
    }

    public function featuredBadge(): Attribute {
        return new Attribute(function () {
            $html = '';

            if ($this->is_featured) {
                $html = '<span class="badge badge--info">' . trans('Yes') . '</span>';
            } else {
                $html = '<span class="badge badge--danger">' . trans('No') . '</span>';
            }

            return $html;
        });
    }

    public function trackingTypeBadge(): Attribute {
        return new Attribute(function () {
            $html = '';
            if ($this->tracking_type == Status::TRACKING_JS) {
                $html = '<span>' . trans('JavaScript') . '</span>';
            } else if ($this->tracking_type == Status::TRACKING_SERVER) {
                $html = '<span>' . trans('Server-side') . '</span>';
            } else {
                $html = '<span>' . trans('N/A') . '</span>';
            }
            return $html;
        });
    }

    // Add these:
    public function scopeApproved($query) {
        return $query->where('status', Status::CAMPAIGN_APPROVED);
    }

    public function scopePending($query) {
        return $query->where('status', Status::CAMPAIGN_PENDING);
    }

    public function scopeRejected($query) {
        return $query->where('status', Status::CAMPAIGN_REJECTED);
    }

    public function scopePaused($query) {
        return $query->where('is_paused', 1);
    }

    public function scopeFeatured($query) {
        return $query->where('is_featured', 1);
    }

    public function scopeRunning($query) {
        return $query->approved()
            ->where('payment_status', Status::PAID)
            ->where('is_paused', Status::NO)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            });
    }
}
