<div>
    <flux:heading size="xl">Edit Package</flux:heading>
    <flux:text class="mt-2">Update {{ $package->name }} information and multi-currency pricing</flux:text>

    <form wire:submit="update" class="mt-6 space-y-6">
        <div class="grid gap-6 lg:grid-cols-2">
            <flux:card>
                <div class="space-y-6">
                    <flux:heading size="lg">Package Information</flux:heading>

                    <flux:field>
                        <flux:label>Package Name *</flux:label>
                        <flux:input wire:model="name" placeholder="e.g. Starter, Professional, Enterprise" />
                        <flux:error name="name" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Description</flux:label>
                        <flux:textarea wire:model="description" placeholder="Describe what this package includes..." rows="3" />
                        <flux:error name="description" />
                    </flux:field>

                    <flux:field>
                        <flux:label class="flex items-center gap-2">
                            <flux:checkbox wire:model="is_active" />
                            Active Package
                        </flux:label>
                        <flux:description>Inactive packages cannot be selected by companies</flux:description>
                        <flux:error name="is_active" />
                    </flux:field>

                    <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                        <flux:text size="sm" class="text-blue-700 dark:text-blue-300">
                            <strong>Companies using this package:</strong> {{ $package->companies_count ?? $package->companies->count() }}
                        </flux:text>
                    </div>
                </div>
            </flux:card>

            <flux:card>
                <div class="space-y-6">
                    <flux:heading size="lg">Pricing ({{ $defaultCurrency }})</flux:heading>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <flux:field>
                            <flux:label>Monthly Price *</flux:label>
                            <flux:input
                                id="monthly_price_edit"
                                wire:model="monthly_price"
                                type="number"
                                step="0.01"
                                min="0"
                                placeholder="0.00"
                                icon:trailing="{{
                                    $defaultCurrency === 'USD' ? 'currency-dollar' :
                                    ($defaultCurrency === 'EUR' ? 'currency-euro' :
                                    ($defaultCurrency === 'GBP' ? 'currency-pound' : 'fire'))
                                }}"
                                oninput="calculateYearlyEdit()"
                            />
                            <flux:description>Price per month in {{ $defaultCurrency }}</flux:description>
                            <flux:error name="monthly_price" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Yearly Price *</flux:label>
                            <flux:input
                                id="yearly_price_edit"
                                wire:model="yearly_price"
                                type="number"
                                step="0.01"
                                min="0"
                                placeholder="Auto-calculated"
                                icon:trailing="{{
                                    $defaultCurrency === 'USD' ? 'currency-dollar' :
                                    ($defaultCurrency === 'EUR' ? 'currency-euro' :
                                    ($defaultCurrency === 'GBP' ? 'currency-pound' : 'fire'))
                                }}"
                            />
                            <flux:description>Auto-calculated as monthly × 12 ({{ $defaultCurrency }})</flux:description>
                            <flux:error name="yearly_price" />
                        </flux:field>
                    </div>

                    @if(count($monthly_currency_prices) > 0)
                        <div class="mt-6">
                            <flux:text class="font-medium mb-3">Auto-calculated prices in other currencies:</flux:text>
                            <div class="space-y-2">
                                @foreach($monthly_currency_prices as $currency => $monthlyPrice)
                                    <div class="flex justify-between p-2 bg-zinc-50 dark:bg-zinc-800 rounded text-sm">
                                        <span class="font-medium">{{ $currency }}</span>
                                        <span>{{ number_format($monthlyPrice, 2) }}/month • {{ number_format($yearly_currency_prices[$currency], 2) }}/year</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </flux:card>
        </div>

        <flux:card>
            <div class="space-y-6">
                <flux:heading size="lg">Package Limits</flux:heading>

                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:field>
                        <flux:label>Max Users</flux:label>
                        <flux:input wire:model="max_users" type="number" min="0" placeholder="Leave empty for unlimited" />
                        <flux:description>Maximum number of users per company</flux:description>
                        <flux:error name="max_users" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Max Checklists</flux:label>
                        <flux:input wire:model="max_checklists" type="number" min="1" placeholder="Leave empty for unlimited" />
                        <flux:description>Maximum number of checklists per company</flux:description>
                        <flux:error name="max_checklists" />
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>Package Features</flux:label>
                    <div class="space-y-2">
                        @foreach($features as $index => $feature)
                            <div class="flex gap-2">
                                <flux:input wire:model="features.{{ $index }}" placeholder="Feature description" class="flex-1" />
                                <flux:button wire:click="removeFeature({{ $index }})" variant="danger" size="sm" icon="trash">
                                    Remove
                                </flux:button>
                            </div>
                        @endforeach
                        <flux:button wire:click="addFeature" variant="outline" size="sm" icon="plus">
                            Add Feature
                        </flux:button>
                    </div>
                    <flux:error name="features" />
                </flux:field>
            </div>
        </flux:card>

        <div class="flex justify-between">
            <flux:button :href="route('admin.packages.index')" wire:navigate variant="danger">
                Cancel
            </flux:button>

            <flux:button type="submit" variant="primary" color="green">
                Update Package
            </flux:button>
        </div>
    </form>

    <script>
        function calculateYearlyEdit() {
            const monthlyInput = document.getElementById('monthly_price_edit');
            const yearlyInput = document.getElementById('yearly_price_edit');

            if (monthlyInput.value && !isNaN(monthlyInput.value)) {
                yearlyInput.value = (parseFloat(monthlyInput.value) * 12).toFixed(2);

                // Trigger Livewire update
                yearlyInput.dispatchEvent(new Event('input'));
            }
        }
    </script>
</div>
