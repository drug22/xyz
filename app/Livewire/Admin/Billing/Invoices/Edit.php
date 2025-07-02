<?php

namespace App\Livewire\Admin\Billing\Invoices;

use App\Models\Invoice;
use App\Models\Package;
use App\Models\Country;
use App\Models\StateCounty;
use App\Models\Settings;
use Livewire\Component;
use Flux\Flux;

class Edit extends Component
{
    public Invoice $invoice;

    // Invoice basics
    public $invoice_date = '';
    public $due_date = '';
    public $notes = '';
    public $terms = '';
    public $status = '';

    // Customer info
    public $customer_name = '';
    public $customer_email = '';
    public $customer_country = '';
    public $customer_county_code = '';
    public $customer_county_name = '';
    public $availableCounties = [];
    public $is_business = false;
    public $customer_vat_number = '';
    public $customer_address = [
        'street' => '',
        'city' => '',
        'postal_code' => '',
        'country' => ''
    ];

    // Company info
    public $company_name = '';
    public $company_registration_number = '';
    public $company_address = '';
    public $company_phone = '';

    // Package & pricing
    public $package_name = '';
    public $package_description = '';
    public $billing_cycle = 'monthly';
    public $base_amount = 0;
    public $tax_rate = 0;
    public $tax_amount = 0;
    public $total_amount = 0;
    public $currency = 'GBP';

    protected $rules = [
        'invoice_date' => 'required|date',
        'due_date' => 'required|date|after:invoice_date',
        'customer_name' => 'required|string|max:255',
        'customer_email' => 'required|email',
        'customer_country' => 'required|exists:countries,code',
        'customer_county_code' => 'nullable|string|max:10',
        'customer_county_name' => 'nullable|string|max:255',
        'is_business' => 'boolean',
        'customer_vat_number' => 'required_if:is_business,true|nullable|string|max:50',
        'company_name' => 'required_if:is_business,true|nullable|string|max:255',
        'company_registration_number' => 'nullable|string|max:100',
        'company_address' => 'required_if:is_business,true|nullable|string|max:500',
        'company_phone' => 'nullable|string|max:50',
        'package_name' => 'required|string|max:255',
        'billing_cycle' => 'required|in:monthly,yearly',
        'base_amount' => 'required|numeric|min:0',
        'currency' => 'required|string|size:3',
        'notes' => 'nullable|string|max:1000',
        'terms' => 'nullable|string|max:2000',
        'status' => 'required|in:draft,sent,paid,overdue,cancelled',
    ];

    public function mount(Invoice $invoice)
    {
        if (!$invoice->canEdit() && $invoice->status !== 'draft') {
            Flux::toast(
                heading: 'Cannot Edit',
                text: 'Only draft invoices can be edited.',
                variant: 'warning'
            );
            return redirect()->route('admin.billing.invoices.show', $invoice);
        }

        $this->invoice = $invoice;

        // Load invoice data
        $this->invoice_date = $invoice->invoice_date->format('Y-m-d');
        $this->due_date = $invoice->due_date->format('Y-m-d');
        $this->notes = $invoice->notes ?? '';
        $this->terms = $invoice->terms ?? '';
        $this->status = $invoice->status;

        // Customer info
        $this->customer_name = $invoice->customer_name;
        $this->customer_email = $invoice->customer_email;
        $this->customer_country = $invoice->customer_country;
        $this->customer_county_code = $invoice->customer_county_code ?? '';
        $this->customer_county_name = $invoice->customer_county_name ?? '';
        $this->is_business = $invoice->is_business;
        $this->customer_vat_number = $invoice->customer_vat_number ?? '';
        $this->customer_address = $invoice->customer_address ?? [
            'street' => '',
            'city' => '',
            'postal_code' => '',
            'country' => ''
        ];

        // Company info
        if ($invoice->company_details) {
            $this->company_name = $invoice->company_details['company_name'] ?? '';
            $this->company_registration_number = $invoice->company_details['registration_number'] ?? '';
            $this->company_address = $invoice->company_details['address'] ?? '';
            $this->company_phone = $invoice->company_details['phone'] ?? '';
        }

        // Package & pricing
        $this->package_name = $invoice->package_details['name'] ?? '';
        $this->package_description = $invoice->package_details['description'] ?? '';
        $this->billing_cycle = $invoice->package_details['billing_cycle'] ?? 'monthly';
        $this->base_amount = $invoice->base_amount;
        $this->tax_rate = $invoice->tax_rate;
        $this->tax_amount = $invoice->tax_amount;
        $this->total_amount = $invoice->total_amount;
        $this->currency = $invoice->currency;

        // Load counties for current country
        $this->loadCountiesForCountry();
    }

