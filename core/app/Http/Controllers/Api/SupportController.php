<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Frontend;
use App\Models\GeneralSetting;

class SupportController extends Controller
{
    public function getSupportLinks()
    {
        $frontend = Frontend::where('data_keys', 'support_links.data')->first();
        $data = $frontend && $frontend->data_values ? (array) $frontend->data_values : [];

        if (empty($data['telegram_link']) && empty($data['whatsapp_link']) && empty($data['live_chat_url'])) {
            $general = gs();
            $socialiteCredentials = $general->socialite_credentials ?? [];
            $data = [
                'telegram_link' => $socialiteCredentials['telegram_link'] ?? 'https://t.me/AdsSkillIndia',
                'telegram_group_link' => $socialiteCredentials['telegram_group_link'] ?? ($socialiteCredentials['telegram_link'] ?? 'https://t.me/AdsSkillIndia'),
                'whatsapp_link' => $socialiteCredentials['whatsapp_link'] ?? 'https://wa.me/919999999999',
                'live_chat_url' => $socialiteCredentials['live_chat_url'] ?? '',
            ];
        }

        $isTelegramEnabled = isset($data['is_telegram_enabled']) ? $data['is_telegram_enabled'] : true;
        $isTelegramGroupEnabled = isset($data['is_telegram_group_enabled']) ? $data['is_telegram_group_enabled'] : true;
        $isWhatsappEnabled = isset($data['is_whatsapp_enabled']) ? $data['is_whatsapp_enabled'] : true;
        $isLiveChatEnabled = isset($data['is_live_chat_enabled']) ? $data['is_live_chat_enabled'] : true;

        $links = [
            'telegram' => $isTelegramEnabled && !empty($data['telegram_link']) ? $data['telegram_link'] : '',
            'telegram_group' => $isTelegramGroupEnabled && !empty($data['telegram_group_link']) ? $data['telegram_group_link'] : '',
            'whatsapp' => $isWhatsappEnabled && !empty($data['whatsapp_link']) ? $data['whatsapp_link'] : '',
            'live_chat' => $isLiveChatEnabled && !empty($data['live_chat_url']),
            'live_chat_url' => $isLiveChatEnabled && !empty($data['live_chat_url']) ? $data['live_chat_url'] : '',
        ];

        return responseSuccess('support_links', ['Support links retrieved successfully'], [
            // Keep response.data as the links object (frontend expects flat)
            ...$links,
        ]);
    }
}
