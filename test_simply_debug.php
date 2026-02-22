<?php
require 'core/vendor/autoload.php';
$app = require_once 'core/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Lib\SimplyPayGateway;

try {
    $data = [
        'merOrderNo' => 'TEST' . time(),
        'amount' => 100,
        'notifyUrl' => 'https://example.com/notify',
        'returnUrl' => 'https://example.com/return',
        'name' => 'Test User',
        'email' => 'test@example.com',
        'mobile' => '1234567890',
        'attach' => 'Test Payment'
    ];
    
    echo "Initiating SimplyPay Payment...\n";
    $result = SimplyPayGateway::createPayment($data);
    print_r($result);
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
