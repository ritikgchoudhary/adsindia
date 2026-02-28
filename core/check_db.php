<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$res = [
    'course_plans' => DB::table('course_plans')->get(),
    'agent_upgrade_rules' => DB::table('agent_upgrade_commission_rules')->get(),
    'agent_1_settings' => DB::table('agent_commission_settings')->where('user_id', 1)->first(),
    'recent_transactions_ads1' => DB::table('transactions')
        ->where('user_id', 1)
        ->whereIn('remark', ['agent_upgrade_commission', 'agent_course_commission'])
        ->latest()
        ->limit(5)
        ->get()
];

print_r($res);
