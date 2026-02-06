<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdvertiserLogin;
use App\Models\NotificationLog;
use App\Models\Transaction;
use Illuminate\Http\Request;

class AdvertiserReportController extends Controller {
    public function transaction(Request $request, $advertiserId = null) {
        $pageTitle = 'Transaction Logs';

        $remarks      = Transaction::where('advertiser_id', '!=', 0)->distinct('remark')->orderBy('remark')->get('remark');
        $transactions = Transaction::where('advertiser_id', '!=', 0)->searchable(['trx', 'advertiser:username'])->dateFilter()->orderBy('id', 'desc')->with('advertiser');
        if ($advertiserId) {
            $transactions = $transactions->where('advertiser_id', $advertiserId);
        }
        $transactions = $transactions->paginate(getPaginate());

        return view('admin.advertiser_reports.transactions', compact('pageTitle', 'transactions', 'remarks'));
    }

    public function loginHistory(Request $request) {
        $pageTitle = 'Advertiser Login History';
        $loginLogs = AdvertiserLogin::orderBy('id', 'desc')->searchable(['advertiser:username'])->dateFilter()->with('advertiser')->paginate(getPaginate());
        return view('admin.advertiser_reports.logins', compact('pageTitle', 'loginLogs'));
    }

    public function loginIpHistory($ip) {
        $pageTitle = 'Login by - ' . $ip;
        $loginLogs = AdvertiserLogin::where('advertiser_ip', $ip)->orderBy('id', 'desc')->with('advertiser')->paginate(getPaginate());
        return view('admin.advertiser_reports.logins', compact('pageTitle', 'loginLogs', 'ip'));
    }

    public function notificationHistory(Request $request) {
        $pageTitle = 'Notification History';
        $logs      = NotificationLog::where('advertiser_id', '!=', 0)->orderBy('id', 'desc')->searchable(['advertiser:username'])->dateFilter()->with('advertiser')->paginate(getPaginate());
        return view('admin.advertiser_reports.notification_history', compact('pageTitle', 'logs'));
    }

    public function emailDetails($id) {
        $pageTitle = 'Email Details';
        $email     = NotificationLog::where('advertiser_id', '!=', 0)->findOrFail($id);
        return view('admin.advertiser_reports.email_details', compact('pageTitle', 'email'));
    }
}
