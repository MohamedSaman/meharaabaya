<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductDetail;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductExportController extends Controller
{
    /**
     * Export products to CSV file (memory efficient)
     * 
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function export(Request $request)
    {
        try {
            // Get search parameter from session or request
            $search = session('products_export_search', null);

            // If not in session, try to get from request
            if (!$search && $request->has('search')) {
                $search = $request->input('search');
            }

            // Clear the session data
            session()->forget('products_export_search');

            // Generate filename with timestamp
            $fileName = 'products_export_' . now()->format('Y-m-d_His') . '.csv';

            // Log the export attempt
            Log::info('Exporting products to CSV', [
                'search' => $search,
                'filename' => $fileName,
                'user_id' => auth()->id() ?? 'guest'
            ]);

            // Create streamed response (memory efficient)
            $response = new StreamedResponse(function () use ($search) {
                $handle = fopen('php://output', 'w');

                // Add BOM for Excel UTF-8 compatibility
                fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

                // Write headers
                fputcsv($handle, [
                    'No.',
                    'Product Code',
                    'Product Name',
                    'Available Stock',
                    'Damage Stock',
                    'Cost Price',
                    'Selling Price'
                ]);

                // Query products in chunks to save memory
                $query = ProductDetail::join('product_prices', 'product_details.id', '=', 'product_prices.product_id')
                    ->join('product_stocks', 'product_details.id', '=', 'product_stocks.product_id')
                    ->select(
                        'product_details.id',
                        'product_details.code',
                        'product_details.name',
                        'product_stocks.available_stock',
                        'product_stocks.damage_stock',
                        'product_prices.supplier_price',
                        'product_prices.selling_price'
                    );

                if ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('product_details.name', 'like', '%' . $search . '%')
                            ->orWhere('product_details.code', 'like', '%' . $search . '%');
                    });
                }

                $query->orderByRaw("CASE WHEN product_details.code LIKE 'G-%' THEN 1 ELSE 0 END ASC")
                    ->orderBy('product_details.code', 'asc');

                $rowNumber = 0;

                // Process in chunks of 500 to avoid memory issues
                $query->chunk(500, function ($products) use ($handle, &$rowNumber) {
                    foreach ($products as $product) {
                        $rowNumber++;
                        fputcsv($handle, [
                            $rowNumber,
                            $product->code,
                            $product->name,
                            $product->available_stock,
                            $product->damage_stock,
                            number_format($product->supplier_price, 2),
                            number_format($product->selling_price, 2)
                        ]);
                    }

                    // Flush output to free memory
                    flush();
                });

                fclose($handle);
            });

            // Set headers for CSV download
            $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
            $response->headers->set('Content-Disposition', 'attachment; filename="' . $fileName . '"');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');

            return $response;
        } catch (\Exception $e) {
            // Log the detailed error
            Log::error('Product export failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id() ?? 'guest'
            ]);

            // Return user-friendly error response
            return back()->with('error', 'Failed to export products. Please try again.');
        }
    }
}
