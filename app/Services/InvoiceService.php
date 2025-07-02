<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\StripeSettings;
use App\Models\Settings;
use Carbon\Carbon;

class InvoiceService
{
    public function createProformaFromOrder(Order $order): Invoice
    {
        return $this->createInvoiceFromOrder($order, 'proforma');
    }

    public function createFinalFromOrder(Order $order): Invoice
    {
        return $this->createInvoiceFromOrder($order, 'final');
    }

    private function createInvoiceFromOrder(Order $order, string $type): Invoice
    {
        $invoiceNumber = $this->generateInvoiceNumber($type);

        return Invoice::create([
            'invoice_number' => $invoiceNumber,
            'type' => $type,
            'status' => 'draft',
            'order_id' => $order->id,
            'company_id' => $order->company_id,
            'created_by' => auth()->id(),

            // Customer info from order
            'customer_name' => $order->customer_name,
            'customer_email' => $order->customer_email,
            'customer_country' => $order->customer_country,
            'customer_county_code' => $order->customer_county_code ?? null,
            'customer_county_name' => $order->customer_county_name ?? null,
            'customer_address' => $order->customer_address,
            'is_business' => $order->is_business,
            'customer_vat_number' => $order->customer_vat_number,

            // Company details
            'company_details' => [
                'company_name' => $order->metadata['company_name'] ?? null,
                'registration_number' => $order->metadata['company_registration_number'] ?? null,
                'address' => $order->metadata['company_address'] ?? null,
                'phone' => $order->metadata['company_phone'] ?? null,
            ],

            // Financial details from order
            'base_amount' => $order->base_amount,
            'tax_rate' => $order->tax_rate,
            'tax_amount' => $order->tax_amount,
            'total_amount' => $order->total_amount,
            'currency' => $order->currency,
            'reverse_vat_applied' => $order->reverse_vat_applied,
            'tax_note' => $order->tax_note,

            // VAT validation
            'vat_number_validated' => $order->vat_number_validated,
            'vat_validation_result' => $order->vat_validation_result,
            'vat_validated_at' => $order->vat_validated_at,

            // Package details snapshot
            'package_details' => [
                'name' => $order->package->name,
                'description' => $order->package->description,
                'billing_cycle' => $order->billing_cycle,
            ],

            // Dates
            'invoice_date' => now(),
            'due_date' => $this->calculateDueDate($type),

            // Default terms
            'terms' => $this->getDefaultTerms($type),
        ]);
    }


    private function generateInvoiceNumber(string $type): string
    {
        $stripeSettings = StripeSettings::first();

        if ($type === 'proforma') {
            $prefix = $stripeSettings->proforma_prefix ?? 'PRO';
            $nextNumber = $stripeSettings->proforma_next_number ?? 1;

            // Update next number
            $stripeSettings->increment('proforma_next_number');
        } else {
            $prefix = $stripeSettings->invoice_prefix ?? 'INV';
            $nextNumber = $stripeSettings->invoice_next_number ?? 1;

            // Update next number
            $stripeSettings->increment('invoice_next_number');
        }

        $year = now()->year;
        $formattedNumber = str_pad($nextNumber, 6, '0', STR_PAD_LEFT);

        return "{$prefix}-{$year}-{$formattedNumber}";
    }

    private function calculateDueDate(string $type): Carbon
    {
        // Proforma: 15 days, Final: 30 days
        $days = $type === 'proforma' ? 15 : 30;
        return now()->addDays($days);
    }

    private function getDefaultTerms(string $type): string
    {
        if ($type === 'proforma') {
            return 'This is a proforma invoice. Payment is required before service delivery.';
        }

        return 'Payment is due within 30 days of invoice date. Late payments may incur additional charges.';
    }
}
