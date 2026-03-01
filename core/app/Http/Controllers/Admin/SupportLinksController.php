<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Frontend;
use Illuminate\Http\Request;

class SupportLinksController extends Controller
{
    /**
     * Get customer support links (for Master Admin panel).
     */
    public function index()
    {
        $frontend = Frontend::where('data_keys', 'support_links.data')->first();
        $data = $frontend && $frontend->data_values ? (array) $frontend->data_values : [];

        $links = [
            'telegram_link' => $data['telegram_link'] ?? '',
            'is_telegram_enabled' => isset($data['is_telegram_enabled']) ? (bool) $data['is_telegram_enabled'] : true,
            'telegram_group_link' => $data['telegram_group_link'] ?? '',
            'is_telegram_group_enabled' => isset($data['is_telegram_group_enabled']) ? (bool) $data['is_telegram_group_enabled'] : true,
            'whatsapp_link' => $data['whatsapp_link'] ?? '',
            'is_whatsapp_enabled' => isset($data['is_whatsapp_enabled']) ? (bool) $data['is_whatsapp_enabled'] : true,
            'live_chat_url' => $data['live_chat_url'] ?? '',
            'is_live_chat_enabled' => isset($data['is_live_chat_enabled']) ? (bool) $data['is_live_chat_enabled'] : true,
        ];

        return response()->json([
            'status' => 'success',
            'message' => ['Support links retrieved successfully'],
            'data' => $links,
        ]);
    }

    /**
     * Update customer support links (from Master Admin panel).
     */
    public function update(Request $request)
    {
        $request->validate([
            'telegram_link' => 'nullable|string|max:500',
            'is_telegram_enabled' => 'nullable|boolean',
            'telegram_group_link' => 'nullable|string|max:500',
            'is_telegram_group_enabled' => 'nullable|boolean',
            'whatsapp_link' => 'nullable|string|max:500',
            'is_whatsapp_enabled' => 'nullable|boolean',
            'live_chat_url' => 'nullable|string|max:500',
            'is_live_chat_enabled' => 'nullable|boolean',
        ]);

        $telegram = $request->input('telegram_link', '');
        $isTelegramEnabled = $request->boolean('is_telegram_enabled');
        
        $telegramGroup = $request->input('telegram_group_link', '');
        $isTelegramGroupEnabled = $request->boolean('is_telegram_group_enabled');
        
        $whatsapp = $request->input('whatsapp_link', '');
        $isWhatsappEnabled = $request->boolean('is_whatsapp_enabled');
        
        $liveChat = $request->input('live_chat_url', '');
        $isLiveChatEnabled = $request->boolean('is_live_chat_enabled');

        $frontend = Frontend::where('data_keys', 'support_links.data')->first();
        if (!$frontend) {
            $frontend = new Frontend();
            $frontend->data_keys = 'support_links.data';
        }

        $frontend->data_values = (object) [
            'telegram_link' => $telegram,
            'is_telegram_enabled' => $isTelegramEnabled,
            'telegram_group_link' => $telegramGroup,
            'is_telegram_group_enabled' => $isTelegramGroupEnabled,
            'whatsapp_link' => $whatsapp,
            'is_whatsapp_enabled' => $isWhatsappEnabled,
            'live_chat_url' => $liveChat,
            'is_live_chat_enabled' => $isLiveChatEnabled,
        ];
        $frontend->save();

        return response()->json([
            'status' => 'success',
            'message' => ['Support links updated successfully'],
            'data' => $frontend->data_values,
        ]);
    }
}
