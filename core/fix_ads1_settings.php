<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$settings = DB::table('agent_commission_settings')->where('user_id', 1)->first();
if (!$settings || !$settings->granular_settings) {
    echo "No settings found for user 1\n";
    exit;
}

$granular = json_decode($settings->granular_settings, true);

// Fix Courses (from 1-5 to 4-8)
if (isset($granular['course'])) {
    $newCourse = [];
    $map = [1 => 4, 2 => 5, 3 => 6, 4 => 7, 5 => 8];
    foreach ($granular['course'] as $oldId => $val) {
        if (isset($map[$oldId])) {
            $newCourse[$map[$oldId]] = $val;
        } else {
            $newCourse[$oldId] = $val;
        }
    }
    // Specifically fix the user's request for AdsPro (ID 5) to be 1500 instead of 8000
    if (isset($newCourse[5])) {
        $newCourse[5]['value'] = 1500;
    }
    $granular['course'] = $newCourse;
}

// Fix Ads Plans (from 1-4 to 4-7)
if (isset($granular['adplan'])) {
    $newAdPlan = [];
    $map = [1 => 4, 2 => 5, 3 => 6, 4 => 7];
    foreach ($granular['adplan'] as $oldId => $val) {
        if (isset($map[$oldId])) {
            $newAdPlan[$map[$oldId]] = $val;
        } else {
            $newAdPlan[$oldId] = $val;
        }
    }
    $granular['adplan'] = $newAdPlan;
}

// Partners are 1-4 which matches DB IDs (verified in PartnerController hardcode), no mapping needed for now.

DB::table('agent_commission_settings')
    ->where('user_id', 1)
    ->update(['granular_settings' => json_encode($granular)]);

echo "ADS1 settings updated successfully.\n";
print_r($granular);
