<?php

namespace App\Livewire\Admin\Billing\Invoices;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\Package;
use App\Models\Country;
use App\Models\StateCounty;
use App\Models\Settings;
use App\Services\InvoiceService;
use Livewire\Component;
use Flux\Flux;

class Create extends Component
{
    // Invoice basics
    public $type = 'proforma';
    public $order_id = '';
    public $invoice_date = '';
    public $due_date = '';
    public $notes = '';
    public $terms = '';
    public $customer_county_code = '';
    public $customer_county_name = '';
    public $availableCounties = [];
    // Customer info (when not from order)
    public $customer_name = '';
    public $customer_email = '';
    public $customer_country = '';
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
    public $package_id = '';
    public $billing_cycle = 'monthly';
    public $base_amount = 0;
    public $tax_rate = 0;
    public $tax_amount = 0;
    public $total_amount = 0;
    public $currency = 'GBP';

    protected $rules = [
        'type' => 'required|in:proforma,final',
        'order_id' => 'nullable|exists:orders,id',
        'invoice_date' => 'required|date',
        'due_date' => 'required|date|after:invoice_date',
        'customer_name' => 'required|string|max:255',
        'customer_email' => 'required|email',
        'customer_country' => 'required|exists:countries,code',
        'is_business' => 'boolean',
        'customer_vat_number' => 'required_if:is_business,true|nullable|string|max:50',
        'company_name' => 'required_if:is_business,true|nullable|string|max:255',
        'company_registration_number' => 'nullable|string|max:100',
        'company_address' => 'required_if:is_business,true|nullable|string|max:500',
        'customer_county_code' => 'nullable|string|max:10',
        'customer_county_name' => 'nullable|string|max:255',
        'company_phone' => 'nullable|string|max:50',
        'package_id' => 'required_without:order_id|exists:packages,id',
        'billing_cycle' => 'required|in:monthly,yearly',
        'base_amount' => 'required|numeric|min:0',
        'currency' => 'required|string|size:3',
        'notes' => 'nullable|string|max:1000',
        'terms' => 'nullable|string|max:2000',
    ];

    public function mount()
    {
        $this->invoice_date = now()->format('Y-m-d');
        $this->due_date = now()->addDays(15)->format('Y-m-d');
        $this->currency = Settings::get('default_currency', 'GBP');

        // Check for order_id in query params
        if (request()->has('order_id')) {
            $this->order_id = request('order_id');
            $this->loadOrderData();
        }
    }

    public function updatedOrderId()
    {
        if ($this->order_id) {
            $this->loadOrderData();
        } else {
            $this->resetOrderData();
        }
    }

    public function updatedPackageId()
    {
        if ($this->package_id) {
            $this->calculatePackagePrice();
        }
    }

    public function updatedBillingCycle()
    {
        if ($this->package_id) {
            $this->calculatePackagePrice();
        }
    }

    public function updatedCurrency()
    {
        if ($this->package_id && !$this->order_id) { // Doar pentru manual invoices
            $this->calculatePackagePrice();
        }
    }

