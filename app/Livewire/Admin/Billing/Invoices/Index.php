<?php

namespace App\Livewire\Admin\Billing\Invoices;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\Country;
use App\Services\InvoiceService;
use Livewire\Component;
use Livewire\WithPagination;
use Flux\Flux;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $typeFilter = '';
    public $statusFilter = '';
    public $countryFilter = '';
    public $businessFilter = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingTypeFilter()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingCountryFilter()
    {
        $this->resetPage();
    }

    public function updatingBusinessFilter()
    {
        $this->resetPage();
    }

    public function createProformaInvoice($orderId)
    {
        $order = Order::findOrFail($orderId);

        if ($order->hasProformaInvoice()) {
            Flux::toast(
                heading: 'Invoice Exists',
                text: 'Proforma invoice already exists for this order.',
                variant: 'warning'
            );
            return;
        }

        $invoiceService = app(InvoiceService::class);
        $invoice = $invoiceService->createProformaFromOrder($order);

        Flux::toast(
            heading: 'Proforma Created',
            text: "Proforma invoice #{$invoice->invoice_number} created successfully.",
            variant: 'success'
        );

        return redirect()->route('admin.billing.invoices.show', $invoice);
    }

    public function createFinalInvoice($orderId)
    {
        $order = Order::findOrFail($orderId);

        if ($order->hasFinalInvoice()) {
            Flux::toast(
                heading: 'Invoice Exists',
                text: 'Final invoice already exists for this order.',
                variant: 'warning'
            );
            return;
        }

        $invoiceService = app(InvoiceService::class);
        $invoice = $invoiceService->createFinalFromOrder($order);

        Flux::toast(
            heading: 'Invoice Created',
            text: "Final invoice #{$invoice->invoice_number} created successfully.",
            variant: 'success'
        );

        return redirect()->route('admin.billing.invoices.show', $invoice);
    }

    public function markAsSent($invoiceId)
    {
        $invoice = Invoice::findOrFail($invoiceId);

        if ($invoice->status !== 'draft') {
            Flux::toast(
                heading: 'Cannot Send',
                text: 'Only draft invoices can be marked as sent.',
                variant: 'warning'
            );
            return;
        }

        $invoice->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        Flux::toast(
            heading: 'Invoice Sent',
            text: "Invoice #{$invoice->invoice_number} marked as sent.",
            variant: 'success'
        );
    }

    public function markAsPaid($invoiceId)
    {
        $invoice = Invoice::findOrFail($invoiceId);

        $invoice->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        // Update order status if final invoice
        if ($invoice->isFinal()) {
            $invoice->order->update(['status' => 'paid']);
        }

        Flux::toast(
            heading: 'Invoice Paid',
            text: "Invoice #{$invoice->invoice_number} marked as paid.",
            variant: 'success'
        );
    }

    public function deleteInvoice($invoiceId)
    {
        $invoice = Invoice::findOrFail($invoiceId);

        if (!$invoice->canDelete()) {
            Flux::toast(
                heading: 'Cannot Delete',
                text: 'Only draft invoices that haven\'t been sent can be deleted.',
                variant: 'warning'
            );
            return;
        }

        $invoiceNumber = $invoice->invoice_number;
        $invoice->delete();

        Flux::toast(
            heading: 'Invoice Deleted',
            text: "Invoice #{$invoiceNumber} has been deleted.",
            variant: 'success'
        );
    }

    public function render()
    {
        $query = Invoice::query()
            ->with(['order', 'company', 'creator', 'customerCountry'])
            ->latest();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('invoice_number', 'like', '%' . $this->search . '%')
                    ->orWhere('customer_email', 'like', '%' . $this->search . '%')
                    ->orWhere('customer_name', 'like', '%' . $this->search . '%')
                    ->orWhere('customer_vat_number', 'like', '%' . $this->search . '%')
                    ->orWhereJsonContains('company_details->company_name', $this->search);
            });
        }

        if ($this->typeFilter) {
            $query->where('type', $this->typeFilter);
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->countryFilter) {
            $query->where('customer_country', $this->countryFilter);
        }

        if ($this->businessFilter !== '') {
            $query->where('is_business', (bool) $this->businessFilter);
        }

        $invoices = $query->paginate(15);
        $countries = Country::where('is_active', true)->orderBy('name')->get();

        // Get orders without proforma invoices for quick creation
        $ordersWithoutProforma = Order::whereDoesntHave('invoices', function($q) {
            $q->where('type', 'proforma');
        })->where('status', 'published')->take(5)->get();

        return view('livewire.admin.billing.invoices.index', [
            'invoices' => $invoices,
            'countries' => $countries,
            'ordersWithoutProforma' => $ordersWithoutProforma,
        ])->layout('components.layouts.admin');
    }
}
