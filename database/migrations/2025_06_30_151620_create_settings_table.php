<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->json('value')->nullable();
            $table->timestamps();
        });

        // Seed initial platform settings
        DB::table('settings')->insert([
            ['key' => 'company_name', 'value' => json_encode('HazWatch'), 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'company_name_original', 'value' => json_encode('HazWatch360 LTD'), 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'company_registration_number', 'value' => json_encode(''), 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'company_country', 'value' => json_encode('GB'), 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'company_logo_light', 'value' => json_encode(null), 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'company_logo_dark', 'value' => json_encode(null), 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'company_address', 'value' => json_encode(''), 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'company_phone', 'value' => json_encode(''), 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'company_email', 'value' => json_encode(''), 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'bank_name', 'value' => json_encode(''), 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'bank_account_number', 'value' => json_encode(''), 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'bank_iban', 'value' => json_encode(''), 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'bank_swift', 'value' => json_encode(''), 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'default_currency', 'value' => json_encode('USD'), 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'supported_currencies', 'value' => json_encode(['USD', 'EUR', 'GBP', 'RON']), 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'exchange_rates', 'value' => json_encode([]), 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::drop('settings');
    }
};
