<div>
    <flux:heading size="xl">Platform Settings</flux:heading>
    <flux:text class="mt-2">Configure platform/company information and currency management</flux:text>

    <form wire:submit="updateSettings" class="mt-6 space-y-6">
        <div class="grid gap-6 lg:grid-cols-2">
            <flux:card>
                <div class="space-y-6">
                    <flux:heading size="lg">Company Information</flux:heading>

                    <flux:field>
                        <flux:label>Company Name *</flux:label>
                        <flux:input wire:model="company_name" placeholder="Enter company name" />
                        <flux:error name="company_name" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Legal company name *</flux:label>
                        <flux:input wire:model="company_name_original" placeholder="Enter legal company name" />
                        <flux:error name="company_name_original" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Registration Number</flux:label>
                        <flux:input wire:model="company_registration_number" placeholder="e.g. J40/12345/2024" />
                        <flux:error name="company_registration_number" />
                    </flux:field>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <flux:field>
                            <flux:label>Logo Light Mode</flux:label>
                            <flux:input type="file" wire:model="company_logo_light" />
                            @if($company_logo_light && is_string($company_logo_light))
                                <img src="{{ asset('storage/'.$company_logo_light) }}" class="h-16 mt-2 rounded bg-white border p-2" alt="Light logo preview" />
                            @elseif($company_logo_light && is_object($company_logo_light))
                                <img src="{{ $company_logo_light->temporaryUrl() }}" class="h-16 mt-2 rounded bg-white border p-2" alt="Light logo preview" />
                            @endif
                            <flux:error name="company_logo_light" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Logo Dark Mode</flux:label>
                            <flux:input type="file" wire:model="company_logo_dark" />
                            @if($company_logo_dark && is_string($company_logo_dark))
                                <img src="{{ asset('storage/'.$company_logo_dark) }}" class="h-16 mt-2 rounded bg-gray-800 border p-2" alt="Dark logo preview" />
                            @elseif($company_logo_dark && is_object($company_logo_dark))
                                <img src="{{ $company_logo_dark->temporaryUrl() }}" class="h-16 mt-2 rounded bg-gray-800 border p-2" alt="Dark logo preview" />
                            @endif
                            <flux:error name="company_logo_dark" />
                        </flux:field>
                    </div>

                    <flux:field>
                        <flux:label>Address</flux:label>
                        <flux:textarea wire:model="company_address" placeholder="Full business address..." rows="2" />
                        <flux:error name="company_address" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Phone</flux:label>
                        <flux:input wire:model="company_phone" placeholder="+40 21 234 5678" />
                        <flux:error name="company_phone" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Email</flux:label>
                        <flux:input wire:model="company_email" type="email" placeholder="contact@company.com" />
                        <flux:error name="company_email" />
                    </flux:field>
                </div>
            </flux:card>

            <flux:card>
                <div class="space-y-6">
                    <flux:heading size="lg">Banking Information</flux:heading>

                    <flux:field>
                        <flux:label>Bank Name</flux:label>
                        <flux:input wire:model="bank_name" placeholder="e.g. Banca Transilvania" />
                        <flux:error name="bank_name" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Account Number</flux:label>
                        <flux:input wire:model="bank_account_number" placeholder="Bank account number" />
                        <flux:error name="bank_account_number" />
                    </flux:field>

                    <flux:field>
                        <flux:label>IBAN</flux:label>
                        <flux:input wire:model="bank_iban" placeholder="RO49 AAAA 1B31 0075 9384 0000" />
                        <flux:error name="bank_iban" />
                    </flux:field>

                    <flux:field>
                        <flux:label>SWIFT/BIC Code</flux:label>
                        <flux:input wire:model="bank_swift" placeholder="BTRLRO22" />
                        <flux:error name="bank_swift" />
                    </flux:field>
                </div>
            </flux:card>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <flux:card>
                <div class="space-y-6">
                    <flux:heading size="lg">Currency Settings</flux:heading>

                    <flux:field>
                        <flux:label>Default Currency</flux:label>
                        <flux:select wire:model="default_currency">
                            <flux:select.option value="USD">🇺🇸 USD - US Dollar</flux:select.option>
                            <flux:select.option value="EUR">🇪🇺 EUR - Euro</flux:select.option>
                            <flux:select.option value="GBP">🇬🇧 GBP - British Pound</flux:select.option>
                            <flux:select.option value="RON">🇷🇴 RON - Romanian Leu</flux:select.option>
                        </flux:select>
                        <flux:error name="default_currency" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Supported Currencies</flux:label>
                        <div class="flex gap-4 flex-wrap">
                            @foreach(['USD','EUR','GBP','RON'] as $currency)
                                <label class="flex items-center gap-2">
                                    <input type="checkbox" wire:model="supported_currencies" value="{{ $currency }}" class="rounded" />
                                    <span class="text-sm">{{ $currency }}</span>
                                </label>
                            @endforeach
                        </div>
                        <flux:error name="supported_currencies" />
                    </flux:field>
                </div>
            </flux:card>

            <flux:card>
                <div class="space-y-6">
                    <div class="flex items-center justify-between">
                        <flux:heading size="lg">Exchange Rates</flux:heading>
                        <flux:button wire:click="fetchExchangeRates" variant="outline" size="sm" icon="arrow-path">
                            Fetch Rates
                        </flux:button>
                    </div>

                    <div>
                        <flux:text class="font-medium">Current Rates (Base: {{ $default_currency }})</flux:text>
                        @if(count($exchange_rates) > 0)
                            <div class="mt-3 space-y-2">
                                @foreach(['USD', 'EUR', 'GBP', 'RON'] as $currency)
                                    @if($currency !== $default_currency && isset($exchange_rates[$currency]))
                                        <div class="flex justify-between p-2 bg-zinc-50 dark:bg-zinc-800 rounded">
                                            <flux:text>1 {{ $default_currency }}</flux:text>
                                            <flux:text>{{ number_format($exchange_rates[$currency], 4) }} {{ $currency }}</flux:text>
                                        </div>
                                    @endif
                                @endforeach
                                <flux:text size="sm" class="text-zinc-500 mt-2">
                                    Last updated: {{ \App\Models\Settings::get('exchange_rates_updated_at') ? date('M j, Y H:i', strtotime(\App\Models\Settings::get('exchange_rates_updated_at'))) : 'Never' }}
                                </flux:text>
                            </div>
                        @else
                            <div class="mt-3 p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                                <flux:text class="text-yellow-700 dark:text-yellow-300">
                                    No exchange rates available. Click "Fetch Rates" to get current rates.
                                </flux:text>
                            </div>
                        @endif
                    </div>
                </div>
            </flux:card>
        </div>

        <div class="flex justify-end">
            <flux:button icon="check" type="submit" variant="primary" color="green">
                Save Settings
            </flux:button>
        </div>
    </form>
</div>
