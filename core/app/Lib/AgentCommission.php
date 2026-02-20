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

        // Per-plan override rules for upgrade/registration (Master Admin controlled)
        // meta keys: plan_type, plan_id
        if (($type === 'upgrade' || $type === 'registration') && !empty($meta['plan_type']) && !empty($meta['plan_id'])) {
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

