<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create an idempotent demo admin for testing admin panel login.
     *
     * Admin login supports both bcrypt and plain-text passwords.
     */
    public function up(): void
    {
        if (!Schema::hasTable('admins')) {
            return;
        }

        $demoEmail = 'demo.admin@a22-com.site';
        $demoUsername = 'demo_admin';
        $demoPassword = 'Demo@12345';

        $exists = DB::table('admins')
            ->where('email', $demoEmail)
            ->orWhere('username', $demoUsername)
            ->exists();

        if ($exists) {
            return;
        }

        $columns = DB::select('SHOW COLUMNS FROM `admins`');
        $now = now();

        $knownDefaults = [
            'name' => 'Demo Admin',
            'username' => $demoUsername,
            'email' => $demoEmail,
            'password' => $demoPassword, // plain text also allowed for admin login in this project
            'created_at' => $now,
            'updated_at' => $now,
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

            $isRequired = (($col->Null ?? 'YES') === 'NO')
                && ($col->Default === null)
                && !str_contains((string) ($col->Extra ?? ''), 'auto_increment');

            if (!$isRequired) {
                continue;
            }

            $type = strtolower((string) ($col->Type ?? ''));
            $insert[$field] = $this->fallbackValueForType($type, $now);
        }

        if (!isset($insert['username'])) {
            $insert['username'] = $demoUsername;
        }
        if (!isset($insert['email'])) {
            $insert['email'] = $demoEmail;
        }
        if (!isset($insert['password'])) {
            $insert['password'] = $demoPassword;
        }

        DB::table('admins')->insert($insert);
    }

    public function down(): void
    {
        if (!Schema::hasTable('admins')) {
            return;
        }

        DB::table('admins')
            ->where('email', 'demo.admin@a22-com.site')
            ->orWhere('username', 'demo_admin')
            ->delete();
    }

    private function fallbackValueForType(string $type, $now)
    {
        if (str_contains($type, 'int') || str_contains($type, 'decimal') || str_contains($type, 'float') || str_contains($type, 'double')) {
            return 0;
        }
        if (str_contains($type, 'timestamp') || str_contains($type, 'datetime')) {
            return $now;
        }
        if (str_contains($type, 'date')) {
            return $now->toDateString();
        }
        return '';
    }
};

