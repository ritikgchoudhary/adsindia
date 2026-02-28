<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$res = [
    'ad_plans' => DB::table('ad_plans')->get(['id', 'name', 'price']),
    'partner_plans' => DB::table('partner_plans')->get(['id', 'name', 'price']),
    'course_plans' => DB::table('course_plans')->get(['id', 'name', 'price'])
];

print_r($res);
