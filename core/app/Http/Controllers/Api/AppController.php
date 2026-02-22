<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Frontend;
use App\Models\GeneralSetting;
use App\Models\Language;
use App\Models\Page;
use App\Models\Subscriber;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use App\Models\Category;
use App\Models\Campaign;
use App\Models\CampaignTrafficType;
use App\Models\TrafficType;
use App\Constants\Status;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Validator;

class AppController extends Controller
{
    private function requiredPolicyPages(): array
    {
        return [
            'privacy-policy'   => 'Privacy Policy',
            'terms-of-service' => 'Terms And Condition',
            'refund-policy'    => 'Refund Policy',
            'disclaimer'       => 'Disclaimer',
        ];
    }

    public function generalSetting()
    {
        $general = gs();
        return responseSuccess('general_setting', ['General setting retrieved successfully'], $general);
    }

    public function getCountries()
    {
        $countries = json_decode(file_get_contents(resource_path('views/partials/country.json')));
        return responseSuccess('countries', ['Countries retrieved successfully'], $countries);
    }

    public function getLanguage($key = null)
    {
        if ($key) {
            $language = Language::where('code', $key)->first();
            if (!$language) {
                return responseError('language_not_found', ['Language not found']);
            }
            return responseSuccess('language', ['Language retrieved successfully'], $language);
        }
        
        $languages = Language::all();
        return responseSuccess('languages', ['Languages retrieved successfully'], $languages);
    }

    public function policies()
    {
        // Ensure required policy pages exist (so footer always shows them)
        $temp = activeTemplateName();
        $required = $this->requiredPolicyPages();

        foreach ($required as $slug => $title) {
            $existing = Frontend::where('data_keys', 'policy_pages.element')
                ->where('slug', $slug)
                ->orderBy('id', 'asc')
                ->get();

            if ($existing->count() > 0) {
                $keep = $existing->first();
                $dupes = $existing->slice(1);
                foreach ($dupes as $d) {
                    $d->delete();
                }

                $values = $keep->data_values ? (array) $keep->data_values : [];
                if (!$keep->tempname) {
                    $keep->tempname = $temp;
                }
                // Ensure a title exists
                $keep->data_values = (object) array_merge([
                    'title' => $values['title'] ?? $title,
                    'details' => $values['details'] ?? ($values['description'] ?? ''),
                    'description' => $values['description'] ?? ($values['details'] ?? ''),
                ], $values);
                $keep->save();
                continue;
            }

            $frontend = new Frontend();
            $frontend->data_keys = 'policy_pages.element';
            $frontend->tempname = $temp;
            $frontend->slug = $slug;
            $frontend->data_values = (object) [
                'title' => $title,
                'details' => '',
                'description' => '',
            ];
            $frontend->save();
        }

        $policies = Frontend::where('data_keys', 'policy_pages.element')
            ->whereIn('slug', array_keys($required))
            ->orderByRaw('FIELD(slug, "' . implode('","', array_keys($required)) . '")')
            ->get();

        $data = $policies->map(function ($policy) {
            return [
                'id' => $policy->id,
                'title' => $policy->data_values->title ?? '',
                'slug' => $policy->slug,
            ];
        })->values();

        return responseSuccess('policies', ['Policies retrieved successfully'], $data);
    }

    public function policyContent($slug)
    {
        $policy = Frontend::where('slug', $slug)->where('data_keys', 'policy_pages.element')->first();
        if (!$policy) {
            return responseError('policy_not_found', ['Policy not found']);
        }
        return responseSuccess('policy', ['Policy retrieved successfully'], $policy);
    }

    public function faq()
    {
        $faqs = Frontend::where('data_keys', 'faq.element')->get();
        return responseSuccess('faq', ['FAQ retrieved successfully'], $faqs);
    }

    public function seo()
    {
        $seo = Frontend::where('data_keys', 'seo.data')->first();
        return responseSuccess('seo', ['SEO data retrieved successfully'], $seo);
    }

    public function getExtension($act)
    {
        $extension = \App\Models\Extension::where('act', $act)->where('status', Status::ENABLE)->first();
        if (!$extension) {
            return responseError('extension_not_found', ['Extension not found']);
        }
        return responseSuccess('extension', ['Extension retrieved successfully'], $extension);
    }

    public function submitContact(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return responseError('validation_failed', $validator->errors());
        }

        // Handle subscription type
        if ($request->type === 'subscribe') {
            $subscriber = Subscriber::where('email', $request->email)->first();
            if ($subscriber) {
                return responseError('already_subscribed', ['Email already subscribed']);
            }
            
            $subscriber = new Subscriber();
            $subscriber->email = $request->email;
            $subscriber->save();
            
            return responseSuccess('subscribed', ['Thanks for subscribing!']);
        }

