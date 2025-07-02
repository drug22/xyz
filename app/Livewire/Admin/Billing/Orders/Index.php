<?php

namespace App\Livewire\Admin\Billing\Orders;

use App\Models\Order;
use App\Models\Country;
use App\Models\Package;
use Livewire\Component;
use Livewire\WithPagination;
use Flux\Flux;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $countryFilter = '';
    public $packageFilter = '';
    public $businessFilter = '';

    public function updatingSearch()
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

    public function updatingPackageFilter()
    {
        $this->resetPage();
    }

    public function updatingBusinessFilter()
    {
        $this->resetPage();
    }

    public function validateVatNumber($orderId)
    {
        $order = Order::findOrFail($orderId);

        if (!$order->is_business || !$order->customer_vat_number) {
            Flux::toast(
                heading: 'VAT Validation Error',
                text: 'Order must be business type with VAT number.',
                variant: 'warning'
            );
            return;
        }

        $viesService = app(\App\Services\ViesValidationService::class);
        $result = $viesService->validateVatNumber($order->customer_country, $order->customer_vat_number);

        if ($result['valid']) {
            $order->update([
                'vat_number_validated' => true,
                'vat_validation_result' => $result,
                'vat_validated_at' => now(),
            ]);

            Flux::toast(
                heading: 'VAT Validated',
                text: "VAT number is valid for {$order->customer_name}",
                variant: 'success'
            );
        } else {
            $order->update([
                'vat_number_validated' => false,
                'vat_validation_result' => $result,
                'vat_validated_at' => now(),
            ]);

            Flux::toast(
                heading: 'VAT Invalid',
                text: $result['error'] ?? 'Invalid VAT number',
                variant: 'danger'
            );
        }
    }

    public function generateProformaInvoice($orderId)
    {
        $order = Order::findOrFail($orderId);

        if ($order->status !== 'published') {
            Flux::toast(
                heading: 'Cannot Generate',
                text: 'Order must be published to generate proforma invoice.',
                variant: 'warning'
            );
            return;
        }

        // Generate proforma invoice logic here
        $order->update([
            'proforma_invoice_generated' => true,
            'proforma_invoice_generated_at' => now(),
        ]);

        Flux::toast(
            heading: 'Proforma Generated',
            text: "Proforma invoice generated for order #{$order->order_number}",
            variant: 'success'
        );
    }

    public function generateFinalInvoice($orderId)
    {
        $order = Order::findOrFail($orderId);

        if ($order->status !== 'paid') {
            Flux::toast(
                heading: 'Cannot Generate',
                text: 'Order must be paid to generate final invoice.',
                variant: 'warning'
            );
            return;
        }

        // Generate final invoice logic here
        $order->update([
            'final_invoice_generated' => true,
            'final_invoice_generated_at' => now(),
        ]);

        Flux::toast(
            heading: 'Invoice Generated',
            text: "Final invoice generated for order #{$order->order_number}",
            variant: 'success'
        );
    }

    public function downloadInvoice($orderId)
    {
        $order = Order::findOrFail($orderId);

        // Download invoice logic here
        Flux::toast(
            heading: 'Invoice Download',
            text: "Invoice download started for order #{$order->order_number}",
            variant: 'info'
        );
    }

    public function publishOrder($orderId)
    {
        $order = Order::findOrFail($orderId);

        if ($order->status !== 'draft') {
            Flux::toast(
                heading: 'Cannot Publish',
                text: 'Only draft orders can be published.',
                variant: 'warning'
            );
            return;
        }

        $order->update([
            'status' => 'published',
            'published_at' => now(),
        ]);

        Flux::toast(
            heading: 'Order Published',
            text: "Order #{$order->order_number} is now published and ready for payment.",
            variant: 'success'
        );
    }

    public function markAsPaid($orderId)
    {
        $order = Order::findOrFail($orderId);
        $order->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        Flux::toast(
            heading: 'Order Paid',
            text: "Order #{$order->order_number} marked as paid.",
            variant: 'success'
        );
    }

    public function cancelOrder($orderId)
    {
        $order = Order::findOrFail($orderId);
        $order->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        Flux::toast(
            heading: 'Order Cancelled',
            text: "Order #{$order->order_number} has been cancelled.",
            variant: 'success'
        );
    }

    public function deleteOrder($orderId)
    {
        $order = Order::findOrFail($orderId);

        if (in_array($order->status, ['paid', 'processing'])) {
            Flux::toast(
                heading: 'Cannot Delete',
                text: 'Cannot delete paid or processing orders.',
                variant: 'warning'
            );
            return;
        }

        $orderNumber = $order->order_number;
        $order->delete();

        Flux::toast(
            heading: 'Order Deleted',
            text: "Order #{$orderNumber} has been permanently deleted.",
            variant: 'success'
        );
    }

    public function render()
    {
        $query = Order::query()
            ->with(['package', 'customerCountry', 'company'])
            ->latest();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('order_number', 'like', '%' . $this->search . '%')
                    ->orWhere('customer_email', 'like', '%' . $this->search . '%')
                    ->orWhere('customer_name', 'like', '%' . $this->search . '%')
                    ->orWhere('customer_vat_number', 'like', '%' . $this->search . '%')
                    ->orWhereJsonContains('metadata->company_name', $this->search);
            });
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->countryFilter) {
            $query->where('customer_country', $this->countryFilter);
        }

        if ($this->packageFilter) {
            $query->where('package_id', $this->packageFilter);
        }

        if ($this->businessFilter !== '') {
            $query->where('is_business', (bool) $this->businessFilter);
        }

        $orders = $query->paginate(15);
        $countries = Country::where('is_active', true)->orderBy('name')->get();
        $packages = Package::where('is_active', true)->get();

        return view('livewire.admin.billing.orders.index', [
            'orders' => $orders,
            'countries' => $countries,
            'packages' => $packages,
        ])->layout('components.layouts.admin');
    }
}
