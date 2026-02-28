<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InfluencerApplication;
use App\Models\InfluencerProgramSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class InfluencerController extends Controller
{
    private function decodeJsonField(Request $request, string $key): void
    {
        $val = $request->input($key);
        if (is_string($val) && $val !== '') {
            $decoded = json_decode($val, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $request->merge([$key => $decoded]);
            }
        }
    }

    public function apply(Request $request)
    {
        // If honeypot is filled, silently "succeed" but don't store.
        if ((string) $request->input('website', '') !== '') {
            return responseSuccess('application_received', ['Thanks! Your application has been received.']);
        }

        // Support multipart/form-data where nested fields are sent as JSON strings
        $this->decodeJsonField($request, 'data');
        $this->decodeJsonField($request, 'payment_details');

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'city' => 'nullable|string|max:255',
            'platform' => 'nullable|string|max:50',
            'platforms' => 'nullable|array',
            'platforms.*' => 'string|max:50',
            'payment_method' => 'nullable|string|max:50',
            'payment_details' => 'nullable|array',
            'apk_download_url' => 'nullable|string|max:500',
            'apk_version' => 'nullable|string|max:50',
            'install_screenshot_url' => 'nullable|string|max:500',
            'install_screenshot' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:4096',
            'lead_key' => 'nullable|string|max:64',
            'data' => 'nullable|array',
            'source_url' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return responseError('validation_failed', $validator->errors());
        }

        $email = trim((string) $request->input('email', ''));
        $phone = trim((string) $request->input('phone', ''));
        if ($email === '' && $phone === '') {
            return responseError('validation_failed', ['email' => ['Email or phone is required'], 'phone' => ['Email or phone is required']]);
        }

        $leadKey = trim((string) $request->input('lead_key', ''));
        if ($leadKey === '') {
            $leadKey = Str::random(32);
        }

        $platform = trim((string) $request->input('platform', ''));
        $platformsIn = (array) ($request->input('platforms') ?? []);
        $platforms = [];
        if ($platform !== '') {
            $platforms = [$platform];
        } else if (!empty($platformsIn)) {
            $platforms = array_values(array_filter(array_map('strval', $platformsIn), fn ($v) => trim($v) !== ''));
        }
        if (count($platforms) < 1) {
            return responseError('validation_failed', ['platform' => ['Platform is required'], 'platforms' => ['Platform is required']]);
        }
        // Only one platform is allowed per submission (frontend wizard).
        $platforms = [reset($platforms)];

        // Require screenshot (file OR URL) for final submission
        $hasScreenshotUrl = trim((string) $request->input('install_screenshot_url', '')) !== '';
        $hasScreenshotFile = $request->hasFile('install_screenshot');
        if (!$hasScreenshotUrl && !$hasScreenshotFile) {
            return responseError('validation_failed', ['install_screenshot' => ['Installation screenshot is required'], 'install_screenshot_url' => ['Installation screenshot is required']]);
        }

        // If this lead_key already exists (draft), we upgrade it to final submission (no dedupe block).
        $existingLead = InfluencerApplication::query()->where('lead_key', $leadKey)->first();

        // Otherwise basic dedupe to avoid accidental double submits (final submissions only)
        if (!$existingLead) {
            $since = Carbon::now()->subHours(12);
            $existing = InfluencerApplication::query()
                ->where('created_at', '>=', $since)
                ->where(function ($q) use ($email, $phone) {
                    if ($email !== '') {
                        $q->orWhere('email', $email);
                    }
                    if ($phone !== '') {
                        $q->orWhere('phone', $phone);
                    }
                })
                ->where(function ($q) {
                    $q->where('is_draft', false)->orWhereNotNull('submitted_at');
                })
                ->orderByDesc('id')
                ->first();

            if ($existing) {
                return responseSuccess('application_received', ['Thanks! Your application has been received.']);
            }
        }

        $data = (array) ($request->input('data') ?? []);
        $city = trim((string) $request->input('city', ''));
        if ($city !== '') {
            $data['city'] = $data['city'] ?? $city;
        }

        $paymentMethod = trim((string) $request->input('payment_method', ''));
        $paymentDetails = (array) ($request->input('payment_details') ?? []);
        if ($paymentMethod !== '') {
            $data['payment_method'] = $data['payment_method'] ?? $paymentMethod;
        }
        if (!empty($paymentDetails)) {
            $data['payment_details'] = $data['payment_details'] ?? $paymentDetails;
        }

        $apkUrl = trim((string) $request->input('apk_download_url', ''));
        $apkVersion = trim((string) $request->input('apk_version', ''));
        $screenshotUrl = trim((string) $request->input('install_screenshot_url', ''));
        if ($apkUrl !== '') {
            $data['apk_download_url'] = $data['apk_download_url'] ?? $apkUrl;
        }
        if ($apkVersion !== '') {
            $data['apk_version'] = $data['apk_version'] ?? $apkVersion;
        }
        if ($screenshotUrl !== '') {
            $data['install_screenshot_url'] = $data['install_screenshot_url'] ?? $screenshotUrl;
        }

        // If lead_key already exists (draft), upgrade it to final submission
        $app = $existingLead ?: InfluencerApplication::query()->where('lead_key', $leadKey)->first();
        if (!$app) {
            $app = new InfluencerApplication();
        }
        $app->lead_key = $leadKey;
        $app->name = trim((string) $request->input('name'));
        $app->email = $email !== '' ? $email : null;
        $app->phone = $phone !== '' ? $phone : null;
        $app->platforms = $platforms;
        $app->data = $data ?: null;
        $app->status = (int) ($app->status ?? 0);
        $app->is_draft = false;
        $app->last_step = 4;
        $app->submitted_at = now();
        $app->source_url = $request->input('source_url');
        $app->ip = $request->ip();
        $app->user_agent = (string) $request->userAgent();
        $app->save();

        // Ensure status remains pending on new rows
        if (!$app->exists || (int) $app->status === 0) {
            $app->status = 0;
            $app->save();
        }

        // Screenshot upload (optional) - store on local disk
        try {
            if ($request->hasFile('install_screenshot')) {
                $file = $request->file('install_screenshot');
                if ($file && $file->isValid()) {
                    $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
                    $dir = 'influencer_applications/' . now()->format('Y/m') . '/screenshots';
                    $path = $file->storeAs($dir, 'application_' . $app->id . '_' . time() . '.' . $ext, 'local');
                    $d = (array) ($app->data ?? []);
                    $d['install_screenshot_path'] = $path;
                    $app->data = $d;
                    $app->save();
                }
            }
        } catch (\Throwable $e) {
            // Ignore upload errors
        }

        // Also save as TXT (requested): storage/app/influencer_applications/YYYY/MM/application_ID.txt
        try {
            $ts = $app->created_at ? $app->created_at->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s');
            $dir = 'influencer_applications/' . now()->format('Y/m');
            $path = $dir . '/application_' . $app->id . '.txt';

            $safe = function ($v) {
                $s = is_scalar($v) ? (string) $v : json_encode($v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                $s = str_replace(["\r\n", "\r"], "\n", $s);
                $s = preg_replace("/\n{3,}/", "\n\n", $s);
                return trim((string) $s);
            };

            $d = (array) ($app->data ?? []);
            $lines = [
                'Influencer Application',
                '----------------------',
                'ID: ' . $app->id,
                'Submitted At: ' . $ts,
                'Name: ' . $safe($app->name),
                'Phone: ' . $safe($app->phone ?? ''),
                'Email: ' . $safe($app->email ?? ''),
                'Platform: ' . $safe($app->platforms ? ($app->platforms[0] ?? '') : ''),
                'Source URL: ' . $safe($app->source_url ?? ''),
                'IP: ' . $safe($app->ip ?? ''),
                '',
                'Details (JSON):',
                $safe($d),
                '',
            ];

            Storage::disk('local')->put($path, implode("\n", $lines));

            // Store txt path inside data for admin reference
            $d['_txt_path'] = $path;
            $app->data = $d;
            $app->save();
        } catch (\Throwable $e) {
            // Ignore TXT write errors so user can still submit successfully.
        }

        return responseSuccess('application_submitted', ['Submitted successfully. We will contact you soon.']);
    }

    public function draft(Request $request)
    {
        $this->decodeJsonField($request, 'data');
        $this->decodeJsonField($request, 'payment_details');

        $validator = Validator::make($request->all(), [
            'lead_key' => 'required|string|max:64',
            'last_step' => 'nullable|integer|min:1|max:4',
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'city' => 'nullable|string|max:255',
            'platform' => 'nullable|string|max:50',
            'payment_method' => 'nullable|string|max:50',
            'payment_details' => 'nullable|array',
            'apk_download_url' => 'nullable|string|max:500',
            'apk_version' => 'nullable|string|max:50',
            'install_screenshot_url' => 'nullable|string|max:500',
            'data' => 'nullable|array',
            'source_url' => 'nullable|string|max:500',
        ]);
        if ($validator->fails()) {
            return responseError('validation_failed', $validator->errors());
        }

        $leadKey = trim((string) $request->input('lead_key'));
        $lastStep = (int) ($request->input('last_step', 1) ?? 1);

        $app = InfluencerApplication::query()->where('lead_key', $leadKey)->first();
        if (!$app) {
            $app = new InfluencerApplication();
            $app->lead_key = $leadKey;
            $app->status = 0;
        }

        // Merge draft data
        $data = (array) ($app->data ?? []);
        $incomingData = (array) ($request->input('data') ?? []);
        $data = array_merge($data, $incomingData);

        $city = trim((string) $request->input('city', ''));
        if ($city !== '') $data['city'] = $city;

        $pm = trim((string) $request->input('payment_method', ''));
        if ($pm !== '') $data['payment_method'] = $pm;

        $pd = (array) ($request->input('payment_details') ?? []);
        if (!empty($pd)) $data['payment_details'] = array_merge((array) ($data['payment_details'] ?? []), $pd);

        foreach (['apk_download_url','apk_version','install_screenshot_url'] as $k) {
            $v = trim((string) $request->input($k, ''));
            if ($v !== '') $data[$k] = $v;
        }

        $platform = trim((string) $request->input('platform', ''));
        if ($platform !== '') {
            $app->platforms = [$platform];
        }

        $name = trim((string) $request->input('name', ''));
        if ($name !== '') $app->name = $name;
        $email = trim((string) $request->input('email', ''));
        if ($email !== '') $app->email = $email;
        $phone = trim((string) $request->input('phone', ''));
        if ($phone !== '') $app->phone = $phone;

        $app->data = $data ?: null;
        $app->is_draft = true;
        $app->last_step = max((int) ($app->last_step ?? 0), $lastStep);
        $app->source_url = $request->input('source_url') ?: $app->source_url;
        $app->ip = $request->ip();
        $app->user_agent = (string) $request->userAgent();
        $app->save();

        return responseSuccess('draft_saved', ['Draft saved'], [
            'id' => (int) $app->id,
            'lead_key' => (string) $app->lead_key,
            'last_step' => (int) $app->last_step,
        ]);
    }

    public function config()
    {
        $row = InfluencerProgramSetting::query()->orderBy('id', 'asc')->first();
        if (!$row) {
            $row = InfluencerProgramSetting::create([
                'apk_path' => null,
                'apk_url' => null,
                'apk_version' => null,
                'updated_by' => null,
            ]);
        }
        return responseSuccess('influencer_config', ['Influencer config'], [
            'apk_url' => (string) ($row->apk_url ?? ''),
            'apk_version' => (string) ($row->apk_version ?? ''),
        ]);
    }
}

