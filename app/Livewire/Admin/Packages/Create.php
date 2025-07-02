<?php

namespace App\Livewire\Admin\Packages;

use App\Models\Package;
use App\Models\Settings;
use App\Services\ExchangeRateService;
use Livewire\Component;
use Flux\Flux;

class Create extends Component
{
    public $name = '';
    public $description = '';
    public $monthly_price = '';
    public $yearly_price = '';
    public $max_users = '';
    public $max_checklists = '';
    public $features = [];
    public $is_active = true;

    // Auto-calculated currency prices
    public $monthly_currency_prices = [];
    public $yearly_currency_prices = [];

    public function mount()
    {
        $this->calculateCurrencyPrices();
    }

    public function updatedMonthlyPrice()
    {
        // Auto-calculate yearly price (monthly × 12)
        if ($this->monthly_price && is_numeric($this->monthly_price)) {
            $this->yearly_price = round($this->monthly_price * 12, 2);
        }

        $this->calculateCurrencyPrices();
    }

    public function updatedYearlyPrice()
    {
        $this->calculateCurrencyPrices();
    }

    public function addFeature()
    {
        $this->features[] = '';
    }

    public function removeFeature($index)
    {
        unset($this->features[$index]);
        $this->features = array_values($this->features);
    }

    public function calculateCurrencyPrices()
    {
        if (!$this->monthly_price || !$this->yearly_price || !is_numeric($this->monthly_price) || !is_numeric($this->yearly_price)) {
            $this->monthly_currency_prices = [];
            $this->yearly_currency_prices = [];
            return;
        }

        $service = new ExchangeRateService();
        $supportedCurrencies = Settings::getSupportedCurrencies();
        $defaultCurrency = Settings::getDefaultCurrency();

        $this->monthly_currency_prices = [];
        $this->yearly_currency_prices = [];

        foreach ($supportedCurrencies as $currency) {
            if ($currency !== $defaultCurrency) {
                $this->monthly_currency_prices[$currency] = $service->convertPrice(
                    $this->monthly_price,
                    $defaultCurrency,
                    $currency
                );
                $this->yearly_currency_prices[$currency] = $service->convertPrice(
                    $this->yearly_price,
                    $defaultCurrency,
                    $currency
                );
            }
        }
    }

    public function store()
    {
        $this->validate([
            'name' => 'required|string|max:255|unique:packages,name',
            'description' => 'nullable|string',
            'monthly_price' => 'required|numeric|min:0',
            'yearly_price' => 'required|numeric|min:0',
            'max_users' => 'nullable|integer|min:0',
            'max_checklists' => 'nullable|integer|min:1',
            'features' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $package = Package::create([
            'name' => $this->name,
            'description' => $this->description,
            'monthly_price' => $this->monthly_price,
            'yearly_price' => $this->yearly_price,
            'monthly_currency_prices' => $this->monthly_currency_prices,
            'yearly_currency_prices' => $this->yearly_currency_prices,
            'max_users' => $this->max_users ?: null,
            'max_checklists' => $this->max_checklists ?: null,
            'features' => array_filter($this->features),
            'is_active' => $this->is_active,
        ]);

        Flux::toast(
            heading: 'Package Created',
            text: "'{$this->name}' has been successfully created with multi-currency pricing.",
            variant: 'success'
        );

        return redirect()->route('admin.packages.index');
    }

    public function render()
    {
        $supportedCurrencies = Settings::getSupportedCurrencies();
        $defaultCurrency = Settings::getDefaultCurrency();

        return view('livewire.admin.packages.create', [
            'supportedCurrencies' => $supportedCurrencies,
            'defaultCurrency' => $defaultCurrency,
        ])->layout('components.layouts.admin');
    }
}
