<div>
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl">Order #{{ $order->order_number }}</flux:heading>
                <flux:text class="mt-2">Created {{ $order->created_at->format('M d, Y H:i') }}</flux:text>
            </div>

            <div class="flex gap-3">
                <flux:button :href="route('admin.billing.orders.index')" wire:navigate variant="outline" icon="arrow-left">
                    Back to Orders
                </flux:button>

                @if($order->is_business && $order->customer_vat_number)
                    <flux:button wire:click="validateVatNumber" variant="outline" icon="shield-check">
                        Validate VAT
                    </flux:button>
                @endif

                @if($order->status === 'pending')
                    <flux:button wire:click="markAsPaid" variant="primary" icon="check-circle">
                        Mark as Paid
                    </flux:button>
                @endif

                @if(in_array($order->status, ['draft', 'pending']))
                    <flux:button wire:click="cancelOrder" variant="danger" icon="x-circle">
                        Cancel
                    </flux:button>
                @endif

                @if($order->canRefund())
                    <flux:button wire:click="refundOrder" variant="outline" icon="arrow-uturn-left">
                        Refund
                    </flux:button>
                @endif
            </div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <!-- Main Order Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Order Status -->
            <flux:card>
                <div class="space-y-4">
                    <flux:heading size="lg" icon="clipboard-document-check">Order Status</flux:heading>

                    <div class="flex items-center justify-between">
                        <div>
                            @switch($order->status)
                                @case('draft')
                                    <flux:badge color="gray" size="lg">Draft</flux:badge>
                                    @break
                                @case('pending')
                                    <flux:badge color="yellow" size="lg">Pending Payment</flux:badge>
                                    @break
                                @case('processing')
                                    <flux:badge color="blue" size="lg">Processing</flux:badge>
                                    @break
                                @case('paid')
                                    <flux:badge color="green" size="lg">
                                        <flux:icon name="check-circle" class="h-4 w-4 mr-1" />
                                        Paid
                                    </flux:badge>
                                    @break
                                @case('failed')
                                    <flux:badge color="red" size="lg">Payment Failed</flux:badge>
                                    @break
                                @case('cancelled')
                                    <flux:badge color="orange" size="lg">Cancelled</flux:badge>
                                    @break
                                @case('refunded')
                                    <flux:badge color="purple" size="lg">Refunded</flux:badge>
                                    @break
                            @endswitch
                        </div>

                        @if($order->paid_at)
                            <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">
                                Paid on {{ $order->paid_at->format('M d, Y H:i') }}
                            </flux:text>
                        @endif
                    </div>

                    @if($order->stripe_payment_intent_id)
                        <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                            <flux:text size="sm" class="text-blue-700 dark:text-blue-300">
                                <strong>Stripe Payment Intent:</strong> {{ $order->stripe_payment_intent_id }}
                            </flux:text>
                        </div>
                    @endif
                </div>
            </flux:card>

            <!-- Customer Information -->
            <flux:card>
                <div class="space-y-4">
                    <flux:heading size="lg" icon="user">Customer Information</flux:heading>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <flux:text class="font-medium">{{ $order->customer_name }}</flux:text>
                            <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">{{ $order->customer_email }}</flux:text>
                        </div>

                        <div>
                            <flux:text class="font-medium">{{ $order->customerCountry->name ?? $order->customer_country }}</flux:text>
                            <div class="flex gap-1 mt-1">
                                @if($order->is_business)
                                    <flux:badge size="sm" color="purple">Business Customer</flux:badge>
                                @else
                                    <flux:badge size="sm" color="gray">Individual Customer</flux:badge>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($order->customer_address && array_filter($order->customer_address))
                        <div>
                            <flux:text class="font-medium">Address:</flux:text>
                            <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">
                                {{ implode(', ', array_filter($order->customer_address)) }}
                            </flux:text>
                        </div>
                    @endif
                </div>
            </flux:card>

            <!-- Business Information -->
            @if($order->is_business)
                <flux:card>
                    <div class="space-y-4">
                        <flux:heading size="lg" icon="building-office">Business Information</flux:heading>

                        @if($order->metadata && isset($order->metadata['company_name']))
                            <div>
                                <flux:text class="font-medium">Company Name:</flux:text>
                                <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">{{ $order->metadata['company_name'] }}</flux:text>
                            </div>
                        @endif

                        @if($order->metadata && isset($order->metadata['company_registration_number']))
                            <div>
                                <flux:text class="font-medium">Registration Number:</flux:text>
                                <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">{{ $order->metadata['company_registration_number'] }}</flux:text>
                            </div>
                        @endif

                        @if($order->customer_vat_number)
                            <div class="p-3 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <flux:text class="font-medium">VAT Number: {{ $order->customer_vat_number }}</flux:text>
                                        @if($order->vat_number_validated)
                                            <flux:badge size="sm" color="green" class="mt-1">
                                                <flux:icon name="check-circle" class="h-3 w-3 mr-1" />
                                                Validated
                                            </flux:badge>
                                            @if($order->vat_validated_at)
                                                <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400 mt-1">
                                                    Validated on {{ $order->vat_validated_at->format('M d, Y H:i') }}
                                                </flux:text>
                                            @endif
                                        @else
                                            <flux:badge size="sm" color="orange" class="mt-1">
                                                <flux:icon name="exclamation-triangle" class="h-3 w-3 mr-1" />
                                                Not Validated
                                            </flux:badge>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($order->metadata && isset($order->metadata['company_address']))
                            <div>
                                <flux:text class="font-medium">Company Address:</flux:text>
                                <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">{{ $order->metadata['company_address'] }}</flux:text>
                            </div>
                        @endif

                        @if($order->metadata && isset($order->metadata['company_phone']))
                            <div>
                                <flux:text class="font-medium">Company Phone:</flux:text>
                                <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">{{ $order->metadata['company_phone'] }}</flux:text>
                            </div>
                        @endif
                    </div>
                </flux:card>
            @endif

            <!-- Package & Pricing -->
            <flux:card>
                <div class="space-y-4">
                    <flux:heading size="lg" icon="cube">Package & Pricing</flux:heading>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <flux:text class="font-medium">{{ $order->package->name }}</flux:text>
                            <flux:badge color="{{ $order->billing_cycle === 'yearly' ? 'green' : 'blue' }}">
                                {{ ucfirst($order->billing_cycle) }} Billing
                            </flux:badge>
                        </div>

                        <div class="text-right">
                            <flux:text class="text-2xl font-bold">{{ number_format($order->total_amount, 2) }} {{ $order->currency }}</flux:text>
                        </div>
                    </div>

                    <div class="border-t pt-4">
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <flux:text>Base Amount:</flux:text>
                                <flux:text>{{ number_format($order->base_amount, 2) }} {{ $order->currency }}</flux:text>
                            </div>

                            @if($order->tax_amount > 0)
                                <div class="flex justify-between">
                                    <flux:text>TAX ({{ $order->tax_rate }}%):</flux:text>
                                    <flux:text>{{ number_format($order->tax_amount, 2) }} {{ $order->currency }}</flux:text>
                                </div>
                            @endif

                            @if($order->reverse_vat_applied)
                                <div class="flex justify-between text-purple-600">
                                    <flux:text>Reverse VAT Applied:</flux:text>
                                    <flux:text>Customer liable for VAT</flux:text>
                                </div>
                            @endif

                            <div class="flex justify-between font-bold text-lg border-t pt-2">
                                <flux:text>Total:</flux:text>
                                <flux:text>{{ number_format($order->total_amount, 2) }} {{ $order->currency }}</flux:text>
                            </div>
                        </div>
                    </div>

                    @if($order->tax_note)
                        <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                            <flux:text size="sm" class="text-blue-700 dark:text-blue-300">
                                <strong>Tax Note:</strong> {{ $order->tax_note }}
                            </flux:text>
                        </div>
                    @endif
                </div>
            </flux:card>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Live Tax Calculation -->
            @if(count($taxCalculation) > 0)
                <flux:card>
                    <div class="space-y-4">
                        <flux:heading size="lg" icon="calculator">Live Tax Calculation</flux:heading>

                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <flux:text size="sm">Base Amount:</flux:text>
                                <flux:text size="sm">{{ number_format($taxCalculation['amount'], 2) }} {{ $order->currency }}</flux:text>
                            </div>

                            <div class="flex justify-between">
                                <flux:text size="sm">Tax Rate:</flux:text>
                                <flux:text size="sm">{{ $taxCalculation['tax_rate'] }}%</flux:text>
                            </div>

                            <div class="flex justify-between">
                                <flux:text size="sm">Tax Amount:</flux:text>
                                <flux:text size="sm">{{ number_format($taxCalculation['tax_amount'], 2) }} {{ $order->currency }}</flux:text>
                            </div>

                            <div class="flex justify-between font-bold border-t pt-2">
                                <flux:text size="sm">Total:</flux:text>
                                <flux:text size="sm">{{ number_format($taxCalculation['total_amount'], 2) }} {{ $order->currency }}</flux:text>
                            </div>
                        </div>

                        @if($taxCalculation['reverse_vat_applied'])
                            <flux:badge color="purple" size="sm">Reverse VAT Applied</flux:badge>
                        @endif

                        @if($taxCalculation['tax_note'])
                            <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">{{ $taxCalculation['tax_note'] }}</flux:text>
                        @endif

                        @if($taxCalculation['total_amount'] != $order->total_amount)
                            <flux:button wire:click="updateTaxCalculation" variant="primary" size="sm" class="w-full">
                                Update Order with New Tax Calculation
                            </flux:button>
                        @endif
                    </div>
                </flux:card>
            @endif

            <!-- Order Timeline -->
            <flux:card>
                <div class="space-y-4">
                    <flux:heading size="lg" icon="clock">Order Timeline</flux:heading>

                    <div class="space-y-3">
                        <div class="flex items-center gap-3">
                            <flux:icon name="document-plus" class="h-4 w-4 text-blue-500" />
                            <div>
                                <flux:text size="sm" class="font-medium">Order Created</flux:text>
                                <flux:text size="xs" class="text-zinc-500 dark:text-zinc-400">{{ $order->created_at->format('M d, Y H:i') }}</flux:text>
                                @if($order->creator)
                                    <flux:text size="xs" class="text-zinc-500 dark:text-zinc-400">by {{ $order->creator->name }}</flux:text>
                                @endif
                            </div>
                        </div>

                        @if($order->vat_validated_at)
                            <div class="flex items-center gap-3">
                                <flux:icon name="shield-check" class="h-4 w-4 text-green-500" />
                                <div>
                                    <flux:text size="sm" class="font-medium">VAT Validated</flux:text>
                                    <flux:text size="xs" class="text-zinc-500 dark:text-zinc-400">{{ $order->vat_validated_at->format('M d, Y H:i') }}</flux:text>
                                </div>
                            </div>
                        @endif

                        @if($order->paid_at)
                            <div class="flex items-center gap-3">
                                <flux:icon name="check-circle" class="h-4 w-4 text-green-500" />
                                <div>
                                    <flux:text size="sm" class="font-medium">Payment Received</flux:text>
                                    <flux:text size="xs" class="text-zinc-500 dark:text-zinc-400">{{ $order->paid_at->format('M d, Y H:i') }}</flux:text>
                                </div>
                            </div>
                        @endif

                        @if($order->metadata && isset($order->metadata['assigned_to_user_id']))
                            <div class="flex items-center gap-3">
                                <flux:icon name="user-plus" class="h-4 w-4 text-purple-500" />
                                <div>
                                    <flux:text size="sm" class="font-medium">Assigned to User</flux:text>
                                    <flux:text size="xs" class="text-zinc-500 dark:text-zinc-400">User ID: {{ $order->metadata['assigned_to_user_id'] }}</flux:text>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </flux:card>

            <!-- VAT Validation Result -->
            @if($order->vat_validation_result)
                <flux:card>
                    <div class="space-y-4">
                        <flux:heading size="lg" icon="shield-check">VAT Validation Result</flux:heading>

                        <div class="space-y-2">
                            @foreach($order->vat_validation_result as $key => $value)
                                @if($value)
                                    <div class="flex justify-between">
                                        <flux:text size="sm" class="capitalize">{{ str_replace('_', ' ', $key) }}:</flux:text>
                                        <flux:text size="sm">{{ is_bool($value) ? ($value ? 'Yes' : 'No') : $value }}</flux:text>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </flux:card>
            @endif
        </div>
    </div>
</div>
