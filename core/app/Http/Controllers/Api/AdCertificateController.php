<?php

namespace App\Http\Controllers\Api;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Lib\WatchPayGateway;
use App\Models\Transaction;
use Illuminate\Http\Request;

class AdCertificateController extends Controller
{
    private const PRICE = 1250.0;
    private const REMARK = 'ad_certificate_fee';

    private function hasAdCertificate($user, $type = 'course'): bool
    {
        return $type === 'view' ? (bool)$user->has_ad_certificate_view : (bool)$user->has_ad_certificate;
    }

    public function status(Request $request)
    {
        $user = auth()->user();
        if ($user) $user->refresh();
        $type = $request->input('type', 'course');
        $general = gs();

        return responseSuccess('ad_certificate_status', ['Ad Certificate status'], [
            'has_ad_certificate' => $this->hasAdCertificate($user, $type),
            'price' => (float) self::PRICE,
            'currency_symbol' => $general->cur_sym ?? '₹',
        ]);
    }

    public function purchase(Request $request)
    {
        $user = auth()->user();
        $general = gs();
        $gateway = $request->input('gateway', 'watchpay');
        $type = $request->input('type', 'course'); // 'course' or 'view'
        
        $gw = \App\Models\Gateway::where('alias', $gateway)->first();
        if (!$gw || $gw->status != 1) {
            return responseError('gateway_unavailable', ['Selected payment gateway is currently unavailable.']);
        }

        if ($this->hasAdCertificate($user, $type)) {
            return responseError('already_purchased', ['This Ad Certificate is already purchased.']);
        }

        $trx = getTrx();
        $remark = ($type === 'view') ? 'ad_certificate_view_fee' : 'ad_certificate_fee';
        
        $deposit = new \App\Models\Deposit();
        $deposit->user_id = $user->id;
        $deposit->method_code = $gw->code;
        $deposit->amount = (float) self::PRICE;
        $deposit->method_currency = $general->cur_text;
        $deposit->charge = 0;
        $deposit->rate = 1;
        $deposit->final_amount = (float) self::PRICE;
        $deposit->btc_amount = 0;
        $deposit->btc_wallet = '';
        $deposit->trx = $trx;
        $deposit->remark = $remark;
        $deposit->status = Status::PAYMENT_INITIATE;
        $deposit->save();

        cache()->put($gateway . '_payment_' . $trx, [
            'type' => 'ad_certificate',
            'cert_type' => $type,
            'user_id' => $user->id,
            'amount' => (float) self::PRICE,
            'status' => 'pending',
            'created_at' => now()->format('Y-m-d H:i:s'),
        ], now()->addHours(2));

        $backPath = ($type === 'view') ? '/user/certificates' : '/user/courses';
        $base = $request->getSchemeAndHttpHost() ?: rtrim((string) config('app.url'), '/');
        $pageUrl = $base . $backPath . '?' . $gateway . '_trx=' . urlencode($trx) . '&ad_certificate=1';
        $notifyUrl = $base . '/ipn/' . $gateway;

        $goodsName = ($type === 'view') ? 'Ad Certificate (View)' : 'Ad Certificate (Course)';

        try {
            if ($gateway == 'simplypay') {
                $sp = \App\Lib\SimplyPayGateway::createPayment([
                    'merOrderNo' => $trx,
                    'amount' => (float) self::PRICE,
                    'goodsName' => $goodsName,
                    'returnUrl' => $pageUrl,
                    'notifyUrl' => $notifyUrl,
                    'name' => $user->fullname ?: $user->username,
                    'email' => $user->email,
                    'mobile' => $user->mobile,
                    'attach' => 'user_id:' . $user->id
                ]);
                $paymentUrl = $sp['pay_link'];
            } elseif ($gateway == 'rupeerush') {
                $ap = \App\Lib\RupeeRushGateway::createPayment([
                    'outTradeNo' => $trx,
                    'totalAmount' => (float) self::PRICE,
                    'notifyUrl' => $notifyUrl,
                    'payViewUrl' => $pageUrl,
                    'payName' => $user->fullname ?: $user->username,
                    'payEmail' => $user->email,
                    'payPhone' => $user->mobile,
                ]);
                $paymentUrl = $ap['pay_link'];
            } else {
                $wp = WatchPayGateway::createWebPayment(
                    $trx,
                    (float) self::PRICE,
                    $goodsName,
                    $pageUrl,
                    $notifyUrl
                );
                $paymentUrl = $wp['pay_link'];
            }
        } catch (\Throwable $e) {
            return responseError('payment_gateway_error', ['Payment gateway init failed. Please try again.']);
        }

        return responseSuccess('payment_initiated', ['Payment gateway initialized'], [
            'payment_url' => $paymentUrl,
            'trx' => $trx,
            'amount' => (float) self::PRICE,
            'currency_symbol' => $general->cur_sym ?? '₹',
            'gateway_name' => $gateway == 'simplypay' ? 'SimplyPay' : ($gateway == 'rupeerush' ? 'RupeeRush' : 'WatchPay'),
        ]);
    }

    public function confirmPayment(Request $request)
    {
        $request->validate([
            'trx' => 'required|string',
        ]);

        $user = auth()->user();
        $trx = (string) $request->trx;
        $gateway = $request->gateway;
        if (!in_array($gateway, ['simplypay', 'watchpay', 'rupeerush'])) {
            $gateway = 'watchpay';
        }
        $session = cache()->get($gateway . '_payment_' . $trx);

        if (!is_array($session) || ($session['type'] ?? '') !== 'ad_certificate' || (int)($session['user_id'] ?? 0) !== (int)$user->id) {
            return responseError('payment_not_found', ['Payment session not found. Please initiate payment again.']);
        }
        if (($session['status'] ?? '') !== 'success') {
            return responseError('payment_pending', ['Payment not verified yet. Please complete payment and try again.']);
        }

        $type = $session['cert_type'] ?? 'course';
        $remark = ($type === 'view') ? 'ad_certificate_view_fee' : 'ad_certificate_fee';

        // Idempotent transaction record (no wallet deduction)
        if (!$this->hasAdCertificate($user, $type)) {
            if ($type === 'view') {
                $user->has_ad_certificate_view = true;
            } else {
                $user->has_ad_certificate = true;
            }
            $user->save();
            
            $t = new Transaction();
            $t->user_id = $user->id;
            $t->amount = (float) self::PRICE;
            $t->post_balance = $user->balance;
            $t->charge = 0;
            $t->trx_type = '-';
            $t->details = "Ad Certificate ($type) purchase via " . ($gateway == 'simplypay' ? 'SimplyPay' : ($gateway == 'rupeerush' ? 'RupeeRush' : 'WatchPay'));
            $t->trx = $trx;
            $t->remark = $remark;
            $t->save();

            // Agent commission
            try {
                $agentId = (int) ($user->ref_by ?? 0);
                if ($agentId > 0) {
                    \App\Lib\AgentCommission::process(
                        $agentId,
                        'certificate',
                        (float) self::PRICE,
                        $trx,
                        'Agent commission from User#' . $user->id . ' (Ad Certificate ' . $type . ') | Base: ₹' . self::PRICE
                    );
                }
            } catch (\Throwable $e) {}
        }

        return responseSuccess('ad_certificate_purchased', ['Ad Certificate purchased successfully.'], [
            'has_ad_certificate' => $this->hasAdCertificate($user, $type),
        ]);
    }
}

