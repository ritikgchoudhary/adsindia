<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CoursePlan;
use App\Models\CoursePlanOrder;
use App\Models\Transaction;
use Illuminate\Http\Request;

class CoursePlanController extends Controller
{
    /**
     * Get learning/course plans that user can buy to access courses.
     */
    public function getPlans()
    {
        $general = gs();

        $plans = CoursePlan::active()
            ->ordered()
            ->get()
            ->map(function ($plan) {
                $courseCount = \App\Models\Course::active()
                    ->where('required_course_plan_id', '<=', $plan->level)
                    ->count();
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
     * Get user's current active course plan (for "Your current plan" on packages page).
     */
    public function getCurrent()
    {
        $user = auth()->user();
        $order = CoursePlanOrder::where('user_id', $user->id)
            ->active()
            ->with('plan')
            ->orderByDesc('course_plan_id')
            ->first();

        if (!$order) {
            return responseSuccess('course_plan_current', ['No active course plan'], [
                'data' => null,
                'has_plan' => false,
            ]);
        }

        $plan = $order->plan;
        $courseCount = \App\Models\Course::active()
            ->where('required_course_plan_id', '<=', $plan->level)
            ->count();

        return responseSuccess('course_plan_current', ['Current course plan'], [
            'data' => [
                'id' => $order->id,
                'plan_id' => $plan->id,
                'name' => $plan->name,
                'price' => (float) $plan->price,
                'level' => (int) $plan->level,
                'validity_days' => (int) $plan->validity_days,
                'expires_at' => $order->expires_at?->format('Y-m-d H:i:s'),
                'courses_count' => $courseCount,
            ],
            'has_plan' => true,
        ]);
    }

    /**
     * Purchase a course plan (gateway or balance - for now balance only for simplicity).
     */
    public function purchase(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|integer',
        ]);

        $user = auth()->user();
        $plan = CoursePlan::where('id', $request->plan_id)->active()->first();

        if (!$plan) {
            return responseError('plan_not_found', ['Course plan not found']);
        }

        if ($user->balance < $plan->price) {
            return responseError('insufficient_balance', ['Insufficient balance. Required: ' . showAmount($plan->price)]);
        }

        $user->balance -= $plan->price;
        $user->save();

        $expiresAt = $plan->validity_days > 0 ? now()->addDays($plan->validity_days) : null;

        CoursePlanOrder::create([
            'user_id' => $user->id,
            'course_plan_id' => $plan->id,
            'amount' => $plan->price,
            'status' => 1,
            'expires_at' => $expiresAt,
        ]);

        $trx = getTrx();
        $t = new Transaction();
        $t->user_id = $user->id;
        $t->amount = $plan->price;
        $t->post_balance = $user->balance;
        $t->charge = 0;
        $t->trx_type = '-';
        $t->details = 'Purchase course plan: ' . $plan->name;
        $t->trx = $trx;
        $t->remark = 'course_plan_purchase';
        $t->save();

        return responseSuccess('course_plan_purchased', ['Course plan purchased! You can now access courses and earn certificates on completion.'], [
            'plan_name' => $plan->name,
            'expires_at' => $expiresAt?->format('Y-m-d H:i:s'),
            'balance' => $user->balance,
        ]);
    }
}
