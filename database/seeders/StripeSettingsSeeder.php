<?php

namespace Database\Seeders;

use App\Models\StripeSettings;
use Illuminate\Database\Seeder;

class StripeSettingsSeeder extends Seeder
{
    public function run(): void
    {
        StripeSettings::create([
            'mode' => 'test',
            'auto_capture' => true,
            'application_fee_percent' => 0,
            'supported_payment_methods' => ['card'],
            'auto_tax_calculation' => true,
            'vies_validation_enabled' => true,
            'tax_exemption_countries' => [],
            'invoice_prefix' => 'INV',
            'invoice_next_number' => 1,
            'proforma_prefix' => 'PRO',
            'proforma_next_number' => 1,
            'webhook_events' => [
                'invoice.payment_succeeded',
                'invoice.payment_failed',
                'customer.subscription.created',
                'customer.subscription.updated',
                'customer.subscription.deleted',
            ],
            'is_active' => false,
        ]);
    }
}
