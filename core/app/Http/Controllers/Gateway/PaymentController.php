<?php

namespace App\Http\Controllers\Gateway;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Lib\FormProcessor;
use App\Models\AdminNotification;
use App\Models\Advertiser;
use App\Models\Campaign;
use App\Models\Deposit;
use App\Models\GatewayCurrency;
use App\Models\Transaction;
use Illuminate\Http\Request;

class PaymentController extends Controller {
    public function deposit() {
        $advertiser      = auth()->guard('advertiser')->user();
        $gatewayCurrency = GatewayCurrency::whereHas('method', function ($gate) {
            $gate->where('status', Status::ENABLE);
        })->with('method')->orderBy('name')->get();
        $pageTitle = 'Deposit Methods';
        return view('Template::advertiser.payment.deposit', compact('gatewayCurrency', 'pageTitle'));
    }

    public function depositInsert(Request $request) {
        $advertiser = auth()->guard('advertiser')->user();
        $request->validate([
            'amount'   => 'required|numeric|gt:0',
            'gateway'  => 'required',
            'currency' => 'nullable|string',
        ]);

        $amount = $request->amount;
        // Gateway flow
        $gate = GatewayCurrency::whereHas('method', function ($gate) {
            $gate->where('status', Status::ENABLE);
        })
            ->where('method_code', $request->gateway)
            ->where('currency', $request->currency)
            ->first();

        if (!$gate) {
            $notify[] = ['error', 'Invalid gateway'];
            return back()->withNotify($notify);
        }

        if ($gate->min_amount > $amount || $gate->max_amount < $amount) {
            $notify[] = ['error', 'Please follow deposit limit'];
            return back()->withNotify($notify);
        }

        $charge      = $gate->fixed_charge + ($amount * $gate->percent_charge / 100);
        $payable     = $amount + $charge;
        $finalAmount = $payable * $gate->rate;

        $data                  = new Deposit();
        $data->advertiser_id   = $advertiser->id;
        $data->method_code     = $gate->method_code;
        $data->method_currency = strtoupper($gate->currency);
        $data->amount          = $amount;
        $data->charge          = $charge;
        $data->rate            = $gate->rate;
        $data->final_amount    = $finalAmount;
        $data->btc_amount      = 0;
        $data->btc_wallet      = "";
        $data->trx             = getTrx();
        $data->success_url     = route('advertiser.deposit.history');
        $data->failed_url      = route('advertiser.deposit.history');
        $data->save();

        session()->put('Track', $data->trx);

        return to_route('advertiser.deposit.confirm');
    }

    public function depositConfirm() {
        $track   = session()->get('Track');
        $deposit = Deposit::where('trx', $track)->where('status', Status::PAYMENT_INITIATE)->orderBy('id', 'DESC')->with('gateway')->firstOrFail();

        if ($deposit->method_code >= 1000) {
            return to_route('advertiser.deposit.manual.confirm');
        }

        $dirName = $deposit->gateway->alias;
        $new     = __NAMESPACE__ . '\\' . $dirName . '\\ProcessController';

        $data = $new::process($deposit);
        $data = json_decode($data);

        if (isset($data->error)) {
            $notify[] = ['error', $data->message];
            return back()->withNotify($notify);
        }
        if (isset($data->redirect)) {
            return redirect($data->redirect_url);
        }

        // for Stripe V3
        if (isset($data->session)) {
            $deposit->btc_wallet = $data->session->id;
            $deposit->save();
        }

        $pageTitle = 'Payment Confirm';
        return view("Template::$data->view", compact('data', 'pageTitle', 'deposit'));
    }

