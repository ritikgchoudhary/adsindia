<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VipPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BetaFeaturesController extends Controller
{
    public function getSummary()
    {
        $testerCount = \App\Models\User::where('beta_access', true)->count();
        return responseSuccess('beta_summary', ['Beta summary retrieved'], [
            'tester_count' => $testerCount,
            'points' => [
                'vip' => [
                    'title' => 'VIP Membership',
                    'points_code' => 1,
                    'status' => 'Testing',
                    'enabled' => VipPlan::where('enabled', true)->exists(),
                ],
                'instant_payout' => [
                    'title' => 'Instant Payout',
                    'points_code' => 2,
                    'status' => 'Testing',
                    'enabled' => true,
                ]
            ]
        ]);
    }

    public function toggleBetaAccess(Request $request)
    {
        $request->validate(['user_id' => 'required|integer']);
        $user = \App\Models\User::findOrFail($request->user_id);
        $user->beta_access = !$user->beta_access;
        $user->save();

        return responseSuccess('beta_toggled', ['Beta access updated for ' . $user->username], [
            'beta_access' => $user->beta_access
        ]);
    }

    public function getVipPlans()
    {
        $plans = VipPlan::all();
        return responseSuccess('vip_plans', ['VIP plans retrieved'], [
            'plans' => $plans
        ]);
    }

    public function saveVipPlan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'nullable|integer',
            'name' => 'required|string',
            'price' => 'required|numeric|min:0',
            'months' => 'required|integer|min:1',
            'withdrawal_fee_discount' => 'required|numeric|min:0|max:100',
            'priority_payout' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return responseError('validation_error', $validator->errors());
        }

        $id = $request->id;
        $plan = $id ? VipPlan::find($id) : new VipPlan();
        
        $plan->name = $request->name;
        $plan->price = $request->price;
        $plan->months = $request->months;
        $plan->withdrawal_fee_discount = $request->withdrawal_fee_discount;
        $plan->priority_payout = $request->priority_payout;
        $plan->save();

        return responseSuccess('vip_plan_saved', ['VIP plan saved successfully'], ['plan' => $plan]);
    }

    public function toggleVipPlan(Request $request)
    {
        $request->validate(['id' => 'required|integer']);
        $plan = VipPlan::findOrFail($request->id);
        $plan->enabled = !$plan->enabled;
        $plan->save();

        return responseSuccess('vip_plan_toggled', ['VIP plan status updated']);
    }

    public function getVerifiedSettings()
    {
        $settings = gs()->beta_verified_settings;
        return responseSuccess('success', ['settings' => $settings ?: ['price' => 99, 'auto_enable' => true]]);
    }

    public function saveVerifiedSettings(Request $request)
    {
        $general = gs();
        $general->beta_verified_settings = $request->all();
        $general->save();
        return responseSuccess('success', ['Settings saved']);
    }

    public function getBoosterSettings()
    {
        $settings = gs()->beta_booster_settings;
        return responseSuccess('success', ['settings' => $settings ?: ['daily_price' => 29, 'weekly_price' => 149, 'extra_ads' => 5]]);
    }

    public function saveBoosterSettings(Request $request)
    {
        $general = gs();
        $general->beta_booster_settings = $request->all();
        $general->save();
        return responseSuccess('success', ['Settings saved']);
    }

    public function getInstantSettings()
    {
        $settings = gs()->beta_instant_settings;
        return responseSuccess('success', ['settings' => $settings ?: ['fee' => 50, 'highlight' => true]]);
    }

    public function saveInstantSettings(Request $request)
    {
        $general = gs();
        $general->beta_instant_settings = $request->all();
        $general->save();
        return responseSuccess('success', ['Settings saved']);
    }

    public function getExtraSettings()
    {
        $settings = gs()->beta_extra_settings;
        return responseSuccess('success', ['settings' => $settings ?: [
            'p6_referral_booster' => ['price' => 299, 'bonus' => 10],
            'p7_kyc_fast' => ['price' => 49],
            'p8_withdraw_pass' => ['price' => 499],
            'p9_featured' => ['price' => 999],
            'p10_elite' => ['price' => 199]
        ]]);
    }

    public function saveExtraSettings(Request $request)
    {
        $general = gs();
        $general->beta_extra_settings = $request->all();
        $general->save();
        return responseSuccess('success', ['Settings saved']);
    }
}
