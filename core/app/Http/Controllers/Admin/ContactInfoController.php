<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Frontend;
use Illuminate\Http\Request;

class ContactInfoController extends Controller
{
    /**
     * Get homepage contact section info (Master Admin).
     * Values are stored in Frontend data_keys: contact_us.content
     */
    public function index()
    {
        $temp = activeTemplateName();

        $frontend = Frontend::where('data_keys', 'contact_us.content')
            ->where('tempname', $temp)
            ->first();

        if (!$frontend) {
            $frontend = Frontend::where('data_keys', 'contact_us.content')->first();
        }

        $data = $frontend && $frontend->data_values ? (array) $frontend->data_values : [];

        return response()->json([
            'status' => 'success',
            'message' => ['Contact info retrieved successfully'],
            'data' => [
                'title' => $data['title'] ?? '',
                'address' => $data['address'] ?? '',
                'email_address' => $data['email_address'] ?? '',
                'contact_number' => $data['contact_number'] ?? '',
            ],
        ]);
    }

    /**
     * Update homepage contact section info (Master Admin).
     */
    public function update(Request $request)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:2000',
            'email_address' => 'nullable|email|max:255',
            'contact_number' => 'nullable|string|max:50',
        ]);

        $temp = activeTemplateName();

        $frontend = Frontend::where('data_keys', 'contact_us.content')
            ->where('tempname', $temp)
            ->first();

        if (!$frontend) {
            // If a record exists without tempname, update it but also set tempname to active template.
            $frontend = Frontend::where('data_keys', 'contact_us.content')->first();
        }

        if (!$frontend) {
            $frontend = new Frontend();
            $frontend->data_keys = 'contact_us.content';
        }

        $frontend->tempname = $temp;
        $frontend->data_values = (object) [
            'title' => $request->input('title', ''),
            'address' => $request->input('address', ''),
            'email_address' => $request->input('email_address', ''),
            'contact_number' => $request->input('contact_number', ''),
        ];
        $frontend->save();

        return response()->json([
            'status' => 'success',
            'message' => ['Contact info updated successfully'],
            'data' => (array) $frontend->data_values,
        ]);
    }
}

