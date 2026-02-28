<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Lib\AgentCommission;
use Illuminate\Support\Facades\DB;

$agentId = 1;
$type = 'registration';
$baseAmount = 2999.0;
$meta = ['plan_type' => 'package', 'plan_id' => 2]; // AdsPro

$amount = AgentCommission::calculate($agentId, $type, $baseAmount, $meta);

echo "Type: $type, Base: $baseAmount, Meta: " . json_encode($meta) . "\n";
echo "Calculated Amount: $amount\n";

$settings = DB::table('agent_commission_settings')->where('user_id', $agentId)->first();
echo "Granular Settings: " . $settings->granular_settings . "\n";
