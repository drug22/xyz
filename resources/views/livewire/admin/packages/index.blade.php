<div>
    <!-- Header Section -->
    <div class="mb-6">
        <flux:heading size="xl">Packages Management</flux:heading>
        <flux:text class="mt-2">Manage pricing packages and multi-currency features</flux:text>

        <div class="mt-4 flex justify-end">
            <flux:button :href="route('admin.packages.create')" wire:navigate variant="primary" icon="plus" color="green">
                Add Package
            </flux:button>
        </div>
    </div>

    <!-- Search & Filters -->
    <flux:card class="mb-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:gap-4">
            <!-- Search -->
            <flux:field class="w-full sm:flex-1">
                <flux:label>Search</flux:label>
                <flux:input wire:model.live="search" placeholder="Search packages..." icon:leading="magnifying-glass" />
            </flux:field>

            <!-- Status Filter -->
            <flux:field class="w-full sm:w-auto sm:min-w-[200px]">
                <flux:label>Status</flux:label>
                <flux:select wire:model.live="statusFilter" placeholder="All Status">
                    <flux:select.option value="">All Status</flux:select.option>
                    <flux:select.option value="1">Active</flux:select.option>
                    <flux:select.option value="0">Inactive</flux:select.option>
                </flux:select>
            </flux:field>
        </div>
    </flux:card>

    <!-- Packages Table -->
    <flux:table :paginate="$packages">
        <flux:table.columns>
            <flux:table.column>Package</flux:table.column>
            <flux:table.column>Pricing ({{ $defaultCurrency }})</flux:table.column>
            <flux:table.column>Multi-Currency</flux:table.column>
            <flux:table.column>Companies</flux:table.column>
            <flux:table.column>Status</flux:table.column>
            <flux:table.column>Limits</flux:table.column>
            <flux:table.column>Actions</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse($packages as $package)
                <flux:table.row :key="$package->id">
                    <flux:table.cell>
                        <div>
                            <flux:text class="font-medium">{{ $package->name }}</flux:text>
                            @if($package->description)
                                <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">{{ Str::limit($package->description, 60) }}</flux:text>
                            @endif
                        </div>
                    </flux:table.cell>

                    <flux:table.cell>
                        <div class="space-y-1">
                            <div class="flex items-center gap-1">
                                <flux:icon name="{{ $currencyIcon }}" class="h-4 w-4 text-zinc-500" />
                                <flux:text class="font-medium">{{ number_format($package->monthly_price, 2) }}/month</flux:text>
                            </div>
                            <div class="flex items-center gap-1">
                                <flux:icon name="{{ $currencyIcon }}" class="h-4 w-4 text-zinc-500" />
                                <flux:text size="sm" class="text-zinc-500">{{ number_format($package->yearly_price, 2) }}/year</flux:text>
                            </div>
                        </div>
                    </flux:table.cell>

                    <flux:table.cell>
                        @if($package->monthly_currency_prices)
                            <div class="flex gap-3">
                                @foreach($supportedCurrencies as $currency)
                                    @if($currency !== $defaultCurrency && isset($package->monthly_currency_prices[$currency]))
                                        <div class="space-y-1">
                                            <!-- Monthly -->
                                            <div class="flex items-center gap-1">
                                                <flux:icon name="{{
                                $currency === 'USD' ? 'currency-dollar' :
                                ($currency === 'EUR' ? 'currency-euro' :
                                ($currency === 'GBP' ? 'currency-pound' : 'fire'))
                            }}" class="h-4 w-4 text-zinc-500" />
                                                <flux:text class="font-medium text-sm">{{ number_format($package->monthly_currency_prices[$currency], 0) }}/m</flux:text>
                                            </div>
                                            <!-- Yearly -->
                                            <div class="flex items-center gap-1">
                                                <flux:icon name="{{
                                $currency === 'USD' ? 'currency-dollar' :
                                ($currency === 'EUR' ? 'currency-euro' :
                                ($currency === 'GBP' ? 'currency-pound' : 'fire'))
                            }}" class="h-4 w-4 text-zinc-500" />
                                                <flux:text size="sm" class="text-zinc-500">{{ number_format($package->yearly_currency_prices[$currency] ?? 0, 0) }}/y</flux:text>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @else
                            <flux:text size="sm" class="text-zinc-400">Not calculated</flux:text>
                        @endif
                    </flux:table.cell>

                    <flux:table.cell>
                        <div class="text-center">
                            <flux:text class="font-medium">{{ $package->companies_count ?? 0 }}</flux:text>
                            <flux:text size="sm" class="text-zinc-500">companies</flux:text>
                        </div>
                    </flux:table.cell>

                    <flux:table.cell>
                        @if($package->is_active)
                            <flux:badge color="green" size="sm" inset="top bottom">
                                <flux:icon name="check-circle" class="h-3 w-3 mr-1" />
                                Active
                            </flux:badge>
                        @else
                            <flux:badge color="red" size="sm" inset="top bottom">
                                <flux:icon name="x-circle" class="h-3 w-3 mr-1" />
                                Inactive
                            </flux:badge>
                        @endif
                    </flux:table.cell>

                    <flux:table.cell>
                        <div class="flex flex-wrap gap-1">
                            @if($package->max_users)
                                <flux:badge color="blue" size="sm" icon="users">{{ $package->max_users }} users</flux:badge>
                            @else
                                <flux:badge color="purple" size="sm" icon="sparkles">Unlimited</flux:badge>
                            @endif

                            @if($package->max_checklists)
                                <flux:badge color="green" size="sm" icon="clipboard-document-list">{{ $package->max_checklists }} lists</flux:badge>
                            @else
                                <flux:badge color="purple" size="sm" icon="sparkles">Unlimited</flux:badge>
                            @endif
                        </div>
                    </flux:table.cell>

                    <flux:table.cell>
                        <flux:dropdown align="end">
                            <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom" />

                            <flux:menu>
                                <flux:menu.item
                                    :href="route('admin.packages.edit', $package)"
                                    wire:navigate
                                    icon="pencil"
                                >
                                    Edit Package
                                </flux:menu.item>

                                <flux:menu.separator />

                                <flux:menu.item
                                    wire:click="updateCurrencyPrices({{ $package->id }})"
                                    icon="arrow-path"
                                >
                                    Recalculate Currencies
                                </flux:menu.item>

                                <flux:menu.separator />

                                <flux:menu.item
                                    wire:click="toggleStatus({{ $package->id }})"
                                    wire:confirm="Are you sure you want to {{ $package->is_active ? 'deactivate' : 'activate' }} this package?"
                                    icon="{{ $package->is_active ? 'eye-slash' : 'eye' }}"
                                >
                                    {{ $package->is_active ? 'Deactivate' : 'Activate' }}
                                </flux:menu.item>

                                <flux:menu.separator />

                                <flux:menu.item
                                    wire:click="deletePackage({{ $package->id }})"
                                    wire:confirm="Are you sure you want to delete this package? This action cannot be undone."
                                    icon="trash"
                                    variant="danger"
                                >
                                    Delete Package
                                </flux:menu.item>
                            </flux:menu>
                        </flux:dropdown>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="7">
                        <div class="text-center py-12 flex flex-col items-center">
                            <flux:icon name="cube-transparent" class="h-12 w-12 text-zinc-400 mb-4" />
                            <flux:heading size="lg">No packages found</flux:heading>
                            <flux:text class="text-zinc-500 dark:text-zinc-400 mt-1">Get started by creating your first package with multi-currency pricing</flux:text>
                            <div class="mt-4">
                                <flux:button :href="route('admin.packages.create')" wire:navigate variant="primary" icon="plus" color="green">
                                    Add Package
                                </flux:button>
                            </div>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</div>
