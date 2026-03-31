<?php

namespace App\Livewire\Admin;

use Exception;
use App\Models\Sale;
use App\Models\User;
use App\Models\Payment;
use Livewire\Component;
use App\Models\Customer;
use App\Models\SaleItem;
use App\Models\StaffSale;
use App\Models\ProductPrice;
use App\Models\ProductStock;
use App\Models\ProductDetail;
use App\Models\StaffProduct;
use Livewire\WithFileUploads;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Livewire\Concerns\WithDynamicLayout;

#[Title('Billing Page')]
class BillingPage extends Component
{
    use WithDynamicLayout;

    use WithFileUploads;

    public $search = '';
    public $searchResults = [];
    public $cart = [];
    public $quantities = [];
    public $discounts = [];
    public $ProductDetails = null;
    public $subtotal = 0;
    public $totalDiscount = 0;
    public $grandTotal = 0;

    public $customers = [];
    public $customerId = null;
    public $customerType = 'retail';

    public $newCustomerName = '';
    public $newCustomerPhone = '';
    public $newCustomerEmail = '';
    public $newCustomerType = 'retail';
    public $newCustomerAddress = '';
    public $newCustomerNotes = '';

    public $saleNotes = '';
    public $paymentType = 'full';
    public $paymentMethod = '';
    public $paymentReceiptImage;
    public $paymentReceiptImagePreview = null;
    public $bankName = '';

    public $initialPaymentAmount = '';
    public $initialPaymentMethod = '';
    public $initialPaymentReceiptImage;
    public $initialPaymentReceiptImagePreview = null;
    public $initialBankName = '';

    public $balanceAmount = 0;
    public $balancePaymentMethod = '';
    public $balanceDueDate = '';
    public $balancePaymentReceiptImage;
    public $balancePaymentReceiptImagePreview = null;
    public $balanceBankName = '';

    public $lastSaleId = null;
    public $showReceipt = false;

    public $selectedStaffId = null;

    protected $listeners = ['quantityUpdated' => 'updateTotals'];

    protected function rules()
    {
        return [
            'selectedStaffId' => 'required',
            // Add any other validation rules you need
        ];
    }

    protected $messages = [
        'selectedStaffId.required' => 'Please select a staff member to assign this sale.',
    ];

    public function mount()
    {
        $this->loadCustomers();
        $this->updateTotals();
        $this->balanceDueDate = date('Y-m-d', strtotime('+7 days'));
    }

    public function loadCustomers()
    {
        $this->customers = Customer::orderBy('name')->get();
    }

    public function updatedSearch()
    {
        if (strlen($this->search) >= 2) {
            $this->searchResults = ProductDetail::join('product_prices', 'product_prices.product_id', '=', 'product_details.id')
                ->join('product_stocks', 'product_stocks.product_id', '=', 'product_details.id')
                ->leftJoin('brand_lists', 'brand_lists.id', '=', 'product_details.brand_id')
                ->leftJoin('category_lists', 'category_lists.id', '=', 'product_details.category_id')
                ->select(
                    'product_details.*',
                    'product_prices.selling_price',
                    'product_prices.discount_price',
                    'product_stocks.available_stock',
                    'brand_lists.brand_name as brand_name',
                    'category_lists.category_name as category_name'
                )
                ->where('product_details.status', '=', 'active')
                ->where('product_stocks.available_stock', '>', 0) // Only show products with stock > 0
                ->where(function ($query) {
                    $query->where('product_details.code', 'like', '%' . $this->search . '%')
                        ->orWhere('product_details.model', 'like', '%' . $this->search . '%')
                        ->orWhere('product_details.barcode', 'like', '%' . $this->search . '%')
                        ->orWhere('brand_lists.brand_name', 'like', '%' . $this->search . '%')
                        ->orWhere('category_lists.category_name', 'like', '%' . $this->search . '%')
                        ->orWhere('product_details.name', 'like', '%' . $this->search . '%');
                })
                ->take(50)
                ->get();
        } else {
            $this->searchResults = [];
        }
    }

    public function addToCart($ProductId)
    {
        $Product = ProductDetail::join('product_prices', 'product_prices.product_id', '=', 'product_details.id')
            ->join('product_stocks', 'product_stocks.product_id', '=', 'product_details.id')
            ->where('product_details.id', $ProductId)
            ->select(
                'product_details.*',
                'product_prices.selling_price',
                'product_prices.discount_price',
                'product_stocks.available_stock'
            )
            ->first();

        if (!$Product || $Product->available_stock <= 0) {
            $this->js('swal.fire("Error", "This product is out of stock.", "error")');
            return;
        }

        $existingItem = collect($this->cart)->firstWhere('id', $ProductId);

        if ($existingItem) {
            // Check if adding one more would exceed stock
            if (($this->quantities[$ProductId] + 1) > $Product->available_stock) {
                $this->js('swal.fire("Warning", "Maximum available quantity reached.", "warning")');
                return;
            }
            $this->quantities[$ProductId]++;
        } else {
            $discountPrice = $Product->selling_price - $Product->discount_price ?? 0;
            $this->cart[$ProductId] = [
                'id' => $Product->id,
                'code' => $Product->code,
                'name' => $Product->name,
                'model' => $Product->model,
                'brand' => $Product->brand,
                'image' => $Product->image,
                'price' => $Product->selling_price ?? 0,
                'discountPrice' => $discountPrice ?? 0,
                'inStock' => $Product->available_stock ?? 0,
            ];

            $this->quantities[$ProductId] = 1;
            $this->discounts[$ProductId] = 0;
        }

        $this->search = '';
        $this->searchResults = [];
        $this->updateTotals();
    }

