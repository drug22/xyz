<div>
    <flux:heading size="xl">Edit Invoice #{{ $invoice->invoice_number }}</flux:heading>
    <flux:text class="mt-2">Update {{ $invoice->isProforma() ? 'proforma' : 'final' }} invoice details</flux:text>

    <form wire:submit="update" class="mt-6 space-y-6">
        <!-- Invoice Header -->
        <flux:card>
            <div class="flex items-center justify-between">
                <div>
                    <flux:heading size="lg">Invoice #{{ $invoice->invoice_number }}</flux:heading>
                    <flux:text class="text-zinc-500 dark:text-zinc-400">{{ $invoice->isProforma() ? 'Proforma' : 'Final' }} invoice created {{ $invoice->created_at->format('M d, Y H:i') }}</flux:text>
                </div>
                <div class="flex gap-2">
                    @if($invoice->isProforma())
                        <flux:badge color="blue" size="lg">Proforma</flux:badge>
                    @else
                        <flux:badge color="green" size="lg">Final</flux:badge>
                    @endif

                    <flux:badge color="gray" size="lg">{{ ucfirst($invoice->status) }}</flux:badge>
                </div>
            </div>
        </flux:card>

        <!-- Invoice Details -->
        <flux:card>
            <div class="space-y-6">
                <flux:heading size="lg" icon="document-text">Invoice Details</flux:heading>

                <div class="grid gap-4 sm:grid-cols-3">
                    <flux:field>
                        <flux:label>Invoice Date *</flux:label>
                        <flux:input wire:model="invoice_date" type="date" />
                        <flux:error name="invoice_date" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Due Date *</flux:label>
                        <flux:input wire:model="due_date" type="date" />
                        <flux:error name="due_date" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Status</flux:label>
                        <flux:select wire:model="status">
                            <flux:select.option value="draft">Draft</flux:select.option>
                            <flux:select.option value="sent">Sent</flux:select.option>
                            <flux:select.option value="paid">Paid</flux:select.option>
                            <flux:select.option value="overdue">Overdue</flux:select.option>
                            <flux:select.option value="cancelled">Cancelled</flux:select.option>
                        </flux:select>
                        <flux:error name="status" />
                    </flux:field>
                </div>
            </div>
        </flux:card>

        <!-- Customer Information -->
        <flux:card>
            <div class="space-y-6">
                <flux:heading size="lg" icon="user">Customer Information</flux:heading>

                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:field>
                        <flux:label>Customer Name *</flux:label>
                        <flux:input wire:model="customer_name" placeholder="John Doe" />
                        <flux:error name="customer_name" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Customer Email *</flux:label>
                        <flux:input wire:model="customer_email" type="email" placeholder="john@example.com" />
                        <flux:error name="customer_email" />
                    </flux:field>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:field>
                        <flux:label>Country *</flux:label>
                        <flux:select wire:model.live="customer_country" variant="listbox" searchable placeholder="Search countries...">
                            <flux:select.option value="">Select country</flux:select.option>
                            @foreach($countries as $country)
                                <flux:select.option value="{{ $country->code }}">
                                    {{ $country->name }} ({{ $country->code }})
                                    @if($country->vat_rate > 0)
                                        - VAT {{ $country->vat_rate }}%
                                    @endif
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="customer_country" />
                    </flux:field>

                    <!-- COUNTY/JUDEȚ FIELD -->
                    @if($customer_country && in_array($customer_country, ['RO', 'GB']))
                        <flux:field>
                            <flux:label>
                                {{ $customer_country === 'RO' ? 'Județ' : 'County' }}
                            </flux:label>
                            <flux:select wire:model.live="customer_county_code" variant="listbox" searchable>
                                <flux:select.option value="">Select {{ $customer_country === 'RO' ? 'județ' : 'county' }}</flux:select.option>
                                @foreach($availableCounties as $county)
                                    <flux:select.option value="{{ $county->code }}">
                                        {{ $county->name }} ({{ $county->code }})
                                    </flux:select.option>
                                @endforeach
                            </flux:select>
                            <flux:error name="customer_county_code" />
                        </flux:field>
                    @else
                        <flux:field>
                            <flux:label>State/Province/County</flux:label>
                            <flux:input wire:model="customer_county_name" placeholder="Enter state, province or county" />
                            <flux:error name="customer_county_name" />
                        </flux:field>
                    @endif
                </div>

                <flux:field>
                    <flux:label class="flex items-center gap-2">
                        <flux:checkbox wire:model.live="is_business" />
                        Business Customer (B2B)
                    </flux:label>
                    <flux:description>Check if this is a business customer for VAT purposes</flux:description>
                    <flux:error name="is_business" />
                </flux:field>

                <!-- Customer Address -->
                <div class="space-y-4">
                    <flux:text class="font-medium">Customer Address</flux:text>

                    <flux:field>
                        <flux:label>Street Address</flux:label>
                        <flux:input wire:model="customer_address.street" placeholder="123 Main Street" />
                        <flux:error name="customer_address.street" />
                    </flux:field>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <flux:field>
                            <flux:label>City</flux:label>
                            <flux:input wire:model="customer_address.city" placeholder="London" />
                            <flux:error name="customer_address.city" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Postal Code</flux:label>
                            <flux:input wire:model="customer_address.postal_code" placeholder="SW1A 1AA" />
                            <flux:error name="customer_address.postal_code" />
                        </flux:field>
                    </div>
                </div>
            </div>
        </flux:card>

        <!-- Business Information -->
        @if($is_business)
            <flux:card>
                <div class="space-y-6">
                    <flux:heading size="lg" icon="building-office">Business Information</flux:heading>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <flux:field>
                            <flux:label>Company Name *</flux:label>
                            <flux:input wire:model="company_name" placeholder="ABC Company Ltd" />
                            <flux:error name="company_name" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Registration Number</flux:label>
                            <flux:input wire:model="company_registration_number" placeholder="J40/1234/2024" />
                            <flux:error name="company_registration_number" />
                        </flux:field>
                    </div>

                    <flux:field>
                        <flux:label>VAT Number *</flux:label>
                        <flux:input wire:model.live="customer_vat_number" placeholder="GB123456789" />
                        <flux:description>EU VAT number for reverse charge validation</flux:description>
                        <flux:error name="customer_vat_number" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Company Address *</flux:label>
                        <flux:textarea wire:model="company_address" rows="3" placeholder="123 Business Street, Business City, BC1 2CD, United Kingdom" />
                        <flux:error name="company_address" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Company Phone</flux:label>
                        <flux:input wire:model="company_phone" placeholder="+44 20 1234 5678" />
                        <flux:error name="company_phone" />
                    </flux:field>
                </div>
            </flux:card>
        @endif

        <!-- Package & Pricing -->
        <flux:card>
            <div class="space-y-6">
                <flux:heading size="lg" icon="cube">Package & Pricing</flux:heading>

                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:field>
                        <flux:label>Package Name *</flux:label>
                        <flux:input wire:model="package_name" placeholder="Package name" />
                        <flux:error name="package_name" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Billing Cycle *</flux:label>
                        <flux:select wire:model="billing_cycle">
                            <flux:select.option value="monthly">Monthly</flux:select.option>
                            <flux:select.option value="yearly">Yearly</flux:select.option>
                        </flux:select>
                        <flux:error name="billing_cycle" />
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>Package Description</flux:label>
                    <flux:textarea wire:model="package_description" rows="2" placeholder="Package description..." />
                    <flux:error name="package_description" />
                </flux:field>

                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:field>
                        <flux:label>Base Amount *</flux:label>
                        <flux:input wire:model.live="base_amount" type="number" step="0.01" placeholder="0.00" />
                        <flux:error name="base_amount" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Currency *</flux:label>
                        <flux:select wire:model.live="currency">
                            @foreach($supportedCurrencies as $curr)
                                <flux:select.option value="{{ $curr }}">{{ $curr }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="currency" />
                    </flux:field>
                </div>

                <!-- Tax Calculation Preview -->
                @if($base_amount > 0)
                    <div class="p-4 border rounded-lg bg-blue-50 dark:bg-blue-900/20">
                        <flux:heading size="md" class="mb-4">Tax Calculation</flux:heading>

                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <flux:text>Base Amount:</flux:text>
                                <flux:text class="font-semibold">{{ number_format($base_amount, 2) }} {{ $currency }}</flux:text>
                            </div>

                            @if($tax_amount > 0)
                                <div class="flex justify-between">
                                    <flux:text>Tax ({{ $tax_rate }}%):</flux:text>
                                    <flux:text>{{ number_format($tax_amount, 2) }} {{ $currency }}</flux:text>
                                </div>
                            @endif

                            <div class="flex justify-between font-bold text-lg border-t pt-2">
                                <flux:text>Total Amount:</flux:text>
                                <flux:text class="text-green-600">{{ number_format($total_amount, 2) }} {{ $currency }}</flux:text>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </flux:card>

        <!-- Notes & Terms -->
        <flux:card>
            <div class="space-y-6">
                <flux:heading size="lg" icon="document-text">Notes & Terms</flux:heading>

                <flux:field>
                    <flux:label>Notes</flux:label>
                    <flux:textarea wire:model="notes" rows="3" placeholder="Any additional notes for this invoice..." />
                    <flux:error name="notes" />
                </flux:field>

                <flux:field>
                    <flux:label>Terms & Conditions</flux:label>
                    <flux:textarea wire:model="terms" rows="4" placeholder="Payment terms and conditions..." />
                    <flux:error name="terms" />
                </flux:field>
            </div>
        </flux:card>

        <!-- Form Actions -->
        <div class="flex justify-between">
            <flux:button :href="route('admin.billing.invoices.show', $invoice)" wire:navigate variant="ghost">
                Cancel
            </flux:button>

            <flux:button type="submit" variant="primary">
                Update Invoice
            </flux:button>
        </div>
    </form>
</div>
