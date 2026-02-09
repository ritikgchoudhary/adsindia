<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Constants\Status;
use App\Models\AdminNotification;
use App\Models\Transaction;
use App\Models\Withdrawal;
use App\Models\WithdrawMethod;
use Illuminate\Http\Request;

class WithdrawController extends Controller
{
    public function withdrawMethod()
    {
        $methods = WithdrawMethod::active()->get()->map(function ($method) {
            // Handle image - use placeholder if image doesn't exist
            $imagePath = null;
            if ($method->image) {
                $imageFile = getFilePath('withdrawMethod') . '/' . $method->image;
                if (file_exists($imageFile)) {
                    $imagePath = getImage($imageFile, getFileSize('withdrawMethod'));
                }
            }
            
            // Use placeholder if no image
            if (!$imagePath) {
                // Try to use a default placeholder route
                $imagePath = route('placeholder.image', getFileSize('withdrawMethod'));
            }
            
            return [
                'id' => $method->id,
                'name' => $method->name ?? 'Payment Method',
                'image' => $imagePath,
                'min_limit' => (float)($method->min_limit ?? 0),
                'max_limit' => (float)($method->max_limit ?? 100000),
                'percent_charge' => (float)($method->percent_charge ?? 0),
                'fixed_charge' => (float)($method->fixed_charge ?? 0),
                'currency' => $method->currency ?? 'INR',
                'rate' => (float)($method->rate ?? 1),
            ];
        });

        return response()->json([
            'remark' => 'withdraw_methods',
            'status' => 'success',
            'message' => ['Withdrawal methods retrieved successfully'],
            'data' => $methods
        ]);
    }

    public function withdrawStore(Request $request)
    {
        $request->validate([
            'method_code' => 'required',
            'amount' => 'required|numeric|min:0.01',
        ]);

        $method = WithdrawMethod::where('id', $request->method_code)->active()->firstOrFail();
        $user = auth()->user();

        // Allow withdrawal of total balance
        $maxWithdrawAmount = $user->balance;
        
        if ($request->amount < $method->min_limit) {
            return response()->json([
                'status' => 'error',
                'message' => ['Your requested amount is smaller than minimum amount']
            ], 400);
        }

        if ($request->amount > $maxWithdrawAmount) {
            return response()->json([
                'status' => 'error',
                'message' => ['Insufficient balance for withdrawal']
            ], 400);
        }

        // Calculate 18% withdrawal fee
        $withdrawalFeePercent = 18;
        $withdrawalFee = ($request->amount * $withdrawalFeePercent) / 100;
        
        // Calculate method charges
        $methodCharge = $method->fixed_charge + ($request->amount * $method->percent_charge / 100);
        $totalCharge = $withdrawalFee + $methodCharge;
        $afterCharge = $request->amount - $totalCharge;

        if ($afterCharge <= 0) {
            return response()->json([
                'status' => 'error',
                'message' => ['Withdraw amount must be sufficient for charges']
            ], 400);
        }

        $finalAmount = $afterCharge * $method->rate;

        // Create withdrawal record with status PAYMENT_INITIATE (waiting for 18% fee payment)
        $withdraw = new Withdrawal();
        $withdraw->method_id = $method->id;
        $withdraw->user_id = $user->id;
        $withdraw->amount = $request->amount;
        $withdraw->currency = $method->currency;
        $withdraw->rate = $method->rate;
        $withdraw->charge = $totalCharge;
        $withdraw->final_amount = $finalAmount;
        $withdraw->after_charge = $afterCharge;
        $withdraw->trx = getTrx();
        $withdraw->status = Status::PAYMENT_INITIATE; // Waiting for 18% fee payment
        $withdraw->save();

        return response()->json([
            'status' => 'success',
            'message' => ['Withdrawal request created. Please pay the 18% withdrawal fee to proceed.'],
            'data' => [
                'withdraw_id' => $withdraw->id,
                'trx' => $withdraw->trx,
                'amount' => $request->amount,
                'withdrawal_fee' => $withdrawalFee,
                'total_charge' => $totalCharge,
                'final_amount' => $finalAmount,
            ]
        ]);
    }

