<?php

use App\Constants\Status;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create an idempotent demo user for testing.
     *
     * Note: This project stores user passwords as plain text (see API LoginController).
     */
    public function up(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        $demoEmail = 'demo@a22-com.site';
        $demoUsername = 'demo_user';
        $demoPassword = 'Demo@12345';

        $exists = DB::table('users')
            ->where('email', $demoEmail)
            ->orWhere('username', $demoUsername)
            ->exists();

        if ($exists) {
            return;
        }

        // Fetch table columns so we can satisfy NOT NULL constraints safely.
        // MySQL: SHOW COLUMNS FROM users
        $columns = DB::select('SHOW COLUMNS FROM `users`');

        $now = now();

        $knownDefaults = [
            'email' => $demoEmail,
            'username' => $demoUsername,
            'password' => $demoPassword, // stored as plain text in this project
            'firstname' => 'Demo',
            'lastname' => 'User',
            'mobile' => '9999999999',
            'dial_code' => '91',
            'country_code' => 'IN',
            'state' => 'Demo',
            'address' => '',
            'ref_by' => 0,
            'balance' => 0,
            'new_user_ads_watched' => 0,
            'status' => Status::USER_ACTIVE,
            'ev' => Status::VERIFIED,
            'sv' => Status::VERIFIED,
            'kv' => Status::KYC_VERIFIED,
            'ts' => Status::DISABLE,
            'tv' => Status::ENABLE,
            'created_at' => $now,
            'updated_at' => $now,
            'email_verified_at' => $now,
        ];

        $insert = [];

        foreach ($columns as $col) {
            $field = $col->Field ?? null;
            if (!$field || $field === 'id') {
                continue;
            }

            if (array_key_exists($field, $knownDefaults)) {
                $insert[$field] = $knownDefaults[$field];
                continue;
            }

            // Only fill columns that are required (NOT NULL with no default), otherwise omit.
            $isRequired = (($col->Null ?? 'YES') === 'NO')
                && ($col->Default === null)
                && !str_contains((string) ($col->Extra ?? ''), 'auto_increment');

            if (!$isRequired) {
                continue;
            }

            $type = strtolower((string) ($col->Type ?? ''));
            $insert[$field] = $this->fallbackValueForType($type, $now);
        }

        // Final guard: email/username must be present.
        if (!isset($insert['email'])) {
            $insert['email'] = $demoEmail;
        }
        if (!isset($insert['username'])) {
            $insert['username'] = $demoUsername;
        }
        if (!isset($insert['password'])) {
            $insert['password'] = $demoPassword;
        }

        DB::table('users')->insert($insert);
    }

    public function down(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        DB::table('users')
            ->where('email', 'demo@a22-com.site')
            ->orWhere('username', 'demo_user')
            ->delete();
    }

    private function fallbackValueForType(string $type, $now)
    {
        // Numeric
        if (str_contains($type, 'int') || str_contains($type, 'decimal') || str_contains($type, 'float') || str_contains($type, 'double')) {
            return 0;
        }

        // Dates
        if (str_contains($type, 'timestamp') || str_contains($type, 'datetime')) {
            return $now;
        }
        if (str_contains($type, 'date')) {
            return $now->toDateString();
        }

        // Strings / text / enums
        return '';
    }
};

