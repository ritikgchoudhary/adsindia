<?php

namespace App\Http\Controllers;

use App\Constants\Status;
use App\Models\Campaign;
use App\Models\Conversion;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;

class TrackController extends Controller {
    public function connect(Request $request, $uuid) {
        $campaign = Campaign::where('tracking_token', $uuid)->first();

        if (!$campaign) {
            $notify[] = 'Invalid tracking token';
            return responseError('invalid_token', $notify);
        }

        if ($campaign->status == Status::CAMPAIGN_APPROVED) {
            $notify[] = 'This campaign is already approved';
            return responseError('already_approved', $notify);
        }

        $campaign->status = Status::CAMPAIGN_PENDING;
        $campaign->save();

        $notify[] = 'Connected successfully';
        return responseSuccess('connected', $notify);
    }

    public function store(Request $request, $uuid) {
        $campaign = Campaign::where('tracking_token', $uuid)->with('conversions', function ($query) {
            $query->where('is_paid', Status::PAID);
        })->first();

        if (!$campaign) {
            $notify[] = 'Invalid tracking token';
            return responseError('invalid_token', $notify);
        }
        $conversionsCount = $campaign->conversions->count();

        if ($conversionsCount >= $campaign->conversion_limit) {
            $notify[] = 'Conversion limit reached';
            return responseError('conversion_limit_reached', $notify);
        }

        if ($campaign->status == Status::CAMPAIGN_COMPLETED) {
            $notify[] = 'This campaign is already completed';
            return responseError('already_completed', $notify);
        }

        if ($campaign->starts_at > now()) {
            $notify[] = 'This campaign has not started yet';
            return responseError('campaign_ended', $notify);
        }

        if ($campaign->ends_at < now()) {
            $notify[] = 'This campaign has ended';
            return responseError('campaign_ended', $notify);
        }

        $userAgent = $request->query('user_agent', $request->header('User-Agent'));
        $ip        = $request->ip();
        $username  = decrypt($request->username);
        if ($username) {
            $user = User::active()->where('username', $username)->first();
            if (!$user) {
                $notify[] = 'User not found';
                return responseError('user_not_found', $notify);
            }
        }

        $this->logConversion($campaign, $userAgent, $ip, $user);

        $notify[] = 'Tracking recorded successfully';
        return responseSuccess('track_recorded', $notify);
    }

    protected function logConversion(Campaign $campaign, $userAgent = null, $ip = null, $user) {
        $ip        = $ip ?? request()->ip();
        $userAgent = $userAgent ?? request()->userAgent();

        $alreadyExists = Conversion::where('campaign_id', $campaign->id)->where('user_id', $user->id)->exists();
        if (!$alreadyExists) {
            $conversion                   = new Conversion();
            $conversion->campaign_id      = $campaign->id;
            $conversion->user_id          = $user->id;
            $conversion->user_payout      = $campaign->payout_per_conversion;
            $conversion->admin_commission = $campaign->admin_commission;
            $conversion->ip_address       = $ip;
            $conversion->user_agent       = $userAgent;
            $conversion->tracking_type    = $campaign->tracking_type;

            $trx = getTrx();

            // If campaign is paused or completed — mark as unpaid
            if ($campaign->is_paused) {
                $conversion->is_paid = Status::UNPAID;
                $conversion->details = 'Campaign is currently paused';
                $conversion->save();
            } else if ($campaign->status == Status::CAMPAIGN_COMPLETED) {
                $conversion->is_paid = Status::UNPAID;
                $conversion->details = 'Campaign has been completed';
                $conversion->save();
            } else {
                $conversion->is_paid = Status::PAID;
                $conversion->details = 'Conversion paid successfully';
                $conversion->save();

                $user->balance += $campaign->payout_per_conversion;
                $user->save();

                $transaction               = new Transaction();
                $transaction->user_id      = $user->id;
                $transaction->amount       = $campaign->payout_per_conversion;
                $transaction->post_balance = $user->balance;
                $transaction->charge       = 0;
                $transaction->trx_type     = '+';
                $transaction->details      = 'Affiliate Commission for Campaign: ' . $campaign->title;
                $transaction->trx          = $trx;
                $transaction->remark       = 'affiliate_commission';
                $transaction->save();

                notify($user, 'CONVERSION_CONFIRMED', [
                    'trx'            => $trx,
                    'campaign_title' => $campaign->title,
                    'amount'         => showAmount($campaign->payout_per_conversion),
                    'post_balance'   => showAmount($user->balance),
                ]);

                $advertiser = $campaign->advertiser;

                notify($advertiser, 'CAMPAIGN_CONVERSION_CONFIRMED', [
                    'trx'            => $trx,
                    'campaign_title' => $campaign->title,
                    'user_username'  => $user->username,
                ]);
            }
        }
    }
}
