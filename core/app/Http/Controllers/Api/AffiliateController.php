<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Withdrawal;
use App\Constants\Status;
use Carbon\Carbon;

class AffiliateController extends Controller
{
    private function parseBaseAmountFromDetails(string $details): ?float
    {
        $details = trim($details);
        if ($details === '') return null;
        if (preg_match('/Base:\s*₹?\s*([0-9]+(?:\.[0-9]+)?)/i', $details, $m)) {
            return (float) $m[1];
        }
        if (preg_match('/Paid:\s*₹?\s*([0-9]+(?:\.[0-9]+)?)/i', $details, $m)) {
            return (float) $m[1];
        }
        return null;
    }

    public function getAffiliateIncome()
    {
        $user = auth()->user();
        $isAgent = (bool) ($user->is_agent ?? false);
        $general = gs();

        $today = now()->startOfDay();
        $thisWeek = now()->startOfWeek();
        $thisMonth = now()->startOfMonth();

        // Affiliate wallet balance (separate from main wallet)
        $affiliateBalance = (float) ($user->affiliate_balance ?? 0);

        $remarks = [
            'referral_commission',
            'downline_commission',
            'affiliate_commission',
            'direct_affiliate_commission',
            'agent_registration_commission',
            'agent_kyc_commission',
            'agent_withdraw_fee_commission',
            'agent_upgrade_commission',
            'agent_partner_override_commission',
        ];

        // Get all affiliate earnings
        $income = [
            'today' => Transaction::where('user_id', $user->id)
                ->whereIn('remark', $remarks)
                ->where('wallet', 'affiliate')
                ->where('trx_type', '+')
                ->where('created_at', '>=', $today)
                ->sum('amount'),
            'this_week' => Transaction::where('user_id', $user->id)
                ->whereIn('remark', $remarks)
                ->where('wallet', 'affiliate')
                ->where('trx_type', '+')
                ->where('created_at', '>=', $thisWeek)
                ->sum('amount'),
            'this_month' => Transaction::where('user_id', $user->id)
                ->whereIn('remark', $remarks)
                ->where('wallet', 'affiliate')
                ->where('trx_type', '+')
                ->where('created_at', '>=', $thisMonth)
                ->sum('amount'),
            'total' => Transaction::where('user_id', $user->id)
                ->whereIn('remark', $remarks)
                ->where('wallet', 'affiliate')
                ->where('trx_type', '+')
                ->sum('amount'),
        ];

        // 50% affiliate earning (commission) – sum of referral_commission transactions
        $affiliateEarning = Transaction::where('user_id', $user->id)
            ->where('remark', 'referral_commission')
            ->where('wallet', 'affiliate')
            ->where('trx_type', '+')
            ->sum('amount');

        $referralCount = User::where('ref_by', $user->id)->count();

        $referralEarnings = [
            'direct_affiliate_commission' => (float) Transaction::where('user_id', $user->id)->where('remark', 'direct_affiliate_commission')->where('wallet', 'affiliate')->where('trx_type', '+')->sum('amount'),
            'referral_commission' => (float) Transaction::where('user_id', $user->id)->where('remark', 'referral_commission')->where('wallet', 'affiliate')->where('trx_type', '+')->sum('amount'),
            'downline_commission' => (float) Transaction::where('user_id', $user->id)->where('remark', 'downline_commission')->where('wallet', 'affiliate')->where('trx_type', '+')->sum('amount'),
            'affiliate_commission' => (float) Transaction::where('user_id', $user->id)->where('remark', 'affiliate_commission')->where('wallet', 'affiliate')->where('trx_type', '+')->sum('amount'),
            'agent_registration_commission' => (float) Transaction::where('user_id', $user->id)->where('remark', 'agent_registration_commission')->where('wallet', 'affiliate')->where('trx_type', '+')->sum('amount'),
            'agent_kyc_commission' => (float) Transaction::where('user_id', $user->id)->where('remark', 'agent_kyc_commission')->where('wallet', 'affiliate')->where('trx_type', '+')->sum('amount'),
            'agent_withdraw_fee_commission' => (float) Transaction::where('user_id', $user->id)->where('remark', 'agent_withdraw_fee_commission')->where('wallet', 'affiliate')->where('trx_type', '+')->sum('amount'),
            'agent_upgrade_commission' => (float) Transaction::where('user_id', $user->id)->where('remark', 'agent_upgrade_commission')->where('wallet', 'affiliate')->where('trx_type', '+')->sum('amount'),
            'agent_partner_override_commission' => (float) Transaction::where('user_id', $user->id)->where('remark', 'agent_partner_override_commission')->where('wallet', 'affiliate')->where('trx_type', '+')->sum('amount'),
        ];

        $withdrawals = Withdrawal::where('user_id', $user->id)
            ->where('wallet', 'affiliate')
            ->orderBy('id', 'desc')
            ->limit(30)
            ->get()
            ->map(function ($w) {
                return [
                    'id' => $w->id,
                    'trx' => $w->trx,
                    'amount' => (float) $w->amount,
                    'fee' => (float) ($w->charge ?? 0),
                    'net_amount' => (float) (($w->amount ?? 0) - ($w->charge ?? 0)),
                    'status' => (int) $w->status,
                    'status_text' => $this->getWithdrawStatusText((int) $w->status),
                    'reason' => (string) ($w->admin_feedback ?? ''),
                    'created_at' => $w->created_at ? $w->created_at->format('Y-m-d H:i:s') : null,
                    'created_at_human' => $w->created_at ? $w->created_at->diffForHumans() : null,
                ];
            });

        $historyTrx = Transaction::where('user_id', $user->id)
            ->whereIn('remark', $remarks)
            ->where('wallet', 'affiliate')
            ->where('trx_type', '+')
            ->latest()
            ->limit(50)
            ->get();

        // Extract referred user id from transaction details (we store like: "User#123" or "ADS123")
        $fromUserIdByTrxId = [];
        $fromUserIds = [];
        foreach ($historyTrx as $trx) {
            $details = (string) ($trx->details ?? '');
            $fromId = null;
            if (preg_match('/User#(\d+)/', $details, $m)) {
                $fromId = (int) $m[1];
            } elseif (preg_match('/ADS(\d+)/', $details, $m)) {
                $fromId = (int) $m[1];
            }
            if ($fromId && $fromId > 0) {
                $fromUserIdByTrxId[(int) $trx->id] = $fromId;
                $fromUserIds[] = $fromId;
            }
        }

        $fromUsers = collect();
        if (!empty($fromUserIds)) {
            $fromUsers = User::whereIn('id', array_values(array_unique($fromUserIds)))
                ->get(['id', 'username', 'firstname', 'lastname', 'email', 'mobile', 'dial_code', 'ref_by'])
                ->keyBy('id');
        }

        $history = $historyTrx->map(function ($trx) use ($fromUsers, $fromUserIdByTrxId) {
            $fromId = $fromUserIdByTrxId[(int) $trx->id] ?? null;
            /** @var ?User $refUser */
            $refUser = $fromId ? $fromUsers->get($fromId) : null;
            $phone = null;
            if ($refUser) {
                $phone = trim(((string) ($refUser->dial_code ?? '')) . ((string) ($refUser->mobile ?? '')));
                if ($phone === '') $phone = null;
            }

            return [
                'id' => (int) $trx->id,
                'created_at' => $trx->created_at ? $trx->created_at->format('Y-m-d H:i:s') : null,
                // Referred user details (requested columns)
                'affiliate_id' => $refUser ? ('ADS' . (int) $refUser->id) : null,
                'user_name' => $refUser ? trim(((string) ($refUser->firstname ?? '')) . ' ' . ((string) ($refUser->lastname ?? ''))) : null,
                'username' => $refUser ? (string) ($refUser->username ?? '') : null,
                'email' => $refUser ? (string) ($refUser->email ?? '') : null,
                'phone' => $phone,
                'sponsor_id' => ($refUser && (int) ($refUser->ref_by ?? 0) > 0) ? ('ADS' . (int) $refUser->ref_by) : null,
                'final_amount' => (float) $trx->amount,
                // Legacy fields (keep for backward compatibility)
                'source' => match ((string) ($trx->remark ?? '')) {
                    'direct_affiliate_commission' => 'Direct Affiliate',
                    'referral_commission' => 'Referral',
                    'affiliate_commission' => 'Affiliate',
                    'downline_commission' => 'Downline',
                    'agent_registration_commission' => 'Registration',
                    'agent_kyc_commission' => 'KYC',
                    'agent_withdraw_fee_commission' => 'Withdrawal Fee',
                    'agent_upgrade_commission' => 'Upgrade',
                    'agent_partner_override_commission' => 'Partner Override',
                    default => 'Commission',
                },
                'amount' => (float) $trx->amount,
                'trx' => (string) ($trx->trx ?? ''),
                'details' => (string) ($trx->details ?? ''),
            ];
        });

        $commissionLog = $historyTrx->map(function ($trx) use ($fromUsers, $fromUserIdByTrxId) {
            $fromId = $fromUserIdByTrxId[(int) $trx->id] ?? null;
            /** @var ?User $refUser */
            $refUser = $fromId ? $fromUsers->get($fromId) : null;
            $name = $refUser ? trim(((string) ($refUser->firstname ?? '')) . ' ' . ((string) ($refUser->lastname ?? ''))) : null;
            if ($name === '') $name = null;

            return [
                'id' => (int) $trx->id,
                'user_name' => $name ?: ($refUser ? (string) ($refUser->username ?? '') : null),
                'transaction_id' => (string) ($trx->trx ?? ''),
                'amount' => $this->parseBaseAmountFromDetails((string) ($trx->details ?? '')),
                'commission_amount' => (float) ($trx->amount ?? 0),
                'type' => match ((string) ($trx->remark ?? '')) {
                    'direct_affiliate_commission' => 'Direct Registration Income',
                    'agent_kyc_commission' => 'KYC Income',
                    'agent_withdraw_fee_commission' => 'Withdrawal Fee Income',
                    'agent_upgrade_commission' => 'Upgrade Income',
                    'agent_registration_commission' => 'Registration Income',
                    default => 'Other',
                },
                'date' => $trx->created_at ? $trx->created_at->format('Y-m-d H:i:s') : null,
            ];
        })->values();

        $breakdown = [
            'direct_registration_income' => (float) ($referralEarnings['direct_affiliate_commission'] ?? 0),
            'kyc_income' => (float) ($referralEarnings['agent_kyc_commission'] ?? 0),
            'withdrawal_fee_income' => (float) ($referralEarnings['agent_withdraw_fee_commission'] ?? 0),
            'upgrade_income' => (float) ($referralEarnings['agent_upgrade_commission'] ?? 0),
            'total_affiliate_income' => (float) ($income['total'] ?? 0),
            'withdrawable_affiliate_balance' => (float) ($affiliateBalance ?? 0),
        ];

        return responseSuccess('affiliate_income', ['Affiliate income retrieved successfully'], [
            'is_agent' => (bool) $isAgent,
            'income' => $income,
            'affiliate_earning' => $affiliateEarning, // 50% commission total
            'referral_count' => (int) $referralCount,
            'referral_earnings' => $referralEarnings,
            'history' => $history,
            'commission_log' => $commissionLog,
            'breakdown' => $breakdown,
            'withdrawals' => $withdrawals,
            'affiliate_balance' => $affiliateBalance,
            'currency_symbol' => $general->cur_sym ?? '₹',
        ]);
    }

    private function getWithdrawStatusText(int $status): string
    {
        $map = [
            Status::PAYMENT_INITIATE => 'Fee Pending',
            Status::PAYMENT_PENDING => 'Processing',
            Status::PAYMENT_SUCCESS => 'Success',
            Status::PAYMENT_REJECT => 'Rejected',
        ];
        return $map[$status] ?? 'Unknown';
    }
}
