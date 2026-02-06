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
use App\Constants\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Validator;

class AppController extends Controller
{
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
        $policies = Frontend::where('data_keys', 'policy_pages.element')->get();
        $data = $policies->map(function ($policy) {
            return [
                'id' => $policy->id,
                'title' => $policy->data_values->title ?? '',
                'slug' => $policy->slug,
            ];
        });
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
            ];

            // Try mapped key first
            $dataKey = $keyMap[$key] ?? null;
            if ($dataKey) {
                $section = Frontend::where('data_keys', $dataKey)->first();
                if ($section) {
                    return responseSuccess('section', ['Section retrieved successfully'], $section);
                }
            }

            // Try direct match
            $section = Frontend::where('data_keys', $key)->first();
            if ($section) {
                return responseSuccess('section', ['Section retrieved successfully'], $section);
            }

            // Try pattern match (key.content, key.element, key.data)
            $section = Frontend::where('data_keys', 'like', $key . '.%')->first();
            if ($section) {
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
        if ($page && $page->secs) {
            $sections = json_decode($page->secs, true);
            return responseSuccess('sections', ['Sections retrieved successfully'], [
                'data' => $sections
            ]);
        }

        // Fallback: get all frontend sections
        $sections = Frontend::where('data_keys', 'like', '%.%')->get();
        return responseSuccess('sections', ['Sections retrieved successfully'], $sections);
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
}