    public function updatedBaseAmount()
    {
        $this->calculateTax();
    }

    public function updatedCurrency()
    {
        $this->convertFromPackagePrices();
        $this->calculateTax();
    }

    public function updatedIsBusiness()
    {
        if (!$this->is_business) {
            $this->customer_vat_number = '';
            $this->company_name = '';
            $this->company_registration_number = '';
            $this->company_address = '';
            $this->company_phone = '';
        }
        $this->calculateTax();
    }

    public function updatedCustomerCountry()
    {
        $this->loadCountiesForCountry();
        $this->customer_county_code = '';
        $this->customer_county_name = '';
        $this->calculateTax();
    }

    public function updatedCustomerCountyCode()
    {
        if ($this->customer_county_code && $this->availableCounties->count() > 0) {
            $county = $this->availableCounties->where('code', $this->customer_county_code)->first();
            $this->customer_county_name = $county ? $county->name : '';
        }
    }

    public function updatedCustomerVatNumber()
    {
        $this->calculateTax();
    }

    private function loadCountiesForCountry()
    {
        if ($this->customer_country) {
            $this->availableCounties = StateCounty::active()
                ->forCountry($this->customer_country)
                ->ordered()
                ->get();
        } else {
            $this->availableCounties = collect();
        }
    }

    private function convertFromPackagePrices()
    {
        if (!$this->package_name || !$this->currency) return;

        // Caut package-ul după nume
        $package = Package::where('name', $this->package_name)->first();
        if (!$package) return;

        // Packages au deja cast la array în model
        $monthlyPrices = $package->monthly_currency_prices ?? [];
        $yearlyPrices = $package->yearly_currency_prices ?? [];

        if ($this->billing_cycle === 'monthly') {
            if (isset($monthlyPrices[$this->currency])) {
                $this->base_amount = $monthlyPrices[$this->currency];
            } else {
                $this->base_amount = $package->monthly_price;
            }
        } else {
            if (isset($yearlyPrices[$this->currency])) {
                $this->base_amount = $yearlyPrices[$this->currency];
            } else {
                $this->base_amount = $package->yearly_price;
            }
        }
    }

    private function calculateTax()
    {
        if (!$this->base_amount || !$this->customer_country) {
            $this->tax_rate = 0;
            $this->tax_amount = 0;
            $this->total_amount = $this->base_amount;
            return;
        }

        $taxService = app(\App\Services\TaxCalculationService::class);
        $businessCountry = Settings::get('company_country', 'GB');

        $taxCalculation = $taxService->calculateTax(
            $businessCountry,
            $this->customer_country,
            $this->base_amount,
            $this->is_business,
            $this->customer_vat_number
        );

        $this->tax_rate = $taxCalculation['tax_rate'] ?? 0;
        $this->tax_amount = $taxCalculation['tax_amount'] ?? 0;
        $this->total_amount = $taxCalculation['total_amount'] ?? $this->base_amount;
    }

    public function update()
    {
        $this->validate();

        $this->invoice->update([
            'invoice_date' => $this->invoice_date,
            'due_date' => $this->due_date,
            'customer_name' => $this->customer_name,
            'customer_email' => $this->customer_email,
            'customer_country' => $this->customer_country,
            'customer_county_code' => $this->customer_county_code,
            'customer_county_name' => $this->customer_county_name,
            'customer_address' => array_filter($this->customer_address),
            'is_business' => $this->is_business,
            'customer_vat_number' => $this->customer_vat_number,
            'company_details' => [
                'company_name' => $this->company_name,
                'registration_number' => $this->company_registration_number,
                'address' => $this->company_address,
                'phone' => $this->company_phone,
            ],
            'package_details' => [
                'name' => $this->package_name,
                'description' => $this->package_description,
                'billing_cycle' => $this->billing_cycle,
            ],
            'base_amount' => $this->base_amount,
            'tax_rate' => $this->tax_rate,
            'tax_amount' => $this->tax_amount,
            'total_amount' => $this->total_amount,
            'currency' => $this->currency,
            'notes' => $this->notes,
            'terms' => $this->terms,
            'status' => $this->status,
        ]);

        Flux::toast(
            heading: 'Invoice Updated',
            text: "Invoice #{$this->invoice->invoice_number} has been updated successfully.",
            variant: 'success'
        );

        return redirect()->route('admin.billing.invoices.show', $this->invoice);
    }

    public function render()
    {
        $countries = Country::where('is_active', true)->orderBy('name')->get();
        $supportedCurrencies = Settings::getSupportedCurrencies();

        return view('livewire.admin.billing.invoices.edit', [
            'countries' => $countries,
            'supportedCurrencies' => $supportedCurrencies,
        ])->layout('components.layouts.admin');
    }
}
