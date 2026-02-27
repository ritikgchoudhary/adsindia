<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::first();
auth()->login($user);

$req = new \Illuminate\Http\Request();
$req->merge([
    'plan_id' => 1,
    'gateway' => 'rupeerush'
]);

$ctrl = new \App\Http\Controllers\Api\AdPlanController();
$res = $ctrl->purchase($req);
echo json_encode($res->getData(), JSON_PRETTY_PRINT);
