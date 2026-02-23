<?php

namespace App\Http\Controllers\Advertiser;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Category;
use App\Models\Conversion;
use App\Models\TrafficType;
use App\Rules\FileTypeValidate;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CampaignController extends Controller {
    public function index() {
        $pageTitle = 'My Campaigns';
        $campaigns = Campaign::where('advertiser_id', advertiserId())->orderBy('id', 'desc')->paginate(getPaginate());
        return view('Template::advertiser.campaigns.index', compact('pageTitle', 'campaigns'));
    }

    public function create() {
        $pageTitle  = 'New Campaign';
        $categories = Category::active()->get();
        $campaign   = null;
        $traffics   = TrafficType::active()->get();
        return view('Template::advertiser.campaigns.form', compact('pageTitle', 'categories', 'campaign', 'traffics'));
    }

    public function edit($id) {
        $campaign   = Campaign::where('id', $id)->where('advertiser_id', advertiserId())->firstOrFail();
        $pageTitle  = 'Edit Campaign';
        $categories = Category::active()->get();
        $traffics   = TrafficType::active()->get();
        return view('Template::advertiser.campaigns.form', compact('pageTitle', 'campaign', 'categories', 'traffics'));
    }

    public function store(Request $request, $id = 0) {
        $request->validate([
            'title'                 => 'required|string|max:255',
            'description'           => 'required|string',
            'category_id'           => 'required|integer|exists:categories,id',
            'url'                   => 'required|url',
            'conversion_limit'      => 'required|integer|min:1',
            'starts_at'             => 'required|date',
            'ends_at'               => 'required|date|after_or_equal:starts_at',
            'payout_per_conversion' => 'required|numeric|min:0',
            'admin_commission'      => 'required|numeric|min:0',
            'image'                 => ['nullable', 'image', new FileTypeValidate(['jpg', 'jpeg', 'png'])],
        ]);

        $advertiser     = advertiser();
        $campaignBudget = round(($request->payout_per_conversion + gs('system_affiliate_commission')) * $request->conversion_limit, 2);
        if ($campaignBudget > $advertiser->balance) {
            $notify[] = ['error', 'Not enough balance in your account.'];
            return back()->withNotify($notify);
        }

        if ($id) {
            $campaign = Campaign::where('id', $id)->where('advertiser_id', $advertiser->id)->firstOrFail();
            $notify[] = ['success', 'Campaign updated successfully'];
        } else {
            $campaign                   = new Campaign();
            $campaign->advertiser_id    = $advertiser->id;
            $campaign->tracking_token   = (string) Str::uuid();
            $campaign->status           = Status::CAMPAIGN_PENDING;
            $campaign->admin_commission = gs('system_affiliate_commission');
            $notify[]                   = ['success', 'Campaign created successfully'];
        }

        $campaign->category_id           = $request->category_id;
        $campaign->title                 = $request->title;
        $campaign->slug                  = slug($request->title . '-' . time());
        $campaign->description           = $request->description;
        $campaign->url                   = $request->url;
        $campaign->starts_at             = $request->starts_at;
        $campaign->ends_at               = $request->ends_at;
        $campaign->conversion_limit      = $request->conversion_limit;
        $campaign->payout_per_conversion = $request->payout_per_conversion;
        $campaign->budget                = $campaignBudget;

        if ($request->hasFile('image')) {
            try {
                $campaign->image = fileUploader($request->file('image'), getFilePath('campaign'), getFileSize('campaign'), $campaign->image, getFileThumb('campaign'));
            } catch (\Exception $e) {
                $notify[] = ['error', 'Could not upload campaign cover image'];
                return back()->withNotify($notify);
            }
        }

        $campaign->save();
        $activeTraffic = TrafficType::active()->whereIn('id', $request->traffic_type_id)->pluck('id');
        $campaign->traffic_types()->sync($activeTraffic);

        return $id ? back()->withNotify($notify) : redirect()->route('advertiser.campaign.index')->withNotify($notify);
    }

    public function togglePause($id) {
        $advertiser = auth()->guard('advertiser')->user();
        $campaign   = Campaign::where('id', $id)
            ->where('advertiser_id', $advertiser->id)
            ->firstOrFail();

        if ($campaign->status != Status::CAMPAIGN_APPROVED || $campaign->payment_status != Status::PAID) {
            $notify[] = ['error', 'Only approved and paid campaigns can be paused or resumed.'];
            return back()->withNotify($notify);
        }

        $campaign->is_paused = !$campaign->is_paused;
        $campaign->save();

        if ($campaign->is_paused) {
            $notify[] = ['success', 'Campaign has been paused. Users will not see it until resumed.'];
        } else {
            $notify[] = ['success', 'Campaign has been resumed and is now visible to users.'];
        }

        return back()->withNotify($notify);
    }

    public function conversions($id) {
        $campaign    = Campaign::where('id', $id)->where('advertiser_id', advertiserId())->firstOrFail();
        $pageTitle   = 'Conversions for Campaign: ' . $campaign->title;
        $conversions = Conversion::where('campaign_id', $campaign->id)->with('user')->latest()->paginate(getPaginate());
        return view('Template::advertiser.campaigns.conversions', compact('pageTitle', 'campaign', 'conversions'));
    }
}
