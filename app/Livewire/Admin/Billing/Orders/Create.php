<?php

namespace App\Livewire\Admin\Billing\Orders;

use App\Models\Order;
use App\Models\Package;
use App\Models\Country;
use App\Models\User;
use App\Services\TaxCalculationService;
use Livewire\Component;
use Flux\Flux;

class Create extends Component
{
    // Customer Type
    public $customer_tab = 'new';

    // User Selection
    public $assigned_to_user_id = '';

    // Package & Billing
    public $package_id = '';
    public $billing_cycle = 'monthly';
    public $status = 'draft'; // Default to draft

    // Customer Info
    public $customer_email = '';
    public $customer_name = '';
    public $customer_country = '';
    public $is_business = false;
    public $customer_vat_number = '';
    public $customer_address = [
        'street' => '',
        'city' => '',
        'postal_code' => '',
        'country' => ''
    ];

    // Company Info (when is_business = true)
    public $company_name = '';
    public $company_registration_number = '';
    public $company_address = '';
    public $company_phone = '';

    // VAT Validation
    public $vatValidated = false;
    public $vatValidationResult = [];

    // Currency
    public $currency = 'GBP';

    // Calculated fields
    public $base_amount = 0;
    public $taxCalculation = [];

    protected $rules = [
        'assigned_to_user_id' => 'nullable|exists:users,id',
        'package_id' => 'required|exists:packages,id',
        'billing_cycle' => 'required|in:monthly,yearly',
        'status' => 'required|in:draft,published',
        'customer_email' => 'required|email',
        'customer_name' => 'required|string|max:255',
        'customer_country' => 'required|exists:countries,code',
        'is_business' => 'boolean',
        'customer_vat_number' => 'required_if:is_business,true|nullable|string|max:50',
        'company_name' => 'required_if:is_business,true|nullable|string|max:255',
        'company_registration_number' => 'nullable|string|max:100',
        'company_address' => 'required_if:is_business,true|nullable|string|max:500',
        'company_phone' => 'nullable|string|max:50',
        'customer_address.street' => 'nullable|string|max:255',
        'customer_address.city' => 'nullable|string|max:100',
        'customer_address.postal_code' => 'nullable|string|max:20',
        'customer_address.country' => 'nullable|string|max:100',
        'currency' => 'required|string|size:3',
    ];

    public function mount()
    {
        $this->currency = \App\Models\Settings::get('default_currency', 'GBP');
    }

    public function updatedAssignedToUserId()
    {
        if ($this->assigned_to_user_id) {
            $user = User::with('company')->find($this->assigned_to_user_id);
            if ($user) {
                $this->customer_name = $user->name;
                $this->customer_email = $user->email;

                if ($user->company) {
                    $this->customer_country = $user->company->country ?? 'GB';
                    $this->is_business = true;

                    // FOLOSEȘTE tax_number din DB (care e VAT number-ul)
                    $this->customer_vat_number = $user->company->tax_number ?? '';  // ← FIX
                    $this->company_name = $user->company->name;
                    $this->company_registration_number = $user->company->registration_number ?? '';

                    $this->company_address = $user->company->address ?? '';
                    $this->company_phone = $user->company->contact_phone ?? '';

                    // Auto-fill personal address from company
                    $this->customer_address = [
                        'street' => $user->company->address ?? '',
                        'city' => $user->company->city ?? '',
                        'postal_code' => $user->company->postal_code ?? '',
                        'country' => $user->company->country ?? '',
                    ];
                } else {
                    $this->is_business = false;
                }
            }
        } else {
            // Reset all when no user selected
            $this->reset([
                'customer_name', 'customer_email', 'customer_country', 'is_business',
                'customer_vat_number', 'company_name', 'company_registration_number',
                'company_address', 'company_phone', 'customer_address'
            ]);
        }
        $this->calculateTax();
    }

    public function updatedPackageId()
    {
        $this->calculatePrice();
    }

    public function updatedBillingCycle()
    {
        $this->calculatePrice();
    }

    public function updatedCustomerCountry()
    {
        $this->customer_address['country'] = $this->customer_country;
        $this->calculateTax();
    }

    public function updatedCustomerVatNumber()
    {
        // Reset VAT validation când se schimbă VAT number-ul
        $this->vatValidated = false;
        $this->vatValidationResult = [];
        $this->calculateTax();
    }

    public function updatedIsBusiness()
    {
        if (!$this->is_business) {
            // Reset business fields când nu mai e business
            $this->customer_vat_number = '';
            $this->company_name = '';
            $this->company_registration_number = '';
            $this->company_address = '';
            $this->company_phone = '';
            $this->vatValidated = false;
            $this->vatValidationResult = [];
        }

        $this->calculateTax();

        // FORȚEAZĂ RE-RENDER pentru ca @if($is_business) să funcționeze instant
        $this->dispatch('$refresh');
    }

    private function calculatePrice()
    {
        if (!$this->package_id) {
            $this->base_amount = 0;
            return;
        }

        $package = Package::find($this->package_id);
        if (!$package) {
            $this->base_amount = 0;
            return;
        }

        $this->base_amount = $this->billing_cycle === 'monthly'
            ? $package->monthly_price
            : $package->yearly_price;

        $this->calculateTax();
    }

