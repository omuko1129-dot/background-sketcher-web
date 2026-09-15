<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // PostGIS エクステンションを有効化
        DB::statement('CREATE EXTENSION IF NOT EXISTS postgis;');
    }

    public function down(): void
    {
        // 無効化（ロールバック時）
        DB::statement('DROP EXTENSION IF EXISTS postgis;');
    }
};