    public function payWithdrawalFee(Request $request)
    {
        $request->validate([
            'trx' => 'required|string',
        ]);

        $user = auth()->user();
        $withdraw = Withdrawal::where('trx', $request->trx)
            ->where('user_id', $user->id)
            ->where('status', Status::PAYMENT_INITIATE)
            ->firstOrFail();

        // Calculate 18% withdrawal fee
        $withdrawalFeePercent = 18;
        $withdrawalFee = ($withdraw->amount * $withdrawalFeePercent) / 100;

        // Check if user has enough balance for both fee and withdrawal amount
        $totalRequired = $withdrawalFee + $withdraw->amount;
        if ($user->balance < $totalRequired) {
            return response()->json([
                'status' => 'error',
                'message' => ['Insufficient balance. You need ' . showAmount($totalRequired) . ' (withdrawal amount + 18% fee)']
            ], 400);
        }

        // Deduct 18% fee from user balance
        $user->balance -= $withdrawalFee;
        $user->save();

        // Create transaction record for fee payment
        $transaction = new Transaction();
        $transaction->user_id = $user->id;
        $transaction->amount = $withdrawalFee;
        $transaction->post_balance = $user->balance;
        $transaction->charge = 0;
        $transaction->trx_type = '-';
        $transaction->details = 'Withdrawal fee payment (18%) for TRX: ' . $withdraw->trx;
        $transaction->trx = getTrx();
        $transaction->remark = 'withdraw_fee';
        $transaction->save();

        // Deduct withdrawal amount from balance
        $user->balance -= $withdraw->amount;
        $user->save();

        // Create transaction record for withdrawal
        $withdrawTransaction = new Transaction();
        $withdrawTransaction->user_id = $user->id;
        $withdrawTransaction->amount = $withdraw->amount;
        $withdrawTransaction->post_balance = $user->balance;
        $withdrawTransaction->charge = $withdraw->charge;
        $withdrawTransaction->trx_type = '-';
        $withdrawTransaction->details = 'Withdraw request via ' . $withdraw->method->name;
        $withdrawTransaction->trx = $withdraw->trx;
        $withdrawTransaction->remark = 'withdraw';
        $withdrawTransaction->save();

        // Update withdrawal status to PENDING (fee paid, ready for admin review)
        $withdraw->status = Status::PAYMENT_PENDING;
        $withdraw->save();

        // Create admin notification
        $adminNotification = new AdminNotification();
        $adminNotification->user_id = $user->id;
        $adminNotification->title = 'New withdraw request from ' . $user->username;
        $adminNotification->click_url = urlPath('admin.withdraw.data.details', $withdraw->id);
        $adminNotification->save();

        return response()->json([
            'status' => 'success',
            'message' => ['Withdrawal fee paid successfully. Your withdrawal request has been submitted for review.'],
            'data' => [
                'trx' => $withdraw->trx,
                'withdrawal_fee_paid' => $withdrawalFee,
                'withdrawal_amount' => $withdraw->amount,
            ]
        ]);
    }

    public function withdrawLog()
    {
        $user = auth()->user();
        $withdrawals = Withdrawal::where('user_id', $user->id)
            ->with('method')
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($withdraw) {
                return [
                    'id' => $withdraw->id,
                    'trx' => $withdraw->trx,
                    'method' => $withdraw->method->name ?? 'N/A',
                    'amount' => $withdraw->amount,
                    'charge' => $withdraw->charge,
                    'final_amount' => $withdraw->final_amount,
                    'status' => $withdraw->status,
                    'status_text' => $this->getStatusText($withdraw->status),
                    'created_at' => $withdraw->created_at->format('Y-m-d H:i:s'),
                    'created_at_human' => $withdraw->created_at->diffForHumans(),
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $withdrawals
        ]);
    }

    private function getStatusText($status)
    {
        $statuses = [
            Status::PAYMENT_INITIATE => 'Fee Pending',
            Status::PAYMENT_PENDING => 'Pending Review',
            Status::PAYMENT_SUCCESS => 'Approved',
            Status::PAYMENT_REJECT => 'Rejected',
        ];

        return $statuses[$status] ?? 'Unknown';
    }
}
