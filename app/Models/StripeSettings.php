<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StripeSettings extends Model
{
    protected $fillable = [
        'public_key', 'secret_key', 'webhook_secret', 'mode',
        'auto_capture', 'supported_payment_methods', 'application_fee_percent',
        'auto_tax_calculation', 'vies_validation_enabled', 'tax_exemption_countries',
        'invoice_prefix', 'invoice_next_number', 'proforma_prefix', 'proforma_next_number',
        'webhook_events', 'webhook_endpoint_id',
        'is_active', 'last_tested_at', 'test_results'
    ];

    protected $casts = [
        'supported_payment_methods' => 'array',
        'tax_exemption_countries' => 'array',
        'webhook_events' => 'array',
        'test_results' => 'array',
        'auto_capture' => 'boolean',
        'auto_tax_calculation' => 'boolean',
        'vies_validation_enabled' => 'boolean',
        'is_active' => 'boolean',
        'last_tested_at' => 'datetime',
        'application_fee_percent' => 'decimal:2',
    ];

    public static function current()
    {
        return static::first() ?: static::create([]);
    }

    public function getNextInvoiceNumber()
    {
        $current = $this->invoice_next_number;
        $this->increment('invoice_next_number');
        return $this->invoice_prefix . str_pad($current, 6, '0', STR_PAD_LEFT);
    }

    public function getNextProformaNumber()
    {
        $current = $this->proforma_next_number;
        $this->increment('proforma_next_number');
        return $this->proforma_prefix . str_pad($current, 6, '0', STR_PAD_LEFT);
    }

    public function isConfigured()
    {
        return !empty($this->public_key) && !empty($this->secret_key);
    }

    public function getBusinessInfo()
    {
        return [
            'name' => Settings::get('company_name'),
            'address' => Settings::get('company_address'),
            'country' => Settings::get('company_country', 'GB'),
            'tax_id' => Settings::get('company_registration_number'),
            'email' => Settings::get('company_email'),
            'phone' => Settings::get('company_phone'),
        ];
    }
}