    private function calculateTax()
    {
        if (!$this->base_amount || !$this->customer_country) {
            $this->taxCalculation = [];
            return;
        }

        $taxService = app(TaxCalculationService::class);
        $businessCountry = \App\Models\Settings::get('company_country', 'GB');

        $this->taxCalculation = $taxService->calculateTax(
            $businessCountry,
            $this->customer_country,
            $this->base_amount,
            $this->is_business,
            $this->customer_vat_number
        );
    }

    public function validateVat()
    {
        if (!$this->is_business || !$this->customer_vat_number || !$this->customer_country) {
            Flux::toast(
                heading: 'Validation Error',
                text: 'Business type, country and VAT number are required.',
                variant: 'warning'
            );
            return;
        }

        try {
            $viesService = app(\App\Services\ViesValidationService::class);
            $result = $viesService->validateVatNumber($this->customer_country, $this->customer_vat_number);

            if ($result['valid']) {
                // SALVEAZĂ rezultatul pentru a-l folosi la store()
                $this->vatValidated = true;
                $this->vatValidationResult = $result;

                $companyName = $result['name'] ?? 'company';

                // Auto-fill company name if not set
                if (empty($this->company_name) && !empty($result['name'])) {
                    $this->company_name = $result['name'];
                }

                $this->calculateTax();

                Flux::toast(
                    heading: 'VAT Valid',
                    text: "Valid VAT number for {$companyName}",
                    variant: 'success'
                );
            } else {
                $this->vatValidated = false;
                $this->vatValidationResult = $result;

                $errorMessage = $result['error'] ?? 'Invalid VAT number';
                Flux::toast(
                    heading: 'VAT Invalid',
                    text: $errorMessage,
                    variant: 'danger'
                );
            }
        } catch (\Exception $e) {
            // FALLBACK când VIES e unavailable
            $this->vatValidated = true;  // Presupunem că e valid
            $this->vatValidationResult = [
                'valid' => true,
                'name' => 'Company (VIES unavailable)',
                'address' => '',
                'error' => 'VIES service temporarily unavailable: ' . $e->getMessage()
            ];

            Flux::toast(
                heading: 'VAT Validation Warning',
                text: 'VIES service unavailable. VAT marked as valid by default.',
                variant: 'warning'
            );
        }
    }

    public function store()
    {
        $this->validate();

        if (empty($this->taxCalculation)) {
            $this->calculateTax();
        }

        $order = Order::create([
            'order_number' => 'ORD-' . now()->format('Ymd') . '-' . str_pad(Order::count() + 1, 6, '0', STR_PAD_LEFT),
            'package_id' => $this->package_id,
            'billing_cycle' => $this->billing_cycle,
            'customer_email' => $this->customer_email,
            'customer_name' => $this->customer_name,
            'customer_country' => $this->customer_country,
            'is_business' => $this->is_business,
            'customer_vat_number' => $this->customer_vat_number,
            'customer_address' => array_filter($this->customer_address),
            'base_amount' => $this->base_amount,
            'tax_rate' => $this->taxCalculation['tax_rate'] ?? 0,
            'tax_amount' => $this->taxCalculation['tax_amount'] ?? 0,
            'total_amount' => $this->taxCalculation['total_amount'] ?? $this->base_amount,
            'currency' => $this->currency,
            'reverse_vat_applied' => $this->taxCalculation['reverse_vat_applied'] ?? false,
            'tax_note' => $this->taxCalculation['tax_note'] ?? null,
            'status' => $this->status, // Use selected status
            'created_by' => auth()->id(),
            'vat_number_validated' => $this->vatValidated,
            'vat_validation_result' => $this->vatValidationResult,
            'vat_validated_at' => $this->vatValidated ? now() : null,
            'metadata' => [
                'company_name' => $this->company_name,
                'company_registration_number' => $this->company_registration_number,
                'company_address' => $this->company_address,
                'company_phone' => $this->company_phone,
                'assigned_to_user_id' => $this->assigned_to_user_id,
            ]
        ]);

        // Link to user if selected
        if ($this->assigned_to_user_id) {
            $user = User::find($this->assigned_to_user_id);
            if ($user && $user->company) {
                $order->update(['company_id' => $user->company->id]);
            }
        }

        Flux::toast(
            heading: 'Order Created',
            text: "Order #{$order->order_number} has been created with status: {$order->status}.",
            variant: 'success'
        );

        return redirect()->route('admin.billing.orders.show', $order);
    }

    public function render()
    {
        $packages = Package::where('is_active', true)->get();
        $countries = Country::where('is_active', true)->orderBy('name')->get();

        // DOAR USERII CU ROLURILE owner SAU user (nu manager, inspector, super-admin)
        $users = User::with('company')
            ->where('is_active', true)
            ->whereHas('roles', function($query) {
                $query->whereIn('name', ['owner', 'user']);  // DOAR aceste roluri
            })
            ->where(function($query) {
                $query->whereNull('company_id')          // Persoane fizice (fără companie)
                ->orWhere(function($subQuery) {
                    $subQuery->whereNotNull('company_id')  // Users cu companie
                    ->where('is_owner', true);     // dar DOAR owner-ii
                });
            })
            ->orderBy('name')
            ->get();

        $supportedCurrencies = \App\Models\Settings::getSupportedCurrencies();

        return view('livewire.admin.billing.orders.create', [
            'packages' => $packages,
            'countries' => $countries,
            'users' => $users,
            'supportedCurrencies' => $supportedCurrencies,
        ])->layout('components.layouts.admin');
    }

}
