<?php

namespace App\Http\Controllers;

use App\Constants\Status;
use App\Models\AdminNotification;
use App\Models\Campaign;
use App\Models\CampaignTrafficType;
use App\Models\Category;
use App\Models\Frontend;
use App\Models\Language;
use App\Models\Page;
use App\Models\Subscriber;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use App\Models\TrafficType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Validator;

class SiteController extends Controller {
    public function index() {
        $pageTitle   = 'Home';
        $sections    = Page::where('tempname', activeTemplate())->where('slug', '/')->first();
        $seoContents = $sections->seo_content;
        $seoImage    = $seoContents?->image ? getImage(getFilePath('seo') . '/' . $seoContents?->image, getFileSize('seo')) : null;
        return view('Template::home', compact('pageTitle', 'sections', 'seoContents', 'seoImage'));
    }

    public function pages($slug) {
        $page        = Page::where('tempname', activeTemplate())->where('slug', $slug)->firstOrFail();
        $pageTitle   = $page->name;
        $sections    = $page->secs;
        $seoContents = $page->seo_content;
        $seoImage    = $seoContents?->image ? getImage(getFilePath('seo') . '/' . $seoContents?->image, getFileSize('seo')) : null;
        return view('Template::pages', compact('pageTitle', 'sections', 'seoContents', 'seoImage'));
    }

    public function contact() {
        $pageTitle   = "Contact Us";
        $user        = auth()->user();
        $sections    = Page::where('tempname', activeTemplate())->where('slug', 'contact')->first();
        $seoContents = $sections->seo_content;
        $seoImage    = $seoContents?->image ? getImage(getFilePath('seo') . '/' . $seoContents?->image, getFileSize('seo')) : null;
        return view('Template::contact', compact('pageTitle', 'user', 'sections', 'seoContents', 'seoImage'));
    }

    public function contactSubmit(Request $request) {
        $request->validate([
            'name'    => 'required',
            'email'   => 'required|email',
            'subject' => 'required|string|max:255',
            'message' => 'required',
        ]);

        $request->session()->regenerateToken();

        if (!verifyCaptcha()) {
            $notify[] = ['error', 'Invalid captcha provided'];
            return back()->withNotify($notify);
        }

        $random = getNumber();

        $ticket             = new SupportTicket();
        $ticket->user_id    = auth()->id() ?? 0;
        $ticket->name       = $request->name;
        $ticket->email      = $request->email;
        $ticket->priority   = Status::PRIORITY_MEDIUM;
        $ticket->ticket     = $random;
        $ticket->subject    = $request->subject;
        $ticket->last_reply = Carbon::now();
        $ticket->status     = Status::TICKET_OPEN;
        $ticket->save();

        $adminNotification            = new AdminNotification();
        $adminNotification->user_id   = auth()->id() ?? 0;
        $adminNotification->title     = 'A new contact message has been submitted';
        $adminNotification->click_url = urlPath('admin.ticket.view', $ticket->id);
        $adminNotification->save();

        $message                    = new SupportMessage();
        $message->support_ticket_id = $ticket->id;
        $message->message           = $request->message;
        $message->save();

        $notify[] = ['success', 'Ticket created successfully!'];

        return to_route('ticket.view', [$ticket->ticket])->withNotify($notify);
    }

    public function policyPages($slug) {
        $policy      = Frontend::where('slug', $slug)->where('data_keys', 'policy_pages.element')->firstOrFail();
        $pageTitle   = $policy->data_values->title;
        $seoContents = $policy->seo_content;
        $seoImage    = $seoContents?->image ? frontendImage('policy_pages', $seoContents?->image, getFileSize('seo'), true) : null;
        return view('Template::policy', compact('policy', 'pageTitle', 'seoContents', 'seoImage'));
    }

    public function changeLanguage($lang = null) {
        $language = Language::where('code', $lang)->first();
        if (!$language) {
            $lang = 'en';
        }

        session()->put('lang', $lang);
        return back();
    }

    public function blogs() {
        $pageTitle   = 'Blog';
        $blogs       = Frontend::where('data_keys', 'blog.element')->orderByDesc('id')->paginate(getPaginate());
        $sections    = Page::where('tempname', activeTemplate())->where('slug', 'blog')->first();
        $seoContents = $sections->seo_content;
        $seoImage    = $seoContents?->image ? getImage(getFilePath('seo') . '/' . $seoContents?->image, getFileSize('seo')) : null;
        return view('Template::blogs', compact('pageTitle', 'blogs', 'sections', 'seoContents', 'seoImage'));
    }

    public function blogDetails($slug) {
        $blog        = Frontend::where('slug', $slug)->where('data_keys', 'blog.element')->firstOrFail();
        $recentBlogs = Frontend::where('data_keys', 'blog.element')->where('id', '!=', $blog->id)->orderBy('id', 'desc')->take(5)->get();
        $pageTitle   = 'Blog Details';
        $seoContents = $blog->seo_content;
        $seoImage    = $seoContents?->image ? frontendImage('blog', $seoContents?->image, getFileSize('seo'), true) : null;
        return view('Template::blog_details', compact('blog', 'pageTitle', 'seoContents', 'seoImage', 'recentBlogs'));
    }

    public function cookieAccept() {
        Cookie::queue('gdpr_cookie', gs('site_name'), 43200);
    }

