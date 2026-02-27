<?php

namespace App\Http\Controllers\Gateway\RupeeRush;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Deposit;
use App\Constants\Status;
use App\Lib\RupeeRushGateway;
use App\Models\User;
use App\Models\AdPackage;
use App\Models\PartnerProgram;
use App\Models\VipSubscription;

class ProcessController extends Controller
{
    /**
     * IPN Webhook URL handler for RupeeRush
     */
    public function ipn(Request $request)
    {
        $payload = $request->all();
        \Log::info('RupeeRush IPN Received:', ['payload' => $payload]);

        // Verify Merchant Number
        $merNo = RupeeRushGateway::merNo();
        if (($payload['merNo'] ?? '') !== $merNo) {
            \Log::error('RupeeRush IPN Error: Invalid merchant number.');
            return response('fail', 400);
        }

        // Verify Signature
        if (!RupeeRushGateway::verifyCallback($payload)) {
            \Log::error('RupeeRush IPN Error: Signature verification failed.');
            return response('fail', 400);
        }

        // Check required fields
        if (!isset($payload['outTradeNo']) || !isset($payload['payState'])) {
            \Log::error('RupeeRush IPN Error: Missing outTradeNo or payState.');
            return response('fail', 400);
        }

        $trx = $payload['outTradeNo'];
        $payState = $payload['payState'];
        $totalAmount = $payload['totalAmount'] ?? 0;

        // If payment failed or pending, we log it and ignore processing
        if ($payState !== '0000') {
            \Log::info("RupeeRush IPN: Payment not successful. Status: {$payState} for TRX: $trx");
            return response('SUCCESS', 200); // Send SUCCESS back to stop further retries from gateway
        }

        // Find Cache Session
        $cacheKey = "rupeerush_payment_$trx";
        $session = cache()->get($cacheKey);

        if (!$session) {
            \Log::error("RupeeRush IPN Error: No cache session found for TRX: $trx");
            return response('fail', 400);
        }

        if (($session['status'] ?? '') === 'success') {
            \Log::info("RupeeRush IPN: Payment already processed for TRX: $trx");
            return response('SUCCESS', 200);
        }

        $paymentType = $session['type'] ?? '';

        try {
            if ($paymentType === 'registration') {
                $this->processRegistration($session, $trx, $totalAmount);
            } elseif ($paymentType === 'kyc_fee') {
                $this->processKycFee($session, $trx, $totalAmount);
            } elseif ($paymentType === 'course_plan') {
                $this->processCoursePlan($session, $trx, $totalAmount);
            } elseif ($paymentType === 'ad_plan_web') {
                $this->processAdPlan($session, $trx, $totalAmount);
            } elseif ($paymentType === 'partner_plan') {
                $this->processPartnerPlan($session, $trx, $totalAmount);
            } elseif ($paymentType === 'withdraw_gst') {
                $this->processWithdrawGst($session, $trx, $totalAmount);
            } elseif ($paymentType === 'ad_certificate_course' || $paymentType === 'ad_certificate_view') {
                $this->processAdCertificate($session, $trx, $totalAmount, $paymentType);
            } elseif ($paymentType === 'special_agent_payment') {
                $this->processSpecialAgent($session, $trx, $totalAmount);
            } else {
                \Log::error("RupeeRush IPN Error: Unknown payment type {$paymentType}");
            }
        } catch (\Exception $e) {
            \Log::error("RupeeRush IPN Exception during processing: " . $e->getMessage());
            return response('fail', 400);
        }

        // Mark session as success and clear after some time
        $session['status'] = 'success';
        cache()->put($cacheKey, $session, now()->addHours(2));

        return response('SUCCESS', 200);
    }

    private function processRegistration($session, $trx, $amount)
    {
        // ... (Registration processing logic handled via session flags later by redirect, but we can set session success here)
        // Usually handled by polling /confirm route, so just updating cache session is enough!
    }

    private function processKycFee($session, $trx, $amount)
    {
        $user = User::find($session['user_id']);
        if ($user && $user->has_paid_kyc_fee == 0) {
            $user->has_paid_kyc_fee = 1;
            $user->kyc_fee_trx = $trx;
            $user->kyc_fee_paid_at = now();
            $user->save();
        }
    }

    private function processCoursePlan($session, $trx, $amount)
    {
        $user = User::find($session['user_id']);
        if ($user) {
            $user->vip_level = 1; // Assuming default 1 or find logic based on ID
            $user->save();
        }
    }

    private function processAdPlan($session, $trx, $amount)
    {
        $packageId = $session['package_id'];
        $user = User::find($session['user_id']);
        $package = AdPackage::find($packageId);

        if ($user && $package) {
            $sub = new VipSubscription();
            $sub->user_id = $user->id;
            $sub->ad_package_id = $package->id;
            $sub->status = 1;
            $sub->amount_paid = $amount;
            $sub->trx = $trx;
            $sub->expires_at = now()->addDays($package->validity_days);
            $sub->daily_ad_limit = $package->daily_limit;
            $sub->save();

            $user->has_bought_ads_plan = 1;
            $user->save();
        }
    }

    private function processPartnerPlan($session, $trx, $amount)
    {
         $user = User::find($session['user_id']);
         if ($user) {
             $user->is_partner = 1;
             $user->partner_join_date = now();
             $user->save();
         }
    }

    private function processWithdrawGst($session, $trx, $amount)
    {
        $req = \App\Models\Withdrawal::where('trx', $session['withdraw_trx'] ?? '')->first();
        if ($req) {
            $req->gst_paid = 1;
            $req->gst_trx = $trx;
            $req->save();
        }
    }

    private function processAdCertificate($session, $trx, $amount, $type)
    {
        $user = User::find($session['user_id']);
        if ($user) {
            $user->has_ad_certificate = 1;
            $user->save();
        }
    }

    private function processSpecialAgent($session, $trx, $amount)
    {
        $user = User::find($session['user_id']);
        if ($user) {
            $user->special_agent_balance += $amount;
            $user->save();
        }
    }
}
