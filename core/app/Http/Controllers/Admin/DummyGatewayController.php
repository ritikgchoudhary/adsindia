<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Models\Deposit;
use App\Models\Gateway;
use App\Models\GatewayCurrency;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Http\Request;

class DummyGatewayController extends Controller
{
    /**
     * Create a pending (admin-approvable) deposit using a GET request.
     *
     * This is intended as a "dummy gateway" for offline/testing payments.
     * Admin can then approve it via existing deposit approve endpoint.
     */
    public function createUserDeposit(Request $request)
    {
        $actor = auth('sanctum')->user();
        if (!$actor || !($actor instanceof Admin)) {
            return responseError('unauthorized', ['Unauthorized']);
        }

        $request->validate([
            'user_id'  => 'required|integer|exists:users,id',
            'amount'   => 'required|numeric|gt:0',
            'currency' => 'nullable|string|max:10',
            'note'     => 'nullable|string|max:500',
        ]);

        $user = User::findOrFail((int) $request->user_id);
        $amount = (float) $request->amount;
        $currency = strtoupper(trim((string) ($request->currency ?: (gs()->cur_text ?? 'INR'))));
        $note = trim((string) ($request->note ?? ''));

        // Ensure dummy manual gateway exists (code >= 1000 so it shows as manual/pending)
        [$methodCode, $methodCurrency] = $this->ensureDummyManualGateway($currency);

        $deposit = new Deposit();
        $deposit->user_id         = $user->id;
        $deposit->method_code     = $methodCode;
        $deposit->method_currency = $methodCurrency;
        $deposit->amount          = $amount;
        $deposit->charge          = 0;
        $deposit->rate            = 1;
        $deposit->final_amount    = $amount;
        $deposit->btc_amount      = 0;
        $deposit->btc_wallet      = '';
        $deposit->trx             = getTrx();
        $deposit->status          = Status::PAYMENT_PENDING; // admin-approvable
        $deposit->detail          = (object) [
            'type' => 'dummy_get_gateway',
            'note' => $note,
            'created_by' => 'admin_api',
            'created_at' => now()->format('Y-m-d H:i:s'),
        ];
        $deposit->save();

        // Notify admin panel (optional)
        $adminNotification = new AdminNotification();
        $adminNotification->user_id = $user->id;
        $adminNotification->title = 'Dummy deposit request: ' . showAmount($amount) . ' ' . $methodCurrency . ' from ' . ($user->username ?? ('User#' . $user->id));
        $adminNotification->click_url = urlPath('admin.deposit.details', $deposit->id);
        $adminNotification->save();

        return responseSuccess('dummy_deposit_created', ['Dummy deposit created. Please approve it from Deposits.'], [
            'deposit_id' => $deposit->id,
            'trx' => $deposit->trx,
            'amount' => $deposit->amount,
            'currency' => $deposit->method_currency,
            'status' => $deposit->status,
        ]);
    }

    private function ensureDummyManualGateway(string $currency): array
    {
        $gatewayName = 'Dummy Gateway (GET)';
        $alias = 'dummy_gateway_get';

        $gateway = Gateway::manual()->where('alias', $alias)->first();

        if (!$gateway) {
            $lastCode = (int) (Gateway::manual()->max('code') ?? 999);
            $code = max(1000, $lastCode + 1);

            $gateway = new Gateway();
            $gateway->code = (string) $code;
            $gateway->form_id = 0;
            $gateway->name = $gatewayName;
            $gateway->image = null;
            $gateway->alias = $alias;
            $gateway->status = Status::ENABLE;
            $gateway->gateway_parameters = json_encode([]);
            $gateway->supported_currencies = [];
            $gateway->crypto = Status::DISABLE;
            $gateway->description = 'Admin-only dummy gateway using GET + manual approval.';
            $gateway->save();
        }

        $methodCode = (string) $gateway->code;

        $gc = GatewayCurrency::where('method_code', $methodCode)
            ->where('currency', $currency)
            ->first();

        if (!$gc) {
            $gc = new GatewayCurrency();
            $gc->name = $gatewayName;
            $gc->gateway_alias = $alias;
            $gc->currency = $currency;
            $gc->symbol = $currency === 'INR' ? '₹' : '';
            $gc->method_code = $methodCode;
            $gc->min_amount = 1;
            $gc->max_amount = 999999999;
            $gc->fixed_charge = 0;
            $gc->percent_charge = 0;
            $gc->rate = 1;
            $gc->save();
        }

        return [$methodCode, $currency];
    }
}

