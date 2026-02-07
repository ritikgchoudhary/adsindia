<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GeneralSetting;

class SupportController extends Controller
{
    public function getSupportLinks()
    {
        $general = gs();

        // Get support links from general settings or use defaults
        $socialiteCredentials = $general->socialite_credentials ?? [];
        
        $links = [
            'telegram' => $socialiteCredentials['telegram_link'] ?? 'https://t.me/AdsSkillIndia',
            'whatsapp' => $socialiteCredentials['whatsapp_link'] ?? 'https://wa.me/919999999999',
            'live_chat' => $socialiteCredentials['live_chat_enabled'] ?? true,
            'live_chat_url' => $socialiteCredentials['live_chat_url'] ?? '/user/ticket/open',
        ];

        return responseSuccess('support_links', ['Support links retrieved successfully'], [
            'data' => $links,
        ]);
    }
}
