<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Package;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        $companies = [
            [
                'name' => 'Tech Solutions SRL',
                'description' => 'Software development company',
                'address' => 'Str. Aviatorilor nr. 15',
                'city' => 'București',
                'postal_code' => '011853',
                'country' => 'România',
                'contact_email' => 'contact@techsolutions.ro',
                'contact_phone' => '+40 21 234 5678',
                'website' => 'https://techsolutions.ro',
                'registration_number' => 'RO12345678',
                'tax_number' => '12345678',
                'trade_register' => 'J40/1234/2024',
                'vat_payer' => true,
                'bank_name' => 'Banca Transilvania',
                'bank_iban' => 'RO49BTRLRONCRT0123456789',
                'package_id' => Package::where('name', 'Professional')->first()->id,
                'billing_cycle' => 'yearly',
                'preferred_currency' => 'RON',
                'subscription_started_at' => Carbon::now()->subMonths(3),
                'subscription_expires_at' => Carbon::now()->addMonths(9),
                'last_payment_at' => Carbon::now()->subMonths(3),
                'is_active' => true,
                'is_trial' => false,
            ],
            [
                'name' => 'StartUp Inc',
                'description' => 'Early stage startup',
                'address' => '123 Innovation Street',
                'city' => 'San Francisco',
                'postal_code' => '94105',
                'country' => 'USA',
                'contact_email' => 'hello@startup.com',
                'contact_phone' => '+1 555 123 4567',
                'website' => 'https://startup.com',
                'registration_number' => 'US123456789',
                'vat_payer' => false,
                'package_id' => Package::where('name', 'Starter')->first()->id,
                'billing_cycle' => 'monthly',
                'preferred_currency' => 'USD',
                'subscription_started_at' => Carbon::now()->subWeeks(2),
                'subscription_expires_at' => Carbon::now()->addWeeks(2),
                'is_active' => true,
                'is_trial' => true,
                'trial_ends_at' => Carbon::now()->addWeeks(2),
            ]
        ];

        foreach ($companies as $companyData) {
            Company::create($companyData);
        }
    }
}