    public function updateQuantity($ProductId, $quantity)
    {
        if (!isset($this->cart[$ProductId])) {
            return;
        }

        $maxAvailable = $this->cart[$ProductId]['inStock'];

        // Ensure quantity is valid
        $quantity = (int)$quantity;
        if ($quantity < 1) {
            $quantity = 1;
        } elseif ($quantity > $maxAvailable) {
            $quantity = $maxAvailable;
            $this->js('swal.fire("Warning", "Quantity limited to maximum available (' . $maxAvailable . ')", "warning")');
        }

        $this->quantities[$ProductId] = $quantity;
        $this->updateTotals();
    }

    public function updateDiscount($ProductId, $discount)
    {
        $this->discounts[$ProductId] = max(0, min($discount, $this->cart[$ProductId]['price']));
        $this->updateTotals();
    }

    public function removeFromCart($ProductId)
    {

        unset($this->cart[$ProductId]);
        unset($this->quantities[$ProductId]);
        unset($this->discounts[$ProductId]);
        $this->updateTotals();
    }

    public function showDetail($ProductId)
    {
        $this->ProductDetails = ProductDetail::join('product_prices', 'product_prices.product_id', '=', 'product_details.id')
            ->join('product_stocks', 'product_stocks.product_id', '=', 'product_details.id')
            ->select('product_details.*', 'product_prices.*', 'product_stocks.*', 'product_suppliers.*', 'product_suppliers.name as supplier_name')
            ->where('product_details.id', $ProductId)
            ->first();

        $this->js('$("#viewDetailModal").modal("show")');
    }

    public function updateTotals()
    {
        $this->subtotal = 0;
        $this->totalDiscount = 0;

        foreach ($this->cart as $id => $item) {
            $price = $item['discountPrice'] ?: $item['price'];
            $this->subtotal += $price * $this->quantities[$id];
            $this->totalDiscount += $this->discounts[$id] * $this->quantities[$id];
        }

        $this->grandTotal = $this->subtotal - $this->totalDiscount;
    }

    public function clearCart()
    {
        $this->cart = [];
        $this->quantities = [];
        $this->discounts = [];
        $this->updateTotals();
    }

    public function completeSale()
    {
        if (empty($this->cart)) {
            $this->js('swal.fire("Error", "Please add items to the cart.", "error")');
            return;
        }

        // Add stock validation
        $invalidItems = [];
        foreach ($this->cart as $id => $item) {
            // Get the latest stock directly from database
            $currentStock = ProductStock::where('product_id', $id)->value('available_stock');

            if ($currentStock < $this->quantities[$id]) {
                $invalidItems[] = $item['name'] . " (Requested: {$this->quantities[$id]}, Available: {$currentStock})";
            }
        }

        if (!empty($invalidItems)) {
            $errorMessage = "Cannot complete sale due to insufficient stock:<br><ul>";
            foreach ($invalidItems as $item) {
                $errorMessage .= "<li>{$item}</li>";
            }
            $errorMessage .= "</ul>";

            $this->js('swal.fire({
                title: "Stock Error",
                html: "' . $errorMessage . '",
                icon: "error"
            })');
            return;
        }

        // Validate staff selection
        $this->validate();

        try {
            // Start a database transaction
            DB::beginTransaction();

            // Create a new StaffSale record
            $staffSale = new StaffSale();
            $staffSale->staff_id = $this->selectedStaffId;
            $staffSale->admin_id = auth()->id();
            $staffSale->total_quantity = array_sum($this->quantities);
            $staffSale->total_value = $this->grandTotal;
            $staffSale->sold_quantity = 0; // Initially 0 as products are just being assigned
            $staffSale->sold_value = 0; // Initially 0 as products are just being assigned
            $staffSale->status = 'assigned';
            $staffSale->save();

            // Create records for each product assigned
            foreach ($this->cart as $ProductId => $item) {
                $unitPrice = $item['discountPrice'] ?: $item['price'];
                $totalDiscount = $this->discounts[$ProductId] * $this->quantities[$ProductId];
                $totalValue = ($unitPrice * $this->quantities[$ProductId]) - $totalDiscount;

                $staffProduct = new StaffProduct();
                $staffProduct->staff_sale_id = $staffSale->id;
                $staffProduct->product_id = $ProductId;
                $staffProduct->staff_id = $this->selectedStaffId;
                $staffProduct->quantity = $this->quantities[$ProductId];
                $staffProduct->unit_price = $unitPrice;
                $staffProduct->discount_per_unit = $this->discounts[$ProductId];
                $staffProduct->total_discount = $totalDiscount;
                $staffProduct->total_value = $totalValue;
                $staffProduct->sold_quantity = 0; // Initially 0
                $staffProduct->sold_value = 0; // Initially 0
                $staffProduct->status = 'assigned';
                $staffProduct->save();

                // Update Product stock
                $ProductStock = ProductStock::where('product_id', $ProductId)->first();
                if ($ProductStock) {
                    $ProductStock->available_stock -= $this->quantities[$ProductId];
                    $ProductStock->assigned_stock = ($ProductStock->assigned_stock ?? 0) + $this->quantities[$ProductId];
                    $ProductStock->save();
                }
            }

            DB::commit();

            // Show success message
            $this->js('swal.fire("Success", "Products successfully assigned to staff.", "success")');

            // Reset the form
            $this->clearCart();
            $this->selectedStaffId = null;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error assigning products to staff: ' . $e->getMessage());
            $this->js('swal.fire("Error", "' . $e->getMessage() . '", "error")');
        }
    }

    public function render()
    {
        $staffs = User::where('role', 'staff')->get();
        return view('livewire.admin.billing-page', [
            'staffs' => $staffs,
        ])->layout($this->layout);
    }
}
