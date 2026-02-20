<?php

namespace App\Lib;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CommissionReversal
{
    public const REVERSAL_REMARK = 'commission_reversal';

    /**
     * Reverse (debit) a previously credited affiliate-wallet commission by trx+remark.
     *
     * - Finds original credit transaction in affiliate wallet.
     * - Creates a debit transaction with remark `commission_reversal` (idempotent by trx).
     * - Decreases affiliate_balance (can go negative to preserve accounting).
     */
    public static function reverseAffiliateCredit(int $userId, string $origTrx, string $origRemark, string $reason = 'Refund / reversal'): float
    {
        $userId = (int) $userId;
        if ($userId <= 0 || trim($origTrx) === '' || trim($origRemark) === '') return 0.0;

        // Idempotent: if reversal already exists, no-op
        $already = Transaction::where('user_id', $userId)
            ->where('wallet', 'affiliate')
            ->where('remark', self::REVERSAL_REMARK)
            ->where('trx', $origTrx)
            ->exists();
        if ($already) return 0.0;

        $orig = Transaction::where('user_id', $userId)
            ->where('wallet', 'affiliate')
            ->where('remark', $origRemark)
            ->where('trx', $origTrx)
            ->where('trx_type', '+')
            ->first();
        if (!$orig) return 0.0;

        $amount = (float) ($orig->amount ?? 0);
        if ($amount <= 0) return 0.0;

        DB::transaction(function () use ($userId, $origTrx, $origRemark, $amount, $reason) {
            /** @var ?User $u */
            $u = User::lockForUpdate()->find($userId);
            if (!$u) return;

            $u->affiliate_balance = (float) ($u->affiliate_balance ?? 0) - $amount;
            $u->save();

            $t = new Transaction();
            $t->user_id = $u->id;
            $t->amount = $amount;
            $t->post_balance = (float) ($u->affiliate_balance ?? 0);
            $t->charge = 0;
            $t->trx_type = '-';
            $t->details = $reason . ' | Reversed: ' . $origRemark;
            $t->trx = $origTrx;
            $t->remark = self::REVERSAL_REMARK;
            $t->wallet = 'affiliate';
            $t->save();
        });

        return round($amount, 8);
    }
}

