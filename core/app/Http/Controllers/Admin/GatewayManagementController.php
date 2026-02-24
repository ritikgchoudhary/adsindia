<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gateway;
use App\Rules\FileTypeValidate;
use Illuminate\Http\Request;

class GatewayManagementController extends Controller
{
    public function index()
    {
        $pageTitle = 'Gateway Management';
        $gateways = Gateway::orderBy('id', 'desc')->get();
        return view('admin.gateways.management', compact('pageTitle', 'gateways'));
    }

    public function toggleStatus($id)
    {
        $gateway = Gateway::findOrFail($id);
        $gateway->status = $gateway->status == 1 ? 0 : 1;
        $gateway->save();

        $notify[] = ['success', $gateway->name . ' status updated successfully'];
        return back()->withNotify($notify);
    }

    public function uploadQr(Request $request)
    {
        $request->validate([
            'qr_images.*' => ['nullable', new FileTypeValidate(['jpg', 'jpeg', 'png', 'webp'])]
        ]);

        $gateway = Gateway::where('alias', 'custom_qr')->first();
        if (!$gateway) {
            // Create custom QR gateway if not exists
            $gateway = new Gateway();
            $gateway->name = 'Custom QR System';
            $gateway->alias = 'custom_qr';
            $gateway->status = 1;
            $gateway->code = 999;
            $gateway->gateway_parameters = json_encode([]);
            $gateway->supported_currencies = [];
            $gateway->save();
        }

        $qrImages = $gateway->extra ?? [];

        if ($request->hasFile('qr_images')) {
            foreach ($request->file('qr_images') as $image) {
                try {
                    $filename = fileUploader($image, getFilePath('gateway'));
                    $qrImages[] = $filename;
                } catch (\Exception $exp) {
                    $notify[] = ['error', 'Image could not be uploaded: ' . $exp->getMessage()];
                    return back()->withNotify($notify);
                }
            }
        }

        $gateway->extra = $qrImages;
        $gateway->save();

        $notify[] = ['success', 'QR images uploaded successfully'];
        return back()->withNotify($notify);
    }

    public function removeQr($index)
    {
        $gateway = Gateway::where('alias', 'custom_qr')->firstOrFail();
        $qrImages = $gateway->extra ?? [];

        if (isset($qrImages[$index])) {
            fileManager()->removeFile(getFilePath('gateway') . '/' . $qrImages[$index]);
            unset($qrImages[$index]);
            $qrImages = array_values($qrImages);
            $gateway->extra = $qrImages;
            $gateway->save();
        }

        $notify[] = ['success', 'QR image removed successfully'];
        return back()->withNotify($notify);
    }
}
