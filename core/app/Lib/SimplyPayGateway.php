<?php

namespace App\Lib;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SimplyPayGateway
{
    public static function appId(): string
    {
        return (string) config('services.simplypay.app_id');
    }

    public static function appSecret(): string
    {
        return (string) config('services.simplypay.app_secret');
    }

    public static function baseUrl(): string
    {
        return rtrim((string) config('services.simplypay.base_url'), '/');
    }

    /**
     * Create Signature based on SimplyPay rules
     */
    public static function createSign($params, $secret): string
    {
        $signStr = self::createSignStr($params, $secret);
        return hash('sha256', $signStr);
    }

    /**
     * Create the base string for signing
     */
    public static function createSignStr($params, $secret): string
    {
        $joined = self::joinMap($params);
        $finalStr = $joined . '&key=' . $secret;
        Log::info('SimplyPay Signature Base String', ['string' => $finalStr]);
        return $finalStr;
    }

    /**
     * Recursively join map for signing
     */
    private static function joinMap($map): string
    {
        if (!is_array($map)) {
            return '';
        }

        // 1. Filter out sign and empty values
        $clean = [];
        foreach ($map as $k => $v) {
            if ($k === 'sign') continue;
            
            // For 'extra', we process it even if it's an array
            if ($k === 'extra' && is_array($v)) {
                $clean[$k] = self::joinMap($v);
                continue;
            }

            // Skip empty values (null or empty string)
            if ($v === null || $v === '') continue;
            
            $clean[$k] = $v;
        }

        // 2. Sort by key (Unicode/Alphabetical)
        ksort($clean);

        // 3. Concatenate key=value
        $pairs = [];
        foreach ($clean as $k => $v) {
            $pairs[] = $k . '=' . $v;
        }

        return implode('&', $pairs);
    }

    /**
     * Create Payment Order (Pay-in)
     */
    public static function createPayment($data)
    {
        $url = self::baseUrl() . '/api/v2/payment/order/create';

        $amount = (string) $data['amount'];
        if (strpos($amount, '.') !== false) {
            $amount = rtrim(rtrim($amount, '0'), '.');
        }

        $params = [
            'appId' => self::appId(),
            'merOrderNo' => (string) $data['merOrderNo'],
            'currency' => $data['currency'] ?? 'INR',
            'amount' => $amount,
            'notifyUrl' => $data['notifyUrl'],
            'returnUrl' => $data['returnUrl'] ?? $data['notifyUrl'],
            'attach' => $data['attach'] ?? 'Payment',
            'extra' => [
                'name' => $data['name'] ?? 'ADS User',
                'email' => $data['email'] ?? 'user@example.com',
                'mobile' => (string) ($data['mobile'] ?? '9999999999'),
            ]
        ];

        $params['sign'] = self::createSign($params, self::appSecret());

        Log::info('SimplyPay Create Payment Request', ['url' => $url, 'params' => $params]);

        $response = Http::withHeaders([
            'Content-Type' => 'application/json;charset=utf-8'
        ])->post($url, $params);

        if ($response->failed()) {
            Log::error('SimplyPay Payment Request Failed', ['status' => $response->status(), 'body' => $response->body()]);
            throw new \Exception('SimplyPay connection failed');
        }

        $resData = $response->json();
        Log::info('SimplyPay Payment Response', $resData);

        if (($resData['code'] ?? -1) !== 0) {
            throw new \Exception('SimplyPay error: ' . ($resData['msg'] ?? 'Unknown error'));
        }

        return [
            'pay_link' => $resData['data']['params']['paymentLink'] ?? null,
            'order_no' => $resData['data']['orderNo'] ?? null,
            'mer_order_no' => $resData['data']['merOrderNo'] ?? null,
        ];
    }



    /**
     * Query Payment Order Status
     */
    public static function queryPayment($merOrderNo)
    {
        $url = self::baseUrl() . '/api/v2/payment/order/query';

        $params = [
            'appId' => self::appId(),
            'merOrderNo' => (string) $merOrderNo,
        ];

        $params['sign'] = self::createSign($params, self::appSecret());

        $response = Http::get($url, $params);
        return $response->json();
    }





    /**
     * Verify Webhook Signature
     */
    public static function verifySign($data): bool
    {
        if (!isset($data['sign'])) return false;
        
        $receivedSign = $data['sign'];
        $calculatedSign = self::createSign($data, self::appSecret());

        return hash_equals($receivedSign, $calculatedSign);
    }
}
