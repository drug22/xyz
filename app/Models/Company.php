<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'name', 'description', 'address', 'city', 'postal_code', 'country',
        'contact_email', 'contact_phone', 'website',
        'registration_number', 'tax_number', 'trade_register', 'vat_payer',
        'bank_name', 'bank_account', 'bank_iban', 'bank_swift',
        'package_id', 'billing_cycle', 'preferred_currency',
        'subscription_started_at', 'subscription_expires_at', 'last_payment_at',
        'is_active', 'is_trial', 'trial_ends_at', 'metadata'
    ];

    protected $casts = [
        'subscription_started_at' => 'datetime',
        'subscription_expires_at' => 'datetime',
        'last_payment_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'vat_payer' => 'boolean',
        'is_active' => 'boolean',
        'is_trial' => 'boolean',
        'metadata' => 'array',
    ];

    // Relationships
    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    // Helper methods
    public function isSubscriptionActive()
    {
        return $this->is_active &&
            $this->subscription_expires_at &&
            $this->subscription_expires_at->isFuture();
    }

    public function isTrialActive()
    {
        return $this->is_trial &&
            $this->trial_ends_at &&
            $this->trial_ends_at->isFuture();
    }

    public function getFullAddressAttribute()
    {
        return "{$this->address}, {$this->city}, {$this->postal_code}, {$this->country}";
    }

    public function getCurrentPrice()
    {
        if (!$this->package) return 0;

        $basePrice = $this->billing_cycle === 'monthly'
            ? $this->package->monthly_price
            : $this->package->yearly_price;

        // Convert to company's preferred currency
        if ($this->preferred_currency !== Settings::getDefaultCurrency()) {
            $service = new \App\Services\ExchangeRateService();
            return $service->convertPrice(
                $basePrice,
                Settings::getDefaultCurrency(),
                $this->preferred_currency
            );
        }

        return $basePrice;
    }
}
