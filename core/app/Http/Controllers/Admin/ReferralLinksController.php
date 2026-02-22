<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SpecialDiscountLink;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class ReferralLinksController extends Controller
{
    private static function hmacKey(): string
    {
        $key = (string) config('app.key', '');
        if (str_starts_with($key, 'base64:')) {
            $decoded = base64_decode(substr($key, 7), true);
            if ($decoded !== false) {
                return $decoded;
            }
        }
        return $key;
    }

    private static function packageSig(string $refCode, int $pkgId, ?int $discount = null): string
    {
        $payload = strtolower(trim($refCode)) . '|' . (int) $pkgId;
        if ($discount !== null) {
            $payload .= '|' . (int) $discount;
        }
        return hash_hmac('sha256', $payload, static::hmacKey());
    }

    private static function packagesData(): array
    {
        return [
            1 => ['name' => 'AdsLite',     'price' => 1499],
            2 => ['name' => 'AdsPro',      'price' => 2999],
            3 => ['name' => 'AdsSupreme',  'price' => 5999],
            4 => ['name' => 'AdsPremium',  'price' => 9999],
            5 => ['name' => 'AdsPremium+', 'price' => 15999],
        ];
    }

    private static function resolveUserIdFromRef(string $refCode): ?int
    {
        $refCode = trim($refCode);
        if (preg_match('/^ADS(\d+)$/i', $refCode, $m)) {
            return (int) $m[1];
        }
        return null;
    }

    public function generateSpecialLink(Request $request)
    {
        $request->validate([
            // If omitted, we generate a GLOBAL discount link (no sponsor/referral).
            'ref_code' => 'nullable|string',
            'package_id' => 'required|integer|in:1,2,3,4,5',
            'discount' => 'required|integer|min:0',
        ]);

        $refCode = strtoupper(trim((string) ($request->ref_code ?? '')));
        $isGlobal = ($refCode === '');
        $user = null;
        if (!$isGlobal) {
            $userId = static::resolveUserIdFromRef($refCode);
            if (!$userId) {
                return responseError('invalid_ref_code', ['Invalid sponsor ref code. Use format ADS12345']);
            }
            $user = User::find($userId);
            if (!$user) {
                return responseError('user_not_found', ['Sponsor not found']);
            }
        }

        $pkgId = (int) $request->package_id;
        $discount = (int) $request->discount;

        $packages = static::packagesData();
        $pkg = $packages[$pkgId] ?? null;
        if (!$pkg) {
            return responseError('invalid_package', ['Invalid package']);
        }

        $price = (int) ($pkg['price'] ?? 0);
        if ($discount > $price) {
            return responseError('invalid_discount', ['Discount cannot be greater than package price']);
        }

        $sigRef = $isGlobal ? 'GLOBAL' : $refCode;
        if ($discount === 0) {
            $sig = static::packageSig($sigRef, $pkgId, null);
            $link = $isGlobal
                ? URL::to('/register?pkg=' . $pkgId . '&sig=' . $sig)
                : URL::to('/register?ref=' . $refCode . '&pkg=' . $pkgId . '&sig=' . $sig);
        } else {
            $sig = static::packageSig($sigRef, $pkgId, $discount);
            $link = $isGlobal
                ? URL::to('/register?pkg=' . $pkgId . '&disc=' . $discount . '&sig=' . $sig)
                : URL::to('/register?ref=' . $refCode . '&pkg=' . $pkgId . '&disc=' . $discount . '&sig=' . $sig);
        }

        // Persist GLOBAL links so they can be shown in user panel (`/user/referral`)
        // (Wrapped to avoid breaking link generation if migrations are not yet run.)
        if ($isGlobal) {
            try {
                $existing = SpecialDiscountLink::where('package_id', $pkgId)
                    ->where('discount', $discount)
                    ->where('sig', $sig)
                    ->where('is_global', true)
                    ->first();

                if ($existing) {
                    if (!$existing->is_active) {
                        $existing->is_active = true;
                        $existing->updated_at = now();
                        $existing->save();
                    }
                } else {
                    SpecialDiscountLink::create([
                        'package_id' => $pkgId,
                        'discount' => $discount,
                        'sig' => $sig,
                        'is_global' => true,
                        'is_active' => true,
                        'created_by' => auth()->id(),
                    ]);
                }
            } catch (\Throwable $e) {
                // ignore
            }
        }

        return responseSuccess('special_link', ['Special link generated'], [
            'ref_code' => $isGlobal ? null : $refCode,
            'sponsor_user_id' => $isGlobal ? null : $user->id,
            'is_global' => $isGlobal,
            'package_id' => $pkgId,
            'package_name' => (string) ($pkg['name'] ?? 'Package'),
            'original_price' => (int) $price,
            'discount' => (int) $discount,
            'final_price' => (int) ($price - $discount),
            'link' => $link,
        ]);
    }

    public function listSpecialLinks(Request $request)
    {
        $limit = (int) $request->get('limit', 50);
        if ($limit < 1) $limit = 1;
        if ($limit > 200) $limit = 200;

        $packages = static::packagesData();

        try {
            $rows = SpecialDiscountLink::where('is_global', true)
                ->orderByDesc('created_at')
                ->limit($limit)
                ->get();

            $data = $rows->map(function (SpecialDiscountLink $row) use ($packages) {
                $pkgId = (int) $row->package_id;
                $pkg = $packages[$pkgId] ?? null;
                $price = (int) ($pkg['price'] ?? 0);
                $discount = max(0, (int) $row->discount);
                if ($discount > $price) $discount = $price;

                $link = ($discount === 0)
                    ? URL::to('/register?pkg=' . $pkgId . '&sig=' . $row->sig)
                    : URL::to('/register?pkg=' . $pkgId . '&disc=' . $discount . '&sig=' . $row->sig);

                return [
                    'id'               => (int) $row->id,
                    'package_id'       => $pkgId,
                    'package_name'     => (string) ($pkg['name'] ?? ('Plan ' . $pkgId)),
                    'original_price'   => $price,
                    'discount'         => $discount,
                    'final_price'      => $price - $discount,
                    'commission_amount'=> (float) ($row->commission_amount ?? 0),
                    'sig'              => (string) $row->sig,
                    'is_active'        => (bool) $row->is_active,
                    'created_at'       => $row->created_at ? (string) $row->created_at : null,
                ];
            })->values();

            return responseSuccess('special_links', ['Special links retrieved'], [
                'links' => $data,
            ]);
        } catch (\Throwable $e) {
            return responseSuccess('special_links', ['Special links retrieved'], [
                'links' => [],
            ]);
        }
    }

    public function updateSpecialLink(Request $request, $id)
    {
        $request->validate([
            'package_id' => 'sometimes|integer|in:1,2,3,4,5',
            'discount' => 'sometimes|integer|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        $packages = static::packagesData();

        try {
            /** @var SpecialDiscountLink $row */
            $row = SpecialDiscountLink::where('is_global', true)->findOrFail($id);

            $pkgId = (int) ($request->has('package_id') ? $request->package_id : $row->package_id);
            $discount = (int) ($request->has('discount') ? $request->discount : $row->discount);
            $isActive = $request->has('is_active') ? (bool) $request->is_active : (bool) $row->is_active;

            $pkg = $packages[$pkgId] ?? null;
            if (!$pkg) {
                return responseError('invalid_package', ['Invalid package']);
            }

            $price = (int) ($pkg['price'] ?? 0);
            if ($discount > $price) {
                return responseError('invalid_discount', ['Discount cannot be greater than package price']);
            }

            $sigRef = 'GLOBAL';
            $sig = ($discount === 0)
                ? static::packageSig($sigRef, $pkgId, null)
                : static::packageSig($sigRef, $pkgId, $discount);

            $row->package_id = $pkgId;
            $row->discount = $discount;
            $row->sig = $sig;
            $row->is_active = $isActive;
            $row->updated_at = now();
            $row->save();

            $link = ($discount === 0)
                ? URL::to('/register?pkg=' . $pkgId . '&sig=' . $sig)
                : URL::to('/register?pkg=' . $pkgId . '&disc=' . $discount . '&sig=' . $sig);

            return responseSuccess('special_link_updated', ['Special link updated'], [
                'id' => (int) $row->id,
                'package_id' => $pkgId,
                'package_name' => (string) ($pkg['name'] ?? ('Plan ' . $pkgId)),
                'original_price' => $price,
                'discount' => $discount,
                'final_price' => $price - $discount,
                'sig' => $sig,
                'is_active' => $isActive,
                'link' => $link,
            ]);
        } catch (\Throwable $e) {
            return responseError('not_found', ['Link not found']);
        }
    }

    public function deleteSpecialLink(Request $request, $id)
    {
        try {
            /** @var SpecialDiscountLink $row */
            $row = SpecialDiscountLink::where('is_global', true)->findOrFail($id);
            $row->delete();
            return responseSuccess('special_link_deleted', ['Special link deleted'], [
                'id' => (int) $id,
            ]);
        } catch (\Throwable $e) {
            return responseError('not_found', ['Link not found']);
        }
    }

    /**
     * Update commission_amount for a specific special discount link.
     * Used by Master Admin → Commission Management → Special Discount Link Commission section.
     */
    public function updateSpecialLinkCommissionAmount(Request $request, $id)
    {
        $request->validate([
            'commission_amount' => 'required|numeric|min:0',
        ]);

        try {
            /** @var SpecialDiscountLink $row */
            $row = SpecialDiscountLink::where('is_global', true)->findOrFail($id);
            $row->commission_amount = (float) $request->commission_amount;
            $row->updated_at = now();
            $row->save();

            $packages = static::packagesData();
            $pkgId = (int) $row->package_id;
            $pkg = $packages[$pkgId] ?? null;
            $price = (int) ($pkg['price'] ?? 0);
            $discount = max(0, (int) $row->discount);

            return responseSuccess('special_link_commission_updated', ['Commission updated'], [
                'id'               => (int) $row->id,
                'package_name'     => (string) ($pkg['name'] ?? ('Plan ' . $pkgId)),
                'final_price'      => $price - $discount,
                'commission_amount'=> (float) $row->commission_amount,
            ]);
        } catch (\Throwable $e) {
            return responseError('not_found', ['Link not found']);
        }
    }
}

