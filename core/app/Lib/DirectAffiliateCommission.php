<?php

namespace App\Lib;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DirectAffiliateCommission
{
    public const REMARK = 'direct_affiliate_commission';

    /**
     * Credits direct affiliate commission (all users) into sponsor's affiliate wallet.
     *
     * Rules:
     * - Commission is package-wise fixed amount (enable/disable) from `direct_affiliate_commissions`
     * - Credit only when sponsor exists and is not banned
     * - Idempotent by (sponsor_id, wallet=affiliate, remark, trx)
     */
    public static function creditForPackage(User $referredUser, int $packageId, float $paidAmount, string $trx, string $packageName = ''): float
    {
        $sponsorId = (int) ($referredUser->ref_by ?? 0);
        if ($sponsorId <= 0) return 0.0;

        // Sponsor must exist
        $sponsor = User::find($sponsorId);
        if (!$sponsor) return 0.0;

        // If sponsor is banned/inactive, freeze (skip credit)
        try {
            $status = (int) ($sponsor->status ?? 1);
            if ($status !== 1) return 0.0;
        } catch (\Throwable $e) {}

        // Load rule
        $rule = DB::table('direct_affiliate_commissions')->where('package_id', (int) $packageId)->first();
        if (!$rule || !(bool) ($rule->enabled ?? false)) return 0.0;

        $commissionAmount = (float) ($rule->commission_amount ?? 0);
        if ($commissionAmount <= 0) return 0.0;

        // Idempotency
        $exists = Transaction::where('user_id', $sponsorId)
            ->where('wallet', 'affiliate')
            ->where('remark', self::REMARK)
            ->where('trx', $trx)
            ->exists();
        if ($exists) return 0.0;

        $details = 'Direct affiliate commission from User#' . (int) $referredUser->id;
        if ($packageName) {
            $details .= ' – Package: ' . $packageName;
        }
        $details .= ' (Paid: ' . (float) $paidAmount . ')';

        DB::transaction(function () use ($sponsorId, $commissionAmount, $trx, $details) {
            /** @var User $u */
            $u = User::lockForUpdate()->find($sponsorId);
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
            $t->remark = self::REMARK;
            $t->wallet = 'affiliate';
            $t->save();
        });

        return round($commissionAmount, 8);
    }
}

