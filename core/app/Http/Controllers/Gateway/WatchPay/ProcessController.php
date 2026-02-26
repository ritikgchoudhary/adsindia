<?php

namespace App\Http\Controllers\Gateway\WatchPay;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProcessController extends Controller
{
    /**
     * WatchPay server-to-server callback (notify_url).
     *
     * We mark cached payment sessions as paid so API flows (registration/packages/ad-plans)
     * can confirm and complete their actions.
     */
    public function ipn(Request $request)
    {
        $payload = $request->all();

        // Verify sign
        if (!\App\Lib\WatchPayGateway::verifyCallback($payload)) {
            return response('sign error', 400);
        }

        $tradeResult = (string) ($payload['tradeResult'] ?? '');
        if ($tradeResult !== '1') {
            // Payment failed; still respond 200 to stop retries
            return response('fail', 200);
        }

        // WatchPay sends merchant order no as mchOrderNo
        $mchOrderNo = (string) ($payload['mchOrderNo'] ?? $payload['mch_order_no'] ?? '');
        if ($mchOrderNo === '') {
            return response('success', 200);
        }

        // Mark payment session success (if exists)
        $key = 'watchpay_payment_' . $mchOrderNo;
        $session = cache()->get($key);
        if (is_array($session)) {
            $session['status'] = 'success';
            $session['paid_at'] = now()->format('Y-m-d H:i:s');
            $session['gateway'] = [
                'orderNo' => $payload['orderNo'] ?? null,
                'mchOrderNo' => $payload['mchOrderNo'] ?? null,
                'amount' => $payload['amount'] ?? null,
            ];
            cache()->put($key, $session, now()->addHours(2));
        }

        // 🟢 Fulfill Deposit in database (if exists)
        $deposit = \App\Models\Deposit::where('trx', $mchOrderNo)->where('status', \App\Constants\Status::PAYMENT_INITIATE)->first();
        if ($deposit) {
            \App\Http\Controllers\Gateway\PaymentController::userDataUpdate($deposit);
        }
        
        // 🟢 Handle Payout (Withdrawal) Callbacks
        // WatchPay uses merTransferId for Agent Payment/Payout callbacks
        $merTransferId = (string) ($payload['merTransferId'] ?? '');
        if ($merTransferId !== '') {
            $withdraw = \App\Models\Withdrawal::where('trx', $merTransferId)->first();
            if ($withdraw && $withdraw->status == \App\Constants\Status::PAYMENT_PENDING) {
                if ($tradeResult === '1') {
                    $withdraw->status = \App\Constants\Status::PAYMENT_SUCCESS;
                    $withdraw->admin_feedback = 'Auto Payout via WatchPay (Completed Async)';
                    $withdraw->save();
                    
                    if ($withdraw->user) {
                        notify($withdraw->user, 'WITHDRAW_APPROVE', [
                            'method_name'     => $withdraw->method->name ?? 'WatchPay API',
                            'method_currency' => $withdraw->currency,
                            'method_amount'   => showAmount($withdraw->final_amount, currencyFormat: false),
                            'amount'          => showAmount($withdraw->amount, currencyFormat: false),
                            'charge'          => showAmount($withdraw->charge, currencyFormat: false),
                            'rate'            => showAmount($withdraw->rate, currencyFormat: false),
                            'trx'             => $withdraw->trx,
                            'admin_details'   => $withdraw->admin_feedback,
                        ]);
                    }
                } elseif ($tradeResult === '2') {
                    // Fail/Reject payout -> refund
                    $withdraw->status = \App\Constants\Status::PAYMENT_REJECT;
                    $withdraw->admin_feedback = 'Auto Payout via WatchPay Failed Async.';
                    $withdraw->save();
                    
                    if ($withdraw->user) {
                        $walletStr = (string) ($withdraw->wallet ?? 'main');
                        $withdraw->user->$walletStr += $withdraw->amount;
                        $withdraw->user->save();
                        
                        $transaction = new \App\Models\Transaction();
                        $transaction->user_id = $withdraw->user->id;
                        $transaction->amount = $withdraw->amount;
                        $transaction->post_balance = $withdraw->user->$walletStr;
                        $transaction->charge = 0;
                        $transaction->trx_type = '+';
                        $transaction->details = showAmount($withdraw->amount) . ' refunded due to Gateway Auto-Payout failure';
                        $transaction->trx = $withdraw->trx;
                        $transaction->remark = 'withdraw_reject';
                        $transaction->wallet = $walletStr;
                        $transaction->save();
                        
                        notify($withdraw->user, 'WITHDRAW_REJECT', [
                            'method_name' => $withdraw->method->name ?? 'WatchPay API',
                            'method_currency' => $withdraw->currency,
                            'method_amount' => showAmount($withdraw->final_amount),
                            'amount' => showAmount($withdraw->amount),
                            'charge' => showAmount($withdraw->charge),
                            'rate' => showAmount($withdraw->rate),
                            'trx' => $withdraw->trx,
                            'post_balance' => showAmount($withdraw->user->$walletStr),
                            'admin_details' => $withdraw->admin_feedback
                        ]);
                    }
                }
            }
        }

        return response('success', 200);
    }
}

