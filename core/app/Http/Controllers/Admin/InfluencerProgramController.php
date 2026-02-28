<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InfluencerApplication;
use App\Models\InfluencerProgramSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InfluencerProgramController extends Controller
{
    private function ensureSuper(): void
    {
        $admin = auth()->user();
        if (!$admin || empty($admin->is_super_admin)) {
            abort(403);
        }
    }

    private function getSettingsRow(): InfluencerProgramSetting
    {
        $this->ensureSuper();
        $row = InfluencerProgramSetting::query()->orderBy('id', 'asc')->first();
        if (!$row) {
            $row = InfluencerProgramSetting::create([
                'apk_path' => null,
                'apk_url' => null,
                'apk_version' => null,
                'updated_by' => auth()->id(),
            ]);
        }
        return $row;
    }

    public function settings()
    {
        $this->ensureSuper();
        $row = $this->getSettingsRow();
        return responseSuccess('influencer_settings', ['Influencer program settings'], [
            'apk_url' => (string) ($row->apk_url ?? ''),
            'apk_version' => (string) ($row->apk_version ?? ''),
            'updated_at' => $row->updated_at ? $row->updated_at->format('Y-m-d H:i:s') : null,
        ]);
    }

    public function uploadApk(Request $request)
    {
        $this->ensureSuper();
        if (!$request->hasFile('apk')) {
            return responseError('validation_failed', ['apk' => ['APK file is required']]);
        }

        $file = $request->file('apk');
        if (!$file || !$file->isValid()) {
            return responseError('validation_failed', ['apk' => ['Invalid file']]);
        }

        $ext = strtolower($file->getClientOriginalExtension() ?: '');
        if ($ext !== 'apk') {
            return responseError('validation_failed', ['apk' => ['Only .apk files are allowed']]);
        }

        $version = trim((string) $request->input('apk_version', ''));
        if ($version === '') {
            $version = trim((string) $request->input('version', ''));
        }

        // Store into project root: /downloads (static served)
        $downloadsDir = base_path('../downloads');
        if (!is_dir($downloadsDir)) {
            @mkdir($downloadsDir, 0755, true);
        }

        $fileName = 'ads-skill-india-' . date('Ymd-His') . '.apk';
        $file->move($downloadsDir, $fileName);

        $publicUrl = '/downloads/' . $fileName;
        if ($version !== '') {
            $publicUrl .= '?v=' . rawurlencode($version);
        }

        $row = $this->getSettingsRow();
        $row->apk_path = 'downloads/' . $fileName;
        $row->apk_url = $publicUrl;
        $row->apk_version = $version !== '' ? $version : $row->apk_version;
        $row->updated_by = auth()->id();
        $row->save();

        return responseSuccess('apk_uploaded', ['APK uploaded successfully'], [
            'apk_url' => (string) $row->apk_url,
            'apk_version' => (string) ($row->apk_version ?? ''),
        ]);
    }

    public function listApplications(Request $request)
    {
        $this->ensureSuper();
        $q = trim((string) $request->query('q', ''));
        $status = $request->query('status', '');
        $draft = $request->query('draft', '');

        $rows = InfluencerApplication::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->orWhere('name', 'like', "%$q%")
                        ->orWhere('email', 'like', "%$q%")
                        ->orWhere('phone', 'like', "%$q%")
                        ->orWhere('lead_key', 'like', "%$q%");
                });
            })
            ->when($status !== '' && is_numeric($status), fn ($query) => $query->where('status', (int) $status))
            ->when($draft !== '', fn ($query) => $query->where('is_draft', (bool) ((int) $draft)))
            ->orderByDesc('id')
            ->limit(200)
            ->get();

        $data = $rows->map(function ($r) {
            $d = (array) ($r->data ?? []);
            $platform = '';
            try {
                $platform = (string) (($r->platforms ?? [])[0] ?? '');
            } catch (\Throwable $e) {}

            return [
                'id' => (int) $r->id,
                'lead_key' => (string) ($r->lead_key ?? ''),
                'is_draft' => (bool) ($r->is_draft ?? false),
                'last_step' => (int) ($r->last_step ?? 0),
                'submitted_at' => $r->submitted_at ? $r->submitted_at->format('Y-m-d H:i:s') : null,
                'name' => (string) ($r->name ?? ''),
                'phone' => (string) ($r->phone ?? ''),
                'email' => (string) ($r->email ?? ''),
                'platform' => $platform,
                'payment_method' => (string) ($d['payment_method'] ?? ''),
                'status' => (int) ($r->status ?? 0),
                'created_at' => $r->created_at ? $r->created_at->format('Y-m-d H:i:s') : null,
                'has_screenshot' => !empty($d['install_screenshot_path']) || !empty($d['install_screenshot_url']),
            ];
        })->values();

        return responseSuccess('influencer_applications', ['Influencer applications'], [
            'rows' => $data,
        ]);
    }

    public function getApplication($id)
    {
        $this->ensureSuper();
        $r = InfluencerApplication::query()->findOrFail($id);
        $d = (array) ($r->data ?? []);
        $platform = (string) (($r->platforms ?? [])[0] ?? '');

        return responseSuccess('influencer_application', ['Influencer application'], [
            'id' => (int) $r->id,
            'lead_key' => (string) ($r->lead_key ?? ''),
            'is_draft' => (bool) ($r->is_draft ?? false),
            'last_step' => (int) ($r->last_step ?? 0),
            'submitted_at' => $r->submitted_at ? $r->submitted_at->format('Y-m-d H:i:s') : null,
            'name' => (string) ($r->name ?? ''),
            'phone' => (string) ($r->phone ?? ''),
            'email' => (string) ($r->email ?? ''),
            'platform' => $platform,
            'platforms' => $r->platforms ?? [],
            'status' => (int) ($r->status ?? 0),
            'admin_notes' => (string) ($r->admin_notes ?? ''),
            'created_at' => $r->created_at ? $r->created_at->format('Y-m-d H:i:s') : null,
            'data' => $d,
            'screenshot_url' => (!empty($d['install_screenshot_path'])) ? (string) url('/api/admin/influencer/applications/' . $r->id . '/screenshot') : '',
        ]);
    }

    public function updateApplication(Request $request, $id)
    {
        $this->ensureSuper();
        $r = InfluencerApplication::query()->findOrFail($id);
        $status = $request->input('status', null);
        $notes = $request->input('admin_notes', null);

        if ($status !== null && $status !== '') {
            $r->status = (int) $status;
        }
        if ($notes !== null) {
            $r->admin_notes = (string) $notes;
        }
        $r->save();

        return responseSuccess('updated', ['Updated successfully']);
    }

    public function screenshot($id)
    {
        $this->ensureSuper();
        $r = InfluencerApplication::query()->findOrFail($id);
        $d = (array) ($r->data ?? []);
        $path = (string) ($d['install_screenshot_path'] ?? '');
        if ($path === '' || !Storage::disk('local')->exists($path)) {
            abort(404);
        }
        return Storage::disk('local')->download($path);
    }

    public function invite(Request $request)
    {
        $this->ensureSuper();
        $platform = trim((string) $request->input('platform', ''));
        $instagram = trim((string) $request->input('instagram_handle', ''));
        $facebook = trim((string) $request->input('facebook_link', ''));
        $youtube = trim((string) $request->input('youtube_link', ''));
        $otherName = trim((string) $request->input('other_platform_name', ''));
        $otherLink = trim((string) $request->input('other_platform_link', ''));

        $leadKey = Str::random(32);
        $query = array_filter([
            'lead_key' => $leadKey,
            'platform' => $platform,
            'instagram_handle' => $instagram,
            'facebook_link' => $facebook,
            'youtube_link' => $youtube,
            'other_platform_name' => $otherName,
            'other_platform_link' => $otherLink,
        ], fn ($v) => $v !== '');

        $url = url('/patner-program.php') . (count($query) ? ('?' . http_build_query($query)) : '');

        return responseSuccess('invite_link', ['Invite link generated'], [
            'lead_key' => $leadKey,
            'url' => $url,
        ]);
    }
}

