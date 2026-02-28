<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class LeaderboardControlController extends Controller
{
    public function index()
    {
        $pageTitle = 'Leaderboard & Dashboard Control';
        $users = User::orderBy('id', 'desc');
        
        if (request()->search) {
            $users = $users->where(function($q) {
                $q->where('username', 'LIKE', '%' . request()->search . '%')
                  ->orWhere('email', 'LIKE', '%' . request()->search . '%');
            });
        }
        
        $users = $users->paginate(getPaginate());
        return view('admin.leaderboard.index', compact('pageTitle', 'users'));
    }

    public function getSettings()
    {
        $general = \App\Models\GeneralSetting::first();
        $settings = [
            'lb_show_today' => (bool)$general->lb_show_today,
            'lb_show_weekly' => (bool)$general->lb_show_weekly,
            'lb_show_monthly' => (bool)$general->lb_show_monthly,
            'lb_show_all_time' => (bool)$general->lb_show_all_time,
        ];
        return response()->json([
            'status' => 'success',
            'settings' => $settings
        ]);
    }

    public function getUsers(Request $request)
    {
        $users = User::orderBy('id', 'desc');
        
        if ($request->search) {
            $users = $users->where(function($q) use ($request) {
                $q->where('id', 'LIKE', '%' . str_replace('ADS', '', $request->search) . '%')
                  ->orWhere('username', 'LIKE', '%' . $request->search . '%')
                  ->orWhere('email', 'LIKE', '%' . $request->search . '%')
                  ->orWhere('firstname', 'LIKE', '%' . $request->search . '%')
                  ->orWhere('lastname', 'LIKE', '%' . $request->search . '%');
            });
        }
        
        return response()->json([
            'status' => 'success',
            'users' => $users->paginate(getPaginate())
        ]);
    }

    public function updateGlobal(Request $request)
    {
        $general = \App\Models\GeneralSetting::first();
        
        // Using filter_var to catch "false" strings or boolean false/0 correctly
        $general->lb_show_today = filter_var($request->lb_show_today, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
        $general->lb_show_weekly = filter_var($request->lb_show_weekly, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
        $general->lb_show_monthly = filter_var($request->lb_show_monthly, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
        $general->lb_show_all_time = filter_var($request->lb_show_all_time, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
        
        $general->save();
        
        \Cache::forget('GeneralSetting');

        return response()->json([
            'status' => 'success', 
            'message' => 'Leaderboard visibility rules broadcasted successfully',
            'settings' => [
                'lb_show_today' => (bool)$general->lb_show_today,
                'lb_show_weekly' => (bool)$general->lb_show_weekly,
                'lb_show_monthly' => (bool)$general->lb_show_monthly,
                'lb_show_all_time' => (bool)$general->lb_show_all_time,
            ]
        ]);
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        // Ads Income Hype Propagation (Today -> Weekly -> Monthly -> All Time)
        $ads_today = (float) $request->lead_ads_today;
        $ads_weekly = max($ads_today, (float) $request->lead_ads_weekly);
        $ads_monthly = max($ads_weekly, (float) $request->lead_ads_monthly);
        $ads_all_time = max($ads_monthly, (float) $request->lead_ads_all_time);

        $user->lead_ads_today = $ads_today;
        $user->lead_ads_weekly = $ads_weekly;
        $user->lead_ads_monthly = $ads_monthly;
        $user->lead_ads_all_time = $ads_all_time;
        
        // Affiliate Income Hype Propagation
        $aff_today = (float) $request->lead_aff_today;
        $aff_weekly = max($aff_today, (float) $request->lead_aff_weekly);
        $aff_monthly = max($aff_weekly, (float) $request->lead_aff_monthly);
        $aff_all_time = max($aff_monthly, (float) $request->lead_aff_all_time);

        $user->lead_aff_today = $aff_today;
        $user->lead_aff_weekly = $aff_weekly;
        $user->lead_aff_monthly = $aff_monthly;
        $user->lead_aff_all_time = $aff_all_time;
        
        $user->is_lb_hidden = $request->is_lb_hidden ? 1 : 0;
        $user->save();

        return response()->json([
            'status' => 'success', 
            'message' => 'User dashboard hype synchronization complete',
            'user' => $user
        ]);
    }
}
