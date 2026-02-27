<?php

namespace App\Lib;

class RupeeRushGateway
{
    public static function merNo(): string
    {
        return (string) (config('services.rupeerush.mer_no') ?: '');
    }

    public static function key(): string
    {
        return (string) (config('services.rupeerush.key') ?: '');
    }

    public static function apiUrl(): string
    {
        return (string) (config('services.rupeerush.api_url') ?: '');
    }

    /**
     * Generate uppercase MD5 sign from JSON string.
     * Rule:
     * 1. Sort parameters alphabetically by key (Ascii order)
     * 2. Remove null/empty keys and the sign itself (handled before calling this)
     * 3. Encode to JSON
     * 4. Append Merchant Key directly to JSON string
     * 5. MD5 hash and uppercase
     */
    public static function generateSign(array $params, string $key): string
    {
        // Recursively remove empty values and 'sign'
        $filtered = array_filter($params, function($value, $key) {
            return $key !== 'sign' && $value !== null && $value !== '';
        }, ARRAY_FILTER_USE_BOTH);

        // Sort keys alphabetically
        ksort($filtered);

        // Encode to JSON string
        $jsonStr = json_encode($filtered, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        
        // Append key and hash
        return strtoupper(md5($jsonStr . $key));
    }

    /**
     * Create connection/payment
     */
    public static function createPayment(array $data): array
    {
        $merNo = static::merNo();
        $key = static::key();
        $requestUrl = static::apiUrl();
        if (!$requestUrl) $requestUrl = 'https://api.rupeerush.cc/payin/create';

        $totalAmount = number_format((float)$data['totalAmount'], 2, '.', '');

        $params = [
            'merNo'        => $merNo,
            'currencyCode' => (string) ($data['currencyCode'] ?? 'INR'),
            'payType'      => (string) ($data['payType'] ?? (config('services.rupeerush.pay_type') ?: 'SCAN')),
            'randomNo'     => (string) sprintf('%014d', mt_rand(1, 99999999999999)),
            'outTradeNo'   => (string) $data['outTradeNo'],
            'totalAmount'  => $totalAmount,
            'notifyUrl'    => (string) $data['notifyUrl'],
            'payCardNo'    => (string) ($data['payCardNo'] ?? '123456'),
            'payBankCode'  => (string) ($data['payBankCode'] ?? 'PAY'),
            'payName'      => (string) ($data['payName'] ?? 'User'),
            'payEmail'     => (string) ($data['payEmail'] ?? 'user@email.com'),
            'payPhone'     => (string) static::formatPhone($data['payPhone'] ?? '+919876543210'),
        ];
        
        if (!empty($data['payViewUrl'])) {
            $params['payViewUrl'] = (string) $data['payViewUrl'];
        }

        // Generate signature
        $params['sign'] = static::generateSign($params, $key);

        \Log::info('RupeeRush Request', ['url' => $requestUrl, 'params' => $params]);

        $jsonPayload = json_encode($params, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $response = CurlRequest::curlPostContent($requestUrl, $jsonPayload, [
            'Content-Type: application/json',
        ]);

        \Log::info('RupeeRush Response', ['response' => $response]);

        $resData = json_decode((string) $response, true);
        if (!is_array($resData)) {
            throw new \RuntimeException('Invalid RupeeRush response.');
        }

        if (($resData['resultCode'] ?? null) !== '0000') {
            $msg = (string) ($resData['stateInfo'] ?? 'Payment API failed');
            throw new \RuntimeException($msg);
        }

        $payURL = (string) ($resData['payURL'] ?? '');
        $payOrderNo = (string) ($resData['payOrderNo'] ?? '');

        if ($payURL === '') {
            throw new \RuntimeException('RupeeRush response missing payURL');
        }

        return [
            'pay_link' => $payURL,
            'order_no' => $payOrderNo,
            'mch_order_no' => $params['outTradeNo'],
            'raw'      => $resData,
        ];
    }

    /**
     * Format phone to ensure it starts with +91 and 10 digit body
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
