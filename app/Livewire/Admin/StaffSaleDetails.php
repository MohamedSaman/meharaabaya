<?php

namespace App\Livewire\Admin;

use Exception;
use Livewire\Component;
use App\Models\StaffSale;
use App\Models\StaffProduct;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;
use App\Livewire\Concerns\WithDynamicLayout;

#[Title('Staff Sale Details')]
class StaffSaleDetails extends Component
{
    use WithDynamicLayout;

    use WithPagination;

    public $isViewModalOpen = false;
    public $staffId;
    public $staffName;
    public $staffDetails;
    public $productDetails;
    public $summaryStats;

    public function viewSaleDetails($userId)
    {
        $this->staffId = $userId;

        // Get staff details
        $this->staffDetails = DB::table('users')
            ->where('id', $userId)
            ->first();

        $this->staffName = $this->staffDetails->name;

        // Get summary statistics using StaffProduct model
        $this->summaryStats = StaffProduct::where('staff_id', $userId)
            ->select([
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('SUM(sold_quantity) as sold_quantity'),
                DB::raw('SUM(quantity) - SUM(sold_quantity) as available_quantity'),
                DB::raw('SUM(total_value) as total_value'),
                DB::raw('SUM(sold_value) as sold_value'),
                DB::raw('SUM(total_value) - SUM(sold_value) as available_value')
            ])
            ->first();

        // Get product-wise details
        $this->productDetails = StaffProduct::join('product_details', 'staff_products.product_id', '=', 'product_details.id')
            ->leftJoin('brand_lists', 'brand_lists.id', '=', 'product_details.brand_id')
            ->where('staff_products.staff_id', $userId)
            ->select(
                'staff_products.*',
                'product_details.name as Product_name',
                'brand_lists.brand_name as Product_brand',
                'product_details.model as Product_model',
                'product_details.code as Product_code',
                'product_details.image as Product_image'
            )
            ->get();

        //    $this->js("$('#salesDetails').modal('show');");
        $this->dispatch('open-sales-modal');
    }

    public function getSummaryStats($staffId)
    {
        $summaryStats = [];
        try {
            $summaryStats = StaffProduct::where('staff_id', $staffId)
                ->select([
                    DB::raw('SUM(quantity) as total_quantity'),
                    DB::raw('SUM(sold_quantity) as sold_quantity'),
                    DB::raw('SUM(quantity) - SUM(sold_quantity) as available_quantity'),
                    DB::raw('SUM(total_value) as total_value'),
                    DB::raw('SUM(sold_value) as sold_value'),
                    DB::raw('SUM(total_value) - SUM(sold_value) as available_value')
                ])
                ->first();
        } catch (Exception) {
            $summaryStats = [];
        }
        return $summaryStats;
    }

    public function exportToCsv()
    {
        return redirect()->route('staff-sales.export');
    }

    public function printStaffDetails($staffId = null)
    {
        // If no staffId provided, use the currently selected staff
        $staffId = $staffId ?? $this->staffId;

        if (!$staffId) {
            return redirect()->back()->with('error', 'No staff selected for printing');
        }

        // Get all the necessary data for printing
        $staffDetails = DB::table('users')->where('id', $staffId)->first();
        $summaryStats = $this->getSummaryStats($staffId);
        $productDetails = StaffProduct::join('product_details', 'staff_products.product_id', '=', 'product_details.id')
            ->leftJoin('brand_lists', 'brand_lists.id', '=', 'product_details.brand_id')
            ->where('staff_products.staff_id', $staffId)
            ->select(
                'staff_products.*',
                'product_details.name as Product_name',
                'brand_lists.brand_name as Product_brand',
                'product_details.model as Product_model',
                'product_details.code as Product_code',
                'product_details.image as Product_image'
            )
            ->get();

        // Return a view optimized for printing
        return view('admin.print.staff-details', compact('staffDetails', 'summaryStats', 'productDetails'))->layout($this->layout);
    }

    public function render()
    {

        // Get summary values for all staff using StaffProduct model
        $staffSales = StaffProduct::join('users', 'staff_products.staff_id', '=', 'users.id')
            ->select(
                'users.id as user_id',
                'users.name',
                'users.email',
                'users.contact',
                DB::raw('SUM(staff_products.quantity) as total_quantity'),
                DB::raw('SUM(staff_products.sold_quantity) as sold_quantity'),
                DB::raw('SUM(staff_products.quantity) - SUM(staff_products.sold_quantity) as available_quantity'),
                DB::raw('SUM(staff_products.total_value) as total_value'),
                DB::raw('SUM(staff_products.sold_value) as sold_value')
            )
            ->groupBy('users.id', 'users.name', 'users.email', 'users.contact')
            ->orderBy('total_value', 'desc')
            ->paginate(10);

        return view('livewire.admin.staff-sale-details', [
            'staffSales' => $staffSales
        ])->layout($this->layout);
    }
}
