<?php

namespace App\Livewire\Admin\Billing\Orders;

use App\Models\Order;
use App\Services\TaxCalculationService;
use Livewire\Component;
use Flux\Flux;

class Show extends Component
{
    public Order $order;
    public $taxCalculation = [];

    public function mount(Order $order)
    {
        $this->order = $order;
        $this->recalculateTax();
    }

    public function recalculateTax()
    {
        $taxService = app(TaxCalculationService::class);
        $businessCountry = \App\Models\Settings::get('company_country', 'GB');

        $this->taxCalculation = $taxService->calculateTax(
            $businessCountry,
            $this->order->customer_country,
            $this->order->base_amount,
            $this->order->is_business,
            $this->order->customer_vat_number
        );
    }

    public function validateVatNumber()
    {
        if (!$this->order->is_business || !$this->order->customer_vat_number) {
            Flux::toast(
                heading: 'VAT Validation Error',
                text: 'Order must be business type with VAT number.',
                variant: 'warning'
            );
            return;
        }

        $viesService = app(\App\Services\ViesValidationService::class);
        $result = $viesService->validateVatNumber($this->order->customer_country, $this->order->customer_vat_number);

        if ($result['valid']) {
            $this->order->update([
                'vat_number_validated' => true,
                'vat_validation_result' => $result,
                'vat_validated_at' => now(),
            ]);

            $this->recalculateTax();
            Flux::toast(
                heading: 'VAT Validated',
                text: "VAT number is valid. Tax calculation updated.",
                variant: 'success'
            );
        } else {
            $this->order->update([
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

        $this->order->refresh();
    }

    public function updateTaxCalculation()
    {
        $this->order->update([
            'tax_rate' => $this->taxCalculation['tax_rate'],
            'tax_amount' => $this->taxCalculation['tax_amount'],
            'total_amount' => $this->taxCalculation['total_amount'],
            'reverse_vat_applied' => $this->taxCalculation['reverse_vat_applied'],
            'tax_note' => $this->taxCalculation['tax_note'],
        ]);

        Flux::toast(
            heading: 'Tax Updated',
            text: 'Order tax calculation has been updated.',
            variant: 'success'
        );

        $this->order->refresh();
    }

    public function markAsPaid()
    {
        $this->order->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        Flux::toast(
            heading: 'Order Paid',
            text: "Order #{$this->order->order_number} marked as paid.",
            variant: 'success'
        );

        $this->order->refresh();
    }

    public function cancelOrder()
    {
        $this->order->update(['status' => 'cancelled']);

        Flux::toast(
            heading: 'Order Cancelled',
            text: "Order #{$this->order->order_number} has been cancelled.",
            variant: 'success'
        );

        $this->order->refresh();
    }

    public function refundOrder()
    {
        if (!$this->order->canRefund()) {
            Flux::toast(
                heading: 'Cannot Refund',
                text: 'Order cannot be refunded.',
                variant: 'warning'
            );
            return;
        }

        $this->order->update(['status' => 'refunded']);

        Flux::toast(
            heading: 'Order Refunded',
            text: "Order #{$this->order->order_number} has been refunded.",
            variant: 'success'
        );

        $this->order->refresh();
    }

    public function render()
    {
        return view('livewire.admin.billing.orders.show')
            ->layout('components.layouts.admin');
    }
}
