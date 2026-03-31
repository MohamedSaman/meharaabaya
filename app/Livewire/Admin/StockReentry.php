<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\StaffProduct;
use App\Models\ProductStock;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use App\Livewire\Concerns\WithDynamicLayout;

#[Title('Staff Product Stock Reentry')]

class StockReentry extends Component
{
    use WithDynamicLayout;

    public $staffId;
    public $staff;
    public $selectedProduct;
    public $damagedQuantity = 0;
    public $restockQuantity = 0;
    public $searchTerm = ''; // <-- Add this line


    public function mount($staffId)
    {
        $this->staffId = $staffId;
        $this->staff = User::findOrFail($staffId);
    }

    public function selectProduct($productId)
    {
        $this->selectedProduct = StaffProduct::with('ProductDetail')->find($productId);
        $this->damagedQuantity = 0;
        $this->restockQuantity = 0;
    }

    public function submitReentry()
    {
        $this->validate([
            'damagedQuantity' => 'nullable|integer|min:0',
            'restockQuantity' => 'nullable|integer|min:0',
        ]);

        $product = StaffProduct::find($this->selectedProduct->id);

        $available = $product->quantity - $product->sold_quantity;
        $totalToRemove = $this->damagedQuantity;
        $totalToRestock = $this->restockQuantity;

        // Check if entered quantities exceed available stock
        if (($totalToRemove + $totalToRestock) > $available) {
            $this->dispatch('notify', 'Entered quantities exceed available stock.', 'error');
            return;
        }

        DB::transaction(function () use ($product, $totalToRemove, $totalToRestock) {
            $product->quantity -= ($totalToRemove + $totalToRestock);
            $product->save();

            $stock = ProductStock::where('product_id', $product->product_id)->first();
            if (!$stock) {
                $stock = new ProductStock();
                $stock->product_id = $product->product_id;
                $stock->damage_stock = 0;
                $stock->available_stock = 0;
            }

            $stock->damage_stock += $this->damagedQuantity;
            $stock->available_stock += $this->restockQuantity;
            $stock->save();

            $this->dispatch('notify', 'Stock updated successfully.');
            $this->reset('selectedProduct', 'damagedQuantity', 'restockQuantity');
        });
    }

public function render()
{
    $products = StaffProduct::with(['ProductDetail' => function ($query) {
            $query->select('id', 'name', 'brand', 'code', 'image'); // Ensure 'image' is selected
        }])
        ->where('staff_id', $this->staffId)
        ->when($this->searchTerm, function ($query) {
            $query->whereHas('ProductDetail', function ($q) {
                $q->where(function ($subQ) {
                    $subQ->where('name', 'like', '%' . $this->searchTerm . '%')
                        ->orWhere('brand', 'like', '%' . $this->searchTerm . '%')
                        ->orWhere('code', 'like', '%' . $this->searchTerm . '%');
                });
            });
        })
        ->get();

    return view('livewire.admin.stock-reentry', [
        'products' => $products,
    ])->layout($this->layout);
}
}

