<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        $driver = DB::connection()->getDriverName();
        if (!in_array($driver, ['mysql', 'mariadb'], true)) {
            return;
        }

        $maxId = (int) (DB::table('users')->max('id') ?? 0);
        $next = max(15000, $maxId + 1);

        // Only affects the next inserted row; existing user IDs remain unchanged.
        DB::statement('ALTER TABLE `users` AUTO_INCREMENT = ' . $next);
    }

    public function down(): void
    {
        // Intentionally left blank (no safe automatic rollback for AUTO_INCREMENT changes)
    }
};

