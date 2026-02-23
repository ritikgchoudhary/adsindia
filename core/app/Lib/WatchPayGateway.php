<?php

namespace App\Lib;

class WatchPayGateway
{
    public static function mchId(): string
    {
        return (string) (config('services.watchpay.mch_id') ?: '100225567');
    }

    /**
     * Payment (web) merchant key
     * Note: The repo already contains keys in /pay/watchpay/*.php; this env override is recommended.
     */
    public static function merchantKey(): string
    {
        return (string) (config('services.watchpay.web_merchant_key') ?: (config('services.watchpay.merchant_key') ?: '49fd706f0a924b679df02131a3df8794'));
    }

    public static function webApiUrl(): string
    {
        return (string) (config('services.watchpay.web_api_url') ?: 'https://api.watchglb.com/pay/web');
    }

    /**
     * Generate lowercase MD5 sign.
     * Excludes sign + sign_type keys, and empty values.
     */
    public static function sign(array $params, string $merchantKey): string
    {
        unset($params['sign'], $params['sign_type'], $params['signType']);

        $filtered = [];
        foreach ($params as $k => $v) {
            if ($v === '' || $v === null) continue;
            $filtered[$k] = $v;
        }

        ksort($filtered);

        $queryString = '';
        foreach ($filtered as $k => $v) {
            $queryString .= $k . '=' . $v . '&';
        }
        $queryString .= 'key=' . $merchantKey;

        return strtolower(md5($queryString));
    }

    /**
     * Create a WatchPay web payment and return payInfo + order numbers.
     *
     * @return array{pay_link:string, order_no:string, mch_order_no:string, raw:array}
     */
    public static function createWebPayment(string $mchOrderNo, float $amount, string $goodsName, string $pageUrl, string $notifyUrl, ?string $payType = null): array
    {
        $mchId = static::mchId();
        $merchantKey = static::merchantKey();
        $requestUrl = static::webApiUrl();

        $params = [
            'version'        => '1.0',
            'mch_id'         => $mchId,
            'notify_url'     => $notifyUrl,
            'page_url'       => $pageUrl,
            'mch_order_no'   => $mchOrderNo,
            'pay_type'       => (string) ($payType ?: (config('services.watchpay.pay_type') ?: '101')),
            'trade_amount'   => number_format($amount, 2, '.', ''),
            'order_date'     => date('Y-m-d H:i:s'),
            'goods_name'     => $goodsName,
            'mch_return_msg' => 'ok',
            'sign_type'      => 'MD5',
        ];

        $params['sign'] = static::sign($params, $merchantKey);

        $response = CurlRequest::curlPostContent($requestUrl, $params, [
            'Content-Type: application/x-www-form-urlencoded',
        ]);

        $data = json_decode((string) $response, true);
        if (!is_array($data)) {
            throw new \RuntimeException('Invalid WatchPay response: ' . substr((string) $response, 0, 300));
        }
        if (($data['respCode'] ?? null) !== 'SUCCESS') {
            $msg = (string) ($data['tradeMsg'] ?? $data['errorMsg'] ?? $data['message'] ?? 'Payment API failed');
            $respCode = (string) ($data['respCode'] ?? 'FAIL');
            throw new \RuntimeException('WatchPay error [' . $respCode . ']: ' . $msg);
        }

        $payLink = (string) ($data['payInfo'] ?? '');
        $orderNo = (string) ($data['orderNo'] ?? '');
        $mchNo   = (string) ($data['mchOrderNo'] ?? $mchOrderNo);

        if ($payLink === '') {
            throw new \RuntimeException('WatchPay response missing payInfo');
        }

        return [
            'pay_link' => $payLink,
            'order_no' => $orderNo,
            'mch_order_no' => $mchNo,
            'raw' => $data,
        ];
    }

    /**
     * Verify callback signature.
     */
    public static function verifyCallback(array $payload): bool
    {
        $merchantKey = static::merchantKey();

        $received = (string) ($payload['sign'] ?? '');
        if ($received === '') return false;

        $expected = static::sign($payload, $merchantKey);
        return hash_equals($expected, strtolower($received));
    }
}

