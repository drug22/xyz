<div>
    <!-- Header Section -->
    <div class="mb-6">
        <flux:heading size="xl">Companies Management</flux:heading>
        <flux:text class="mt-2">Manage companies, subscriptions, and billing information</flux:text>

        <div class="mt-4 flex justify-end">
            <flux:button :href="route('admin.companies.create')" wire:navigate variant="primary" icon="plus" color="green">
                Add Company
            </flux:button>
        </div>
    </div>

    <!-- Search & Filters -->
    <flux:card class="mb-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:gap-4">
            <!-- Search -->
            <flux:field class="w-full sm:flex-1">
                <flux:label>Search</flux:label>
                <flux:input wire:model.live="search" placeholder="Search companies, email, registration..." icon:leading="magnifying-glass" />
            </flux:field>

            <!-- Package Filter -->
            <flux:field class="w-full sm:w-auto sm:min-w-[200px]">
                <flux:label>Package</flux:label>
                <flux:select wire:model.live="packageFilter" placeholder="All Packages">
                    <flux:select.option value="">All Packages</flux:select.option>
                    @foreach($packages as $package)
                        <flux:select.option value="{{ $package->id }}">{{ $package->name }}</flux:select.option>
                    @endforeach
                </flux:select>
            </flux:field>

            <!-- Status Filter -->
            <flux:field class="w-full sm:w-auto sm:min-w-[200px]">
                <flux:label>Status</flux:label>
                <flux:select wire:model.live="statusFilter" placeholder="All Status">
                    <flux:select.option value="">All Status</flux:select.option>
                    <flux:select.option value="1">Active</flux:select.option>
                    <flux:select.option value="0">Inactive</flux:select.option>
                    <flux:select.option value="trial">Trial</flux:select.option>
                    <flux:select.option value="expired">Expired</flux:select.option>
                </flux:select>
            </flux:field>

            <!-- Country Filter -->
            <flux:field class="w-full sm:w-auto sm:min-w-[150px]">
                <flux:label>Country</flux:label>
                <flux:select wire:model.live="countryFilter" placeholder="All Countries">
                    <flux:select.option value="">All Countries</flux:select.option>
                    @foreach($countries as $country)
                        <flux:select.option value="{{ $country }}">{{ $country }}</flux:select.option>
                    @endforeach
                </flux:select>
            </flux:field>
        </div>
    </flux:card>

    <!-- Companies Table -->
    <flux:table :paginate="$companies">
        <flux:table.columns>
            <flux:table.column>Company</flux:table.column>
            <flux:table.column>Contact</flux:table.column>
            <flux:table.column>Package</flux:table.column>
            <flux:table.column>Subscription</flux:table.column>
            <flux:table.column>Users</flux:table.column>
            <flux:table.column>Status</flux:table.column>
            <flux:table.column>Actions</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse($companies as $company)
                <flux:table.row :key="$company->id">
                    <flux:table.cell>
                        <div>
                            <flux:text class="font-medium">{{ $company->name }}</flux:text>
                            <div class="flex gap-2 mt-1">
                                @if($company->registration_number)
                                    <flux:badge size="sm" color="blue">{{ $company->registration_number }}</flux:badge>
                                @endif
                                @if($company->vat_payer)
                                    <flux:badge size="sm" color="green">VAT</flux:badge>
                                @endif
                            </div>
                            <flux:text size="sm" class="text-zinc-500">{{ $company->city }}, {{ $company->country }}</flux:text>
                        </div>
                    </flux:table.cell>

                    <flux:table.cell>
                        <div>
                            <flux:text class="font-medium">{{ $company->contact_email }}</flux:text>
                            @if($company->contact_phone)
                                <flux:text size="sm" class="text-zinc-500">{{ $company->contact_phone }}</flux:text>
                            @endif
                            @if($company->website)
                                <flux:text size="sm" class="text-blue-600">
                                    <a href="{{ $company->website }}" target="_blank">Website</a>
                                </flux:text>
                            @endif
                        </div>
                    </flux:table.cell>

                    <flux:table.cell>
                        <div>
                            <flux:text class="font-medium">{{ $company->package->name }}</flux:text>
                            <div class="flex gap-1 mt-1">
                                <flux:badge size="sm" color="{{ $company->billing_cycle === 'yearly' ? 'green' : 'blue' }}">
                                    {{ ucfirst($company->billing_cycle) }}
                                </flux:badge>
                                <flux:badge size="sm" color="purple">
                                    {{ $company->preferred_currency }}
                                </flux:badge>
                            </div>
                        </div>
                    </flux:table.cell>

                    <flux:table.cell>
                        <div>
                            @if($company->is_trial)
                                <flux:badge color="orange" size="sm" icon="clock">
                                    Trial until {{ $company->trial_ends_at?->format('M d, Y') }}
                                </flux:badge>
                            @else
                                @if($company->subscription_expires_at?->isPast())
                                    <flux:badge color="red" size="sm" icon="exclamation-triangle">
                                        Expired {{ $company->subscription_expires_at->format('M d, Y') }}
                                    </flux:badge>
                                @else
                                    <flux:badge color="green" size="sm" icon="check">
                                        Until {{ $company->subscription_expires_at?->format('M d, Y') }}
                                    </flux:badge>
                                @endif
                            @endif
                            @if($company->last_payment_at)
                                <flux:text size="sm" class="text-zinc-500 mt-1">
                                    Last payment: {{ $company->last_payment_at->format('M d, Y') }}
                                </flux:text>
                            @endif
                        </div>
                    </flux:table.cell>

                    <flux:table.cell>
                        <div class="text-center">
                            <flux:text class="font-medium">{{ $company->users_count }}</flux:text>
                            <flux:text size="sm" class="text-zinc-500">users</flux:text>
                        </div>
                    </flux:table.cell>

                    <flux:table.cell>
                        @if($company->is_active)
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
                        <flux:dropdown align="end">
                            <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom" />

                            <flux:menu>
                                <flux:menu.item
                                    :href="route('admin.companies.edit', $company)"
                                    wire:navigate
                                    icon="pencil"
                                >
                                    Edit Company
                                </flux:menu.item>

                                <flux:menu.separator />

                                @if($company->is_trial)
                                    <flux:menu.item
                                        wire:click="extendTrial({{ $company->id }})"
                                        wire:confirm="Extend trial by 30 days?"
                                        icon="clock"
                                    >
                                        Extend Trial
                                    </flux:menu.item>
                                @endif

                                <flux:menu.item
                                    wire:click="toggleStatus({{ $company->id }})"
                                    wire:confirm="Are you sure you want to {{ $company->is_active ? 'deactivate' : 'activate' }} this company?"
                                    icon="{{ $company->is_active ? 'eye-slash' : 'eye' }}"
                                >
                                    {{ $company->is_active ? 'Deactivate' : 'Activate' }}
                                </flux:menu.item>

                                <flux:menu.separator />

                                <flux:menu.item
                                    wire:click="deleteCompany({{ $company->id }})"
                                    wire:confirm="Are you sure you want to delete this company? This action cannot be undone."
                                    icon="trash"
                                    variant="danger"
                                >
                                    Delete Company
                                </flux:menu.item>
                            </flux:menu>
                        </flux:dropdown>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="7">
                        <div class="text-center py-12 flex flex-col items-center">
                            <flux:icon name="building-office" class="h-12 w-12 text-zinc-400 mb-4" />
                            <flux:heading size="lg">No companies found</flux:heading>
                            <flux:text class="text-zinc-500 dark:text-zinc-400 mt-1">Get started by adding your first company</flux:text>
                            <div class="mt-4">
                                <flux:button :href="route('admin.companies.create')" wire:navigate variant="primary" icon="plus" color="green">
                                    Add Company
                                </flux:button>
                            </div>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</div>
