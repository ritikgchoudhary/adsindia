<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentCommissionSetting extends Model
{
    protected $table = 'agent_commission_settings';

    protected $fillable = [
        'user_id',
        'registration_enabled',
        'registration_mode',
        'registration_value',
        'kyc_enabled',
        'kyc_mode',
        'kyc_value',
        'withdraw_fee_enabled',
        'withdraw_fee_mode',
        'withdraw_fee_value',
        'upgrade_enabled',
        'upgrade_mode',
        'upgrade_value',
        'partner_override_enabled',
        'partner_override_percent',
        'adplan_enabled',
        'adplan_mode',
        'adplan_value',
        'course_enabled',
        'course_mode',
        'course_value',
        'partner_enabled',
        'partner_mode',
        'partner_value',
        'certificate_enabled',
        'certificate_mode',
        'certificate_value',
        'special_discount_enabled',
        'special_discount_mode',
        'special_discount_value',
        'passive_enabled',
        'passive_mode',
        'passive_value',
        'granular_settings'
    ];

    protected $casts = [
        'registration_enabled' => 'boolean',
        'kyc_enabled'          => 'boolean',
        'withdraw_fee_enabled' => 'boolean',
        'upgrade_enabled'      => 'boolean',
        'partner_override_enabled' => 'boolean',
        'adplan_enabled'       => 'boolean',
        'course_enabled'       => 'boolean',
        'partner_enabled'      => 'boolean',
        'certificate_enabled'  => 'boolean',
        'special_discount_enabled' => 'boolean',
        'passive_enabled'      => 'boolean',
        'granular_settings'    => 'object',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