    public static function userDataUpdate($deposit, $isManual = null) {
        if ($deposit->status == Status::PAYMENT_INITIATE || $deposit->status == Status::PAYMENT_PENDING) {
            $deposit->status = Status::PAYMENT_SUCCESS;
            if ($isManual) {
                $deposit->approved_by_admin = true;
            }
            $deposit->save();

            $methodName = $deposit->methodName();
            $userOrAdvertiser = null;
            $details = 'Deposit Via ' . $methodName;
            
            // Determine success message/details based on remark
            if ($deposit->remark == 'kyc_fee') {
                $details = 'KYC Verification Fee via ' . $methodName;
            } elseif ($deposit->remark == 'ad_plan_purchase') {
                $details = 'Ad Plan Purchase via ' . $methodName;
            } elseif ($deposit->remark == 'course_plan_purchase_gateway') {
                $details = 'Course Plan Purchase via ' . $methodName;
            } elseif ($deposit->remark == 'package_upgrade_gateway') {
                $details = 'Package Upgrade via ' . $methodName;
            } elseif ($deposit->remark == 'partner_program_gateway') {
                $details = 'Partner Program Join via ' . $methodName;
            } elseif ($deposit->remark == 'registration_fee') {
                $details = 'Registration Fee via ' . $methodName;
            }

            // Support both advertiser deposits and user deposits
            if (!empty($deposit->advertiser_id)) {
                $advertiser = Advertiser::find($deposit->advertiser_id);
                if (!$advertiser) return;
                
                $userOrAdvertiser = $advertiser;
                
                // If it's a plain deposit, add to balance
                if (empty($deposit->remark) || $deposit->remark == 'deposit') {
                    $advertiser->balance += $deposit->amount;
                    $advertiser->save();

                    $transaction                = new Transaction();
                    $transaction->advertiser_id = $deposit->advertiser_id;
                    $transaction->amount        = $deposit->amount;
                    $transaction->post_balance  = $advertiser->balance;
                    $transaction->charge        = $deposit->charge;
                    $transaction->trx_type      = '+';
                    $transaction->details       = $details;
                    $transaction->trx           = $deposit->trx;
                    $transaction->remark        = 'deposit';
                    $transaction->save();
                }

                if (!$isManual) {
                    $adminNotification                = new AdminNotification();
                    $adminNotification->advertiser_id = $advertiser->id;
                    $adminNotification->title         = 'Deposit successful via ' . $methodName;
                    $adminNotification->click_url     = urlPath('admin.deposit.successful');
                    $adminNotification->save();
                }

                notify($advertiser, $isManual ? 'DEPOSIT_APPROVE' : 'DEPOSIT_COMPLETE', [
                    'method_name'     => $methodName,
                    'method_currency' => $deposit->method_currency,
                    'method_amount'   => showAmount($deposit->final_amount, currencyFormat: false),
                    'amount'          => showAmount($deposit->amount, currencyFormat: false),
                    'charge'          => showAmount($deposit->charge, currencyFormat: false),
                    'rate'            => showAmount($deposit->rate, currencyFormat: false),
                    'trx'             => $deposit->trx,
                    'post_balance'    => showAmount($advertiser->balance),
                ]);
            } elseif (empty($deposit->user_id) && $deposit->remark === 'registration_fee') {
                // 🟢 Handle Manual Approval for registration (before user exists)
                $paymentTrx = $deposit->trx;
                $regPaymentKey = 'reg_payment_' . $paymentTrx;
                $paymentData = cache()->get($regPaymentKey);
                
                if ($paymentData && !empty($paymentData['registration_token'])) {
                    $regToken = $paymentData['registration_token'];
                    $result = \App\Http\Controllers\Api\Auth\RegisterController::finalizeRegistration($regToken, $paymentTrx);
                    
                    if (is_array($result) && isset($result['user'])) {
                        // User created successfully via manual approval
                        $user = $result['user'];
                        $isManual = true; // ensure correct notification template
                    }
                }
            } elseif (!empty($deposit->user_id)) {
                $user = \App\Models\User::find($deposit->user_id);
                if (!$user) return;
                
                $userOrAdvertiser = $user;

                // 1. Handle Balance Deposits
                if (empty($deposit->remark) || $deposit->remark == 'deposit') {
                    $user->balance = (float) ($user->balance ?? 0) + (float) $deposit->amount;
                    $user->save();

                    $transaction               = new Transaction();
                    $transaction->user_id      = $deposit->user_id;
                    $transaction->amount       = $deposit->amount;
                    $transaction->post_balance = $user->balance;
                    $transaction->charge       = $deposit->charge;
                    $transaction->trx_type     = '+';
                    $transaction->details      = $details;
                    $transaction->trx          = $deposit->trx;
                    $transaction->remark       = 'deposit';
                    $transaction->save();
                } 
                // 2. Handle KYC Fee
                elseif ($deposit->remark == 'kyc_fee') {
                    if (!(bool) ($user->has_paid_kyc_fee ?? false)) {
                        $user->has_paid_kyc_fee = true;
                        $user->kyc_fee_trx = $deposit->trx;
                        $user->kyc_fee_paid_at = now();
                        $user->save();
                        
                        // Process Agent Commission
                        try {
                            $agentId = (int) ($user->ref_by ?? 0);
                            if ($agentId > 0) {
                                \App\Lib\AgentCommission::process(
                                    $agentId, 'kyc', (float)$deposit->amount, $deposit->trx,
                                    'Agent KYC commission from User#' . $user->id . ' (KYC fee) | Base: ₹' . $deposit->amount
                                );
                            }
                        } catch (\Throwable $e) {}
                    }
                }
                // 3. Handle Ad Plan Purchase
                elseif ($deposit->remark == 'ad_plan_purchase') {
                    $planId = $deposit->detail->plan_id ?? null;
                    if ($planId) {
                        $plan = \App\Models\AdPackage::find($planId);
                        if ($plan) {
                            $validityDays = 30;
                            if ($plan->price == 2999) $validityDays = 30;
                            elseif ($plan->price == 4999) $validityDays = 60;
                            elseif ($plan->price == 7499) $validityDays = 180;
                            elseif ($plan->price == 9999) $validityDays = 365;

                            \App\Models\AdPackageOrder::create([
                                'user_id' => $user->id,
                                'package_id' => $plan->id,
                                'amount' => $plan->price,
                                'status' => 1,
                                'expires_at' => now()->addDays($validityDays),
                            ]);

                            $transaction = new Transaction();
                            $transaction->user_id = $user->id;
                            $transaction->amount = $plan->price;
                            $transaction->post_balance = $user->balance;
                            $transaction->charge = 0;
                            $transaction->trx_type = '-';
                            $transaction->details = $details . ': ' . $plan->name;
                            $transaction->trx = $deposit->trx;
                            $transaction->remark = 'ad_plan_purchase';
                            $transaction->save();

                            // Process Agent Commission
                            try {
                                $agentId = (int) ($user->ref_by ?? 0);
                                if ($agentId > 0) {
                                    \App\Lib\AgentCommission::process(
                                        $agentId, 'adplan', (float)$plan->price, $deposit->trx,
                                        'Agent commission from User#' . $user->id . ' – Ad Plan: ' . $plan->name,
                                        ['plan_type' => 'ad_plan', 'plan_id' => (int)$plan->id]
                                    );
                                }
                            } catch (\Throwable $e) {}
                        }
                    }
                }

                if (!$isManual) {
                    $adminNotification            = new AdminNotification();
                    $adminNotification->user_id   = $user->id;
                    $adminNotification->title     = 'Deposit successful via ' . $methodName . ($deposit->remark ? ' ('.$deposit->remark.')' : '');
                    $adminNotification->click_url = urlPath('admin.deposit.successful');
                    $adminNotification->save();
                }

                notify($user, $isManual ? 'DEPOSIT_APPROVE' : 'DEPOSIT_COMPLETE', [
                    'method_name'     => $methodName,
                    'method_currency' => $deposit->method_currency,
                    'method_amount'   => showAmount($deposit->final_amount, currencyFormat: false),
                    'amount'          => showAmount($deposit->amount, currencyFormat: false),
                    'charge'          => showAmount($deposit->charge, currencyFormat: false),
                    'rate'            => showAmount($deposit->rate, currencyFormat: false),
                    'trx'             => $deposit->trx,
                    'post_balance'    => showAmount($user->balance),
                ]);
            }

            // Subtract balance and confirm campaign
            if ($deposit->campaign_id != 0) {
                $campaign = $deposit->campaign;
                self::confirmCampaign($campaign);
            }
        }
    }

