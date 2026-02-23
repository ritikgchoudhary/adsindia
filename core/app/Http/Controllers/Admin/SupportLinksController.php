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
            'telegram_group_link' => $data['telegram_group_link'] ?? '',
            'whatsapp_link' => $data['whatsapp_link'] ?? '',
            'live_chat_url' => $data['live_chat_url'] ?? '',
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
            'telegram_group_link' => 'nullable|string|max:500',
            'whatsapp_link' => 'nullable|string|max:500',
            'live_chat_url' => 'nullable|string|max:500',
        ]);

        $telegram = $request->input('telegram_link', '');
        $telegramGroup = $request->input('telegram_group_link', '');
        $whatsapp = $request->input('whatsapp_link', '');
        $liveChat = $request->input('live_chat_url', '');

        $frontend = Frontend::where('data_keys', 'support_links.data')->first();
        if (!$frontend) {
            $frontend = new Frontend();
            $frontend->data_keys = 'support_links.data';
        }

        $frontend->data_values = (object) [
            'telegram_link' => $telegram,
            'telegram_group_link' => $telegramGroup,
            'whatsapp_link' => $whatsapp,
            'live_chat_url' => $liveChat,
        ];
        $frontend->save();

        return response()->json([
            'status' => 'success',
            'message' => ['Support links updated successfully'],
            'data' => [
                'telegram_link' => $telegram,
                'telegram_group_link' => $telegramGroup,
                'whatsapp_link' => $whatsapp,
                'live_chat_url' => $liveChat,
            ],
        ]);
    }
}