        // Handle cookie accept
        if ($request->type === 'cookie_accept') {
            Cookie::queue('gdpr_cookie', gs('site_name'), 43200);
            return responseSuccess('cookie_accepted', ['Cookie accepted']);
        }

        // Handle regular contact form
        $random = getNumber();
        $ticket = new SupportTicket();
        $ticket->user_id = auth()->id() ?? 0;
        $ticket->name = $request->name;
        $ticket->email = $request->email;
        $ticket->priority = Status::PRIORITY_MEDIUM;
        $ticket->ticket = $random;
        $ticket->subject = $request->subject;
        $ticket->last_reply = now();
        $ticket->status = Status::TICKET_OPEN;
        $ticket->save();

        $adminNotification = new \App\Models\AdminNotification();
        $adminNotification->user_id = auth()->id() ?? 0;
        $adminNotification->title = 'A new contact message has been submitted';
        $adminNotification->click_url = urlPath('admin.ticket.view', $ticket->id);
        $adminNotification->save();

        $message = new SupportMessage();
        $message->support_ticket_id = $ticket->id;
        $message->message = $request->message;
        $message->save();

        return responseSuccess('contact_submitted', ['Ticket created successfully!']);
    }

    public function cookie()
    {
        $cookie = Frontend::where('data_keys', 'cookie.data')->first();
        if (!$cookie) {
            return responseError('cookie_not_found', ['Cookie data not found']);
        }
        return responseSuccess('cookie', ['Cookie data retrieved successfully'], $cookie);
    }

    public function cookieAccept()
    {
        Cookie::queue('gdpr_cookie', gs('site_name'), 43200);
        return responseSuccess('cookie_accepted', ['Cookie accepted']);
    }

    public function customPages()
    {
        $pages = Page::where('tempname', activeTemplateName())->get();
        $data = $pages->map(function ($page) {
            return [
                'id' => $page->id,
                'name' => $page->name,
                'slug' => $page->slug,
            ];
        });
        return responseSuccess('custom_pages', ['Custom pages retrieved successfully'], $data);
    }

    public function customPageData($slug)
    {
        $page = Page::where('tempname', activeTemplateName())->where('slug', $slug)->first();
        if (!$page) {
            return responseError('page_not_found', ['Page not found']);
        }
        return responseSuccess('custom_page', ['Custom page retrieved successfully'], $page);
    }

    public function allSections($key = null)
    {
        if ($key) {
            // Map common section keys to their data_keys patterns
            $keyMap = [
                'footer' => 'footer.content',
                'account_modal' => 'account_modal.content',
                'social_icon' => 'social_icon.element',
                'cookie' => 'cookie.data',
                'about' => 'about.content',
                'login' => 'login.content',
                'register' => 'register.content',
                'banner' => 'banner.content',
            ];

            // Try mapped key first
            $dataKey = $keyMap[$key] ?? null;
            if ($dataKey) {
                // Prefer records that have actual data_values (non-null) to avoid returning empty records
                $section = Frontend::where('data_keys', $dataKey)
                    ->whereNotNull('data_values')
                    ->first();
                // Fallback to any record if all are null
                if (!$section) {
                    $section = Frontend::where('data_keys', $dataKey)->first();
                }
                if ($section) {
                    return responseSuccess('section', ['Section retrieved successfully'], $section);
                }
            }

            // Try direct match - if it ends with .element, get all
            if (str_ends_with($key, '.element')) {
                $sections = Frontend::where('data_keys', $key)->orderBy('id', 'desc')->get()->map(function($item) {
                    return array_merge(['id' => $item->id], (array)$item->data_values);
                });
                return responseSuccess('sections', ['Sections retrieved successfully'], $sections);
            }

            // Prefer active template & latest non-null content (important when multiple rows exist)
            $section = Frontend::where('data_keys', $key)
                ->where('tempname', activeTemplateName())
                ->whereNotNull('data_values')
                ->orderBy('id', 'desc')
                ->first();

            if (!$section) {
                $section = Frontend::where('data_keys', $key)
                    ->whereNotNull('data_values')
                    ->orderBy('id', 'desc')
                    ->first();
            }

            if (!$section) {
                $section = Frontend::where('data_keys', $key)->orderBy('id', 'desc')->first();
            }
            if ($section) {
                return responseSuccess('section', ['Section retrieved successfully'], $section);
            }

            // Try pattern match (key.content, key.element, key.data)
            $section = Frontend::where('data_keys', 'like', $key . '.%')->first();
            if ($section) {
                if (str_contains($section->data_keys, '.element')) {
                    $sections = Frontend::where('data_keys', $section->data_keys)->orderBy('id', 'desc')->get()->map(function($item) {
                        return array_merge(['id' => $item->id], (array)$item->data_values);
                    });
                    return responseSuccess('sections', ['Sections retrieved successfully'], $sections);
                }
                return responseSuccess('section', ['Section retrieved successfully'], $section);
            }

            // Try to get from Page sections
            $page = Page::where('tempname', activeTemplateName())->where('slug', '/')->first();
            if ($page && $page->secs) {
                $sections = json_decode($page->secs, true);
                if (isset($sections[$key])) {
                    return responseSuccess('section', ['Section retrieved successfully'], [
                        'data' => [
                            'content' => [
                                'data_values' => $sections[$key]
                            ]
                        ]
                    ]);
                }
            }

            return responseError('section_not_found', ['Section not found']);
        }

        // Get all sections from home page
        $page = Page::where('tempname', activeTemplateName())->where('slug', '/')->first();
        $sections = null;
        if ($page && $page->secs) {
            $decoded = json_decode($page->secs, true);
            if (is_array($decoded) && !empty($decoded)) {
                $sections = $decoded;
            }
        }
        // Default homepage section order (old project / full UI)
        if (empty($sections)) {
            $sections = [
                'about', 'category', 'campaigns', 'work_process', 'why_choose_us',
                'benefit_section', 'counter_section', 'testimonials', 'cta_section',
                'faq_section', 'blog', 'partner_section'
            ];
        }
        return responseSuccess('sections', ['Sections retrieved successfully'], [
            'data' => $sections,
            'secs' => $sections
        ]);
    }

    public function viewTicket($ticket)
    {
        $ticket = SupportTicket::where('ticket', $ticket)->with('messages')->first();
        if (!$ticket) {
            return responseError('ticket_not_found', ['Ticket not found']);
        }
        return responseSuccess('ticket', ['Ticket retrieved successfully'], $ticket);
    }

    public function replyTicket(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return responseError('validation_failed', $validator->errors());
        }

        $ticket = SupportTicket::findOrFail($id);
        $message = new SupportMessage();
        $message->support_ticket_id = $ticket->id;
        $message->message = $request->message;
        $message->save();

        $ticket->last_reply = now();
        $ticket->save();

        return responseSuccess('reply_sent', ['Reply sent successfully']);
    }
    
    public function getCategories()
    {
        $categories = Category::active()->withCount(['campaigns' => function ($query) {
            $query->running();
        }])->get();
        return responseSuccess('categories', ['Categories retrieved successfully'], $categories);
    }

    public function getTrafficTypes()
    {
        $traffics = TrafficType::active()->get();
        return responseSuccess('traffic_types', ['Traffic types retrieved successfully'], $traffics);
    }

    public function getCampaigns(Request $request)
    {
        $campaigns = Campaign::running()->searchable(['title', 'description'])->whereHas('category', function ($query) {
            $query->active();
        });

        if ($request->category_id) {
            $campaigns = $campaigns->where('category_id', $request->category_id);
        }
        if ($request->has('category') && is_array($request->category) && !in_array('on', $request->category)) {
            $campaigns = $campaigns->whereIn('category_id', $request->category);
        }
        if ($request->has('traffic_type') && is_array($request->traffic_type) && !in_array('on', $request->traffic_type)) {
            $ids = CampaignTrafficType::whereIn('traffic_type_id', $request->traffic_type)->pluck('campaign_id')->toArray();
            $campaigns = $campaigns->whereIn('id', $ids);
        }
        if ($request->date && $request->date !== 'All') {
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
        if ($request->sort && $request->sort !== 'All') {
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
                case 'Most conversions':
                case 'Most Conversions':
                    $campaigns = $campaigns->orderBy('conversion_limit', 'desc');
                    break;
                default:
                    $campaigns = $campaigns->latest();
            }
        } else {
            $campaigns = $campaigns->latest();
        }

        $campaigns = $campaigns->paginate(12);
        $campaigns->getCollection()->transform(function ($c) {
            $c->payout_text = showAmount($c->payout_per_conversion ?? 0);
            return $c;
        });
        return responseSuccess('campaigns', ['Campaigns retrieved successfully'], $campaigns);
    }

    public function getCampaignDetails($slug)
    {
        $campaign = Campaign::running()->where('slug', $slug)->withWhereHas('category', function ($query) {
            $query->active();
        })->with(['advertiser'])->first();

        if (!$campaign) {
            return responseError('campaign_not_found', ['Campaign not found']);
        }

        return responseSuccess('campaign', ['Campaign details retrieved successfully'], $campaign);
    }

    public function paymentMethodsStatus()
    {
        $allowedAliases = ['simplypay', 'watchpay', 'rupeerush', 'custom_qr'];
        $gateways = \App\Models\Gateway::whereIn('alias', $allowedAliases)->get(['alias', 'status']);
        
        $status = [];
        foreach ($gateways as $gateway) {
            $status[strtolower($gateway->alias)] = (int)$gateway->status;
        }
        
        // Handle variations
        if (isset($status['custom_qr'])) $status['customqr'] = $status['custom_qr'];

        return responseSuccess('gateway_status', ['Gateway status retrieved successfully'], $status);
    }
}