    public static function confirmCampaign($campaign) {
        $trx        = getTrx();
        $advertiser = $campaign->advertiser;
        $advertiser->balance -= $campaign->budget;
        $advertiser->save();

        $transaction                = new Transaction();
        $transaction->advertiser_id = $advertiser->id;
        $transaction->amount        = $campaign->budget;
        $transaction->post_balance  = $advertiser->balance;
        $transaction->charge        = 0;
        $transaction->trx_type      = '-';
        $transaction->details       = 'Payment Completed for Campaign: ' . $campaign->title;
        $transaction->trx           = $trx;
        $transaction->remark        = 'campaign_payment';
        $transaction->save();

        // Mark campaign as paid
        $campaign->payment_status = Status::PAID;
        $campaign->save();

        notify($advertiser, 'CAMPAIGN_PAYMENT_CONFIRMED', [
            'trx'            => $trx,
            'campaign_title' => $campaign->title,
            'budget'         => showAmount($campaign->budget),
            'post_balance'   => showAmount($advertiser->balance),
        ]);

        // Also notify admin
        $adminNotification                = new AdminNotification();
        $adminNotification->advertiser_id = $advertiser->id;
        $adminNotification->title         = 'Campaign Payment received: ' . $campaign->title;
        $adminNotification->click_url     = route('admin.campaign.details', $campaign->id);
        $adminNotification->save();
    }

