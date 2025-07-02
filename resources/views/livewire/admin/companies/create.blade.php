<div>
    <flux:heading size="xl">Create New Company</flux:heading>
    <flux:text class="mt-2">Add a new company with complete billing and subscription information</flux:text>

    <form wire:submit="store" class="mt-6 space-y-6">
        <!-- Company Information -->
        <flux:card>
            <div class="space-y-6">
                <flux:heading size="lg" icon="building-office">Company Information</flux:heading>

                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:field>
                        <flux:label>Company Name *</flux:label>
                        <flux:input wire:model="name" placeholder="e.g. Tech Solutions SRL" />
                        <flux:error name="name" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Country *</flux:label>
                        <flux:input wire:model="country" placeholder="e.g. România, USA, Germany" />
                        <flux:error name="country" />
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>Description</flux:label>
                    <flux:textarea wire:model="description" placeholder="Brief description of the company..." rows="3" />
                    <flux:error name="description" />
                </flux:field>
            </div>
        </flux:card>

        <!-- Address Information -->
        <flux:card>
            <div class="space-y-6">
                <flux:heading size="lg" icon="map-pin">Address Information</flux:heading>

                <flux:field>
                    <flux:label>Street Address *</flux:label>
                    <flux:input wire:model="address" placeholder="e.g. Str. Aviatorilor nr. 15, Sector 1" />
                    <flux:error name="address" />
                </flux:field>

                <div class="grid gap-4 sm:grid-cols-3">
                    <flux:field>
                        <flux:label>City *</flux:label>
                        <flux:input wire:model="city" placeholder="e.g. București" />
                        <flux:error name="city" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Postal Code *</flux:label>
                        <flux:input wire:model="postal_code" placeholder="e.g. 011853" />
                        <flux:error name="postal_code" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Country *</flux:label>
                        <flux:input wire:model="country" placeholder="e.g. România" />
                        <flux:error name="country" />
                    </flux:field>
                </div>
            </div>
        </flux:card>

        <!-- Contact Information -->
        <flux:card>
            <div class="space-y-6">
                <flux:heading size="lg" icon="phone">Contact Information</flux:heading>

                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:field>
                        <flux:label>Contact Email *</flux:label>
                        <flux:input wire:model="contact_email" type="email" placeholder="contact@company.com" icon:leading="envelope" />
                        <flux:error name="contact_email" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Contact Phone</flux:label>
                        <flux:input wire:model="contact_phone" placeholder="+40 21 234 5678" icon:leading="phone" />
                        <flux:error name="contact_phone" />
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>Website</flux:label>
                    <flux:input wire:model="website" type="url" placeholder="https://company.com" icon:leading="globe-alt" />
                    <flux:error name="website" />
                </flux:field>
            </div>
        </flux:card>

        <!-- Legal & Billing Information -->
        <flux:card>
            <div class="space-y-6">
                <flux:heading size="lg" icon="document-text">Legal & Billing Information</flux:heading>

                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:field>
                        <flux:label>Registration Number</flux:label>
                        <flux:input wire:model="registration_number" placeholder="e.g. J40/1234/2024" />
                        <flux:description>Commercial registry number</flux:description>
                        <flux:error name="registration_number" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Tax Number (VAT)</flux:label>
                        <flux:input wire:model="tax_number" placeholder="e.g. RO12345678, GB123456789" />
                        <flux:description>Company VAT number for billing and tax purposes</flux:description>
                        <flux:error name="tax_number" />
                    </flux:field>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:field>
                        <flux:label>Trade Register</flux:label>
                        <flux:input wire:model="trade_register" placeholder="e.g. J40/1234/2024" />
                        <flux:description>Commercial registry number</flux:description>
                        <flux:error name="trade_register" />
                    </flux:field>

                    <flux:field>
                        <flux:label class="flex items-center gap-2">
                            <flux:checkbox wire:model="vat_payer" />
                            VAT Payer
                        </flux:label>
                        <flux:description>Company is registered for VAT</flux:description>
                        <flux:error name="vat_payer" />
                    </flux:field>
                </div>
            </div>
        </flux:card>

        <!-- Banking Information -->
        <flux:card>
            <div class="space-y-6">
                <flux:heading size="lg" icon="credit-card">Banking Information</flux:heading>

                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:field>
                        <flux:label>Bank Name</flux:label>
                        <flux:input wire:model="bank_name" placeholder="e.g. Banca Transilvania" />
                        <flux:error name="bank_name" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Bank Account</flux:label>
                        <flux:input wire:model="bank_account" placeholder="Account number" />
                        <flux:error name="bank_account" />
                    </flux:field>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:field>
                        <flux:label>IBAN</flux:label>
                        <flux:input wire:model="bank_iban" placeholder="e.g. RO49BTRLRONCRT0123456789" />
                        <flux:error name="bank_iban" />
                    </flux:field>

                    <flux:field>
                        <flux:label>SWIFT/BIC</flux:label>
                        <flux:input wire:model="bank_swift" placeholder="e.g. BTRLRO22" />
                        <flux:error name="bank_swift" />
                    </flux:field>
                </div>
            </div>
        </flux:card>

        <!-- Package & Subscription -->
        <flux:card>
            <div class="space-y-6">
                <flux:heading size="lg" icon="cube">Package & Subscription</flux:heading>

                <div class="grid gap-4 sm:grid-cols-3">
                    <flux:field>
                        <flux:label>Package *</flux:label>
                        <flux:select wire:model="package_id" placeholder="Select package">
                            @foreach($packages as $package)
                                <flux:select.option value="{{ $package->id }}">
                                    {{ $package->name }} ({{ number_format($package->monthly_price, 2) }}/mo)
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="package_id" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Billing Cycle *</flux:label>
                        <flux:select wire:model="billing_cycle">
                            <flux:select.option value="monthly">Monthly</flux:select.option>
                            <flux:select.option value="yearly">Yearly</flux:select.option>
                        </flux:select>
                        <flux:error name="billing_cycle" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Preferred Currency *</flux:label>
                        <flux:select wire:model="preferred_currency">
                            @foreach($supportedCurrencies as $currency)
                                <flux:select.option value="{{ $currency }}">{{ $currency }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="preferred_currency" />
                    </flux:field>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:field>
                        <flux:label>Subscription Expires At *</flux:label>
                        <flux:input wire:model="subscription_expires_at" type="date" />
                        <flux:error name="subscription_expires_at" />
                    </flux:field>

                    <flux:field>
                        <flux:label class="flex items-center gap-2">
                            <flux:checkbox wire:model="is_trial" />
                            Trial Account
                        </flux:label>
                        @if($is_trial)
                            <flux:input wire:model="trial_ends_at" type="date" placeholder="Trial end date" class="mt-2" />
                        @endif
                        <flux:error name="trial_ends_at" />
                    </flux:field>
                </div>
            </div>
        </flux:card>

        <!-- Status -->
        <flux:card>
            <div class="space-y-6">
                <flux:heading size="lg" icon="check-circle">Status</flux:heading>

                <flux:field>
                    <flux:label class="flex items-center gap-2">
                        <flux:checkbox wire:model="is_active" />
                        Active Company
                    </flux:label>
                    <flux:description>Inactive companies cannot access the system</flux:description>
                    <flux:error name="is_active" />
                </flux:field>
            </div>
        </flux:card>

        <!-- Form Actions -->
        <div class="flex justify-between">
            <flux:button :href="route('admin.companies.index')" wire:navigate variant="ghost">
                Cancel
            </flux:button>

            <flux:button type="submit" variant="primary">
                Create Company
            </flux:button>
        </div>
    </form>
</div>
