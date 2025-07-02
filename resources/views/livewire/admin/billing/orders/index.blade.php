<div>
    <!-- Header Section -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl">Orders Management</flux:heading>
                <flux:text class="mt-2">Manage customer orders, payments, and VAT compliance</flux:text>
            </div>
            <flux:button :href="route('admin.billing.orders.create')" wire:navigate variant="primary" icon="plus" color="green">
                Create Order
            </flux:button>
        </div>
    </div>

    <!-- Search & Filters -->
    <flux:card class="mb-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:gap-4">
            <!-- Search -->
            <flux:field class="w-full sm:flex-1">
                <flux:label>Search</flux:label>
                <flux:input wire:model.live="search" placeholder="Search orders, emails, VAT numbers, companies..." icon:leading="magnifying-glass" />
            </flux:field>

            <!-- Status Filter -->
            <flux:field class="w-full sm:w-auto sm:min-w-[150px]">
                <flux:label>Status</flux:label>
                <flux:select wire:model.live="statusFilter" placeholder="All Status">
                    <flux:select.option value="">All Status</flux:select.option>
                    <flux:select.option value="draft">Draft</flux:select.option>
                    <flux:select.option value="published">Published</flux:select.option>
                    <flux:select.option value="pending">Pending</flux:select.option>
                    <flux:select.option value="processing">Processing</flux:select.option>
                    <flux:select.option value="paid">Paid</flux:select.option>
                    <flux:select.option value="failed">Failed</flux:select.option>
                    <flux:select.option value="cancelled">Cancelled</flux:select.option>
                    <flux:select.option value="refunded">Refunded</flux:select.option>
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

            <!-- Package Filter -->
            <flux:field class="w-full sm:w-auto sm:min-w-[150px]">
                <flux:label>Package</flux:label>
                <flux:select wire:model.live="packageFilter" placeholder="All Packages">
                    <flux:select.option value="">All Packages</flux:select.option>
                    @foreach($packages as $package)
                        <flux:select.option value="{{ $package->id }}">{{ $package->name }}</flux:select.option>
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

    <!-- Orders Table -->
    <flux:table :paginate="$orders">
        <flux:table.columns>
            <flux:table.column>Order</flux:table.column>
            <flux:table.column>Customer</flux:table.column>
            <flux:table.column>Package</flux:table.column>
            <flux:table.column>Amount & Tax</flux:table.column>
            <flux:table.column>Status</flux:table.column>
            <flux:table.column>Actions</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse($orders as $order)
                <flux:table.row :key="$order->id">
                    <flux:table.cell>
                        <div>
                            <flux:text class="font-medium"># {{ $order->order_number }}</flux:text>
                            <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">{{ $order->created_at->format('M d, Y H:i') }}</flux:text>
                            @if($order->stripe_payment_intent_id)
                                <flux:badge size="xs" color="blue">Stripe: {{ substr($order->stripe_payment_intent_id, -8) }}</flux:badge>
                            @endif

                            <!-- Invoice Status -->
                            <div class="mt-1 flex gap-1">
                                @if($order->proforma_invoice_generated)
                                    <flux:badge size="xs" color="green">Proforma ✓</flux:badge>
                                @endif
                                @if($order->final_invoice_generated)
                                    <flux:badge size="xs" color="purple">Invoice ✓</flux:badge>
                                @endif
                            </div>
                        </div>
                    </flux:table.cell>

                    <flux:table.cell>
                        <div>
                            <flux:text class="font-medium">{{ $order->customer_name }}</flux:text>
                            <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">{{ $order->customer_email }}</flux:text>
                            <div class="flex gap-1 mt-1">
                                <flux:badge size="xs" color="blue">{{ $order->customerCountry->name ?? $order->customer_country }}</flux:badge>
                                @if($order->is_business)
                                    <flux:badge size="xs" color="purple">B2B</flux:badge>
                                    @if($order->metadata && isset($order->metadata['company_name']))
                                        <flux:text size="xs" class="text-zinc-500 dark:text-zinc-400">{{ $order->metadata['company_name'] }}</flux:text>
                                    @endif
                                    @if($order->vat_number_validated)
                                        <flux:badge size="xs" color="green">VAT ✓</flux:badge>
                                    @elseif($order->customer_vat_number)
                                        <flux:badge size="xs" color="orange">VAT ?</flux:badge>
                                    @endif
                                @else
                                    <flux:badge size="xs" color="gray">B2C</flux:badge>
                                @endif
                            </div>
                        </div>
                    </flux:table.cell>

                    <flux:table.cell>
                        <div>
                            <flux:text class="font-medium">{{ $order->package->name }}</flux:text>
                            <flux:badge size="sm" color="{{ $order->billing_cycle === 'yearly' ? 'green' : 'blue' }}">
                                {{ ucfirst($order->billing_cycle) }}
                            </flux:badge>
                        </div>
                    </flux:table.cell>

                    <flux:table.cell>
                        <div>
                            <flux:text class="font-medium">{{ number_format($order->total_amount, 2) }} {{ $order->currency }}</flux:text>
                            @if($order->tax_amount > 0)
                                <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">
                                    Base: {{ number_format($order->base_amount, 2) }} + Tax: {{ number_format($order->tax_amount, 2) }} ({{ $order->tax_rate }}%)
                                </flux:text>
                            @endif
                            @if($order->reverse_vat_applied)
                                <flux:badge size="xs" color="purple">Reverse VAT</flux:badge>
                            @endif
                        </div>
                    </flux:table.cell>

                    <flux:table.cell>
                        @switch($order->status)
                            @case('draft')
                                <flux:badge color="gray" size="sm">Draft</flux:badge>
                                @break
                            @case('published')
                                <flux:badge color="blue" size="sm">Published</flux:badge>
                                @break
                            @case('pending')
                                <flux:badge color="yellow" size="sm">Pending</flux:badge>
                                @break
                            @case('processing')
                                <flux:badge color="blue" size="sm">Processing</flux:badge>
                                @break
                            @case('paid')
                                <flux:badge color="green" size="sm">
                                    <flux:icon name="check-circle" class="h-3 w-3 mr-1" />
                                    Paid
                                </flux:badge>
                                @break
                            @case('failed')
                                <flux:badge color="red" size="sm">Failed</flux:badge>
                                @break
                            @case('cancelled')
                                <flux:badge color="orange" size="sm">Cancelled</flux:badge>
                                @break
                            @case('refunded')
                                <flux:badge color="purple" size="sm">Refunded</flux:badge>
                                @break
                        @endswitch

                        @if($order->paid_at)
                            <flux:text size="xs" class="text-zinc-500 dark:text-zinc-400 mt-1">
                                Paid: {{ $order->paid_at->format('M d, Y') }}
                            </flux:text>
                        @endif
                    </flux:table.cell>

                    <flux:table.cell>
                        <flux:dropdown align="end">
                            <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom" />

                            <flux:menu>
                                <flux:menu.item
                                    :href="route('admin.billing.orders.show', $order)"
                                    wire:navigate
                                    icon="eye"
                                >
                                    View Order
                                </flux:menu.item>

                                @if($order->is_business && $order->customer_vat_number && !$order->vat_number_validated)
                                    <flux:menu.item
                                        wire:click="validateVatNumber({{ $order->id }})"
                                        icon="shield-check"
                                    >
                                        Validate VAT
                                    </flux:menu.item>
                                @endif

                                <flux:menu.separator />

                                <!-- Invoice Actions -->
                                @if($order->status === 'published' && !$order->proforma_invoice_generated)
                                    <flux:menu.item
                                        wire:click="generateProformaInvoice({{ $order->id }})"
                                        icon="document-text"
                                        variant="primary"
                                    >
                                        Generate Proforma Invoice
                                    </flux:menu.item>
                                @endif

                                @if($order->status === 'paid' && !$order->final_invoice_generated)
                                    <flux:menu.item
                                        wire:click="generateFinalInvoice({{ $order->id }})"
                                        icon="document-check"
                                        variant="primary"
                                    >
                                        Generate Final Invoice
                                    </flux:menu.item>
                                @endif

                                @if($order->proforma_invoice_generated || $order->final_invoice_generated)
                                    <flux:menu.item
                                        wire:click="downloadInvoice({{ $order->id }})"
                                        icon="arrow-down-tray"
                                    >
                                        Download Invoice
                                    </flux:menu.item>
                                @endif

                                <!-- Status Actions -->
                                @if($order->status === 'draft')
                                    <flux:menu.item
                                        wire:click="publishOrder({{ $order->id }})"
                                        icon="paper-airplane"
                                    >
                                        Publish Order
                                    </flux:menu.item>
                                    <flux:menu.item
                                        :href="route('admin.billing.orders.edit', $order)"
                                        wire:navigate
                                        icon="pencil"
                                    >
                                        Edit Order
                                    </flux:menu.item>
                                @endif

                                @if($order->status === 'published')
                                    <flux:menu.item
                                        wire:click="markAsPaid({{ $order->id }})"
                                        wire:confirm="Mark this order as paid?"
                                        icon="check-circle"
                                    >
                                        Mark as Paid
                                    </flux:menu.item>
                                @endif

                                @if(in_array($order->status, ['published']))
                                    <flux:menu.item
                                        wire:click="cancelOrder({{ $order->id }})"
                                        wire:confirm="Are you sure you want to cancel this order?"
                                        icon="x-circle"
                                        variant="danger"
                                    >
                                        Cancel Order
                                    </flux:menu.item>
                                @endif

                                <flux:menu.separator />

                                @if(in_array($order->status, ['draft', 'cancelled']))
                                    <flux:menu.item
                                        wire:click="deleteOrder({{ $order->id }})"
                                        wire:confirm="Are you sure you want to delete this order? This action cannot be undone."
                                        icon="trash"
                                        variant="danger"
                                    >
                                        Delete Order
                                    </flux:menu.item>
                                @endif

                            </flux:menu>
                        </flux:dropdown>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="6">
                        <div class="text-center py-12 flex flex-col items-center">
                            <flux:icon name="document-text" class="h-12 w-12 text-zinc-400 mb-4" />
                            <flux:heading size="lg">No orders found</flux:heading>
                            <flux:text class="text-zinc-500 dark:text-zinc-400 mt-1">Get started by creating your first order</flux:text>
                            <div class="mt-4">
                                <flux:button :href="route('admin.billing.orders.create')" wire:navigate variant="primary" icon="plus" color="green">
                                    Create Order
                                </flux:button>
                            </div>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</div>
