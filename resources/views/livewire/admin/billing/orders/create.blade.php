<div>
    <flux:heading size="xl">Create New Order</flux:heading>
    <flux:text class="mt-2">Create a new order with automatic tax calculation and VAT compliance</flux:text>

    <form wire:submit="store" class="mt-6 space-y-6">
        <!-- Customer Type Tabs -->
        <flux:card>
            <div class="space-y-6">
                <flux:heading size="lg" icon="user-group">Customer Selection</flux:heading>

                <flux:tab.group>
                    <flux:tabs wire:model="customer_tab">
                        <flux:tab name="existing" icon="user">Existing Customer</flux:tab>
                        <flux:tab name="new" icon="user-plus">New Customer</flux:tab>
                    </flux:tabs>

                    <flux:tab.panel name="existing">
                        <div class="space-y-4 mt-4">
                            <flux:field>
                                <flux:label>Select Existing User</flux:label>
                                <flux:select wire:model.live="assigned_to_user_id" variant="listbox" searchable placeholder="Search users...">
                                    <flux:select.option value="">Select a user</flux:select.option>
                                    @foreach($users as $user)
                                        <flux:select.option value="{{ $user->id }}">
                                            {{ $user->name }} ({{ $user->email }})
                                            @if($user->company)
                                                - {{ $user->company->name }} ({{ $user->company->country }})
                                                @if($user->company->tax_number)
                                                    - VAT: {{ $user->company->tax_number }}
                                                @endif
                                            @else
                                                - Individual User
                                            @endif
                                        </flux:select.option>
                                    @endforeach
                                </flux:select>
                                <flux:description>All customer details will be auto-filled from selected user</flux:description>
                                <flux:error name="assigned_to_user_id" />
                            </flux:field>

                            @if($assigned_to_user_id)
                                <div class="p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
                                    <flux:text class="text-green-700 dark:text-green-300">
                                        ✅ Customer details auto-filled from selected user
                                    </flux:text>
                                </div>
                            @endif
                        </div>
                    </flux:tab.panel>

                    <flux:tab.panel name="new">
                        <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg mt-4">
                            <flux:text class="text-blue-700 dark:text-blue-300">
                                📝 Fill in all customer details manually below
                            </flux:text>
                        </div>
                    </flux:tab.panel>
                </flux:tab.group>
            </div>
        </flux:card>

        <!-- Package Selection -->
        <flux:card>
            <div class="space-y-6">
                <flux:heading size="lg" icon="cube">Package & Billing</flux:heading>

                <div class="grid gap-4 sm:grid-cols-4">
                    <flux:field>
                        <flux:label>Package *</flux:label>
                        <flux:select wire:model.live="package_id">
                            <flux:select.option value="">Select package</flux:select.option>
                            @foreach($packages as $package)
                                <flux:select.option value="{{ $package->id }}">
                                    {{ $package->name }} (£{{ number_format($package->monthly_price, 2) }}/mo)
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
                        <flux:label>Currency</flux:label>
                        <flux:select wire:model="currency">
                            @foreach($supportedCurrencies as $curr)
                                <flux:select.option value="{{ $curr }}">{{ $curr }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="currency" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Order Status *</flux:label>
                        <flux:select wire:model.live="status">
                            <flux:select.option value="draft">Draft (Review Required)</flux:select.option>
                            <flux:select.option value="published">Published (Ready for Payment)</flux:select.option>
                        </flux:select>
                        <flux:error name="status" />
                    </flux:field>
                </div>

                @if($base_amount > 0)
                    <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                        <flux:text class="text-blue-700 dark:text-blue-300">
                            <strong>Base Price:</strong> {{ number_format($base_amount, 2) }} {{ $currency }}
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
                    <flux:text class="font-medium">Customer Address (Optional)</flux:text>

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

                        <flux:field>
                            <flux:label>Postal Code</flux:label>
                            <flux:input wire:model="customer_address.postal_code" placeholder="SW1A 1AA" />
                            <flux:error name="customer_address.postal_code" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Country</flux:label>
                            <flux:input wire:model="customer_address.country" placeholder="United Kingdom" />
                            <flux:error name="customer_address.country" />
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
                        <div class="flex gap-2">
                            <flux:input wire:model.live="customer_vat_number" placeholder="GB123456789" class="flex-1" />
                            <flux:button wire:click="validateVat" variant="outline" size="sm" icon="shield-check">
                                Validate VAT
                            </flux:button>
                        </div>
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

        <!-- Tax Calculation Preview -->
        @if(count($taxCalculation) > 0)
            <flux:card>
                <div class="space-y-4">
                    <flux:heading size="lg" icon="calculator">Tax Calculation Preview</flux:heading>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <flux:text>Base Amount:</flux:text>
                                <flux:text>{{ number_format($taxCalculation['amount'], 2) }} {{ $currency }}</flux:text>
                            </div>

                            <div class="flex justify-between">
                                <flux:text>Tax Rate:</flux:text>
                                <flux:text>{{ $taxCalculation['tax_rate'] }}%</flux:text>
                            </div>

                            <div class="flex justify-between">
                                <flux:text>Tax Amount:</flux:text>
                                <flux:text>{{ number_format($taxCalculation['tax_amount'], 2) }} {{ $currency }}</flux:text>
                            </div>

                            <div class="flex justify-between font-bold text-lg border-t pt-2">
                                <flux:text>Total:</flux:text>
                                <flux:text>{{ number_format($taxCalculation['total_amount'], 2) }} {{ $currency }}</flux:text>
                            </div>
                        </div>

                        <div>
                            @if($taxCalculation['reverse_vat_applied'])
                                <flux:badge color="purple" size="lg" class="mb-2">
                                    <flux:icon name="arrow-path" class="h-4 w-4 mr-1" />
                                    Reverse VAT Applied
                                </flux:badge>
                            @endif

                            @if($taxCalculation['tax_note'])
                                <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                                    <flux:text size="sm" class="text-blue-700 dark:text-blue-300">
                                        <strong>Tax Note:</strong> {{ $taxCalculation['tax_note'] }}
                                    </flux:text>
                                </div>
                            @endif

                            @if($is_business && $customer_vat_number)
                                <div class="mt-2">
                                    <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">
                                        VAT Number: {{ $customer_vat_number }}
                                    </flux:text>
                                </div>
                            @endif

                            @if($is_business && $company_name)
                                <div class="mt-2">
                                    <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">
                                        Company: {{ $company_name }}
                                    </flux:text>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </flux:card>
        @endif

        <!-- Form Actions -->
        <div class="flex justify-between">
            <flux:button :href="route('admin.billing.orders.index')" wire:navigate variant="danger">
                Cancel
            </flux:button>

            <div class="flex gap-2">
                @if($status === 'draft')
                    <flux:button type="submit" variant="primary" icon="document-text" color="yellow">
                        Save as Draft
                    </flux:button>
                @endif

                @if($status === 'published')
                    <flux:button type="submit" variant="primary" icon="paper-airplane" color="green">
                        Create & Publish Order
                    </flux:button>
                @endif
            </div>
        </div>
    </form>
</div>
