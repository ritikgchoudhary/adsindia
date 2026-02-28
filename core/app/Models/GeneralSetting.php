<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeneralSetting extends Model
{
    protected $guarded = [];

    protected $casts = [
        'mail_config' => 'object',
        'sms_config' => 'object',
        'global_shortcodes' => 'object',
        'socialite_credentials' => 'object',
        'firebase_config' => 'object',
        'config_progress'=>'object',
        'beta_verified_settings' => 'array',
        'beta_booster_settings' => 'array',
        'beta_instant_settings' => 'array',
        'beta_extra_settings' => 'array',
        'lb_show_today' => 'boolean',
        'lb_show_weekly' => 'boolean',
        'lb_show_monthly' => 'boolean',
        'lb_show_all_time' => 'boolean',
    ];

    protected $hidden = ['email_template','mail_config','sms_config','system_info'];

    public function scopeSiteName($query, $pageTitle)
    {
        $pageTitle = empty($pageTitle) ? '' : ' - ' . $pageTitle;
        return $this->site_name . $pageTitle;
    }

    protected static function boot()
    {
        parent::boot();
        static::saved(function(){
            \Cache::forget('GeneralSetting');
        });
    }
}
