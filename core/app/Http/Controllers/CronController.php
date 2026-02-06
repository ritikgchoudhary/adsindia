<?php

namespace App\Http\Controllers;

use App\Constants\Status;
use App\Lib\CurlRequest;
use App\Models\AdminNotification;
use App\Models\Campaign;
use App\Models\CronJob;
use App\Models\CronJobLog;
use App\Models\Transaction;
use Carbon\Carbon;

class CronController extends Controller {
    public function cron() {
        $general            = gs();
        $general->last_cron = now();
        $general->save();

        $crons = CronJob::with('schedule');

        if (request()->alias) {
            $crons->where('alias', request()->alias);
        } else {
            $crons->where('next_run', '<', now())->where('is_running', Status::YES);
        }
        $crons = $crons->get();
        foreach ($crons as $cron) {
            $cronLog              = new CronJobLog();
            $cronLog->cron_job_id = $cron->id;
            $cronLog->start_at    = now();
            if ($cron->is_default) {
                $controller = new $cron->action[0];
                try {
                    $method = $cron->action[1];
                    $controller->$method();
                } catch (\Exception $e) {
                    $cronLog->error = $e->getMessage();
                }
            } else {
                try {
                    CurlRequest::curlContent($cron->url);
                } catch (\Exception $e) {
                    $cronLog->error = $e->getMessage();
                }
            }
            $cron->last_run = now();
            $cron->next_run = now()->addSeconds($cron->schedule->interval);
            $cron->save();

            $cronLog->end_at = $cron->last_run;

            $startTime         = Carbon::parse($cronLog->start_at);
            $endTime           = Carbon::parse($cronLog->end_at);
            $diffInSeconds     = $startTime->diffInSeconds($endTime);
            $cronLog->duration = $diffInSeconds;
            $cronLog->save();
        }
        if (request()->target == 'all') {
            $notify[] = ['success', 'Cron executed successfully'];
            return back()->withNotify($notify);
        }
        if (request()->alias) {
            $notify[] = ['success', keyToTitle(request()->alias) . ' executed successfully'];
            return back()->withNotify($notify);
        }
    }

    public function expiredCampaign() {

        $campaigns = Campaign::where('status', Status::CAMPAIGN_APPROVED)->where('ends_at', '<', now())->with('conversions')->take(50)->latest()->get();
        foreach ($campaigns as $campaign) {
            $conversions     = $campaign->conversions()->where('is_paid', Status::YES)->selectRaw('SUM(user_payout+admin_commission) as total_used')->first();
            $totalUsedPayout = $conversions->total_used ?? 0;

            $unusedAmount = $campaign->budget - $totalUsedPayout;
            if ($unusedAmount < 0) {
                $unusedAmount = 0;
            }

            $advertiser = $campaign->advertiser;
            if ($unusedAmount > 0) {
                $advertiser->balance += $unusedAmount;
                $advertiser->save();

                $transaction                = new Transaction();
                $transaction->advertiser_id = $advertiser->id;
                $transaction->amount        = $unusedAmount;
                $transaction->post_balance  = $advertiser->balance;
                $transaction->charge        = 0;
                $transaction->trx_type      = '+';
                $transaction->details       = "Unused budget refund for campaign: {$campaign->title}";
                $transaction->trx           = getTrx();
                $transaction->remark        = 'campaign_refund';
                $transaction->save();

                $adminNotification                = new AdminNotification();
                $adminNotification->advertiser_id = $advertiser->id;
                $adminNotification->title         = "Unused budget refunded for campaign: {$campaign->title}";
                $adminNotification->click_url     = urlPath('admin.campaign.details', $campaign->id);
                $adminNotification->save();
            }

            $campaign->status = Status::CAMPAIGN_COMPLETED;
            $campaign->save();
        }
    }
}
