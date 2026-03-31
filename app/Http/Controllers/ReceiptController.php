<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReceiptController extends Controller
{
    public function download($id)
    {
        try {
            // Load the sale with all necessary relationships
            $sale = Sale::with(['customer', 'items.product', 'payments'])->findOrFail($id);

            // Set PDF options for better rendering
            $pdf = Pdf::loadView('receipts.download', compact('sale'))
                ->setPaper('a4')
                ->setOptions([
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => true,
                    'defaultFont' => 'sans-serif'
                ]);

            // Return the PDF as a download
            return $pdf->download('receipt-' . $sale->invoice_number . '.pdf');
        } catch (\Exception $e) {
            Log::error('PDF generation failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }
}
