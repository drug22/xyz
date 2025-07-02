<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Settings;
use TCPDF;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;

class InvoicePdfService
{
    public function generatePdf(Invoice $invoice, bool $save = true): string
    {
        $data = $this->prepareInvoiceData($invoice);

        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8');

        // PDF metadata
        $pdf->SetCreator('HazWatch360');
        $pdf->SetAuthor('HazWatch360');
        $pdf->SetTitle('Invoice #' . $invoice->invoice_number);

        // Disable header and footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Set margins
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(true, 15);

        // Add page
        $pdf->AddPage();

        // Set font
        $pdf->SetFont('helvetica', '', 10);

        // Generate HTML content
        $html = View::make('pdfs.invoice-pdf', $data)->render();

        // Write HTML
        $pdf->writeHTML($html, true, false, true, false, '');

        if ($save) {
            $filename = $this->generateFilename($invoice);
            $path = "invoices/{$filename}";

            // Save PDF to storage
            Storage::disk('public')->put($path, $pdf->Output('', 'S'));

            // Update invoice with PDF path
            $invoice->update([
                'pdf_path' => $path,
                'pdf_generated_at' => now(),
            ]);

            return $path;
        }

        return $pdf->Output('', 'S');
    }

    public function downloadPdf(Invoice $invoice): \Symfony\Component\HttpFoundation\Response
    {
        // Generate PDF if not exists
        if (!$invoice->pdf_path || !Storage::disk('public')->exists($invoice->pdf_path)) {
            $this->generatePdf($invoice);
        }

        $filename = $this->generateFilename($invoice);

        return response()->download(
            Storage::disk('public')->path($invoice->pdf_path),
            $filename
        );
    }

    public function streamPdf(Invoice $invoice): \Symfony\Component\HttpFoundation\Response
    {
        $data = $this->prepareInvoiceData($invoice);
        $filename = $this->generateFilename($invoice);

        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4'
        ]);

        // FOLOSESC TEMPLATE-UL HETZNER STYLE
        $html = View::make('pdfs.invoice-pdf', $data)->render();
        $mpdf->WriteHTML($html);

        return response()->streamDownload(function () use ($mpdf) {
            echo $mpdf->Output('', 'S');
        }, $filename);
    }


    private function prepareInvoiceData(Invoice $invoice): array
    {
        $invoice->load(['order.package', 'company', 'creator', 'customerCountry']);

        // Company details from settings
        $companyDetails = [
            'name' => Settings::get('company_name_original', 'Your Company'),
            'address' => Settings::get('company_address', ''),
            'city' => Settings::get('company_city', ''),
            'postal_code' => Settings::get('company_postal_code', ''),
            'country' => Settings::get('company_country_name', ''),
            'phone' => Settings::get('company_phone', ''),
            'email' => Settings::get('company_email', ''),
            'website' => Settings::get('company_website', ''),
            'vat_number' => Settings::get('company_vat_number', ''),
            'registration_number' => Settings::get('company_registration_number', ''),
        ];

        return [
            'invoice' => $invoice,
            'company' => $companyDetails,
            'logo_path' => Settings::get('company_logo', ''),
        ];
    }

    public function generateFilename(Invoice $invoice): string
    {
        $type = $invoice->isProforma() ? 'Proforma' : 'Invoice';
        return "{$type}_{$invoice->invoice_number}.pdf";
    }
}
