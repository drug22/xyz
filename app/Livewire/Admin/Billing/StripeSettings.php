<?php

namespace App\Livewire\Admin\Billing;

use App\Models\StripeSettings as StripeSettingsModel;
use App\Models\Settings;
use Livewire\Component;
use Flux\Flux;
use Stripe\StripeClient;

class StripeSettings extends Component
{
    // API Configuration
    public $public_key = '';
    public $secret_key = '';
    public $webhook_secret = '';
    public $mode = 'test';

    // Payment Configuration
    public $auto_capture = true;
    public $supported_payment_methods = ['card'];
    public $application_fee_percent = 0;

    // Tax Configuration
    public $auto_tax_calculation = true;
    public $vies_validation_enabled = true;
    public $tax_exemption_countries = [];

    // Invoice Configuration
    public $invoice_prefix = 'INV';
    public $invoice_next_number = 1;
    public $proforma_prefix = 'PRO';
    public $proforma_next_number = 1;

    // Webhook Configuration
    public $webhook_events = [];

    // Status
    public $is_active = false;

    // UI State
    public $testing = false;
    public $test_results = [];

    protected $rules = [
        'public_key' => 'nullable|string|starts_with:pk_',
        'secret_key' => 'nullable|string|starts_with:sk_',
        'webhook_secret' => 'nullable|string|starts_with:whsec_',
        'mode' => 'required|in:test,live',
        'auto_capture' => 'boolean',
        'supported_payment_methods' => 'array',
        'application_fee_percent' => 'numeric|min:0|max:100',
        'auto_tax_calculation' => 'boolean',
        'vies_validation_enabled' => 'boolean',
        'tax_exemption_countries' => 'array',
        'invoice_prefix' => 'required|string|max:10',
        'invoice_next_number' => 'required|integer|min:1',
        'proforma_prefix' => 'required|string|max:10',
        'proforma_next_number' => 'required|integer|min:1',
        'webhook_events' => 'array',
        'is_active' => 'boolean',
    ];

    public function mount()
    {
        $settings = StripeSettingsModel::current();

        $this->public_key = $settings->public_key ?? '';
        $this->secret_key = $settings->secret_key ?? '';
        $this->webhook_secret = $settings->webhook_secret ?? '';
        $this->mode = $settings->mode ?? 'test';
        $this->auto_capture = $settings->auto_capture ?? true;
        $this->supported_payment_methods = $settings->supported_payment_methods ?? ['card'];
        $this->application_fee_percent = $settings->application_fee_percent ?? 0;
        $this->auto_tax_calculation = $settings->auto_tax_calculation ?? true;
        $this->vies_validation_enabled = $settings->vies_validation_enabled ?? true;
        $this->tax_exemption_countries = $settings->tax_exemption_countries ?? [];
        $this->invoice_prefix = $settings->invoice_prefix ?? 'INV';
        $this->invoice_next_number = $settings->invoice_next_number ?? 1;
        $this->proforma_prefix = $settings->proforma_prefix ?? 'PRO';
        $this->proforma_next_number = $settings->proforma_next_number ?? 1;
        $this->webhook_events = $settings->webhook_events ?? [
            'invoice.payment_succeeded',
            'invoice.payment_failed',
            'customer.subscription.created',
            'customer.subscription.updated',
            'customer.subscription.deleted',
        ];
        $this->is_active = $settings->is_active ?? false;
        $this->test_results = $settings->test_results ?? [];
    }

    public function testConnection()
    {
        $this->testing = true;
        $this->test_results = [];

        try {
            if (empty($this->secret_key)) {
                throw new \Exception('Secret key is required for testing');
            }

            $stripe = new StripeClient($this->secret_key);

            // Test 1: Account retrieval
            $account = $stripe->account->retrieve();
            $this->test_results['account'] = [
                'status' => 'success',
                'message' => "Connected to account: {$account->display_name}",
                'details' => [
                    'country' => $account->country,
                    'currency' => $account->default_currency,
                    'charges_enabled' => $account->charges_enabled,
                ]
            ];

            // Test 2: Create test product
            $product = $stripe->products->create([
                'name' => 'Test Product - ' . now()->format('Y-m-d H:i:s'),
                'metadata' => ['test' => 'true']
            ]);

            $this->test_results['product'] = [
                'status' => 'success',
                'message' => 'Successfully created test product',
                'details' => ['id' => $product->id]
            ];

            // Test 3: Delete test product
            $stripe->products->delete($product->id);
            $this->test_results['cleanup'] = [
                'status' => 'success',
                'message' => 'Test product cleaned up successfully'
            ];

            Flux::toast(
                heading: 'Connection Successful',
                text: 'Stripe connection test passed!',
                variant: 'success'
            );

        } catch (\Exception $e) {
            $this->test_results['error'] = [
                'status' => 'error',
                'message' => $e->getMessage()
            ];

            Flux::toast(
                heading: 'Connection Failed',
                text: $e->getMessage(),
                variant: 'danger'
            );
        }

        $this->testing = false;
    }

    public function save()
    {
        $this->validate();

        $settings = StripeSettingsModel::current();

        $settings->update([
            'public_key' => $this->public_key,
            'secret_key' => $this->secret_key,
            'webhook_secret' => $this->webhook_secret,
            'mode' => $this->mode,
            'auto_capture' => $this->auto_capture,
            'supported_payment_methods' => $this->supported_payment_methods,
            'application_fee_percent' => $this->application_fee_percent,
            'auto_tax_calculation' => $this->auto_tax_calculation,
            'vies_validation_enabled' => $this->vies_validation_enabled,
            'tax_exemption_countries' => $this->tax_exemption_countries,
            'invoice_prefix' => $this->invoice_prefix,
            'invoice_next_number' => $this->invoice_next_number,
            'proforma_prefix' => $this->proforma_prefix,
            'proforma_next_number' => $this->proforma_next_number,
            'webhook_events' => $this->webhook_events,
            'is_active' => $this->is_active,
            'last_tested_at' => now(),
            'test_results' => $this->test_results,
        ]);

        Flux::toast(
            heading: 'Settings Saved',
            text: 'Stripe settings have been successfully updated.',
            variant: 'success'
        );
    }

    public function render()
    {
        $availablePaymentMethods = [
            'card' => 'Credit/Debit Cards',
            'sepa_debit' => 'SEPA Direct Debit',
            'ideal' => 'iDEAL',
            'bancontact' => 'Bancontact',
            'giropay' => 'Giropay',
            'sofort' => 'SOFORT',
        ];

        $availableWebhookEvents = [
            'invoice.payment_succeeded' => 'Invoice Payment Succeeded',
            'invoice.payment_failed' => 'Invoice Payment Failed',
            'customer.subscription.created' => 'Subscription Created',
            'customer.subscription.updated' => 'Subscription Updated',
            'customer.subscription.deleted' => 'Subscription Deleted',
            'payment_intent.succeeded' => 'Payment Intent Succeeded',
            'payment_intent.payment_failed' => 'Payment Intent Failed',
        ];

        return view('livewire.admin.billing.stripe-settings', [
            'availablePaymentMethods' => $availablePaymentMethods,
            'availableWebhookEvents' => $availableWebhookEvents,
        ])->layout('components.layouts.admin');
    }
}
