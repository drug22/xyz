<div>
    <flux:heading size="xl">Stripe Payment & Tax Settings</flux:heading>
    <flux:text class="mt-2">Configure Stripe payment gateway with VAT compliance and VIES validation</flux:text>

    <form wire:submit="save" class="mt-6 space-y-6">
        <!-- API Configuration -->
        <flux:card>
            <div class="space-y-6">
                <div class="flex items-center justify-between">
                    <flux:heading size="lg" icon="key">API Configuration</flux:heading>
                    <flux:badge color="{{ $mode === 'live' ? 'red' : 'blue' }}" size="sm">
                        {{ ucfirst($mode) }} Mode
                    </flux:badge>
                </div>

                <flux:field>
                    <flux:label>Mode</flux:label>
                    <flux:select wire:model="mode">
                        <flux:select.option value="test">Test Mode</flux:select.option>
                        <flux:select.option value="live">Live Mode</flux:select.option>
                    </flux:select>
                    <flux:description>Use test mode for development and live mode for production</flux:description>
                    <flux:error name="mode" />
                </flux:field>

                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:field>
                        <flux:label>Publishable Key</flux:label>
                        <flux:input wire:model="public_key" placeholder="pk_test_..." type="password" />
                        <flux:description>Starts with pk_test_ or pk_live_</flux:description>
                        <flux:error name="public_key" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Secret Key</flux:label>
                        <flux:input wire:model="secret_key" placeholder="sk_test_..." type="password" />
                        <flux:description>Starts with sk_test_ or sk_live_</flux:description>
                        <flux:error name="secret_key" />
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>Webhook Secret</flux:label>
                    <flux:input wire:model="webhook_secret" placeholder="whsec_..." type="password" />
                    <flux:description>Webhook endpoint signing secret from Stripe dashboard</flux:description>
                    <flux:error name="webhook_secret" />
                </flux:field>

                <div class="flex gap-3">
                    <flux:button
                        wire:click="testConnection"
                        variant="outline"
                        :loading="$testing"
                        icon="wifi"
                    >
                        Test Connection
                    </flux:button>

                    @if(count($test_results) > 0)
                        <div class="flex-1">
                            @foreach($test_results as $test => $result)
                                <flux:badge
                                    color="{{ $result['status'] === 'success' ? 'green' : 'red' }}"
                                    size="sm"
                                    class="mr-2"
                                >
                                    {{ $result['message'] }}
                                </flux:badge>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </flux:card>

        <!-- Business Information Reference -->
        <flux:card>
            <div class="space-y-4">
                <flux:heading size="lg" icon="building-office">Business Information</flux:heading>
                <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">
                    Business details for invoicing are managed in
                    <flux:link :href="route('admin.settings.index')" wire:navigate class="text-blue-600 hover:text-blue-500">
                        General Settings
                    </flux:link>
                </flux:text>

                <div class="p-4 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                    <flux:text size="sm">
                        <strong>Company:</strong> {{ \App\Models\Settings::get('company_name', 'Not set') }}<br>
                        <strong>Country:</strong> {{ \App\Models\Settings::get('company_country', 'Not set') }}<br>
                        <strong>Email:</strong> {{ \App\Models\Settings::get('company_email', 'Not set') }}<br>
                        <strong>Tax ID:</strong> {{ \App\Models\Settings::get('company_registration_number', 'Not set') }}
                    </flux:text>
                </div>
            </div>
        </flux:card>

        <!-- Payment Configuration -->
        <flux:card>
            <div class="space-y-6">
                <flux:heading size="lg" icon="credit-card">Payment Configuration</flux:heading>

                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:field>
                        <flux:label class="flex items-center gap-2">
                            <flux:checkbox wire:model="auto_capture" />
                            Auto Capture Payments
                        </flux:label>
                        <flux:description>Automatically capture payments when authorized</flux:description>
                        <flux:error name="auto_capture" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Application Fee (%)</flux:label>
                        <flux:input wire:model="application_fee_percent" type="number" step="0.01" min="0" max="100" />
                        <flux:description>Platform fee percentage (0 for no fee)</flux:description>
                        <flux:error name="application_fee_percent" />
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>Supported Payment Methods</flux:label>
                    <div class="grid gap-2 sm:grid-cols-2">
                        @foreach($availablePaymentMethods as $method => $label)
                            <flux:label class="flex items-center gap-2">
                                <flux:checkbox
                                    wire:model="supported_payment_methods"
                                    value="{{ $method }}"
                                />
                                {{ $label }}
                            </flux:label>
                        @endforeach
                    </div>
                    <flux:error name="supported_payment_methods" />
                </flux:field>
            </div>
        </flux:card>

        <!-- Tax & VAT Configuration -->
        <flux:card>
            <div class="space-y-6">
                <flux:heading size="lg" icon="calculator">Tax & VAT Configuration</flux:heading>

                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:field>
                        <flux:label class="flex items-center gap-2">
                            <flux:checkbox wire:model="auto_tax_calculation" />
                            Auto Tax Calculation
                        </flux:label>
                        <flux:description>Automatically calculate VAT based on customer location</flux:description>
                        <flux:error name="auto_tax_calculation" />
                    </flux:field>

                    <flux:field>
                        <flux:label class="flex items-center gap-2">
                            <flux:checkbox wire:model="vies_validation_enabled" />
                            VIES VAT Validation
                        </flux:label>
                        <flux:description>Validate EU VAT numbers through VIES system</flux:description>
                        <flux:error name="vies_validation_enabled" />
                    </flux:field>
                </div>

                <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                    <flux:text size="sm" class="text-blue-700 dark:text-blue-300">
                        <strong>VAT Logic:</strong><br>
                        • <strong>EU B2B with valid VAT:</strong> Reverse VAT (0%)<br>
                        • <strong>Non-EU exports:</strong> No VAT (0%)<br>
                        • <strong>EU B2C or invalid VAT:</strong> Local VAT rate
                    </flux:text>
                </div>
            </div>
        </flux:card>

        <!-- Invoice Configuration -->
        <flux:card>
            <div class="space-y-6">
                <flux:heading size="lg" icon="document-text">Invoice Configuration</flux:heading>

                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:field>
                        <flux:label>Invoice Prefix</flux:label>
                        <flux:input wire:model="invoice_prefix" placeholder="INV" />
                        <flux:error name="invoice_prefix" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Next Invoice Number</flux:label>
                        <flux:input wire:model="invoice_next_number" type="number" min="1" />
                        <flux:error name="invoice_next_number" />
                    </flux:field>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:field>
                        <flux:label>Proforma Prefix</flux:label>
                        <flux:input wire:model="proforma_prefix" placeholder="PRO" />
                        <flux:error name="proforma_prefix" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Next Proforma Number</flux:label>
                        <flux:input wire:model="proforma_next_number" type="number" min="1" />
                        <flux:error name="proforma_next_number" />
                    </flux:field>
                </div>
            </div>
        </flux:card>

        <!-- Webhook Configuration -->
        <flux:card>
            <div class="space-y-6">
                <flux:heading size="lg" icon="bolt">Webhook Configuration</flux:heading>

                <flux:field>
                    <flux:label>Webhook Events to Listen For</flux:label>
                    <div class="grid gap-2 sm:grid-cols-2">
                        @foreach($availableWebhookEvents as $event => $label)
                            <flux:label class="flex items-center gap-2">
                                <flux:checkbox
                                    wire:model="webhook_events"
                                    value="{{ $event }}"
                                />
                                {{ $label }}
                            </flux:label>
                        @endforeach
                    </div>
                    <flux:error name="webhook_events" />
                </flux:field>

                <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                    <flux:text size="sm" class="text-blue-700 dark:text-blue-300">
                        <strong>Webhook URL:</strong> {{ url('/api/stripe/webhook') }}
                    </flux:text>
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
                        Enable Stripe Payments
                    </flux:label>
                    <flux:description>Enable Stripe payment processing for the application</flux:description>
                    <flux:error name="is_active" />
                </flux:field>
            </div>
        </flux:card>

        <!-- Form Actions -->
        <div class="flex justify-end">
            <flux:button type="submit" variant="primary" color="green">
                Save Stripe Settings
            </flux:button>
        </div>
    </form>
</div>
