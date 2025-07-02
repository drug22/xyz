<?php

namespace App\Livewire\Admin\Billing\Invoices;

use App\Models\Invoice;
use App\Services\InvoiceService;
use Livewire\Component;
use Flux\Flux;
use App\Services\InvoicePdfService;
use App\Mail\InvoiceMail;
use Illuminate\Support\Facades\Mail;

class Show extends Component
{
    public Invoice $invoice;

    public function mount(Invoice $invoice)
    {
        $this->invoice = $invoice->load(['order.package', 'company', 'creator', 'customerCountry']);
    }

    public function markAsSent()
    {
        if (!$this->invoice->canSend()) {
            Flux::toast(
                heading: 'Cannot Send',
                text: 'Only draft invoices can be marked as sent.',
                variant: 'warning'
            );
            return;
        }

        try {
            // Trimite emailul automat
            Mail::to($this->invoice->customer_email)
                ->send(new InvoiceMail($this->invoice));

            // Update invoice status și email info
            $recipients = $this->invoice->email_recipients ?? [];
            $recipients[] = [
                'email' => $this->invoice->customer_email,
                'sent_at' => now(),
                'sent_by' => auth()->id(),
                'action' => 'mark_as_sent'
            ];

            $this->invoice->update([
                'status' => 'sent',
                'sent_at' => now(),
                'email_sent_at' => now(),
                'email_recipients' => $recipients,
            ]);

            Flux::toast(
                heading: 'Invoice Sent',
                text: "Invoice #{$this->invoice->invoice_number} marked as sent and emailed to {$this->invoice->customer_email}",
                variant: 'success'
            );
        } catch (\Exception $e) {
            // Doar marchează ca sent fără email dacă mailul eșuează
            $this->invoice->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);

            Flux::toast(
                heading: 'Invoice Sent (Email Failed)',
                text: "Invoice marked as sent but email failed: " . $e->getMessage(),
                variant: 'warning'
            );
        }

        $this->invoice->refresh();
    }

    public function markAsPaid()
    {
        $this->invoice->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        // Update order status if final invoice
        if ($this->invoice->isFinal() && $this->invoice->order) {
            $this->invoice->order->update(['status' => 'paid']);
        }

        Flux::toast(
            heading: 'Invoice Paid',
            text: "Invoice #{$this->invoice->invoice_number} marked as paid.",
            variant: 'success'
        );

        $this->invoice->refresh();
    }

    public function createFinalInvoice()
    {
        if (!$this->invoice->isProforma()) {
            Flux::toast(
                heading: 'Cannot Create',
                text: 'Only proforma invoices can generate final invoices.',
                variant: 'warning'
            );
            return;
        }

        $invoiceService = app(InvoiceService::class);

        if ($this->invoice->order_id) {
            // Create from order
            $finalInvoice = $invoiceService->createFinalFromOrder($this->invoice->order);
        } else {
            // Create manual final invoice based on proforma data
            $finalInvoice = $this->createManualFinalInvoice();
        }

        Flux::toast(
            heading: 'Final Invoice Created',
            text: "Final invoice #{$finalInvoice->invoice_number} created successfully.",
            variant: 'success'
        );

        return redirect()->route('admin.billing.invoices.show', $finalInvoice);
    }

    private function createManualFinalInvoice()
    {
        $stripeSettings = \App\Models\StripeSettings::first();
        $prefix = $stripeSettings->invoice_prefix ?? 'INV';
        $nextNumber = $stripeSettings->invoice_next_number ?? 1;
        $stripeSettings->increment('invoice_next_number');

        $year = now()->year;
        $formattedNumber = str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
        $invoiceNumber = "{$prefix}-{$year}-{$formattedNumber}";

        return Invoice::create([
            'invoice_number' => $invoiceNumber,
            'type' => 'final',
            'status' => 'draft',
            'order_id' => null,
            'company_id' => $this->invoice->company_id,
            'created_by' => auth()->id(),

            // Copy data from proforma
            'customer_name' => $this->invoice->customer_name,
            'customer_email' => $this->invoice->customer_email,
            'customer_country' => $this->invoice->customer_country,
            'customer_address' => $this->invoice->customer_address,
            'is_business' => $this->invoice->is_business,
            'customer_vat_number' => $this->invoice->customer_vat_number,
            'company_details' => $this->invoice->company_details,
            'base_amount' => $this->invoice->base_amount,
            'tax_rate' => $this->invoice->tax_rate,
            'tax_amount' => $this->invoice->tax_amount,
            'total_amount' => $this->invoice->total_amount,
            'currency' => $this->invoice->currency,
            'reverse_vat_applied' => $this->invoice->reverse_vat_applied,
            'tax_note' => $this->invoice->tax_note,
            'vat_number_validated' => $this->invoice->vat_number_validated,
            'vat_validation_result' => $this->invoice->vat_validation_result,
            'vat_validated_at' => $this->invoice->vat_validated_at,
            'package_details' => $this->invoice->package_details,
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'terms' => 'Payment is due within 30 days of invoice date. Late payments may incur additional charges.',
        ]);
    }

    public function downloadPdf()
    {
        $pdfService = app(InvoicePdfService::class);
        return $pdfService->streamPdf($this->invoice);
    }

    public function sendEmail()
    {
        try {
            Mail::to($this->invoice->customer_email)
                ->send(new InvoiceMail($this->invoice));

            // Update invoice
            $recipients = $this->invoice->email_recipients ?? [];
            $recipients[] = [
                'email' => $this->invoice->customer_email,
                'sent_at' => now(),
                'sent_by' => auth()->id(),
            ];

            $this->invoice->update([
                'email_sent_at' => now(),
                'email_recipients' => $recipients,
            ]);

            Flux::toast(
                heading: 'Email Sent',
                text: "Invoice emailed to {$this->invoice->customer_email}",
                variant: 'success'
            );
        } catch (\Exception $e) {
            Flux::toast(
                heading: 'Email Failed',
                text: 'Failed to send email: ' . $e->getMessage(),
                variant: 'danger'
            );
        }

        $this->invoice->refresh();
    }

    public function render()
    {
        return view('livewire.admin.billing.invoices.show')
            ->layout('components.layouts.admin');
    }
}
