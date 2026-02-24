<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Deposit;
use App\Constants\Status;
use App\Models\AdminNotification;

class ManualPaymentController extends Controller
{
    public function submitManualPayment(Request $request)
    {
        $request->validate([
            'trx' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'utr' => 'required|string|max:255',
            'screenshot' => 'required|image|mimes:jpeg,png,jpg|max:4096',
        ]);

        $trx = $request->trx;
        $deposit = Deposit::where('trx', $trx)->where('status', Status::PAYMENT_INITIATE)->first();

        if (!$deposit) {
            return responseError('payment_not_found', ['Payment session not found or already processed.']);
        }

        try {
            $imageName = fileUploader($request->screenshot, getFilePath('verify'), getFileSize('verify'));
        } catch (\Exception $e) {
            return responseError('image_upload_error', ['Failed to upload payment screenshot.']);
        }

        $detail = is_array($deposit->detail) ? $deposit->detail : (json_decode($deposit->detail, true) ?? []);
        
        $adminDetail = [];
        // Preserve existing details if any, so nothing is lost
        if (is_array($detail)) {
            foreach ($detail as $k => $v) {
                // If it's already in the structure {"name": "...", "type": "...", "value": "..."}
                if (is_array($v) && isset($v['name']) && isset($v['value'])) {
                    $adminDetail[] = $v;
                } elseif (!is_array($v) && !is_object($v)) {
                    $adminDetail[] = [
                        'name' => str_replace('_', ' ', ucfirst($k)),
                        'type' => 'text',
                        'value' => (string) $v
                    ];
                }
            }
        }
        
        $adminDetail[] = ['name' => 'Amount Paid', 'type' => 'text', 'value' => (string) $request->amount];
        $adminDetail[] = ['name' => 'UTR / Reference No', 'type' => 'text', 'value' => $request->utr];
        $adminDetail[] = ['name' => 'Payment Proof', 'type' => 'file', 'value' => $imageName];

        $deposit->detail = json_encode($adminDetail);
        $deposit->status = Status::PAYMENT_PENDING;
        $deposit->save();

        // Admin Notification
        $adminNotification = new AdminNotification();
        $adminNotification->user_id = $deposit->user_id ?? 0; 
        $adminNotification->title = 'Manual payment deposit request via QR (' . $trx . ')';
        $adminNotification->click_url = urlPath('admin.deposit.details', $deposit->id);
        $adminNotification->save();

        return responseSuccess('payment_submitted', ['Manual payment proof submitted successfully. It will be verified by our team.']);
    }
}
