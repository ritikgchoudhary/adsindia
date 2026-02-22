<?php

namespace App\Lib;

class RupeeRushGateway
{
    public static function merNo(): string
    {
        return (string) (env('RUPEERUSH_MER_NO') ?: '');
    }

    public static function key(): string
    {
        return (string) (env('RUPEERUSH_KEY') ?: '');
    }

    public static function apiUrl(): string
    {
        return (string) (env('RUPEERUSH_API_URL') ?: '');
    }

    /**
     * Generate uppercase MD5 sign.
     * Excludes sign, and empty values.
     */
    public static function generateSign(array $params, string $key): string
    {
        unset($params['sign']);

        $filtered = [];
        foreach ($params as $k => $v) {
            if ($v === '' || $v === null) continue;
            $filtered[$k] = (string) $v;
        }

        ksort($filtered);

        // Documentation shows: {"key":"val",...}merchantKey
        $jsonString = json_encode($filtered, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $signString = $jsonString . $key;

        return strtoupper(md5($signString));
    }

    /**
     * Create an AdsPay payment and return payURL + order numbers.
     *
     * @return array{pay_link:string, order_no:string, mch_order_no:string, raw:array}
     */
    public static function createPayment(array $data): array
    {
        $merNo = static::merNo();
        $key = static::key();
        $requestUrl = static::apiUrl();
        if (!$requestUrl) $requestUrl = 'https://api.rupeerush.cc/payin/create';

        $params = [
            'merNo'        => $merNo,
            'currencyCode' => (string) ($data['currencyCode'] ?? 'INR'),
            'payType'      => (string) ($data['payType'] ?? (env('RUPEERUSH_PAY_TYPE') ?: 'SCAN')),
            'randomNo'     => (string) sprintf('%014d', mt_rand(1, 99999999999999)),
            'outTradeNo'   => (string) $data['outTradeNo'],
            'totalAmount'  => number_format((float)$data['totalAmount'], 2, '.', ''),
            'notifyUrl'    => (string) $data['notifyUrl'],
            'payCardNo'    => (string) ($data['payCardNo'] ?? '123456'),
            'payBankCode'  => (string) ($data['payBankCode'] ?? (env('RUPEERUSH_BANK_CODE') ?: 'PAY')),
            'payName'      => (string) ($data['payName'] ?? 'User'),
            'payEmail'     => (string) ($data['payEmail'] ?? 'user@email.com'),
            'payPhone'     => (string) static::formatPhone($data['payPhone'] ?? '9876543210'),
        ];

        if (isset($data['payViewUrl'])) {
            $params['payViewUrl'] = $data['payViewUrl'];
        }

        $params['sign'] = static::generateSign($params, $key);

        \Log::info('RupeeRush Request', ['url' => $requestUrl, 'params' => $params]);

        $response = CurlRequest::curlPostContent($requestUrl, json_encode($params), [
            'Content-Type: application/json',
        ]);

        \Log::info('RupeeRush Response', ['response' => $response]);

        $resData = json_decode((string) $response, true);
        if (!is_array($resData)) {
            throw new \RuntimeException('Invalid RupeeRush response: ' . substr((string) $response, 0, 300));
        }

        if (($resData['resultCode'] ?? null) !== '0000') {
            $msg = (string) ($resData['stateInfo'] ?? 'Payment API failed');
            throw new \RuntimeException('RupeeRush error: ' . $msg . ' (Code: ' . ($resData['resultCode'] ?? 'N/A') . ')');
        }

        $payURL = (string) ($resData['payURL'] ?? '');
        $payOrderNo = (string) ($resData['payOrderNo'] ?? '');
        $mchNo = (string) ($resData['outTradeNo'] ?? $data['outTradeNo']);

        if ($payURL === '') {
            throw new \RuntimeException('RupeeRush response missing payURL');
        }

        return [
            'pay_link' => $payURL,
            'order_no' => $payOrderNo,
            'mch_order_no' => $mchNo,
            'raw'      => $resData,
        ];
    }

    /**
     * Format phone to +91XXXXXXXXXX
     */
    public static function formatPhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($phone) > 10) {
            $phone = substr($phone, -10);
        }
        return '+91' . $phone;
    }

    /**
     * Verify callback signature.
     */
    public static function verifyCallback(array $payload): bool
    {
        $key = static::key();

        $received = (string) ($payload['sign'] ?? '');
        if ($received === '') return false;

        $expected = static::generateSign($payload, $key);
        return hash_equals($expected, strtoupper($received));
    }
}
