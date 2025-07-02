<?php

namespace App\Livewire\Admin\Companies;

use App\Models\Company;
use App\Models\Package;
use App\Models\Settings;
use Livewire\Component;
use Flux\Flux;

class Edit extends Component
{
    public Company $company;

    // Basic info
    public $name;
    public $description;
    public $address;
    public $city;
    public $postal_code;
    public $country;

    // Contact info
    public $contact_email;
    public $contact_phone;
    public $website;

    // Legal/Billing info
    public $registration_number;
    public $tax_number;
    public $trade_register;
    public $vat_payer;

    // Banking info
    public $bank_name;
    public $bank_account;
    public $bank_iban;
    public $bank_swift;

    // Package & subscription
    public $package_id;
    public $billing_cycle;
    public $preferred_currency;
    public $subscription_expires_at;

    // Status
    public $is_active;
    public $is_trial;
    public $trial_ends_at;

    public function mount(Company $company)
    {
        $this->company = $company;

        // Basic info
        $this->name = $company->name;
        $this->description = $company->description;
        $this->address = $company->address;
        $this->city = $company->city;
        $this->postal_code = $company->postal_code;
        $this->country = $company->country;

        // Contact info
        $this->contact_email = $company->contact_email;
        $this->contact_phone = $company->contact_phone;
        $this->website = $company->website;

        // Legal/Billing info
        $this->registration_number = $company->registration_number;
        $this->tax_number = $company->tax_number;
        $this->trade_register = $company->trade_register;
        $this->vat_payer = $company->vat_payer;

        // Banking info
        $this->bank_name = $company->bank_name;
        $this->bank_account = $company->bank_account;
        $this->bank_iban = $company->bank_iban;
        $this->bank_swift = $company->bank_swift;

        // Package & subscription
        $this->package_id = $company->package_id;
        $this->billing_cycle = $company->billing_cycle;
        $this->preferred_currency = $company->preferred_currency;
        $this->subscription_expires_at = $company->subscription_expires_at?->format('Y-m-d');

        // Status
        $this->is_active = $company->is_active;
        $this->is_trial = $company->is_trial;
        $this->trial_ends_at = $company->trial_ends_at?->format('Y-m-d');
    }

    public function update()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'country' => 'required|string|max:100',
            'contact_email' => 'required|email|max:255|unique:companies,contact_email,' . $this->company->id,
            'contact_phone' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:255',
            'registration_number' => 'nullable|string|max:50',
            'tax_number' => 'nullable|string|max:50',
            'trade_register' => 'nullable|string|max:50',
            'vat_payer' => 'boolean',
            'bank_name' => 'nullable|string|max:255',
            'bank_account' => 'nullable|string|max:50',
            'bank_iban' => 'nullable|string|max:50',
            'bank_swift' => 'nullable|string|max:20',
            'package_id' => 'required|exists:packages,id',
            'billing_cycle' => 'required|in:monthly,yearly',
            'preferred_currency' => 'required|string|size:3',
            'subscription_expires_at' => 'required|date',
            'is_active' => 'boolean',
            'is_trial' => 'boolean',
            'trial_ends_at' => 'nullable|date',
        ]);

        $this->company->update([
            'name' => $this->name,
            'description' => $this->description,
            'address' => $this->address,
            'city' => $this->city,
            'postal_code' => $this->postal_code,
            'country' => $this->country,
            'contact_email' => $this->contact_email,
            'contact_phone' => $this->contact_phone,
            'website' => $this->website,
            'registration_number' => $this->registration_number,
            'tax_number' => $this->tax_number,
            'trade_register' => $this->trade_register,
            'vat_payer' => $this->vat_payer,
            'bank_name' => $this->bank_name,
            'bank_account' => $this->bank_account,
            'bank_iban' => $this->bank_iban,
            'bank_swift' => $this->bank_swift,
            'package_id' => $this->package_id,
            'billing_cycle' => $this->billing_cycle,
            'preferred_currency' => $this->preferred_currency,
            'subscription_expires_at' => $this->subscription_expires_at,
            'is_active' => $this->is_active,
            'is_trial' => $this->is_trial,
            'trial_ends_at' => $this->is_trial ? $this->trial_ends_at : null,
        ]);

        Flux::toast(
            heading: 'Company Updated',
            text: "'{$this->name}' has been successfully updated.",
            variant: 'success'
        );

        return redirect()->route('admin.companies.index');
    }

    public function render()
    {
        $packages = Package::where('is_active', true)->get();
        $supportedCurrencies = Settings::getSupportedCurrencies();

        return view('livewire.admin.companies.edit', [
            'packages' => $packages,
            'supportedCurrencies' => $supportedCurrencies,
        ])->layout('components.layouts.admin');
    }
}
