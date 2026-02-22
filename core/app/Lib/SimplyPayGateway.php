<?php

namespace App\Lib;

class SimplyPayGateway
{
    public static function appId(): string
    {
        return (string) (config('services.simplypay.app_id') ?: '');
    }

    public static function appSecret(): string
    {
        return (string) (config('services.simplypay.app_secret') ?: '');
    }

    public static function apiUrl(): string
    {
        return (string) (config('services.simplypay.api_url') ?: 'https://api.simplypay.vip/api/v2/payment/order/create');
    }

    /**
     * Generate lowercase SHA256 sign.
     * Excludes sign, and empty values.
     */
    public static function sign(array $params, string $appSecret): string
    {
        unset($params['sign']);
        ksort($params);

        $parts = [];
        foreach ($params as $k => $v) {
            if ($v === '' || $v === null) continue;
            
            if (is_array($v) || is_object($v)) {
                if ($k === 'extra') {
                    // For 'extra' field (request), serialize as sorted query string WITHOUT trailing &
                    $sub = (array) $v;
                    ksort($sub);
                    $subParts = [];
                    foreach ($sub as $sk => $sv) {
                        $subParts[] = $sk . '=' . $sv;
                    }
                    $v = implode('&', $subParts);
                } else {
                    // For others (like 'params' in response), serialize as JSON with unescaped slashes
                    $v = json_encode($v, JSON_UNESCAPED_SLASHES);
                }
            }
            $parts[] = $k . '=' . $v;
        }
        $parts[] = 'key=' . $appSecret;
        $queryString = implode('&', $parts);

        \Log::info('SimplyPay Signature Base String', ['string' => $queryString]);

        return strtolower(hash('sha256', $queryString));
    }

    /**
     * Create a SimplyPay payment and return paymentLink + order numbers.
     *
     * @return array{pay_link:string, order_no:string, mch_order_no:string, raw:array}
     */
    public static function createPayment(array $data): array
    {
        \Log::info('SimplyPay createPayment data', ['data' => $data]);
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
                'name'   => (string) ($data['name'] ?? ($data['extra']['name'] ?? 'User')),
                'email'  => (string) ($data['email'] ?? ($data['extra']['email'] ?? 'user@example.com')),
                'mobile' => (string) ($data['mobile'] ?? ($data['extra']['mobile'] ?? '0000000000')),
            ],
        ];

        $params['sign'] = static::sign($params, $appSecret);

        \Log::info('SimplyPay Request', ['url' => $requestUrl, 'params' => $params]);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_URL, $requestUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params, JSON_UNESCAPED_SLASHES));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
        ]);
        
        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($error) {
            \Log::error('SimplyPay CURL Error', ['error' => $error]);
            throw new \RuntimeException('Gateway connection error');
        }

        \Log::info('SimplyPay Response', ['code' => $httpCode, 'response' => $response]);

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
        $paymentUrl  = (string) ($paramsData['url'] ?? ($paramsData['paymentLink'] ?? ''));
        $orderNo     = (string) ($paymentData['orderNo'] ?? '');
        $mchNo       = (string) ($paymentData['merOrderNo'] ?? ($data['merOrderNo'] ?? ''));

        if ($paymentUrl === '') {
            throw new \RuntimeException('SimplyPay response missing payment URL');
        }

        return [
            'pay_link' => $paymentUrl,
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
