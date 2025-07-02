<div>
    <!-- Header Section -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl">Invoices Management</flux:heading>
                <flux:text class="mt-2">Manage proforma and final invoices with automatic generation</flux:text>
            </div>
            <div class="flex gap-2">
                <flux:button :href="route('admin.billing.invoices.create')" wire:navigate variant="primary" icon="plus">
                    Create Invoice
                </flux:button>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    @if($ordersWithoutProforma->count() > 0)
        <flux:card class="mb-6">
            <div class="space-y-4">
                <flux:heading size="lg" icon="lightning-bolt">Quick Actions</flux:heading>
                <flux:text class="text-zinc-500 dark:text-zinc-400">Published orders without proforma invoices</flux:text>

                <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($ordersWithoutProforma as $order)
                        <div class="flex items-center justify-between p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                            <div>
                                <flux:text class="font-medium">Order #{{ $order->order_number }}</flux:text>
                                <flux:text size="sm" class="text-zinc-500">{{ $order->customer_name }}</flux:text>
                            </div>
                            <flux:button
                                wire:click="createProformaInvoice({{ $order->id }})"
                                size="sm"
                                variant="primary"
                                icon="document-text"
                            >
                                Create Proforma
                            </flux:button>
                        </div>
                    @endforeach
                </div>
            </div>
        </flux:card>
    @endif

    <!-- Search & Filters -->
    <flux:card class="mb-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:gap-4">
            <!-- Search -->
            <flux:field class="w-full sm:flex-1">
                <flux:label>Search</flux:label>
                <flux:input wire:model.live="search" placeholder="Search invoices, emails, VAT numbers..." icon:leading="magnifying-glass" />
            </flux:field>

            <!-- Type Filter -->
            <flux:field class="w-full sm:w-auto sm:min-w-[150px]">
                <flux:label>Type</flux:label>
                <flux:select wire:model.live="typeFilter" placeholder="All Types">
                    <flux:select.option value="">All Types</flux:select.option>
                    <flux:select.option value="proforma">Proforma</flux:select.option>
                    <flux:select.option value="final">Final</flux:select.option>
                </flux:select>
            </flux:field>

            <!-- Status Filter -->
            <flux:field class="w-full sm:w-auto sm:min-w-[150px]">
                <flux:label>Status</flux:label>
                <flux:select wire:model.live="statusFilter" placeholder="All Status">
                    <flux:select.option value="">All Status</flux:select.option>
                    <flux:select.option value="draft">Draft</flux:select.option>
                    <flux:select.option value="sent">Sent</flux:select.option>
                    <flux:select.option value="paid">Paid</flux:select.option>
                    <flux:select.option value="overdue">Overdue</flux:select.option>
                    <flux:select.option value="cancelled">Cancelled</flux:select.option>
                </flux:select>
            </flux:field>

            <!-- Country Filter -->
            <flux:field class="w-full sm:w-auto sm:min-w-[150px]">
                <flux:label>Country</flux:label>
                <flux:select wire:model.live="countryFilter" placeholder="All Countries">
                    <flux:select.option value="">All Countries</flux:select.option>
                    @foreach($countries as $country)
                        <flux:select.option value="{{ $country->code }}">{{ $country->name }}</flux:select.option>
                    @endforeach
                </flux:select>
            </flux:field>

            <!-- Business Filter -->
            <flux:field class="w-full sm:w-auto sm:min-w-[150px]">
                <flux:label>Customer Type</flux:label>
                <flux:select wire:model.live="businessFilter" placeholder="All Types">
                    <flux:select.option value="">All Types</flux:select.option>
                    <flux:select.option value="1">Business (B2B)</flux:select.option>
                    <flux:select.option value="0">Individual (B2C)</flux:select.option>
                </flux:select>
            </flux:field>
        </div>
    </flux:card>

    <!-- Invoices Table -->
    <flux:table :paginate="$invoices">
        <flux:table.columns>
            <flux:table.column>Invoice</flux:table.column>
            <flux:table.column>Customer</flux:table.column>
            <flux:table.column>Order</flux:table.column>
            <flux:table.column>Amount</flux:table.column>
            <flux:table.column>Dates</flux:table.column>
            <flux:table.column>Status</flux:table.column>
            <flux:table.column>Actions</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse($invoices as $invoice)
                <flux:table.row :key="$invoice->id">
                    <flux:table.cell>
                        <div>
                            <flux:text class="font-medium"># {{ $invoice->invoice_number }}</flux:text>
                            <div class="flex gap-1 mt-1">
                                @if($invoice->isProforma())
                                    <flux:badge size="xs" color="blue">Proforma</flux:badge>
                                @else
                                    <flux:badge size="xs" color="green">Final</flux:badge>
                                @endif

                                @if($invoice->vat_number_validated)
                                    <flux:badge size="xs" color="green">VAT ✓</flux:badge>
                                @endif
                            </div>
                        </div>
                    </flux:table.cell>

                    <flux:table.cell>
                        <div>
                            <flux:text class="font-medium">{{ $invoice->customer_display_name }}</flux:text>
                            <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">{{ $invoice->customer_email }}</flux:text>
                            <div class="flex gap-1 mt-1">
                                <flux:badge size="xs" color="blue">{{ $invoice->customerCountry->name ?? $invoice->customer_country }}</flux:badge>
                                @if($invoice->is_business)
                                    <flux:badge size="xs" color="purple">B2B</flux:badge>
                                @else
                                    <flux:badge size="xs" color="gray">B2C</flux:badge>
                                @endif
                            </div>
                        </div>
                    </flux:table.cell>

                    <flux:table.cell>
                        <div>
                            @if($invoice->order)
                                <flux:text class="font-medium">{{ $invoice->order->order_number }}</flux:text>
                                <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">{{ $invoice->order->package->name }}</flux:text>
                                <flux:badge size="xs" color="{{ $invoice->order->billing_cycle === 'yearly' ? 'green' : 'blue' }}">
                                    {{ ucfirst($invoice->order->billing_cycle) }}
                                </flux:badge>
                            @else
                                <flux:text class="font-medium">Manual Invoice</flux:text>
                                <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">{{ $invoice->package_details['name'] ?? 'No package' }}</flux:text>
                                <flux:badge size="xs" color="{{ ($invoice->package_details['billing_cycle'] ?? 'monthly') === 'yearly' ? 'green' : 'blue' }}">
                                    {{ ucfirst($invoice->package_details['billing_cycle'] ?? 'monthly') }}
                                </flux:badge>
                            @endif
                        </div>
                    </flux:table.cell>

                    <flux:table.cell>
                        <div>
                            <flux:text class="font-medium">{{ $invoice->formatted_total }}</flux:text>
                            @if($invoice->tax_amount > 0)
                                <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">
                                    Tax: {{ number_format($invoice->tax_amount, 2) }} ({{ $invoice->tax_rate }}%)
                                </flux:text>
                            @endif
                            @if($invoice->reverse_vat_applied)
                                <flux:badge size="xs" color="purple">Reverse VAT</flux:badge>
                            @endif
                        </div>
                    </flux:table.cell>

                    <flux:table.cell>
                        <div>
                            <flux:text size="sm">
                                <strong>Invoice:</strong> {{ $invoice->invoice_date->format('M d, Y') }}
                            </flux:text>
                            <flux:text size="sm" class="{{ $invoice->isOverdue() ? 'text-red-600' : 'text-zinc-500' }}">
                                <strong>Due:</strong> {{ $invoice->due_date->format('M d, Y') }}
                            </flux:text>
                            @if($invoice->sent_at)
                                <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">
                                    <strong>Sent:</strong> {{ $invoice->sent_at->format('M d, Y') }}
                                </flux:text>
                            @endif
                        </div>
                    </flux:table.cell>

                    <flux:table.cell>
                        @switch($invoice->status)
                            @case('draft')
                                <flux:badge color="gray" size="sm">Draft</flux:badge>
                                @break
                            @case('sent')
                                <flux:badge color="blue" size="sm">Sent</flux:badge>
                                @break
                            @case('paid')
                                <flux:badge color="green" size="sm">
                                    <flux:icon name="check-circle" class="h-3 w-3 mr-1" />
                                    Paid
                                </flux:badge>
                                @break
                            @case('overdue')
                                <flux:badge color="red" size="sm">Overdue</flux:badge>
                                @break
                            @case('cancelled')
                                <flux:badge color="orange" size="sm">Cancelled</flux:badge>
                                @break
                        @endswitch

                        @if($invoice->isOverdue())
                            <flux:text size="xs" class="text-red-600 mt-1">
                                {{ $invoice->due_date->diffForHumans() }}
                            </flux:text>
                        @endif
                    </flux:table.cell>

                    <flux:table.cell>
                        <flux:dropdown align="end">
                            <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom" />

                            <flux:menu>
                                <flux:menu.item
                                    :href="route('admin.billing.invoices.show', $invoice)"
                                    wire:navigate
                                    icon="eye"
                                >
                                    View Invoice
                                </flux:menu.item>

                                @if($invoice->canEdit())
                                    <flux:menu.item
                                        :href="route('admin.billing.invoices.edit', $invoice)"
                                        wire:navigate
                                        icon="pencil"
                                    >
                                        Edit Invoice
                                    </flux:menu.item>
                                @endif

                                <flux:menu.separator />

                                @if($invoice->canSend())
                                    <flux:menu.item
                                        wire:click="markAsSent({{ $invoice->id }})"
                                        wire:confirm="Mark this invoice as sent?"
                                        icon="paper-airplane"
                                    >
                                        Mark as Sent
                                    </flux:menu.item>
                                @endif

                                @if(!$invoice->isPaid())
                                    <flux:menu.item
                                        wire:click="markAsPaid({{ $invoice->id }})"
                                        wire:confirm="Mark this invoice as paid?"
                                        icon="check-circle"
                                    >
                                        Mark as Paid
                                    </flux:menu.item>
                                @endif

                                @if($invoice->canDelete())
                                    <flux:menu.separator />
                                    <flux:menu.item
                                        wire:click="deleteInvoice({{ $invoice->id }})"
                                        wire:confirm="Are you sure you want to delete this invoice?"
                                        icon="trash"
                                        variant="danger"
                                    >
                                        Delete Invoice
                                    </flux:menu.item>
                                @endif
                            </flux:menu>
                        </flux:dropdown>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="7">
                        <div class="text-center py-12 flex flex-col items-center">
                            <flux:icon name="document-text" class="h-12 w-12 text-zinc-400 mb-4" />
                            <flux:heading size="lg">No invoices found</flux:heading>
                            <flux:text class="text-zinc-500 dark:text-zinc-400 mt-1">Get started by creating your first invoice</flux:text>
                            <div class="mt-4">
                                <flux:button :href="route('admin.billing.invoices.create')" wire:navigate variant="primary" icon="plus">
                                    Create Invoice
                                </flux:button>
                            </div>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</div>
