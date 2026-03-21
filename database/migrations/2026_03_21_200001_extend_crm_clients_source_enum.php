<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE crm_clients MODIFY COLUMN source ENUM('whatsapp','instagram','referral','direct','website') NOT NULL DEFAULT 'whatsapp'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE crm_clients MODIFY COLUMN source ENUM('whatsapp','instagram','referral') NOT NULL DEFAULT 'whatsapp'");
    }
};
