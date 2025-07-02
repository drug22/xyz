<?php

namespace App\Mail;

use App\Models\Invoice;
use App\Services\InvoicePdfService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Invoice $invoice,
        public string $message = ''
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->invoice->isProforma()
            ? "Proforma Invoice #{$this->invoice->invoice_number}"
            : "Invoice #{$this->invoice->invoice_number}";

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.invoice',
            with: [
                'invoice' => $this->invoice,
                'message' => $this->message,
            ],
        );
    }

    public function attachments(): array
    {
        $pdfService = new InvoicePdfService();

        // Generate PDF if not exists
        if (!$this->invoice->pdf_path) {
            $pdfService->generatePdf($this->invoice);
        }

        $filename = $pdfService->generateFilename($this->invoice);

        return [
            Attachment::fromStorageDisk('public', $this->invoice->pdf_path)
                ->as($filename)
                ->withMime('application/pdf'),
        ];
    }
}