    public function cookiePolicy() {
        $cookieContent = Frontend::where('data_keys', 'cookie.data')->first();
        abort_if($cookieContent->data_values->status != Status::ENABLE, 404);
        $pageTitle = 'Cookie Policy';
        $cookie    = Frontend::where('data_keys', 'cookie.data')->first();
        return view('Template::cookie', compact('pageTitle', 'cookie'));
    }

    public function placeholderImage($size = null) {
        $imgWidth  = explode('x', $size)[0];
        $imgHeight = explode('x', $size)[1];
        $text      = $imgWidth . '×' . $imgHeight;
        $fontFile  = realpath('assets/font/solaimanLipi_bold.ttf');
        $fontSize  = round(($imgWidth - 50) / 8);
        if ($fontSize <= 9) {
            $fontSize = 9;
        }
        if ($imgHeight < 100 && $fontSize > 30) {
            $fontSize = 30;
        }

        $image     = imagecreatetruecolor($imgWidth, $imgHeight);
        $colorFill = imagecolorallocate($image, 100, 100, 100);
        $bgFill    = imagecolorallocate($image, 255, 255, 255);
        imagefill($image, 0, 0, $bgFill);
        $textBox    = imagettfbbox($fontSize, 0, $fontFile, $text);
        $textWidth  = abs($textBox[4] - $textBox[0]);
        $textHeight = abs($textBox[5] - $textBox[1]);
        $textX      = ($imgWidth - $textWidth) / 2;
        $textY      = ($imgHeight + $textHeight) / 2;
        header('Content-Type: image/jpeg');
        imagettftext($image, $fontSize, 0, $textX, $textY, $colorFill, $fontFile, $text);
        imagejpeg($image);
        imagedestroy($image);
    }

    public function maintenance() {
        $pageTitle = 'Maintenance Mode';
        if (gs('maintenance_mode') == Status::DISABLE) {
            return to_route('home');
        }
        $maintenance = Frontend::where('data_keys', 'maintenance.data')->first();
        return view('Template::maintenance', compact('pageTitle', 'maintenance'));
    }

    public function subscribe(Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:subscribers,email',
        ]);

        if ($validator->fails()) {
            return responseError('validation_failed', $validator->errors());
        }

        $subscriber        = new Subscriber();
        $subscriber->email = $request->email;
        $subscriber->save();

        return responseSuccess('subscribed', ['Thanks for subscribing!']);
    }

    public function campaigns($categoryId = 0) {
        $pageTitle = 'All Campaigns';
        $campaigns = Campaign::running()->searchable(['title', 'description'])->whereHas('category', function ($query) {
            $query->active();
        });

        if ($categoryId) {
            $campaigns = $campaigns->where('category_id', $categoryId);
        }

        $campaigns  = $campaigns->latest()->paginate(12);
        $categories = Category::active()->withCount(['campaigns' => function ($query) {
            $query->running();
        }])->get();
        $traffics = TrafficType::active()->get();
        return view('Template::campaign.index', compact('pageTitle', 'campaigns', 'categories', 'traffics', 'categoryId'));
    }

    public function campaignsFilter(Request $request) {
        $campaigns = Campaign::running()->searchable(['title', 'description'])->whereHas('category', function ($query) {
            $query->active();
        });
        if ($request->category && !in_array('on', $request->category)) {
            $campaigns = $campaigns->whereIn('category_id', $request->category);
        }

        if ($request->traffic_type && !in_array('on', $request->traffic_type)) {
            $traffics  = CampaignTrafficType::whereIn('traffic_type_id', $request->traffic_type)->pluck('campaign_id')->toArray();
            $campaigns = $campaigns->whereIn('id', $traffics);
        }

        if ($request->date) {
            switch ($request->date) {
            case 'Today':
                $campaigns = $campaigns->whereDate('starts_at', today());
                break;

            case 'Yesterday':
                $campaigns = $campaigns->whereDate('starts_at', Carbon::yesterday());
                break;

            case 'Last 7 Days':
                $campaigns = $campaigns->whereBetween('starts_at', [now()->subDays(7), now()]);
                break;

            case 'Last 30 Days':
                $campaigns = $campaigns->whereBetween('starts_at', [now()->subDays(30), now()]);
                break;
            }
        }

        if ($request->sort) {
            switch ($request->sort) {
            case 'Most Recent':
                $campaigns = $campaigns->orderBy('created_at', 'desc');
                break;

            case 'Highest Budget':
                $campaigns = $campaigns->orderBy('budget', 'desc');
                break;

            case 'Lowest Budget':
                $campaigns = $campaigns->orderBy('budget', 'asc');
                break;

            case 'Most Conversions':
                $campaigns = $campaigns->orderBy('conversion_limit', 'desc');
                break;
            }
        }

        if ($request->date == 'on' && $request->sort == 'on' && ($request->traffic_type && in_array('on', $request->traffic_type)) && ($request->category && in_array('on', $request->category))) {
            $campaigns = $campaigns->latest();
        }

        $campaigns = $campaigns->paginate(12);

        return view("Template::partials.campaigns", compact('campaigns'));
    }

    public function campaignDetails($slug, ) {
        $campaign = Campaign::running()->where('slug', $slug)->withWhereHas('category', function ($query) {
            $query->active();
        })->with(['advertiser'])->firstOrFail();
        $pageTitle = 'Campaign Detail';
        return view('Template::campaign.details', compact('pageTitle', 'campaign'));
    }
}
