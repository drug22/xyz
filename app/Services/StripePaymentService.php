<?php

namespace App\Services;

use Stripe\StripeClient;
use App\Models\StripeSettings;
use App\Models\Settings;

class StripePaymentService
{
    protected $stripe;
    protected $taxService;

    public function __construct(TaxCalculationService $taxService)
    {
        $settings = StripeSettings::current();
        $this->stripe = new StripeClient($settings->secret_key);
        $this->taxService = $taxService;
    }

    public function createPaymentIntent($orderData)
    {
        // Calculează tax-urile cu business country din Settings
        $businessCountry = Settings::get('company_country', 'GB');

        $taxCalculation = $this->taxService->calculateTax(
            $businessCountry,
            $orderData['buyer_country'],
            $orderData['amount'],
            $orderData['is_b2b'] ?? false,
            $orderData['buyer_vat_number'] ?? null
        );

        $paymentIntentData = [
            'amount' => (int) ($taxCalculation['total_amount'] * 100), // Stripe cents
            'currency' => strtolower($orderData['currency'] ?? 'gbp'),
            'metadata' => [
                'order_id' => $orderData['order_id'] ?? null,
                'buyer_country' => $orderData['buyer_country'],
                'tax_rate' => $taxCalculation['tax_rate'],
                'tax_amount' => $taxCalculation['tax_amount'],
                'reverse_vat_applied' => $taxCalculation['reverse_vat_applied'] ? 'true' : 'false',
                'tax_note' => $taxCalculation['tax_note'],
            ]
        ];

        // Adaugă customer dacă există
        if (!empty($orderData['customer_email'])) {
            $customer = $this->findOrCreateCustomer($orderData);
            $paymentIntentData['customer'] = $customer->id;
        }

        $paymentIntent = $this->stripe->paymentIntents->create($paymentIntentData);

        return [
            'payment_intent' => $paymentIntent,
            'tax_calculation' => $taxCalculation,
            'client_secret' => $paymentIntent->client_secret
        ];
    }

    private function findOrCreateCustomer($orderData)
    {
        // Caută customer existent
        $customers = $this->stripe->customers->all([
            'email' => $orderData['customer_email'],
            'limit' => 1
        ]);

        if ($customers->data) {
            return $customers->data[0];
        }

        // Creează customer nou
        return $this->stripe->customers->create([
            'email' => $orderData['customer_email'],
            'name' => $orderData['customer_name'] ?? null,
            'metadata' => [
                'country' => $orderData['buyer_country'],
                'vat_number' => $orderData['buyer_vat_number'] ?? null,
            ]
        ]);
    }
}
