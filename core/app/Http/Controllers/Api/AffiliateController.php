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
            'agent_course_commission',
            'agent_adplan_commission',
            'agent_partner_commission',
            'agent_certificate_commission',
            'agent_partner_override_commission',
        ];

        $referralCount = User::where('ref_by', $user->id)->count();

        // Get all affiliate earnings
        $income = [
            'today' => Transaction::where('user_id', $user->id)->whereIn('remark', $remarks)->where('wallet', 'affiliate')->where('trx_type', '+')->where('created_at', '>=', $today)->sum('amount'),
            'this_week' => Transaction::where('user_id', $user->id)->whereIn('remark', $remarks)->where('wallet', 'affiliate')->where('trx_type', '+')->where('created_at', '>=', $thisWeek)->sum('amount'),
            'this_month' => Transaction::where('user_id', $user->id)->whereIn('remark', $remarks)->where('wallet', 'affiliate')->where('trx_type', '+')->where('created_at', '>=', $thisMonth)->sum('amount'),
            'total' => Transaction::where('user_id', $user->id)->whereIn('remark', $remarks)->where('wallet', 'affiliate')->where('trx_type', '+')->sum('amount'),
        ];

        // Sum individual categories for breakdown
        $referralEarnings = [
            'direct_reg' => (float) Transaction::where('user_id', $user->id)->whereIn('remark', ['direct_affiliate_commission', 'agent_registration_commission'])->where('wallet', 'affiliate')->where('trx_type', '+')->sum('amount'),
            'kyc' => (float) Transaction::where('user_id', $user->id)->where('remark', 'agent_kyc_commission')->where('wallet', 'affiliate')->where('trx_type', '+')->sum('amount'),
            'withdraw_fee' => (float) Transaction::where('user_id', $user->id)->where('remark', 'agent_withdraw_fee_commission')->where('wallet', 'affiliate')->where('trx_type', '+')->sum('amount'),
            'upgrade' => (float) Transaction::where('user_id', $user->id)->whereIn('remark', ['agent_upgrade_commission', 'agent_course_commission'])->where('wallet', 'affiliate')->where('trx_type', '+')->sum('amount'),
            'ad_cert' => (float) Transaction::where('user_id', $user->id)->where('remark', 'agent_certificate_commission')->where('wallet', 'affiliate')->where('trx_type', '+')->sum('amount'),
            'ad_plan' => (float) Transaction::where('user_id', $user->id)->where('remark', 'agent_adplan_commission')->where('wallet', 'affiliate')->where('trx_type', '+')->sum('amount'),
            'partner' => (float) Transaction::where('user_id', $user->id)->whereIn('remark', ['agent_partner_commission', 'agent_partner_override_commission'])->where('wallet', 'affiliate')->where('trx_type', '+')->sum('amount'),
        ];

        $historyTrx = Transaction::where('user_id', $user->id)
            ->whereIn('remark', $remarks)
            ->where('wallet', 'affiliate')
            ->where('trx_type', '+')
            ->latest()
            ->limit(100)
            ->get();

        // Map fromUserIds
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
            $fromUsers = User::whereIn('id', array_unique($fromUserIds))->get()->keyBy('id');
        }

        $commissionLog = $historyTrx->map(function ($trx) use ($fromUsers, $fromUserIdByTrxId) {
            $fromId = $fromUserIdByTrxId[(int) $trx->id] ?? null;
            $refUser = $fromId ? $fromUsers->get($fromId) : null;
            $name = $refUser ? trim(((string) ($refUser->firstname ?? '')) . ' ' . ((string) ($refUser->lastname ?? ''))) : null;
            if ($name === '') $name = null;
            $phone = $refUser ? trim((string) ($refUser->mobile ?? '')) : null;

            return [
                'id' => (int) $trx->id,
                'affiliate_id' => $refUser ? ('ADS' . $refUser->id) : '—',
                'name' => $name ?: '—',
                'email' => $refUser->email ?? '—',
                'phone' => $phone ?: '—',
                'sponsor_id' => $refUser && $refUser->ref_by ? ('ADS' . $refUser->ref_by) : '—',
                'transaction_id' => (string) ($trx->trx ?? ''),
                'amount' => $this->parseBaseAmountFromDetails((string) ($trx->details ?? '')),
                'commission_amount' => (float) ($trx->amount ?? 0),
                'type' => match ((string) ($trx->remark ?? '')) {
                    'direct_affiliate_commission', 'agent_registration_commission' => 'Direct Registration',
                    'agent_kyc_commission' => 'KYC Income',
                    'agent_withdraw_fee_commission' => 'Withdrawal Fee Income',
                    'agent_upgrade_commission', 'agent_course_commission' => 'Upgrade Income',
                    'agent_certificate_commission' => 'Ad Certificate Income',
                    'agent_adplan_commission' => 'Ads Plan Income',
                    'agent_partner_commission', 'agent_partner_override_commission' => 'Partner Income',
                    default => 'Other Commission',
                },
                'date' => $trx->created_at ? $trx->created_at->format('Y-m-d H:i:s') : null,
            ];
        })->values();

        $periods = [
            'today' => $today,
            'this_week' => $thisWeek,
            'this_month' => $thisMonth,
            'all_time' => null,
        ];

        $breakdowns = [];
        foreach ($periods as $key => $date) {
            $q = Transaction::where('user_id', $user->id)->whereIn('remark', $remarks)->where('wallet', 'affiliate')->where('trx_type', '+');
            if ($date) {
                $q->where('created_at', '>=', $date);
            }

            $breakdowns[$key] = [
                'direct_registration_income' => (float) (clone $q)->whereIn('remark', ['direct_affiliate_commission', 'agent_registration_commission'])->sum('amount'),
                'kyc_income' => (float) (clone $q)->where('remark', 'agent_kyc_commission')->sum('amount'),
                'withdrawal_fee_income' => (float) (clone $q)->where('remark', 'agent_withdraw_fee_commission')->sum('amount'),
                'upgrade_income' => (float) (clone $q)->whereIn('remark', ['agent_upgrade_commission', 'agent_course_commission'])->sum('amount'),
                'ad_certificate_income' => (float) (clone $q)->where('remark', 'agent_certificate_commission')->sum('amount'),
                'ads_plan_income' => (float) (clone $q)->where('remark', 'agent_adplan_commission')->sum('amount'),
                'partner_program_income' => (float) (clone $q)->whereIn('remark', ['agent_partner_commission', 'agent_partner_override_commission'])->sum('amount'),
                'total' => (float) (clone $q)->sum('amount'),
            ];
        }

        return responseSuccess('affiliate_income', ['Affiliate income retrieved successfully'], [
            'is_agent' => (bool) $isAgent,
            'income' => $income,
            'affiliate_earning' => (float) ($income['total'] ?? 0),
            'referral_count' => (int) $referralCount,
            'commission_log' => $commissionLog,
            'breakdowns' => $breakdowns,
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
