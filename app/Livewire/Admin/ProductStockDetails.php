<?php

namespace App\Livewire\Admin;

use Exception;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Models\ProductStock;
use App\Models\ProductDetail;
use App\Livewire\Concerns\WithDynamicLayout;
use Livewire\WithPagination;

#[Title('Product Stock Details')]
class ProductStockDetails extends Component
{
    use WithDynamicLayout , WithPagination;
    public $search;
    public $perPage = 10;

    public function render()
    {
        $ProductStocks = ProductDetail::join('product_stocks', 'product_details.id', '=', 'product_stocks.product_id')
            ->join('brand_lists', 'product_details.brand_id', '=', 'brand_lists.id')
            ->select(
                'product_stocks.*',
                'product_details.name as Product_name',
                'brand_lists.brand_name as Product_brand',
                'product_details.model as Product_model',
                'product_details.code as Product_code',
                'product_details.image as Product_image'
            )
            ->where(function ($query) {
                $query->where('product_details.name', 'like', '%' . $this->search . '%')
                    ->orWhere('product_details.code', 'like', '%' . $this->search . '%');
                    
            })
            ->orderby('product_stocks.available_stock', 'desc')
            ->paginate($this->perPage);
        return view('livewire.admin.Product-stock-details', [
            'ProductStocks' => $ProductStocks
        ])->layout($this->layout);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }
    public function exportToCSV()
    {
        // Get data
        $ProductStocks = ProductDetail::join('product_stocks', 'product_details.id', '=', 'product_stocks.product_id')
            ->join('brand_lists', 'product_details.brand_id', '=', 'brand_lists.id')
            ->select(
                'product_details.name',
                'product_details.code',
                'brand_lists.brand_name as brand',
                'product_details.model',
                'product_stocks.total_stock',
                'product_stocks.available_stock',
                'product_stocks.sold_count',
                'product_stocks.damage_stock'
            )
            ->get();

        if ($ProductStocks->isEmpty()) {
            $this->dispatch('banner-message', [
                'style' => 'danger',
                'message' => 'No data available to export'
            ]);
            return;
        }

        
        // Generate filename with date
        $fileName = 'Product_stock_' . date('Y-m-d_His') . '.csv';

        // Create CSV content with headers
        $headers = [
            'Name',
            'Code',
            'Brand',
            'Model',
            'Total Stock',
            'Available Stock',
            'Sold Count',
            'Damage Stock'
        ];

        $callback = function () use ($ProductStocks, $headers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);

            foreach ($ProductStocks as $stock) {
                $row = [
                    $stock->name ?? '-',
                    $stock->code ?? '-',
                    $stock->brand ?? '-',
                    $stock->model ?? '-',
                    $stock->total_stock ?? '0',
                    $stock->available_stock ?? '0',
                    $stock->sold_count ?? '0',
                    $stock->damage_stock ?? '0'
                ];
                fputcsv($file, $row);
            }

            fclose($file);
        };

        // Create response with headers for browser download
        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
        ]);
    }
}