    public function updatedBaseAmount()
    {
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

    private function loadOrderData()
    {
        $order = Order::with('package')->find($this->order_id);
        if (!$order) return;

        $this->customer_name = $order->customer_name;
        $this->customer_email = $order->customer_email;
        $this->customer_country = $order->customer_country;
        $this->is_business = $order->is_business;
        $this->customer_vat_number = $order->customer_vat_number ?? '';
        $this->customer_address = $order->customer_address ?? [
            'street' => '',
            'city' => '',
            'postal_code' => '',
            'country' => ''
        ];

        if ($order->metadata) {
            $this->company_name = $order->metadata['company_name'] ?? '';
            $this->company_registration_number = $order->metadata['company_registration_number'] ?? '';
            $this->company_address = $order->metadata['company_address'] ?? '';
            $this->company_phone = $order->metadata['company_phone'] ?? '';
        }

        $this->package_id = $order->package_id;
        $this->billing_cycle = $order->billing_cycle;
        $this->base_amount = $order->base_amount;
        $this->tax_rate = $order->tax_rate;
        $this->tax_amount = $order->tax_amount;
        $this->total_amount = $order->total_amount;
        $this->currency = $order->currency;
    }

    private function resetOrderData()
    {
        $this->reset([
            'customer_name', 'customer_email', 'customer_country', 'is_business',
            'customer_vat_number', 'customer_address', 'company_name',
            'company_registration_number', 'company_address', 'company_phone',
            'package_id', 'billing_cycle', 'base_amount', 'tax_rate',
            'tax_amount', 'total_amount'
        ]);
        $this->currency = Settings::get('default_currency', 'GBP');
    }

    private function calculatePackagePrice()
    {
        if (!$this->package_id || !$this->currency) return;

        $package = Package::find($this->package_id);
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

        $this->calculateTax();
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

    public function store()
    {
        $this->validate();

        // If creating from order, use InvoiceService
        if ($this->order_id) {
            $order = Order::findOrFail($this->order_id);
            $invoiceService = app(InvoiceService::class);

            if ($this->type === 'proforma') {
                $invoice = $invoiceService->createProformaFromOrder($order);
            } else {
                $invoice = $invoiceService->createFinalFromOrder($order);
            }

            // Update with custom dates and notes
            $invoice->update([
                'invoice_date' => $this->invoice_date,
                'due_date' => $this->due_date,
                'notes' => $this->notes,
                'terms' => $this->terms,
            ]);
        } else {
            // Create manual invoice
            $invoiceNumber = $this->generateInvoiceNumber();

            $invoice = Invoice::create([
                'invoice_number' => $invoiceNumber,
                'type' => $this->type,
                'status' => 'draft',
                'order_id' => null,
                'created_by' => auth()->id(),
                'customer_name' => $this->customer_name,
                'customer_email' => $this->customer_email,
                'customer_country' => $this->customer_country,
                'customer_address' => array_filter($this->customer_address),
                'is_business' => $this->is_business,
                'customer_vat_number' => $this->customer_vat_number,
                'company_details' => [
                    'company_name' => $this->company_name,
                    'registration_number' => $this->company_registration_number,
                    'address' => $this->company_address,
                    'phone' => $this->company_phone,
                ],
                'base_amount' => $this->base_amount,
                'tax_rate' => $this->tax_rate,
                'tax_amount' => $this->tax_amount,
                'total_amount' => $this->total_amount,
                'currency' => $this->currency,
                'package_details' => $this->getPackageDetails(),
                'invoice_date' => $this->invoice_date,
                'due_date' => $this->due_date,
                'notes' => $this->notes,
                'terms' => $this->terms ?: $this->getDefaultTerms(),
            ]);
        }

        Flux::toast(
            heading: 'Invoice Created',
            text: "Invoice #{$invoice->invoice_number} has been created successfully.",
            variant: 'success'
        );

        return redirect()->route('admin.billing.invoices.show', $invoice);
    }

    private function generateInvoiceNumber(): string
    {
        $stripeSettings = \App\Models\StripeSettings::first();

        if ($this->type === 'proforma') {
            $prefix = $stripeSettings->proforma_prefix ?? 'PRO';
            $nextNumber = $stripeSettings->proforma_next_number ?? 1;

            // Update next number
            $stripeSettings->increment('proforma_next_number');
        } else {
            $prefix = $stripeSettings->invoice_prefix ?? 'INV';
            $nextNumber = $stripeSettings->invoice_next_number ?? 1;

            // Update next number
            $stripeSettings->increment('invoice_next_number');
        }

        $year = now()->year;
        $formattedNumber = str_pad($nextNumber, 6, '0', STR_PAD_LEFT);

        return "{$prefix}-{$year}-{$formattedNumber}";
    }

    private function getPackageDetails(): array
    {
        if ($this->package_id) {
            $package = Package::find($this->package_id);
            return [
                'name' => $package->name,
                'description' => $package->description,
                'billing_cycle' => $this->billing_cycle,
            ];
        }

        return [
            'name' => 'Manual Invoice',
            'description' => 'Manually created invoice',
            'billing_cycle' => $this->billing_cycle,
        ];
    }

    private function getDefaultTerms(): string
    {
        if ($this->type === 'proforma') {
            return 'This is a proforma invoice. Payment is required before service delivery.';
        }

        return 'Payment is due within 30 days of invoice date. Late payments may incur additional charges.';
    }

    public function render()
    {
        $orders = Order::where('status', 'published')
            ->whereDoesntHave('invoices', function($q) {
                $q->where('type', $this->type);
            })
            ->with('package')
            ->latest()
            ->get();

        $packages = Package::where('is_active', true)->get();
        $countries = Country::where('is_active', true)->orderBy('name')->get();
        $supportedCurrencies = Settings::getSupportedCurrencies();

        return view('livewire.admin.billing.invoices.create', [
            'orders' => $orders,
            'packages' => $packages,
            'countries' => $countries,
            'supportedCurrencies' => $supportedCurrencies,
        ])->layout('components.layouts.admin');
    }
}
