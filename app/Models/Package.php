<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    protected $fillable = [
        'name', 'description',
        'monthly_price', 'yearly_price',
        'monthly_currency_prices', 'yearly_currency_prices',
        'max_users', 'max_checklists', 'features', 'is_active'
    ];

    protected $casts = [
        'features' => 'array',
        'monthly_currency_prices' => 'array',
        'yearly_currency_prices' => 'array',
        'is_active' => 'boolean',
        'monthly_price' => 'decimal:2',
        'yearly_price' => 'decimal:2',
    ];

    public function getPriceInCurrency($currency, $billing = 'monthly')
    {
        $defaultCurrency = Settings::getDefaultCurrency();
        $basePrice = $billing === 'monthly' ? $this->monthly_price : $this->yearly_price;

        if ($currency === $defaultCurrency) {
            return $basePrice;
        }

        $currencyPrices = $billing === 'monthly'
            ? ($this->monthly_currency_prices ?? [])
            : ($this->yearly_currency_prices ?? []);

        return $currencyPrices[$currency] ?? $basePrice;
    }

    public function updateCurrencyPrices()
    {
        $supportedCurrencies = Settings::getSupportedCurrencies();
        $exchangeRates = Settings::getExchangeRates();
        $defaultCurrency = Settings::getDefaultCurrency(); // DIN SETTINGS!

        $monthlyCurrencyPrices = [];
        $yearlyCurrencyPrices = [];

        foreach ($supportedCurrencies as $currency) {
            if ($currency !== $defaultCurrency) {
                $rate = $exchangeRates[$currency] ?? 1;
                $monthlyCurrencyPrices[$currency] = round($this->monthly_price * $rate, 2);
                $yearlyCurrencyPrices[$currency] = round($this->yearly_price * $rate, 2);
            }
        }

        $this->update([
            'monthly_currency_prices' => $monthlyCurrencyPrices,
            'yearly_currency_prices' => $yearlyCurrencyPrices,
        ]);
    }

    // Relationships
    public function companies()
    {
        return $this->hasMany(Company::class);
    }
}
