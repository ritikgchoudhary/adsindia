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

    private function hasAdCertificate(int $userId): bool
    {
        return Transaction::where('user_id', $userId)
            ->where('remark', self::REMARK)
            ->where('trx_type', '-')
            ->where('amount', self::PRICE)
            ->exists();
    }

    public function status()
    {
        $user = auth()->user();
        $general = gs();

        return responseSuccess('ad_certificate_status', ['Ad Certificate status'], [
            'has_ad_certificate' => $this->hasAdCertificate((int) $user->id),
            'price' => (float) self::PRICE,
            'currency_symbol' => $general->cur_sym ?? '₹',
        ]);
    }

    public function purchase(Request $request)
    {
        $user = auth()->user();
        $general = gs();
        $gateway = $request->input('gateway', 'watchpay');
        
        $gw = \App\Models\Gateway::where('alias', $gateway)->first();
        if (!$gw || $gw->status != 1) {
            return responseError('gateway_unavailable', ['Selected payment gateway is currently unavailable.']);
        }

        if ($this->hasAdCertificate((int) $user->id)) {
            return responseError('already_purchased', ['Ad Certificate is already purchased.']);
        }

        $trx = getTrx();
        cache()->put($gateway . '_payment_' . $trx, [
            'type' => 'ad_certificate',
            'user_id' => $user->id,
            'amount' => (float) self::PRICE,
            'status' => 'pending',
            'created_at' => now()->format('Y-m-d H:i:s'),
        ], now()->addHours(2));

        $base = $request->getSchemeAndHttpHost() ?: rtrim((string) config('app.url'), '/');
        $pageUrl = $base . '/user/certificates?' . $gateway . '_trx=' . urlencode($trx) . '&ad_certificate=1';
        $notifyUrl = $base . '/ipn/' . $gateway;

        try {
            if ($gateway == 'simplypay') {
                $sp = \App\Lib\SimplyPayGateway::createPayment([
                    'merOrderNo' => $trx,
                    'amount' => (float) self::PRICE,
                    'goodsName' => 'Ad Certificate',
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
                    'Ad Certificate',
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

        // Idempotent transaction record (no wallet deduction)
        if (!$this->hasAdCertificate((int) $user->id)) {
            $t = new Transaction();
            $t->user_id = $user->id;
            $t->amount = (float) self::PRICE;
            $t->post_balance = $user->balance;
            $t->charge = 0;
            $t->trx_type = '-';
            $t->details = 'Ad Certificate purchase via ' . ($gateway == 'simplypay' ? 'SimplyPay' : ($gateway == 'rupeerush' ? 'RupeeRush' : 'WatchPay'));
            $t->trx = $trx;
            $t->remark = self::REMARK;
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
                        'Agent commission from User#' . $user->id . ' (Ad Certificate) | Base: ₹' . self::PRICE
                    );
                }
            } catch (\Throwable $e) {}
        }

        return responseSuccess('ad_certificate_purchased', ['Ad Certificate purchased successfully. Courses and certificates are now unlocked.'], [
            'has_ad_certificate' => true,
        ]);
    }
}

