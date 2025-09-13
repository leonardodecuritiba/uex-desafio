<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'pgsql') {
            DB::statement('CREATE INDEX IF NOT EXISTS idx_contacts_lower_name ON contacts (LOWER(name));');
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS idx_contacts_lower_name;');
        }
    }
};