    public function manualDepositConfirm() {
        $track = session()->get('Track');
        $data  = Deposit::with('gateway')->where('status', Status::PAYMENT_INITIATE)->where('trx', $track)->first();
        abort_if(!$data, 404);
        if ($data->method_code > 999) {
            $pageTitle = 'Confirm Deposit';
            $method    = $data->gatewayCurrency();
            $gateway   = $method->method;
            return view('Template::advertiser.payment.manual', compact('data', 'pageTitle', 'method', 'gateway'));
        }
        abort(404);
    }

    public function manualDepositUpdate(Request $request) {
        $track = session()->get('Track');
        $data  = Deposit::with('gateway')->where('status', Status::PAYMENT_INITIATE)->where('trx', $track)->first();
        abort_if(!$data, 404);
        $gatewayCurrency = $data->gatewayCurrency();
        $gateway         = $gatewayCurrency->method;
        $formData        = $gateway->form->form_data;

        $formProcessor  = new FormProcessor();
        $validationRule = $formProcessor->valueValidation($formData);
        $request->validate($validationRule);
        $advertiserData = $formProcessor->processFormData($request, $formData);

        $data->detail = $advertiserData;
        $data->status = Status::PAYMENT_PENDING;
        $data->save();

        $adminNotification                = new AdminNotification();
        $adminNotification->advertiser_id = $data->advertiser->id;
        $adminNotification->title         = 'Deposit request from ' . $data->advertiser->username;
        $adminNotification->click_url     = urlPath('admin.deposit.details', $data->id);
        $adminNotification->save();

        notify($data->advertiser, 'DEPOSIT_REQUEST', [
            'method_name'     => $data->gatewayCurrency()->name,
            'method_currency' => $data->method_currency,
            'method_amount'   => showAmount($data->final_amount, currencyFormat: false),
            'amount'          => showAmount($data->amount, currencyFormat: false),
            'charge'          => showAmount($data->charge, currencyFormat: false),
            'rate'            => showAmount($data->rate, currencyFormat: false),
            'trx'             => $data->trx,
        ]);

        $notify[] = ['success', 'You have deposit request has been taken'];
        return to_route('advertiser.deposit.history')->withNotify($notify);
    }
}
