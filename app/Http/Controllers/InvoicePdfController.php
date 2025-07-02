<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Services\InvoicePdfService;
use Illuminate\Http\Request;

class InvoicePdfController extends Controller
{
    public function __construct(
        private InvoicePdfService $pdfService
    ) {}

    public function download(Invoice $invoice)
    {
        return $this->pdfService->downloadPdf($invoice);
    }

    public function stream(Invoice $invoice)
    {
        return $this->pdfService->streamPdf($invoice);
    }
}
