<?php

namespace App\Livewire\Admin;

use App\Models\StaffProduct;
use Exception;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Models\StaffSale;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Livewire\Concerns\WithDynamicLayout;

#[Title('Staff Product Stock')]

class StaffStockDetails extends Component
{
    use WithDynamicLayout;


    public $stockDetails;

    public function viewStockDetails($id)
    {
        try {
            $this->stockDetails = StaffProduct::join('users', 'staff_products.staff_id', '=', 'users.id')
                ->join('product_details', 'staff_products.product_id', '=', 'product_details.id')
                ->leftJoin('brand_lists', 'brand_lists.id', '=', 'product_details.brand_id')
                ->where('staff_products.staff_id', $id)
                ->select(

                    'staff_products.*',
                    'users.name as staff_name',
                    'users.email as staff_email',
                    'product_details.name as Product_name',
                    'brand_lists.brand_name as Product_brand',
                    'product_details.model as Product_model',
                    'product_details.code as Product_code',
                    'product_details.image as Product_image'
                )
                ->get();
            // dd($this->stockDetails);
            $this->dispatch('open-stock-details-modal');
        } catch (Exception $e) {
            $this->js('swal.fire("Error", "Unable to fetch stock details: ' . $e->getMessage() . '", "error")');
            return;
        }
    }

    // Add export function
    public function exportToCSV()
    {
        return response()->streamDownload(function () {
            $output = fopen('php://output', 'w');

            // Add headers
            fputcsv($output, ['Staff Name', 'Email', 'Contact', 'Total Quantity', 'Sold Quantity', 'Available Quantity']);

            // Get data
            $staffStocks = DB::table('staff_sales')
                ->join('users', 'staff_sales.staff_id', '=', 'users.id')
                ->select(
                    'users.name',
                    'users.email',
                    'users.contact',
                    DB::raw('SUM(staff_sales.total_quantity) as total_quantity'),
                    DB::raw('SUM(staff_sales.sold_quantity) as sold_quantity'),
                    DB::raw('SUM(staff_sales.total_quantity) - SUM(staff_sales.sold_quantity) as available_quantity')
                )
                ->groupBy('users.id', 'users.name', 'users.email', 'users.contact')
                ->get();

            foreach ($staffStocks as $stock) {
                fputcsv($output, [
                    $stock->name,
                    $stock->email,
                    $stock->contact,
                    $stock->total_quantity,
                    $stock->sold_quantity,
                    $stock->available_quantity,
                ]);
            }

            fclose($output);
        }, 'staff_stock_details_' . date('Y-m-d_His') . '.csv');
    }

    public function render()
    {
        // Get aggregated staff stock data using StaffProduct model
        $staffStocks = StaffProduct::join('users', 'staff_products.staff_id', '=', 'users.id')
            ->select(
                'users.id as user_id',
                'users.name',
                'users.email',
                'users.contact',
                DB::raw('SUM(staff_products.quantity) as total_quantity'),
                DB::raw('SUM(staff_products.sold_quantity) as sold_quantity'),
                DB::raw('SUM(staff_products.quantity) - SUM(staff_products.sold_quantity) as available_quantity')
            )
            ->groupBy('users.id', 'users.name', 'users.email', 'users.contact')
            ->get();

        return view('livewire.admin.staff-stock-details', [
            'staffStocks' => $staffStocks,
        ])->layout($this->layout);
    }
}
