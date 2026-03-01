<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LandingPageSetting extends Model
{
    use HasFactory;

    protected $table = 'landing_page_settings';

    protected $fillable = [
        'video_url',
        'active_package_id',
        'discounted_price',
        'timer_hours',
        'whatsapp_heading',
        'marketing_heading',
        'marketing_subheading',
    ];

    /**
     * Helper to get the single row of settings.
     */
    public static function getSetting()
    {
        $setting = self::first();
        if (!$setting) {
            $setting = self::create([
                'active_package_id' => 2,
                'discounted_price' => 2999,
                'timer_hours' => 2,
                'video_url' => 'https://www.youtube.com/embed/PWeH_qM4sZY',
                'marketing_heading' => 'Watch Ads. Earn Money. Simple as that.',
                'marketing_subheading' => 'No Team Building. No Recruiting. Start making daily income from your phone right now.',
                'whatsapp_heading' => 'Earn Money by just Watching Ads',
            ]);
        }
        return $setting;
    }
}
