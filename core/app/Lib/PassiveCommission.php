<?php

namespace App\Lib;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PassiveCommission
{
    public const REMARK = 'passive_commission';

    /**
     * Credits Level 1 passive commission (sponsor of sponsor) into their affiliate wallet.
     */
    public static function creditForPackage(User $buyer, int $packageId, float $paidAmount, string $trx, string $packageName = ''): float
    {
        // 1. Level 1 Sponsor (Direct)
        $directSponsorId = (int) ($buyer->ref_by ?? 0);
        if ($directSponsorId <= 0) return 0.0;

        $directSponsor = User::find($directSponsorId);
        if (!$directSponsor) return 0.0;

        // 2. Level 2 Sponsor (Passive Receiver)
        $passiveSponsorId = (int) ($directSponsor->ref_by ?? 0);
        if ($passiveSponsorId <= 0) return 0.0;

        $passiveSponsor = User::find($passiveSponsorId);
        if (!$passiveSponsor) return 0.0;

        // Freeze for banned/inactive
        try {
            if ((int) ($passiveSponsor->status ?? 1) !== 1) return 0.0;
        } catch (\Throwable $e) {}

        $commissionAmount = 0.0;

        // A. If Passive Receiver is an AGENT, use Agent rules
        if ((bool) ($passiveSponsor->is_agent ?? false)) {
            $commissionAmount = AgentCommission::calculate(
                $passiveSponsorId,
                'passive',
                $paidAmount,
                ['plan_type' => 'package', 'plan_id' => $packageId]
            );
        } 
        // B. Regular User passive rule
        else {
            $rule = DB::table('direct_affiliate_commissions')->where('package_id', (int) $packageId)->first();
            if ($rule && (bool) ($rule->enabled ?? false)) {
                $commissionAmount = (float) ($rule->passive_amount ?? 0);
            }
        }

        if ($commissionAmount <= 0) return 0.0;

        // Idempotency: use 'agent_passive_commission' or 'passive_commission' consistently?
        // If it's an agent, AgentCommission::credit will use 'agent_passive_commission' remark.
        // Let's use AgentCommission::credit if it's an agent, else custom credit.

        if ((bool) ($passiveSponsor->is_agent ?? false)) {
            return AgentCommission::credit(
                $passiveSponsorId,
                'passive',
                $commissionAmount,
                $trx,
                'Passive commission from Team Member: User#' . (int)$buyer->id . ' | Referred By: User#' . (int)$directSponsor->id . ' (' . $packageName . ')'
            );
        }

        // Standard credit for regular users
        $remark = self::REMARK;
        $exists = Transaction::where('user_id', $passiveSponsorId)
            ->where('wallet', 'affiliate')
            ->where('remark', $remark)
            ->where('trx', $trx)
            ->exists();
        if ($exists) return 0.0;

        $details = 'Passive commission from User#' . (int) $buyer->id . ' referred by User#' . (int) $directSponsor->id;
        if ($packageName) $details .= ' – Package: ' . $packageName;

        DB::transaction(function () use ($passiveSponsorId, $commissionAmount, $trx, $details, $remark) {
            $u = User::lockForUpdate()->find($passiveSponsorId);
            if (!$u) return;

            $u->affiliate_balance = (float) ($u->affiliate_balance ?? 0) + (float) $commissionAmount;
            $u->save();

            $t = new Transaction();
            $t->user_id = $u->id;
            $t->amount = (float) $commissionAmount;
            $t->post_balance = (float) ($u->affiliate_balance ?? 0);
            $t->charge = 0;
            $t->trx_type = '+';
            $t->details = $details;
            $t->trx = $trx;
            $t->remark = $remark;
            $t->wallet = 'affiliate';
            $t->save();
        });

        return round($commissionAmount, 8);
    }
}
