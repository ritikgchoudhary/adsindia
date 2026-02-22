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
     * Generate uppercase MD5 sign.
     * Excludes sign, and empty values.
     */
    /**
     * Generate uppercase MD5 sign from JSON string.
     */
    public static function generateSign(string $jsonString, string $key): string
    {
        return strtoupper(md5($jsonString . $key));
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
            'payType'      => (string) ($data['payType'] ?? (config('services.rupeerush.pay_type') ?: 'SCAN')),
            'randomNo'     => (string) sprintf('%014d', mt_rand(1, 99999999999999)),
            'outTradeNo'   => (string) $data['outTradeNo'],
            'totalAmount'  => number_format((float)$data['totalAmount'], 2, '.', ''),
            'notifyUrl'    => (string) $data['notifyUrl'],
            'payCardNo'    => (string) ($data['payCardNo'] ?? '123456'),
            'payBankCode'  => (string) ($data['payBankCode'] ?? (config('services.rupeerush.bank_code') ?: 'PAY')),
            'payName'      => (string) ($data['payName'] ?? 'User'),
            'payEmail'     => (string) ($data['payEmail'] ?? 'user@email.com'),
            'payPhone'     => (string) static::formatPhone($data['payPhone'] ?? '9876543210'),
        ];

        // Sort keys alphabetically as required for sign consistency
        ksort($params);

        // Encode to JSON string once with specific flags
        $jsonBody = json_encode($params, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        
        // Generate sign from that exact string
        $params['sign'] = static::generateSign($jsonBody, $key);
        
        // Re-encode with sign included
        $finalJson = json_encode($params, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        \Log::info('RupeeRush Request', ['url' => $requestUrl, 'params' => $params]);

        $response = CurlRequest::curlPostContent($requestUrl, $finalJson, [
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
        return $phone; // Return 10 digit number without +
    }

    /**
     * Verify callback signature.
     */
    /**
     * Verify callback signature.
     */
    public static function verifyCallback(array $payload): bool
    {
        $key = static::key();

        $received = (string) ($payload['sign'] ?? '');
        if ($received === '') return false;

        $temp = $payload;
        unset($temp['sign']);
        ksort($temp);
        
        $jsonString = json_encode($temp, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $expected = strtoupper(md5($jsonString . $key));
        
        return hash_equals($expected, strtoupper($received));
    }
}
