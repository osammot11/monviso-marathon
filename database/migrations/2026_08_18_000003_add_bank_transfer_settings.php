<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $settings = [
            'bank_transfer_enabled' => '0',
            'bank_account_holder' => '',
            'bank_iban' => '',
            'bank_bic' => '',
            'bank_name' => '',
            'event_date' => '2027-05-16 09:00:00',
            'bank_transfer_cutoff_days' => '7',
            'bank_transfer_reservation_days' => '5',
        ];

        foreach ($settings as $key => $value) {
            DB::table('settings')->updateOrInsert(['key' => $key], ['value' => $value, 'created_at' => $now, 'updated_at' => $now]);
        }
    }

    public function down(): void
    {
        DB::table('settings')->whereIn('key', [
            'bank_transfer_enabled', 'bank_account_holder', 'bank_iban', 'bank_bic', 'bank_name',
            'event_date', 'bank_transfer_cutoff_days', 'bank_transfer_reservation_days',
        ])->delete();
    }
};
