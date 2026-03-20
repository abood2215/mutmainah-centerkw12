<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Encrypt existing plain-text phone and email values in crm_clients.
     * Uses Crypt::encryptString (no serialization) to match the 'encrypted' model cast.
     */
    public function up(): void
    {
        DB::table('crm_clients')->get()->each(function ($client) {
            $updates = [];

            if (!empty($client->phone)) {
                try {
                    Crypt::decryptString($client->phone);
                    // Already correctly encrypted, skip
                } catch (\Exception $e) {
                    $updates['phone'] = Crypt::encryptString($client->phone);
                }
            }

            if (!empty($client->email)) {
                try {
                    Crypt::decryptString($client->email);
                    // Already correctly encrypted, skip
                } catch (\Exception $e) {
                    $updates['email'] = Crypt::encryptString($client->email);
                }
            }

            if (!empty($updates)) {
                DB::table('crm_clients')->where('id', $client->id)->update($updates);
            }
        });
    }

    public function down(): void
    {
        DB::table('crm_clients')->get()->each(function ($client) {
            $updates = [];

            if (!empty($client->phone)) {
                try {
                    $updates['phone'] = Crypt::decryptString($client->phone);
                } catch (\Exception $e) {
                    // Already plain text, skip
                }
            }

            if (!empty($client->email)) {
                try {
                    $updates['email'] = Crypt::decryptString($client->email);
                } catch (\Exception $e) {
                    // Already plain text, skip
                }
            }

            if (!empty($updates)) {
                DB::table('crm_clients')->where('id', $client->id)->update($updates);
            }
        });
    }
};
