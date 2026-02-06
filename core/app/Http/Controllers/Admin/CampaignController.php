<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Transaction;
use Illuminate\Http\Request;

class CampaignController extends Controller {
    public function pending() {
        $pageTitle = 'Pending Campaigns';
        $campaigns = $this->campaignData('pending');
        return view('admin.campaign.list', compact('pageTitle', 'campaigns'));
    }

    public function approved() {
        $pageTitle = 'Approved Campaigns';
        $campaigns = $this->campaignData('approved');
        return view('admin.campaign.list', compact('pageTitle', 'campaigns'));
    }

    public function rejected() {
        $pageTitle = 'Rejected Campaigns';
        $campaigns = $this->campaignData('rejected');
        return view('admin.campaign.list', compact('pageTitle', 'campaigns'));
    }
    public function paused() {
        $pageTitle = 'Paused Campaigns';
        $campaigns = $this->campaignData('paused');
        return view('admin.campaign.list', compact('pageTitle', 'campaigns'));
    }

    public function index() {
        $pageTitle = 'All Campaigns';
        $campaigns = $this->campaignData();
        return view('admin.campaign.list', compact('pageTitle', 'campaigns'));
    }

    protected function campaignData($scope = null) {
        $campaigns = Campaign::query();

        if ($scope) {
            $campaigns = $campaigns->$scope();
        }

        $campaigns = $campaigns->with(['advertiser', 'category']);

        $campaigns = $campaigns->searchable(['title', 'advertiser:username'])->dateFilter();

        return $campaigns->latest()->paginate(getPaginate());
    }

    public function details($id) {
        $campaign = Campaign::with(['advertiser' => function ($q) {
            $q->withCount('campaigns');
        }, 'category'])->findOrFail($id);
        $pageTitle = 'Campaign Detail: ' . $campaign->title;
        return view('admin.campaign.detail', compact('pageTitle', 'campaign'));
    }

    public function approve($id) {
        $campaign = Campaign::where('id', $id)->where('status', Status::CAMPAIGN_PENDING)->firstOrFail();

        $advertiser = $campaign->advertiser;
        if ($campaign->budget > $advertiser->balance) {
            $notify[] = ['error', 'Advertiser does not have enough balance'];
            return back()->withNotify($notify);
        }

        $advertiser->balance -= $campaign->budget;
        $advertiser->save();

        $transaction                = new Transaction();
        $transaction->advertiser_id = $advertiser->id;
        $transaction->amount        = $campaign->budget;
        $transaction->post_balance  = $advertiser->balance;
        $transaction->charge        = 0;
        $transaction->trx_type      = '-';
        $transaction->details       = 'Campaign payment has been successfully completed';
        $transaction->trx           = getTrx();
        $transaction->remark        = 'campaign_payment';
        $transaction->save();

        $campaign->status         = Status::CAMPAIGN_APPROVED;
        $campaign->payment_status = Status::PAID;
        $campaign->save();

        notify($advertiser, 'CAMPAIGN_APPROVED', [
            'username'       => $advertiser->username,
            'campaign_title' => $campaign->title,
            'budget'         => showAmount($campaign->budget),
            "trx"            => $transaction->trx,
        ]);

        $notify[] = ['success', 'Campaign approved successfully'];
        return to_route('admin.campaign.pending')->withNotify($notify);
    }

    public function reject(Request $request, $id) {
        $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        $campaign                 = Campaign::where('id', $id)->where('status', Status::CAMPAIGN_PENDING)->firstOrFail();
        $campaign->admin_feedback = $request->reason;
        $campaign->status         = Status::CAMPAIGN_REJECTED;
        $campaign->save();

        $advertiser = $campaign->advertiser;

        notify($advertiser, 'CAMPAIGN_REJECTED', [
            'username'       => $advertiser->username,
            'campaign_title' => $campaign->title,
            "reason"         => $request->reason,
        ]);

        $notify[] = ['success', 'Campaign rejected successfully'];
        return to_route('admin.campaign.pending')->withNotify($notify);
    }

    public function togglePause($id) {
        $campaign            = Campaign::where('id', $id)->where('status', Status::CAMPAIGN_APPROVED)->firstOrFail();
        $campaign->is_paused = !$campaign->is_paused;
        $campaign->save();
        $message  = $campaign->is_paused ? 'Campaign paused successfully' : 'Campaign resume successfully';
        $notify[] = ['success', $message];
        return to_route('admin.campaign.approved')->withNotify($notify);
    }

    public function toggleFeatured($id) {
        $campaign              = Campaign::findOrFail($id);
        $campaign->is_featured = $campaign->is_featured ? 0 : 1;
        $campaign->save();
        if ($campaign->is_featured) {
            $notify[] = ['success', 'Campaign has been marked as Featured.'];
        } else {
            $notify[] = ['success', 'Campaign has been removed from Featured.'];
        }
        return back()->withNotify($notify);
    }
}
