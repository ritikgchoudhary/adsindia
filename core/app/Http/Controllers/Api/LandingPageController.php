<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LandingPageSetting;
use App\Models\User;
use App\Http\Controllers\Api\Auth\RegisterController;

class LandingPageController extends Controller
{
    /**
     * Get the dynamic landing page settings.
     * This API is public but accepts a ?ref= parameter to return dynamic checkout links.
     */
    public function getSettings(Request $request)
    {
        $setting = LandingPageSetting::getSetting();
        $ref = $request->input('ref', '');

        // Generate the exact link for the CTA button based on active plan and ref
        $activePackageId = $setting->active_package_id;
        $finalPrice = $setting->discounted_price;
        
        // Fetch original package price to calculate discount amount for the signature
        $pkgMeta = RegisterController::packageMeta((int)$activePackageId);
        $originalPrice = (int)($pkgMeta['price'] ?? 0);
        
        $discountAmount = 0;
        if ($finalPrice !== null && $finalPrice > 0 && $finalPrice < $originalPrice) {
            $discountAmount = $originalPrice - (int)$finalPrice;
        }

        // Base link fallback
        $registerLink = env('APP_URL') . '/register';
        $whatsappNumber = '';
        $sponsorName = '';

        if ($ref) {
            $referrer = RegisterController::findReferrerByRef($ref);
            if ($referrer) {
                $whatsappNumber = $referrer->mobile ?: '';
                $sponsorName = trim($referrer->firstname . ' ' . $referrer->lastname);
                if (!$sponsorName) $sponsorName = $referrer->username;
                
                // For signed package logic
                $sigRef = $ref;
                $pkgSig = RegisterController::packageSig($sigRef, $activePackageId, $discountAmount ?: null);
                
                // Build explicit checkout prep link that auto-locks the desired package and sponsor
                $registerLink = url('/register?ref=' . urlencode($ref) 
                    . '&pkg=' . $activePackageId 
                    . '&disc=' . ($discountAmount > 0 ? $discountAmount : '')
                    . '&sig=' . urlencode($pkgSig));
            } else {
                // If ref is invalid, default without ref
                $pkgSig = RegisterController::packageSig('GLOBAL', $activePackageId, $discountAmount ?: null);
                $registerLink = url('/register?pkg=' . $activePackageId 
                    . '&disc=' . ($discountAmount > 0 ? $discountAmount : '')
                    . '&sig=' . urlencode($pkgSig));
            }
        } else {
            // No ref, generated a clean direct link but with the correct package settings
            $pkgSig = RegisterController::packageSig('GLOBAL', $activePackageId, $discountAmount ?: null);
            $registerLink = url('/register?pkg=' . $activePackageId 
                . '&disc=' . ($discountAmount > 0 ? $discountAmount : '')
                . '&sig=' . urlencode($pkgSig));
        }

        // To make it fully SPA friendly, provide relative path for Vue router
        $parsedLink = parse_url($registerLink);
        $vueRoute = '/register' . (isset($parsedLink['query']) ? '?' . $parsedLink['query'] : '');

        return responseSuccess('landing_page_settings', ['Data loaded'], [
            'settings' => $setting,
            'cta_url' => $vueRoute,    // SPA friendly
            'whatsapp_number' => $whatsappNumber, // Plain format, e.g. 9876543210
            'sponsor_name' => $sponsorName,
            'prefilled_whatsapp_msg' => 'Hi ' . ($sponsorName ?: 'Sir/Ma\'am') . ', I am interested in joining ADS SKILL INDIA to earn money daily. Please guide me on how to start!',
        ]);
    }
    
    /**
     * Admin functionality to update settings
     */
    public function updateSettings(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'video_url' => 'nullable',
            // relax file part if it's causing issues
            'active_package_id' => 'required',
            'discounted_price' => 'nullable',
            'timer_hours' => 'required',
        ]);

        if ($validator->fails()) {
            return responseError('validation_error', $validator->errors()->all());
        }

        try {
            $setting = LandingPageSetting::getSetting();

            if ($request->hasFile('media_file')) {
                $file = $request->file('media_file');
                $originalName = $file->getClientOriginalName();
                $filename = time() . '_' . preg_replace('/[^A-Za-z0-9.\-]/', '_', $originalName);
                
                $destPath = base_path('../public/assets/images/landing');
                if (!file_exists($destPath)) {
                    mkdir($destPath, 0755, true);
                }
                
                $file->move($destPath, $filename);
                $setting->video_url = asset('assets/images/landing/' . $filename);
            } else if ($request->filled('video_url')) {
                $setting->video_url = $request->video_url;
            }

            $setting->active_package_id = $request->active_package_id;
            $setting->discounted_price = $request->discounted_price;
            $setting->timer_hours = $request->timer_hours;
            
            if ($request->has('whatsapp_heading')) $setting->whatsapp_heading = $request->whatsapp_heading;
            if ($request->has('marketing_heading')) $setting->marketing_heading = $request->marketing_heading;
            if ($request->has('marketing_subheading')) $setting->marketing_subheading = $request->marketing_subheading;

            $setting->save();

            return responseSuccess('landing_page_settings_updated', ['Landing page settings updated successfully'], [
                'settings' => $setting
            ]);
        } catch (\Exception $e) {
            \Log::error('LP Update Error: ' . $e->getMessage());
            return responseError('update_failed', ['An error occurred: ' . $e->getMessage()]);
        }
    }

    /**
     * User functionality to retrieve their own generic tracking links
     */
    public function getMyLinks(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return responseError('unauthorized', ['Unauthorized']);
        }
        
        $refCode = 'ADS' . $user->id;
        $appUrl = env('APP_URL') ?? url('/');
        // Ensure appUrl doesn't have trailing slash
        $appUrl = rtrim($appUrl, '/');
        
        return responseSuccess('my_landing_links', ['Landing Links Loaded'], [
            'wa_link' => $appUrl . '/wa/' . $refCode,
            'offer_link' => $appUrl . '/offer/' . $refCode,
        ]);
    }
}
