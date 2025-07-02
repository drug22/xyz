<div>
    <flux:heading size="xl">Create New Invoice</flux:heading>
    <flux:text class="mt-2">Create a proforma or final invoice with automatic tax calculation</flux:text>

    <form wire:submit="store" class="mt-6 space-y-6">
        <!-- Invoice Type & Order Selection -->
        <flux:card>
            <div class="space-y-6">
                <flux:heading size="lg" icon="document-text">Invoice Details</flux:heading>

                <div class="grid gap-4 sm:grid-cols-3">
                    <flux:field>
                        <flux:label>Invoice Type *</flux:label>
                        <flux:select wire:model.live="type">
                            <flux:select.option value="proforma">Proforma Invoice</flux:select.option>
                            <flux:select.option value="final">Final Invoice</flux:select.option>
                        </flux:select>
                        <flux:error name="type" />
                    </flux:field>

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
                </div>

                <flux:field>
                    <flux:label>Create from Existing Order (Optional)</flux:label>
                    <flux:select wire:model.live="order_id" variant="listbox" searchable placeholder="Select an order or create manual invoice">
                        <flux:select.option value="">Manual Invoice (no order)</flux:select.option>
                        @foreach($orders as $order)
                            <flux:select.option value="{{ $order->id }}">
                                Order #{{ $order->order_number }} - {{ $order->customer_name }} - {{ $order->package->name }} ({{ number_format($order->total_amount, 2) }} {{ $order->currency }})
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:description>Select an order to auto-fill all details, or leave empty for manual entry</flux:description>
                    <flux:error name="order_id" />
                </flux:field>

                @if($order_id)
                    <div class="p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
                        <flux:text class="text-green-700 dark:text-green-300">
                            ✅ Invoice details auto-filled from Order #{{ $orders->where('id', $order_id)->first()?->order_number }}
                        </flux:text>
                    </div>
                @endif
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

                    <flux:field>
                        <flux:label class="flex items-center gap-2">
                            <flux:checkbox wire:model.live="is_business" />
                            Business Customer (B2B)
                        </flux:label>
                        <flux:description>Check if this is a business customer for VAT purposes</flux:description>
                        <flux:error name="is_business" />
                    </flux:field>
                </div>

                <!-- Customer Address -->
                <div class="space-y-4">
                    <flux:text class="font-medium">Customer Address</flux:text>

                    <flux:field>
                        <flux:label>Street Address</flux:label>
                        <flux:input wire:model="customer_address.street" placeholder="123 Main Street" />
                        <flux:error name="customer_address.street" />
                    </flux:field>

                    <div class="grid gap-4 sm:grid-cols-3">
                        <flux:field>
                            <flux:label>City</flux:label>
                            <flux:input wire:model="customer_address.city" placeholder="London" />
                            <flux:error name="customer_address.city" />
                        </flux:field>
                        @if($customer_country && in_array($customer_country, ['RO', 'GB']))
                            <flux:field>
                                <flux:label>
                                    County *
                                </flux:label>
                                <flux:select wire:model.live="customer_county_code" variant="listbox" searchable>
                                    <flux:select.option value="">Select county </flux:select.option>
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

                @if(!$order_id)
                    <div class="grid gap-4 sm:grid-cols-3">
                        <flux:field>
                            <flux:label>Package *</flux:label>
                            <flux:select wire:model.live="package_id">
                                <flux:select.option value="">Select package</flux:select.option>
                                @foreach($packages as $package)
                                    <flux:select.option value="{{ $package->id }}">
                                        {{ $package->name }}
                                    </flux:select.option>
                                @endforeach
                            </flux:select>
                            <flux:error name="package_id" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Billing Cycle *</flux:label>
                            <flux:select wire:model.live="billing_cycle">
                                <flux:select.option value="monthly">Monthly</flux:select.option>
                                <flux:select.option value="yearly">Yearly</flux:select.option>
                            </flux:select>
                            <flux:error name="billing_cycle" />
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

                    <flux:field>
                        <flux:label>Base Amount *</flux:label>
                        <flux:input wire:model.live="base_amount" type="number" step="0.01" placeholder="0.00" />
                        <flux:description>This will be auto-calculated when you select a package</flux:description>
                        <flux:error name="base_amount" />
                    </flux:field>
                @else
                    <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                        <flux:text class="text-blue-700 dark:text-blue-300">
                            Package and pricing details are auto-filled from the selected order
                        </flux:text>
                    </div>
                @endif

                <!-- Tax Calculation Preview -->
                @if($base_amount > 0)
                    <div class="p-4 border rounded-lg bg-green-50 dark:bg-green-900/20">
                        <flux:heading size="md" class="mb-4">Tax Calculation Preview</flux:heading>

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
                    <flux:description>Leave empty to use default terms</flux:description>
                    <flux:error name="terms" />
                </flux:field>
            </div>
        </flux:card>

        <!-- Form Actions -->
        <div class="flex justify-between">
            <flux:button :href="route('admin.billing.invoices.index')" wire:navigate variant="ghost">
                Cancel
            </flux:button>

            <flux:button type="submit" variant="primary">
                Create {{ $type === 'proforma' ? 'Proforma' : 'Final' }} Invoice
            </flux:button>
        </div>
    </form>
</div>
