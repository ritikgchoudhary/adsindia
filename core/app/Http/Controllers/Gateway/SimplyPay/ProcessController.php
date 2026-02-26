<?php

namespace App\Http\Controllers\Gateway\SimplyPay;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Lib\SimplyPayGateway;
use App\Models\Deposit;
use App\Models\Withdrawal;
use App\Constants\Status;
use App\Http\Controllers\Gateway\PaymentController;

class ProcessController extends Controller
{
    /**
     * SimplyPay Webhook (IPN) handler
     */
    public function ipn(Request $request)
    {
        $payload = $request->all();
        Log::info('SimplyPay Webhook Received', $payload);

        // 1. Verify Signature
        if (!SimplyPayGateway::verifySign($payload)) {
            Log::error('SimplyPay Webhook: Signature Error');
            return response('sign error', 400);
        }

        $orderStatus = (int) ($payload['orderStatus'] ?? -1);
        $merOrderNo = (string) ($payload['merOrderNo'] ?? '');

        // 2. Handle Success (Status 2 and 3 mean Success)
        if ($orderStatus === 2 || $orderStatus === 3) {
            
            // 🟢 Handle Deposits (Pay-in)
            // Mark cached session success (for registration/KYC flow)
            $cacheKey = 'simplypay_payment_' . $merOrderNo;
            $session = cache()->get($cacheKey);
            if (is_array($session)) {
                $session['status'] = 'success';
                $session['paid_at'] = now()->format('Y-m-d H:i:s');
                $session['gateway'] = [
                    'orderNo' => $payload['orderNo'] ?? null,
                    'merOrderNo' => $merOrderNo,
                    'amount' => $payload['amount'] ?? null,
                ];
                cache()->put($cacheKey, $session, now()->addHours(2));
                Log::info('SimplyPay Webhook: Session Updated', ['key' => $cacheKey]);
            }

            // Update Deposit in DB (if exists)
            $deposit = Deposit::where('trx', $merOrderNo)->where('status', Status::PAYMENT_INITIATE)->first();
            if ($deposit) {
                PaymentController::userDataUpdate($deposit);
                Log::info('SimplyPay Webhook: Deposit Approved', ['trx' => $merOrderNo]);
            }

            // 🟢 Handle Payouts (Withdrawals)
            $withdraw = Withdrawal::where('trx', $merOrderNo)->where('status', Status::PAYMENT_PENDING)->first();
            if ($withdraw) {
                $withdraw->status = Status::PAYMENT_SUCCESS;
                $withdraw->admin_feedback = 'Auto Payout via SimplyPay (Completed Async)';
                $withdraw->save();

                if ($withdraw->user) {
                    notify($withdraw->user, 'WITHDRAW_APPROVE', [
                        'method_name'     => $withdraw->method->name ?? 'SimplyPay API',
                        'method_currency' => $withdraw->currency,
                        'method_amount'   => showAmount($withdraw->final_amount, currencyFormat: false),
                        'amount'          => showAmount($withdraw->amount, currencyFormat: false),
                        'charge'          => showAmount($withdraw->charge, currencyFormat: false),
                        'rate'            => showAmount($withdraw->rate, currencyFormat: false),
                        'trx'             => $withdraw->trx,
                        'admin_details'   => $withdraw->admin_feedback,
                    ]);
                }
                Log::info('SimplyPay Webhook: Withdrawal Approved', ['trx' => $merOrderNo]);
            }

            return response('success', 200);
        }

        // 3. Handle Failures (-1, -2, etc.)
        if ($orderStatus === -1 || $orderStatus === -2) {
            Log::warning('SimplyPay Webhook: Payment Failed', ['status' => $orderStatus, 'trx' => $merOrderNo]);
            
            // Handle Payout Failure (Refund user)
            $withdraw = Withdrawal::where('trx', $merOrderNo)->where('status', Status::PAYMENT_PENDING)->first();
            if ($withdraw) {
                $withdraw->status = Status::PAYMENT_REJECT;
                $withdraw->admin_feedback = 'Auto Payout via SimplyPay Failed Async (' . ($payload['message'] ?? 'Status Code: ' . $orderStatus) . ')';
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
                    $transaction->details = showAmount($withdraw->amount) . ' refunded due to SimplyPay Auto-Payout failure';
                    $transaction->trx = $withdraw->trx;
                    $transaction->remark = 'withdraw_reject';
                    $transaction->wallet = $walletStr;
                    $transaction->save();
                    
                    notify($withdraw->user, 'WITHDRAW_REJECT', [
                        'method_name' => $withdraw->method->name ?? 'SimplyPay API',
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
                Log::info('SimplyPay Webhook: Withdrawal Rejected/Refunded', ['trx' => $merOrderNo]);
            }
        }

        // Always return success/ok for defined statuses to stop retries if appropriate
        return response('success', 200);
    }
}
