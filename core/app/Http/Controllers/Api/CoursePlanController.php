<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Lib\AgentCommission;
use App\Models\CoursePlan;
use App\Models\CoursePlanOrder;
use App\Models\Course;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;

class CoursePlanController extends Controller
{
    /**
     * Count courses unlocked by a given plan level.
     * Backward compatible with courses.required_course_plan_id being either:
     * - a CoursePlan id, or
     * - a level (1..5)
     */
    private function countCoursesForLevel(int $planLevel, array $planIdToLevel): int
    {
        if ($planLevel <= 0) return 0;

        return Course::active()
            ->get(['id', 'required_course_plan_id'])
            ->filter(function ($c) use ($planLevel, $planIdToLevel) {
                $req = (int) ($c->required_course_plan_id ?? 1);
                $reqLevel = $planIdToLevel[$req] ?? $req; // if it's not a plan id, treat as level
                return (int) $reqLevel <= $planLevel;
            })
            ->count();
    }

    /**
     * Get learning/course plans that user can buy to access courses.
     */
    public function getPlans()
    {
        $general = gs();
        $planIdToLevel = CoursePlan::query()->pluck('level', 'id')->map(fn ($v) => (int) $v)->toArray();

        $plans = CoursePlan::active()
            ->ordered()
            ->get()
            ->map(function ($plan) use ($planIdToLevel) {
                $courseCount = $this->countCoursesForLevel((int) $plan->level, $planIdToLevel);
                return [
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'slug' => $plan->slug,
                    'price' => (float) $plan->price,
                    'description' => $plan->description,
                    'level' => (int) $plan->level,
                    'validity_days' => (int) $plan->validity_days,
                    'courses_count' => $courseCount,
                ];
            });

        return responseSuccess('course_plans', ['Course plans retrieved successfully'], [
            'data' => $plans,
            'currency_symbol' => $general->cur_sym ?? '₹',
        ]);
    }

    /**
     * Get user's current/latest course plan (for "Your current plan" on packages page).
     * Returns active plan if any; otherwise latest order (even expired) so plan is visible.
     */
    public function getCurrent()
    {
        $user = auth()->user();
        $planIdToLevel = CoursePlan::query()->pluck('level', 'id')->map(fn ($v) => (int) $v)->toArray();
        $order = CoursePlanOrder::where('user_id', $user->id)
            ->active()
            ->with('plan')
            ->orderByDesc('id')
            ->first();

        if (!$order || !$order->plan) {
            return responseSuccess('course_plan_current', ['No course plan'], [
                'data' => null,
                'has_plan' => false,
            ]);
        }

        $plan = $order->plan;
        $courseCount = $this->countCoursesForLevel((int) $plan->level, $planIdToLevel);

        // Course plans are lifetime (no expiry) as per product requirement.
        $isActive = $order->status == 1;

        return responseSuccess('course_plan_current', ['Current course plan'], [
            'data' => [
                'id' => $order->id,
                'plan_id' => $plan->id,
                'name' => $plan->name,
                'price' => (float) $plan->price,
                'level' => (int) $plan->level,
                'validity_days' => 0,
                'expires_at' => $order->expires_at?->format('Y-m-d H:i:s'),
                'courses_count' => $courseCount,
                'is_active' => $isActive,
            ],
            'has_plan' => true,
        ]);
    }

