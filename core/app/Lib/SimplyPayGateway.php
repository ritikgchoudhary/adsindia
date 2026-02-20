<?php

namespace App\Lib;

class SimplyPayGateway
{
    public static function appId(): string
    {
        return (string) (env('SIMPLYPAY_APP_ID') ?: '');
    }

    public static function appSecret(): string
    {
        return (string) (env('SIMPLYPAY_APP_SECRET') ?: '');
    }

    public static function apiUrl(): string
    {
        return (string) (env('SIMPLYPAY_API_URL') ?: 'https://api.simplypay.vip/api/v2/payment/order/create');
    }

    /**
     * Generate lowercase SHA256 sign.
     * Excludes sign, and empty values.
     */
    public static function sign(array $params, string $appSecret): string
    {
        unset($params['sign']);

        $filtered = [];
        foreach ($params as $k => $v) {
            // Include everything except 'sign', even empty objects/arrays if they are strings
            if ($v === '' || $v === null) continue;
            $filtered[$k] = $v;
        }

        ksort($filtered);

        $queryString = '';
        foreach ($filtered as $k => $v) {
            if (is_array($v) || is_object($v)) {
                $v = json_encode($v, JSON_UNESCAPED_SLASHES);
            }
            $queryString .= $k . '=' . $v . '&';
        }
        $queryString .= 'key=' . $appSecret;

        return strtolower(hash('sha256', $queryString));
    }

    /**
     * Create a SimplyPay payment and return paymentLink + order numbers.
     *
     * @return array{pay_link:string, order_no:string, mch_order_no:string, raw:array}
     */
    public static function createPayment(array $data): array
    {
        $appId = static::appId();
        $appSecret = static::appSecret();
        $requestUrl = static::apiUrl();

        $params = [
            'appId'      => $appId,
            'merOrderNo' => (string) $data['merOrderNo'],
            'notifyUrl'  => (string) $data['notifyUrl'],
            'returnUrl'  => (string) $data['returnUrl'],
            'currency'   => (string) ($data['currency'] ?? 'INR'),
            'amount'     => number_format((float)$data['amount'], 2, '.', ''),
            'attach'     => (string) ($data['attach'] ?? 'Payment'),
            'extra'      => [
                'name'   => (string) ($data['name'] ?? 'User'),
                'email'  => (string) ($data['email'] ?? 'user@example.com'),
                'mobile' => (string) ($data['mobile'] ?? '0000000000'),
            ],
        ];

        $params['sign'] = static::sign($params, $appSecret);

        $response = CurlRequest::curlPostContent($requestUrl, json_encode($params), [
            'Content-Type: application/json',
        ]);

        $resData = json_decode((string) $response, true);
        if (!is_array($resData)) {
            throw new \RuntimeException('Invalid SimplyPay response: ' . substr((string) $response, 0, 300));
        }

        if (($resData['code'] ?? null) !== 0) {
            $msg = (string) ($resData['msg'] ?? 'Payment API failed');
            \Log::error('SimplyPay API Error: ' . json_encode($resData));
            throw new \RuntimeException('SimplyPay error: ' . $msg);
        }

        $paymentData = $resData['data'] ?? [];
        $paramsData  = $paymentData['params'] ?? [];
        
        $payLink = (string) ($paramsData['paymentLink'] ?? '');
        $orderNo = (string) ($paymentData['orderNo'] ?? '');
        $mchNo   = (string) ($paymentData['merOrderNo'] ?? $data['merOrderNo']);

        if ($payLink === '') {
            throw new \RuntimeException('SimplyPay response missing paymentLink');
        }

        return [
            'pay_link' => $payLink,
            'order_no' => $orderNo,
            'mch_order_no' => $mchNo,
            'raw' => $resData,
        ];
    }

    /**
     * Verify callback signature.
     */
    public static function verifyCallback(array $payload): bool
    {
        $appSecret = static::appSecret();

        $received = (string) ($payload['sign'] ?? '');
        if ($received === '') return false;

        $expected = static::sign($payload, $appSecret);
        return hash_equals($expected, strtolower($received));
    }
}
