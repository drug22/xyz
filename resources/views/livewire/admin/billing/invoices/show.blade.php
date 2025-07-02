<div>
    <!-- Header Section -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl">Invoice #{{ $invoice->invoice_number }}</flux:heading>
                <flux:text class="mt-2">
                    {{ $invoice->isProforma() ? 'Proforma' : 'Final' }} invoice created {{ $invoice->created_at->format('M d, Y H:i') }}
                </flux:text>
            </div>
            <div class="flex gap-2">
                @if($invoice->canEdit())
                    <flux:button :href="route('admin.billing.invoices.edit', $invoice)" wire:navigate variant="outline" icon="pencil">
                        Edit
                    </flux:button>
                @endif

                @if($invoice->canSend())
                    <flux:button wire:click="markAsSent" wire:confirm="Mark this invoice as sent?" variant="outline" icon="paper-airplane">
                        Mark as Sent
                    </flux:button>
                @endif

                @if(!$invoice->isPaid())
                    <flux:button wire:click="markAsPaid" wire:confirm="Mark this invoice as paid?" variant="primary" icon="check-circle">
                        Mark as Paid
                    </flux:button>
                @endif
                <flux:button wire:click="sendEmail" variant="outline" icon="envelope">
                    Send Email
                </flux:button>
                @if($invoice->isProforma())
                    <flux:button wire:click="createFinalInvoice" variant="outline" icon="document-check">
                        Create Final Invoice
                    </flux:button>
                @endif

                <flux:button wire:click="downloadPdf" variant="outline" icon="arrow-down-tray">
                    Download PDF
                </flux:button>

            </div>
        </div>
    </div>

    <!-- Status & Type Badges -->
    <div class="mb-6 flex gap-2">
        @if($invoice->isProforma())
            <flux:badge color="blue" size="lg" icon="document-text">Proforma Invoice</flux:badge>
        @else
            <flux:badge color="green" size="lg" icon="document-check">Final Invoice</flux:badge>
        @endif

        @switch($invoice->status)
            @case('draft')
                <flux:badge color="gray" size="lg">Draft</flux:badge>
                @break
            @case('sent')
                <flux:badge color="blue" size="lg">Sent</flux:badge>
                @break
            @case('paid')
                <flux:badge color="green" size="lg" icon="check-circle">Paid</flux:badge>
                @break
            @case('overdue')
                <flux:badge color="red" size="lg">Overdue</flux:badge>
                @break
        @endswitch

        @if($invoice->vat_number_validated)
            <flux:badge color="green" size="lg">VAT Validated</flux:badge>
        @endif
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <!-- Main Invoice Details -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Invoice Information -->
            <flux:card>
                <div class="space-y-6">
                    <flux:heading size="lg" icon="document-text">Invoice Details</flux:heading>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <flux:text class="font-medium">Invoice Number</flux:text>
                            <flux:text class="text-zinc-500 dark:text-zinc-400">{{ $invoice->invoice_number }}</flux:text>
                        </div>

                        @if($invoice->order)
                            <div>
                                <flux:text class="font-medium">Related Order</flux:text>
                                <flux:text class="text-zinc-500 dark:text-zinc-400">
                                    <a href="{{ route('admin.billing.orders.show', $invoice->order) }}" class="text-blue-600 hover:underline">
                                        {{ $invoice->order->order_number }}
                                    </a>
                                </flux:text>
                            </div>
                        @else
                            <div>
                                <flux:text class="font-medium">Invoice Type</flux:text>
                                <flux:text class="text-zinc-500 dark:text-zinc-400">Manual Invoice</flux:text>
                            </div>
                        @endif

                        <div>
                            <flux:text class="font-medium">Invoice Date</flux:text>
                            <flux:text class="text-zinc-500 dark:text-zinc-400">{{ $invoice->invoice_date->format('M d, Y') }}</flux:text>
                        </div>

                        <div>
                            <flux:text class="font-medium">Due Date</flux:text>
                            <flux:text class="{{ $invoice->isOverdue() ? 'text-red-600' : 'text-zinc-500 dark:text-zinc-400' }}">
                                {{ $invoice->due_date->format('M d, Y') }}
                                @if($invoice->isOverdue())
                                    ({{ $invoice->due_date->diffForHumans() }})
                                @endif
                            </flux:text>
                        </div>

                        @if($invoice->sent_at)
                            <div>
                                <flux:text class="font-medium">Sent Date</flux:text>
                                <flux:text class="text-zinc-500 dark:text-zinc-400">{{ $invoice->sent_at->format('M d, Y H:i') }}</flux:text>
                            </div>
                        @endif

                        @if($invoice->paid_at)
                            <div>
                                <flux:text class="font-medium">Paid Date</flux:text>
                                <flux:text class="text-green-600">{{ $invoice->paid_at->format('M d, Y H:i') }}</flux:text>
                            </div>
                        @endif
                    </div>
                </div>
            </flux:card>

            <!-- Customer Information -->
            <flux:card>
                <div class="space-y-6">
                    <flux:heading size="lg" icon="user">Customer Information</flux:heading>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <flux:text class="font-medium">Customer Name</flux:text>
                            <flux:text class="text-zinc-500 dark:text-zinc-400">{{ $invoice->customer_name }}</flux:text>
                        </div>

                        <div>
                            <flux:text class="font-medium">Email</flux:text>
                            <flux:text class="text-zinc-500 dark:text-zinc-400">{{ $invoice->customer_email }}</flux:text>
                        </div>

                        <div>
                            <flux:text class="font-medium">Country</flux:text>
                            <flux:text class="text-zinc-500 dark:text-zinc-400">{{ $invoice->customerCountry->name ?? $invoice->customer_country }}</flux:text>
                        </div>

                        <div>
                            <flux:text class="font-medium">Customer Type</flux:text>
                            <flux:badge size="sm" color="{{ $invoice->is_business ? 'purple' : 'gray' }}">
                                {{ $invoice->is_business ? 'Business (B2B)' : 'Individual (B2C)' }}
                            </flux:badge>
                        </div>
                    </div>
                </div>
            </flux:card>

            <!-- Business Information -->
            @if($invoice->is_business && $invoice->company_details)
                <flux:card>
                    <div class="space-y-6">
                        <flux:heading size="lg" icon="building-office">Business Information</flux:heading>

                        <div class="grid gap-4 sm:grid-cols-2">
                            @if($invoice->company_details['company_name'])
                                <div>
                                    <flux:text class="font-medium">Company Name</flux:text>
                                    <flux:text class="text-zinc-500 dark:text-zinc-400">{{ $invoice->company_details['company_name'] }}</flux:text>
                                </div>
                            @endif

                            @if($invoice->customer_vat_number)
                                <div>
                                    <flux:text class="font-medium">VAT Number</flux:text>
                                    <div class="flex items-center gap-2">
                                        <flux:text class="text-zinc-500 dark:text-zinc-400">{{ $invoice->customer_vat_number }}</flux:text>
                                        @if($invoice->vat_number_validated)
                                            <flux:badge size="xs" color="green">Validated</flux:badge>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            @if($invoice->company_details['registration_number'])
                                <div>
                                    <flux:text class="font-medium">Registration Number</flux:text>
                                    <flux:text class="text-zinc-500 dark:text-zinc-400">{{ $invoice->company_details['registration_number'] }}</flux:text>
                                </div>
                            @endif

                            @if($invoice->company_details['phone'])
                                <div>
                                    <flux:text class="font-medium">Phone</flux:text>
                                    <flux:text class="text-zinc-500 dark:text-zinc-400">{{ $invoice->company_details['phone'] }}</flux:text>
                                </div>
                            @endif
                        </div>

                        @if($invoice->company_details['address'])
                            <div>
                                <flux:text class="font-medium">Company Address</flux:text>
                                <flux:text class="text-zinc-500 dark:text-zinc-400">{{ $invoice->company_details['address'] }}</flux:text>
                            </div>
                        @endif
                    </div>
                </flux:card>
            @endif

            <!-- Package Details -->
            <flux:card>
                <div class="space-y-6">
                    <flux:heading size="lg" icon="cube">Package Information</flux:heading>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <flux:text class="font-medium">Package</flux:text>
                            <flux:text class="text-zinc-500 dark:text-zinc-400">{{ $invoice->package_details['name'] ?? 'No package' }}</flux:text>
                        </div>

                        <div>
                            <flux:text class="font-medium">Billing Cycle</flux:text>
                            <flux:badge size="sm" color="{{ ($invoice->package_details['billing_cycle'] ?? 'monthly') === 'yearly' ? 'green' : 'blue' }}">
                                {{ ucfirst($invoice->package_details['billing_cycle'] ?? 'monthly') }}
                            </flux:badge>
                        </div>
                    </div>

                    @if($invoice->package_details['description'] ?? false)
                        <div>
                            <flux:text class="font-medium">Description</flux:text>
                            <flux:text class="text-zinc-500 dark:text-zinc-400">{{ $invoice->package_details['description'] }}</flux:text>
                        </div>
                    @endif
                </div>
            </flux:card>

            <!-- Notes & Terms -->
            @if($invoice->notes || $invoice->terms)
                <flux:card>
                    <div class="space-y-6">
                        <flux:heading size="lg" icon="document-text">Notes & Terms</flux:heading>

                        @if($invoice->notes)
                            <div>
                                <flux:text class="font-medium">Notes</flux:text>
                                <flux:text class="text-zinc-500 dark:text-zinc-400">{{ $invoice->notes }}</flux:text>
                            </div>
                        @endif

                        @if($invoice->terms)
                            <div>
                                <flux:text class="font-medium">Terms & Conditions</flux:text>
                                <flux:text class="text-zinc-500 dark:text-zinc-400">{{ $invoice->terms }}</flux:text>
                            </div>
                        @endif
                    </div>
                </flux:card>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Financial Summary -->
            <flux:card>
                <div class="space-y-6">
                    <flux:heading size="lg" icon="calculator">Financial Summary</flux:heading>

                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <flux:text>Base Amount:</flux:text>
                            <flux:text>{{ number_format($invoice->base_amount, 2) }} {{ $invoice->currency }}</flux:text>
                        </div>

                        @if($invoice->tax_amount > 0)
                            <div class="flex justify-between">
                                <flux:text>Tax ({{ $invoice->tax_rate }}%):</flux:text>
                                <flux:text>{{ number_format($invoice->tax_amount, 2) }} {{ $invoice->currency }}</flux:text>
                            </div>
                        @endif

                        @if($invoice->reverse_vat_applied)
                            <div class="flex justify-between">
                                <flux:text>Reverse VAT:</flux:text>
                                <flux:badge size="xs" color="purple">Applied</flux:badge>
                            </div>
                        @endif

                        <div class="flex justify-between font-bold text-lg border-t pt-3">
                            <flux:text>Total Amount:</flux:text>
                            <flux:text>{{ $invoice->formatted_total }}</flux:text>
                        </div>
                    </div>

                    @if($invoice->tax_note)
                        <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                            <flux:text size="sm" class="text-blue-700 dark:text-blue-300">
                                <strong>Tax Note:</strong> {{ $invoice->tax_note }}
                            </flux:text>
                        </div>
                    @endif
                </div>
            </flux:card>

            <!-- Related Invoices - DOAR PENTRU FACTURILE CU ORDER -->
            @if($invoice->order && $invoice->order->invoices->count() > 1)
                <flux:card>
                    <div class="space-y-4">
                        <flux:heading size="lg" icon="document-duplicate">Related Invoices</flux:heading>

                        @foreach($invoice->order->invoices->where('id', '!=', $invoice->id) as $relatedInvoice)
                            <div class="p-3 border rounded-lg">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <flux:text class="font-medium">{{ $relatedInvoice->invoice_number }}</flux:text>
                                        <flux:text size="sm" class="text-zinc-500">{{ $relatedInvoice->isProforma() ? 'Proforma' : 'Final' }}</flux:text>
                                    </div>
                                    <flux:badge size="sm" color="{{ $relatedInvoice->status === 'paid' ? 'green' : 'blue' }}">
                                        {{ ucfirst($relatedInvoice->status) }}
                                    </flux:badge>
                                </div>
                                <div class="mt-2">
                                    <flux:button :href="route('admin.billing.invoices.show', $relatedInvoice)" wire:navigate size="sm" variant="outline">
                                        View Invoice
                                    </flux:button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </flux:card>
            @endif

            <!-- Activity Log -->
            <flux:card>
                <div class="space-y-4">
                    <flux:heading size="lg" icon="clock">Activity Timeline</flux:heading>

                    <div class="space-y-3">
                        <div class="flex items-start gap-3">
                            <div class="w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
                            <div>
                                <flux:text size="sm" class="font-medium">Invoice Created</flux:text>
                                <flux:text size="sm" class="text-zinc-500">{{ $invoice->created_at->format('M d, Y H:i') }}</flux:text>
                                <flux:text size="sm" class="text-zinc-500">by {{ $invoice->creator->name }}</flux:text>
                            </div>
                        </div>

                        @if($invoice->sent_at)
                            <div class="flex items-start gap-3">
                                <div class="w-2 h-2 bg-green-500 rounded-full mt-2"></div>
                                <div>
                                    <flux:text size="sm" class="font-medium">Invoice Sent</flux:text>
                                    <flux:text size="sm" class="text-zinc-500">{{ $invoice->sent_at->format('M d, Y H:i') }}</flux:text>
                                </div>
                            </div>
                        @endif

                        @if($invoice->paid_at)
                            <div class="flex items-start gap-3">
                                <div class="w-2 h-2 bg-green-500 rounded-full mt-2"></div>
                                <div>
                                    <flux:text size="sm" class="font-medium">Invoice Paid</flux:text>
                                    <flux:text size="sm" class="text-zinc-500">{{ $invoice->paid_at->format('M d, Y H:i') }}</flux:text>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </flux:card>
        </div>
    </div>
</div>