    /**
     * Purchase a course plan (gateway only).
     */
    public function purchase(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|integer',
        ]);

        $user = auth()->user();
        $plan = CoursePlan::where('id', (int)$request->plan_id)->active()->first();
        if (!$plan) {
            return responseError('plan_not_found', ['Course plan not found']);
        }

        $gateway = $request->input('gateway', 'watchpay');
        if (!in_array($gateway, ['simplypay', 'watchpay', 'rupeerush', 'custom_qr'])) {
             $gateway = 'watchpay';
        }
        
        $gw = \App\Models\Gateway::where('alias', $gateway)->first();
        if (!$gw || $gw->status != 1) {
            return responseError('gateway_unavailable', ['Selected payment gateway is currently unavailable.']);
        }

        // If already active, block duplicate purchase (lifetime)
        $existing = CoursePlanOrder::where('user_id', $user->id)
            ->where('course_plan_id', $plan->id)
            ->where('status', 1)
            ->exists();
        if ($existing) {
            return responseError('already_active', ['You already have this course plan active.']);
        }

        $trx = getTrx();
        $cachePrefix = 'watchpay_payment_';
        if ($gateway === 'simplypay') $cachePrefix = 'simplypay_payment_';
        if ($gateway === 'rupeerush') $cachePrefix = 'rupeerush_payment_';
        
        $general = gs();
        $cacheKey = $cachePrefix . $trx;
        $deposit = new \App\Models\Deposit();
        $deposit->user_id = $user->id;
        $deposit->method_code = $gw->code;
        $deposit->amount = (float) $plan->price;
        $deposit->method_currency = $general->cur_text;
        $deposit->charge = 0;
        $deposit->rate = 1;
        $deposit->final_amount = (float) $plan->price;
        $deposit->trx = $trx;
        $deposit->remark = 'course_plan_purchase_gateway';
        $deposit->detail = json_encode(['plan_id' => $plan->id]);
        $deposit->status = \App\Constants\Status::PAYMENT_INITIATE;
        $deposit->save();

        cache()->put($cacheKey, [
            'type' => 'course_plan',
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'amount' => (float) $plan->price,
            'status' => 'pending',
            'created_at' => now()->format('Y-m-d H:i:s'),
        ], now()->addHours(2));

        $gw_param = 'watchpay_trx=';
        if ($gateway === 'simplypay') $gw_param = 'simplypay_trx=';
        if ($gateway === 'rupeerush') $gw_param = 'rupeerush_trx=';
        
        $base = $request->getSchemeAndHttpHost() ?: rtrim((string) config('app.url'), '/');
        $pageUrl = $base . '/user/packages?' . $gw_param . urlencode($trx) . '&course_plan_id=' . $plan->id;
        $notifyUrl = $base . '/ipn/' . $gateway;

        try {
            if ($gateway == 'simplypay') {
                $sp = \App\Lib\SimplyPayGateway::createPayment([
                    'merOrderNo' => $trx,
                    'amount' => (float) $plan->price,
                    'goodsName' => 'Course Plan: ' . $plan->name,
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
                    'totalAmount' => (float) $plan->price,
                    'notifyUrl' => $notifyUrl,
                    'payViewUrl' => $pageUrl,
                    'payName' => $user->fullname ?: $user->username,
                    'payEmail' => $user->email,
                    'payPhone' => $user->mobile,
                ]);
                $paymentUrl = $ap['pay_link'];
            } else {
                $wp = \App\Lib\WatchPayGateway::createWebPayment(
                    $trx,
                    (float) $plan->price,
                    'Course Plan: ' . $plan->name,
                    $pageUrl,
                    $notifyUrl
                );
                $paymentUrl = $wp['pay_link'];
            }
        } catch (\Throwable $e) {
            \Log::error('Payment initiation error: ' . $e->getMessage(), ['exception' => $e]);
            return responseError('payment_gateway_error', ['Payment gateway init failed. Please try again.']);
        }

        return responseSuccess('payment_initiated', ['Payment gateway initialized'], [
            'payment_url' => $paymentUrl,
            'trx' => $trx,
            'amount' => (float) $plan->price,
            'plan_id' => $plan->id,
            'plan_name' => $plan->name,
            'gateway_name' => $gateway == 'simplypay' ? 'SimplyPay' : ($gateway == 'rupeerush' ? 'RupeeRush' : 'WatchPay'),
        ]);
    }

    public function confirmPayment(Request $request)
    {
        $request->validate([
            'trx' => 'required|string',
            'plan_id' => 'required|integer',
        ]);

        $user = auth()->user();
        $trx = (string) $request->trx;
        $gateway = $request->gateway;
        if (!in_array($gateway, ['simplypay', 'watchpay', 'rupeerush', 'custom_qr'])) {
            $gateway = 'watchpay';
        }
        $cachePrefix = 'watchpay_payment_';
        if ($gateway === 'simplypay') $cachePrefix = 'simplypay_payment_';
        if ($gateway === 'rupeerush') $cachePrefix = 'rupeerush_payment_';
        
        $session = cache()->get($cachePrefix . $trx);

        if (!is_array($session) || ($session['type'] ?? '') !== 'course_plan' || (int)($session['user_id'] ?? 0) !== (int)$user->id) {
            return responseError('payment_not_found', ['Payment session not found. Please initiate payment again.']);
        }
        if (($session['status'] ?? '') !== 'success') {
            return responseError('payment_pending', ['Payment not verified yet. Please complete payment and try again.']);
        }

        $plan = CoursePlan::where('id', (int) $request->plan_id)->active()->first();
        if (!$plan) {
            return responseError('plan_not_found', ['Course plan not found']);
        }

        // Idempotent: if already purchased, return ok (lifetime)
        $existing = CoursePlanOrder::where('user_id', $user->id)
            ->where('course_plan_id', $plan->id)
            ->where('status', 1)
            ->first();
        if ($existing) {
            return responseSuccess('course_plan_purchased', ['Course plan already active'], [
                'plan_name' => $plan->name,
                'expires_at' => $existing->expires_at?->format('Y-m-d H:i:s'),
            ]);
        }

        // Lifetime (no expiry)
        $expiresAt = null;

        // For dynamic agent commissions: detect first ever activation (registration) vs later (upgrade)
        $hadAnyPlanBefore = CoursePlanOrder::where('user_id', $user->id)->where('status', 1)->exists();
        $commissionType = $hadAnyPlanBefore ? 'upgrade' : 'registration';

        CoursePlanOrder::create([
            'user_id' => $user->id,
            'course_plan_id' => $plan->id,
            'amount' => $plan->price,
            'status' => 1,
            'expires_at' => $expiresAt,
        ]);

        // Transaction log (no wallet deduction)
        $t = new Transaction();
        $t->user_id = $user->id;
        $t->amount = $plan->price;
        $t->post_balance = $user->balance;
        $t->charge = 0;
        $t->trx_type = '-';
        $t->details = 'Purchase course plan via ' . ($gateway == 'simplypay' ? 'SimplyPay' : ($gateway == 'rupeerush' ? 'RupeeRush' : 'WatchPay')) . ': ' . $plan->name;
        $t->trx = $trx;
        $t->remark = 'course_plan_purchase_gateway';
        $t->wallet = 'main';
        $t->save();

        // Agent commission (dynamic per-agent settings; credited only if sponsor is an Agent)
        try {
            $agentId = (int) ($user->ref_by ?? 0);
            if ($agentId > 0) {
                AgentCommission::process(
                    $agentId,
                    'course',
                    (float) $plan->price,
                    $trx,
                    'Agent commission from User#' . $user->id . ' – Course package: ' . $plan->name . ' | Base: ₹' . (float) $plan->price,
                    ['plan_type' => 'course_plan', 'plan_id' => (int) $plan->id]
                );
            }
        } catch (\Throwable $e) {
            // Never block purchase because of commission failure
        }

        return responseSuccess('course_plan_purchased', ['Course plan purchased! You can now access courses and earn certificates on completion.'], [
            'plan_name' => $plan->name,
            'expires_at' => $expiresAt?->format('Y-m-d H:i:s'),
        ]);
    }
}
