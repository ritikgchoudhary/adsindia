<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::first();
auth()->login($user);

$req = new \Illuminate\Http\Request();
$req->merge([
    'gateway' => 'rupeerush'
]);

try {
    $ctrl = new \App\Http\Controllers\Api\UserController();
    $res = $ctrl->kycPayment($req);
    echo json_encode($res->getData(), JSON_PRETTY_PRINT);
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
