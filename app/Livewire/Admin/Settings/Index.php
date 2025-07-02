<?php

namespace App\Livewire\Admin\Settings;

use App\Models\Settings;
use App\Services\ExchangeRateService;
use Livewire\Component;
use Livewire\WithFileUploads;
use Flux\Flux;

class Index extends Component
{
    use WithFileUploads;

    public $company_name = '';
    public $company_name_original = '';
    public $company_registration_number = '';
    public $company_logo_light = null;  // DUAL LOGO
    public $company_logo_dark = null;   // DUAL LOGO
    public $company_address = '';
    public $company_phone = '';
    public $company_email = '';
    public $bank_name = '';
    public $bank_account_number = '';
    public $bank_iban = '';
    public $bank_swift = '';
    public $default_currency = 'USD';
    public $supported_currencies = [];
    public $exchange_rates = [];

    public function mount()
    {
        $this->company_name = Settings::get('company_name', 'HazWatch');
        $this->company_name_original = Settings::get('company_name_original', 'HazWatch360 LTD');
        $this->company_registration_number = Settings::get('company_registration_number', '');
        $this->company_logo_light = Settings::get('company_logo_light', null);  // DUAL LOGO
        $this->company_logo_dark = Settings::get('company_logo_dark', null);    // DUAL LOGO
        $this->company_address = Settings::get('company_address', '');
        $this->company_phone = Settings::get('company_phone', '');
        $this->company_email = Settings::get('company_email', '');
        $this->bank_name = Settings::get('bank_name', '');
        $this->bank_account_number = Settings::get('bank_account_number', '');
        $this->bank_iban = Settings::get('bank_iban', '');
        $this->bank_swift = Settings::get('bank_swift', '');
        $this->default_currency = Settings::getDefaultCurrency();
        $this->supported_currencies = Settings::getSupportedCurrencies();
        $this->exchange_rates = Settings::getExchangeRates();
    }

    public function updateSettings()
    {
        // VALIDATION RULES DINAMICE
        $rules = [
            'company_name' => 'required|string|max:255',
            'company_name_original' => 'required|string|max:255',
            'company_registration_number' => 'nullable|string|max:255',
            'company_address' => 'nullable|string|max:255',
            'company_phone' => 'nullable|string|max:255',
            'company_email' => 'nullable|email|max:255',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:255',
            'bank_iban' => 'nullable|string|max:255',
            'bank_swift' => 'nullable|string|max:255',
            'default_currency' => 'required|string|size:3',
            'supported_currencies' => 'required|array|min:1',
        ];

        // ADAUGĂ VALIDATION PENTRU LOGO DOAR DACĂ E OBIECT NOU
        if ($this->company_logo_light && is_object($this->company_logo_light)) {
            $rules['company_logo_light'] = 'image|max:2048';
        }

        if ($this->company_logo_dark && is_object($this->company_logo_dark)) {
            $rules['company_logo_dark'] = 'image|max:2048';
        }

        $this->validate($rules);

        // Salvează logo light dacă s-a încărcat unul nou
        if ($this->company_logo_light && is_object($this->company_logo_light)) {
            $logoPath = $this->company_logo_light->store('logos', 'public');
            Settings::set('company_logo_light', $logoPath);
        }

        // Salvează logo dark dacă s-a încărcat unul nou
        if ($this->company_logo_dark && is_object($this->company_logo_dark)) {
            $logoPath = $this->company_logo_dark->store('logos', 'public');
            Settings::set('company_logo_dark', $logoPath);
        }

        Settings::set('company_name', $this->company_name);
        Settings::set('company_name_original', $this->company_name_original);
        Settings::set('company_registration_number', $this->company_registration_number);
        Settings::set('company_address', $this->company_address);
        Settings::set('company_phone', $this->company_phone);
        Settings::set('company_email', $this->company_email);
        Settings::set('bank_name', $this->bank_name);
        Settings::set('bank_account_number', $this->bank_account_number);
        Settings::set('bank_iban', $this->bank_iban);
        Settings::set('bank_swift', $this->bank_swift);
        Settings::set('default_currency', $this->default_currency);
        Settings::set('supported_currencies', $this->supported_currencies);

        Flux::toast(
            heading: 'Settings Updated',
            text: 'Platform settings have been saved successfully.',
            variant: 'success'
        );
    }

    public function fetchExchangeRates()
    {
        $service = new ExchangeRateService();
        $rates = $service->fetchRatesFromECB();
        $this->exchange_rates = $rates;

        Flux::toast(
            heading: 'Exchange Rates Updated',
            text: 'Latest exchange rates have been fetched successfully.',
            variant: 'success'
        );
    }

    public function render()
    {
        return view('livewire.admin.settings.index')->layout('components.layouts.admin');
    }
}
