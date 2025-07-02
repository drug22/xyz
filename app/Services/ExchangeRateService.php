<?php

namespace App\Services;

use App\Models\Settings;
use App\Models\Package;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExchangeRateService
{
    public function fetchRatesFromECB()
    {
        try {
            // PREIA CURRENCY-UL DEFAULT DIN SETTINGS
            $baseCurrency = Settings::getDefaultCurrency();

            // Exchange Rate API cu currency-ul corect
            $response = Http::timeout(30)->get("https://api.exchangerate-api.com/v4/latest/{$baseCurrency}");

            if ($response->successful()) {
                $data = $response->json();
                $rates = $data['rates'] ?? [];

                // Store rates in settings
                Settings::set('exchange_rates', $rates);
                Settings::set('exchange_rates_updated_at', now()->toISOString());
                Settings::set('exchange_rates_base_currency', $baseCurrency);

                // ACTUALIZEAZĂ AUTOMAT TOATE PACKAGES CURRENCY PRICES
                $this->updateAllPackageCurrencies();

                Log::info('Exchange rates and package currencies updated successfully');
                return $rates;
            }
        } catch (\Exception $e) {
            Log::error('Failed to fetch exchange rates: ' . $e->getMessage());
        }

        return Settings::getExchangeRates();
    }

    private function updateAllPackageCurrencies()
    {
        $packages = Package::all();
        $updated = 0;

        foreach ($packages as $package) {
            try {
                $package->updateCurrencyPrices();
                $updated++;
            } catch (\Exception $e) {
                Log::error("Failed to update currency prices for package {$package->name}: " . $e->getMessage());
            }
        }

        Log::info("Updated currency prices for {$updated} packages");
    }

    public function getExchangeRate($fromCurrency, $toCurrency)
    {
        if ($fromCurrency === $toCurrency) {
            return 1;
        }

        $rates = Settings::getExchangeRates();
        $baseCurrency = Settings::get('exchange_rates_base_currency', Settings::getDefaultCurrency());

        if ($fromCurrency === $baseCurrency && isset($rates[$toCurrency])) {
            return $rates[$toCurrency];
        }

        // Cross rate calculation if needed
        if (isset($rates[$fromCurrency]) && isset($rates[$toCurrency])) {
            return $rates[$toCurrency] / $rates[$fromCurrency];
        }

        return 1; // Fallback
    }

    public function convertPrice($amount, $fromCurrency, $toCurrency)
    {
        $rate = $this->getExchangeRate($fromCurrency, $toCurrency);
        return round($amount * $rate, 2);
    }

    public function forceUpdatePackageCurrencies()
    {
        $this->updateAllPackageCurrencies();
        return true;
    }
}
