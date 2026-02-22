<?php

namespace App\Lib;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AgentCommission
{
    /**
     * Map commission type -> transaction remark
     */
    public static function remarkFor(string $type): string
    {
        return match ($type) {
            'registration' => 'agent_registration_commission',
            'kyc' => 'agent_kyc_commission',
            'withdraw_fee' => 'agent_withdraw_fee_commission',
            'upgrade' => 'agent_upgrade_commission',
            'adplan' => 'agent_adplan_commission',
            'course' => 'agent_course_commission',
            'partner' => 'agent_partner_commission',
            'certificate' => 'agent_certificate_commission',
            'partner_override' => 'agent_partner_override_commission',
            default => 'agent_commission',
        };
    }

    /**
     * Compute commission amount for an agent and type.
     * Returns 0 if disabled/missing/invalid.
     */
    public static function calculate(int $agentId, string $type, float $baseAmount, array $meta = []): float
    {
        if ($baseAmount <= 0) return 0.0;

        $settings = DB::table('agent_commission_settings')->where('user_id', $agentId)->first();
        if (!$settings) return 0.0;

        $enabledField = null;
        $modeField = null;
        $valueField = null;

        if ($type === 'registration') {
            $enabledField = 'registration_enabled';
            $modeField = 'registration_mode';
            $valueField = 'registration_value';
        } elseif ($type === 'kyc') {
            $enabledField = 'kyc_enabled';
            $modeField = 'kyc_mode';
            $valueField = 'kyc_value';
        } elseif ($type === 'withdraw_fee') {
            $enabledField = 'withdraw_fee_enabled';
            $modeField = 'withdraw_fee_mode';
            $valueField = 'withdraw_fee_value';
        } elseif ($type === 'upgrade') {
            $enabledField = 'upgrade_enabled';
            $modeField = 'upgrade_mode';
            $valueField = 'upgrade_value';
        } elseif ($type === 'adplan') {
            $enabledField = 'adplan_enabled';
            $modeField = 'adplan_mode';
            $valueField = 'adplan_value';
        } elseif ($type === 'course') {
            $enabledField = 'course_enabled';
            $modeField = 'course_mode';
            $valueField = 'course_value';
        } elseif ($type === 'partner') {
            $enabledField = 'partner_enabled';
            $modeField = 'partner_mode';
            $valueField = 'partner_value';
        } elseif ($type === 'certificate') {
            $enabledField = 'certificate_enabled';
            $modeField = 'certificate_mode';
            $valueField = 'certificate_value';
        } elseif ($type === 'special_discount') {
            $enabledField = 'special_discount_enabled';
            $modeField = 'special_discount_mode';
            $valueField = 'special_discount_value';
        } elseif ($type === 'partner_override') {
            // Partner override is percent only
            if (!(bool) ($settings->partner_override_enabled ?? false)) return 0.0;
            $pct = (float) ($settings->partner_override_percent ?? 0);
            if ($pct <= 0) return 0.0;
            return round($baseAmount * $pct / 100, 8);
        } else {
            return 0.0;
        }

        if (!(bool) ($settings->{$enabledField} ?? false)) return 0.0;

        // 1. Check Granular Settings for this specific Agent (Highest priority)
        if (!empty($meta['plan_id'])) {
            $granular = is_string($settings->granular_settings) ? json_decode($settings->granular_settings, true) : (array)($settings->granular_settings ?? []);
            $planId = (string) $meta['plan_id'];
            if (isset($granular[$type][$planId])) {
                $item = $granular[$type][$planId];
                $mode = (string) ($item['mode'] ?? 'percent');
                $value = (float) ($item['value'] ?? 0);
                if ($value > 0) {
                    if ($mode === 'fixed') {
                        return round($value, 8);
                    }
                    return round($baseAmount * $value / 100, 8);
                }
            }
        }

        // 2. Per-plan GLOBAL override rules (Master Admin controlled)
        if (($type === 'upgrade' || $type === 'registration' || $type === 'adplan' || $type === 'course') && !empty($meta['plan_type']) && !empty($meta['plan_id'])) {
            try {
                $rule = DB::table('agent_upgrade_commission_rules')
                    ->where('plan_type', (string) $meta['plan_type'])
                    ->where('plan_id', (int) $meta['plan_id'])
                    ->first();
                if ($rule && (bool) ($rule->enabled ?? false)) {
                    $mode = (string) ($rule->mode ?? 'percent');
                    $value = (float) ($rule->value ?? 0);
                    if ($value > 0) {
                        if ($mode === 'fixed') {
                            return round($value, 8);
                        }
                        return round($baseAmount * $value / 100, 8);
                    }
                }
            } catch (\Throwable $e) {
                // ignore rule read errors
            }
        }

        // 3. Fallback to Agent's General Setting
        $mode = (string) ($settings->{$modeField} ?? 'percent');
        $value = (float) ($settings->{$valueField} ?? 0);
        if ($value <= 0) return 0.0;

        if ($mode === 'fixed') {
            return round($value, 8);
        }

        // percent
        return round($baseAmount * $value / 100, 8);
    }

    /**
     * Credits agent commission into affiliate wallet with transaction.
     *
     * Idempotency: if a transaction exists with same agent + remark + trx + wallet, no-op.
     */
    public static function credit(int $agentId, string $type, float $amount, string $trx, string $details): float
    {
        $amount = (float) $amount;
        if ($amount <= 0) return 0.0;

        $remark = self::remarkFor($type);

        // Check idempotency
        $exists = Transaction::where('user_id', $agentId)
            ->where('wallet', 'affiliate')
            ->where('remark', $remark)
            ->where('trx', $trx)
            ->exists();
        if ($exists) return 0.0;

        DB::transaction(function () use ($agentId, $amount, $trx, $details, $remark) {
            /** @var User $agent */
            $agent = User::lockForUpdate()->find($agentId);
            if (!$agent) return;

            $agent->affiliate_balance = (float) ($agent->affiliate_balance ?? 0) + $amount;
            $agent->save();

            $t = new Transaction();
            $t->user_id = $agent->id;
            $t->amount = $amount;
            $t->post_balance = (float) ($agent->affiliate_balance ?? 0);
            $t->charge = 0;
            $t->trx_type = '+';
            $t->details = $details;
            $t->trx = $trx;
            $t->remark = $remark;
            $t->wallet = 'affiliate';
            $t->save();
        });

        return $amount;
    }

    /**
     * End-to-end helper (safe to call inside try/catch).
     */
    public static function process(?int $agentId, string $type, float $baseAmount, string $trx, string $details, array $meta = []): float
    {
        $agentId = (int) ($agentId ?? 0);
        if ($agentId <= 0) return 0.0;

        $agent = User::find($agentId);
        if (!$agent || !(bool) ($agent->is_agent ?? false)) return 0.0;
        // Freeze pending commission for banned/inactive agents
        try {
            if ((int) ($agent->status ?? 1) !== 1) return 0.0;
        } catch (\Throwable $e) {}

        $amount = self::calculate($agentId, $type, $baseAmount, $meta);
        if ($amount <= 0) return 0.0;

        return self::credit($agentId, $type, $amount, $trx, $details);
    }
}

