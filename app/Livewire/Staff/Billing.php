<?php

namespace App\Livewire\Staff;

use Exception;
use App\Models\Sale;
use App\Models\Payment;
use Livewire\Component;
use App\Models\Customer;
use App\Models\SaleItem;
use App\Models\ProductPrice;
use App\Models\ProductStock;
use App\Models\ProductDetail;
use App\Models\StaffProduct;
use App\Services\FIFOStockService;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

#[Layout('components.layouts.staff')]
#[Title('Billing Page')]
class Billing extends Component
{
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

    // Add these properties to your existing properties list
    public $duePaymentMethod = '';
    public $duePaymentAttachment;
    public $duePaymentAttachmentPreview = null;

    protected $listeners = ['quantityUpdated' => 'updateTotals'];

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

    // Show all products from product table with stock information - Modified for staff billing
    public function updatedSearch()
    {
        if (strlen($this->search) >= 2) {
            // Search all products from product_details table and join with stock information
            $this->searchResults = ProductDetail::leftJoin('product_stocks', 'product_stocks.product_id', '=', 'product_details.id')
                ->leftJoin('product_prices', 'product_prices.product_id', '=', 'product_details.id')
                ->leftJoin('brand_lists', 'brand_lists.id', '=', 'product_details.brand_id')
                ->leftJoin('category_lists', 'category_lists.id', '=', 'product_details.category_id')
                ->select(
                    'product_details.*',
                    'product_prices.selling_price as selling_price',
                    'product_prices.discount_price as discount_price',
                    DB::raw('COALESCE(product_stocks.available_stock, 0) as available_stock'),
                    'brand_lists.brand_name as brand_name',
                    'category_lists.name as category_name'
                )
                ->where(function ($query) {
                    $query->where('product_details.code', 'like', '%' . $this->search . '%')
                        ->orWhere('product_details.model', 'like', '%' . $this->search . '%')
                        ->orWhere('product_details.barcode', 'like', '%' . $this->search . '%')
                        ->orWhere('brand_lists.brand_name', 'like', '%' . $this->search . '%')
                        ->orWhere('category_lists.name', 'like', '%' . $this->search . '%')
                        ->orWhere('product_details.name', 'like', '%' . $this->search . '%');
                })
                ->orderBy('product_details.name')
                ->take(50)
                ->get();
        } else {
            $this->searchResults = [];
        }
    }
    public function addToCart($ProductId)
    {
        // Get product details with stock and pricing from main inventory
        $Product = ProductDetail::leftJoin('product_stocks', 'product_stocks.product_id', '=', 'product_details.id')
            ->leftJoin('product_prices', 'product_prices.product_id', '=', 'product_details.id')
            ->where('product_details.id', $ProductId)
            ->select(
                'product_details.id',
                'product_details.name',
                'product_details.code',
                'product_details.model',
                'product_details.brand',
                'product_details.image',
                DB::raw('COALESCE(product_stocks.available_stock, 0) as available_stock'),
                DB::raw('COALESCE(product_prices.selling_price, 0) as selling_price'),
                DB::raw('COALESCE(product_prices.discount_price, 0) as discount_price')
            )
            ->first();

        if (!$Product) {
            $this->dispatch('showToast', ['type' => 'danger', 'message' => 'Product not found.']);
            return;
        }

        if ($Product->available_stock <= 0) {
            $this->dispatch('showToast', ['type' => 'danger', 'message' => 'This product is out of stock.']);
            return;
        }

        $existingItem = collect($this->cart)->firstWhere('id', $ProductId);

        if ($existingItem) {
            if (($this->quantities[$ProductId] + 1) > $Product->available_stock) {
                $this->dispatch('showToast', ['type' => 'warning', 'message' => "Maximum available quantity ({$Product->available_stock}) reached."]);
                return;
            }
            $this->quantities[$ProductId]++;
        } else {
            // Set default discount to 0 instead of using product's discount price
            $this->cart[$ProductId] = [
                'id' => $Product->id,
                'code' => $Product->code,
                'name' => $Product->name,
                'model' => $Product->model,
                'brand' => $Product->brand,
                'image' => $Product->image,
                'price' => $Product->selling_price ?? 0,
                'discountPrice' => 0, // Set default discount to 0
                'inStock' => $Product->available_stock,
            ];

            $this->quantities[$ProductId] = 1;
            $this->discounts[$ProductId] = 0; // Initialize discount as 0
        }

        // Clear search and update totals
        $this->search = '';
        $this->searchResults = [];
        $this->updateTotals();

        // Reset the search input field in the UI
        $this->dispatch('resetSearchInput');

        $this->dispatch('showToast', ['type' => 'success', 'message' => 'Product added to cart successfully.']);
    }
    public function validateQuantity($ProductId)
    {
        if (!isset($this->cart[$ProductId]) || !isset($this->quantities[$ProductId])) {
            return;
        }

        $maxAvailable = $this->cart[$ProductId]['inStock'];
        $currentQuantity = (int)$this->quantities[$ProductId];

        // Enforce the minimum and maximum limits
        if ($currentQuantity <= 0) {
            $this->quantities[$ProductId] = 1;
            $this->dispatch('showToast', [
                'type' => 'warning',
                'message' => 'Minimum quantity is 1'
            ]);
        } elseif ($currentQuantity > $maxAvailable) {
            // Cap the quantity to available stock
            $this->quantities[$ProductId] = $maxAvailable;
            $this->dispatch('showToast', [
                'type' => 'warning',
                'message' => "Maximum available quantity is {$maxAvailable}"
            ]);
        }

        $this->updateTotals();
    }

    /**
     * Update quantity with validation
     *
     * @param int $ProductId Product ID
     * @param int $quantity Requested quantity
     */
    public function updateQuantity($ProductId, $quantity)
    {
        if (!isset($this->cart[$ProductId])) {
            return;
        }

        $maxAvailable = $this->cart[$ProductId]['inStock'];

        // Apply limits and validation
        if ($quantity <= 0) {
            $quantity = 1;
        } elseif ($quantity > $maxAvailable) {
            $quantity = $maxAvailable;
            $this->dispatch('showToast', [
                'type' => 'warning',
                'message' => "Maximum available quantity is {$maxAvailable}"
            ]);
        }

        // Update the quantity with the validated value
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
        $this->ProductDetails = ProductDetail::leftJoin('product_stocks', 'product_stocks.product_id', '=', 'product_details.id')
            ->leftJoin('product_prices', 'product_prices.product_id', '=', 'product_details.id')
            ->select(
                'product_details.*',
                'product_prices.selling_price as selling_price',
                'product_prices.discount_price as discount_price',
                DB::raw('COALESCE(product_stocks.available_stock, 0) as available_stock'),
                'product_suppliers.*',
                'product_suppliers.name as supplier_name'
            )
            ->where('product_details.id', $ProductId)
            ->first();

        $this->js('$("#viewDetailModal").modal("show")');
    }

    public function updateTotals()
    {
        $this->subtotal = 0;
        $this->totalDiscount = 0;

        foreach ($this->cart as $id => $item) {
            $price = $item['price'] ?: $item['price'];
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

    public function saveCustomer()
    {
        $this->validate([
            'newCustomerName' => 'required|min:3',
            'newCustomerPhone' => 'required',
        ]);

        $customer = Customer::create([
            'name' => $this->newCustomerName,
            'phone' => $this->newCustomerPhone,
            'email' => $this->newCustomerEmail,
            'type' => $this->newCustomerType,
            'address' => $this->newCustomerAddress,
            'notes' => $this->newCustomerNotes,
        ]);

        $this->loadCustomers();

        $this->newCustomerName = '';
        $this->newCustomerPhone = '';
        $this->newCustomerEmail = '';
        $this->newCustomerAddress = '';
        $this->newCustomerNotes = '';

        $this->js('$("#addCustomerModal").modal("hide")');
        $this->js('swal.fire("Success", "Customer added successfully!", "success")');
    }

    public function calculateBalanceAmount()
    {
        if ($this->paymentType == 'partial') {
            if ($this->initialPaymentAmount > $this->grandTotal) {
                $this->initialPaymentAmount = $this->grandTotal;
            }

            $this->balanceAmount = $this->grandTotal - $this->initialPaymentAmount;
        } else {
            $this->initialPaymentAmount = 0;
            $this->balanceAmount = 0;
        }
    }

    public function updatedPaymentType($value)
    {
        if ($value == 'partial') {
            // Default to 50% initial payment when switching to partial
            $this->initialPaymentAmount = round($this->grandTotal / 2, 2);
            $this->calculateBalanceAmount();
        } else {
            // Reset partial payment fields when switching back to full
            $this->initialPaymentAmount = 0;
            $this->initialPaymentMethod = '';
            $this->initialPaymentReceiptImage = null;
            $this->initialPaymentReceiptImagePreview = null;
            $this->initialBankName = '';

            $this->balanceAmount = 0;
            $this->balancePaymentMethod = '';
            $this->balancePaymentReceiptImage = null;
            $this->balancePaymentReceiptImagePreview = null;
            $this->balanceBankName = '';
        }
    }

    public function updatedPaymentReceiptImage()
    {
        $this->validate([
            'paymentReceiptImage' => 'file|mimes:jpg,jpeg,png,gif,pdf|max:2048',
        ]);

        if ($this->paymentReceiptImage) {
            try {
                $extension = strtolower($this->paymentReceiptImage->getClientOriginalExtension());
                if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                    $this->paymentReceiptImagePreview = $this->paymentReceiptImage->temporaryUrl();
                } else {
                    $this->paymentReceiptImagePreview = 'pdf';
                }
            } catch (Exception $e) {
                // If temporary URL generation fails, mark the file type but don't set URL
                $this->paymentReceiptImagePreview = $extension == 'pdf' ? 'pdf' : 'image';
            }
        }
    }

    public function updatedInitialPaymentReceiptImage()
    {
        $this->validate([
            'initialPaymentReceiptImage' => 'file|mimes:jpg,jpeg,png,gif,pdf|max:2048',
        ]);

        if ($this->initialPaymentReceiptImage) {
            $extension = $this->initialPaymentReceiptImage->getClientOriginalExtension();
            if (in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif'])) {
                $this->initialPaymentReceiptImagePreview = $this->initialPaymentReceiptImage->temporaryUrl();
            } else {
                // For PDF we'll just set a flag that it's a PDF
                $this->initialPaymentReceiptImagePreview = 'pdf';
            }
        }
    }

    public function updatedBalancePaymentReceiptImage()
    {
        $this->validate([
            'balancePaymentReceiptImage' => 'file|mimes:jpg,jpeg,png,gif,pdf|max:2048',
        ]);

        if ($this->balancePaymentReceiptImage) {
            $extension = $this->balancePaymentReceiptImage->getClientOriginalExtension();
            if (in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif'])) {
                $this->balancePaymentReceiptImagePreview = $this->balancePaymentReceiptImage->temporaryUrl();
            } else {
                // For PDF we'll just set a flag that it's a PDF
                $this->balancePaymentReceiptImagePreview = 'pdf';
            }
        }
    }

    // Add this method with your other updater methods
    public function updatedDuePaymentAttachment()
    {
        $this->validate([
            'duePaymentAttachment' => 'file|mimes:jpg,jpeg,png,gif,pdf|max:2048',
        ]);

        if ($this->duePaymentAttachment) {
            $extension = $this->duePaymentAttachment->getClientOriginalExtension();
            if (in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif'])) {
                $this->duePaymentAttachmentPreview = $this->duePaymentAttachment->temporaryUrl();
            } else {
                // For PDF we'll just set a flag that it's a PDF
                $this->duePaymentAttachmentPreview = 'pdf';
            }
        }
    }

    protected $validationAttributes = [
        'paymentMethod' => 'payment method',
        'paymentReceiptImage' => 'payment receipt',
        'bankName' => 'bank name',
        'initialPaymentMethod' => 'initial payment method',
        'initialPaymentReceiptImage' => 'initial payment receipt',
        'initialBankName' => 'initial bank name',
        'balancePaymentMethod' => 'balance payment method',
        'balancePaymentReceiptImage' => 'balance payment receipt',
        'balanceBankName' => 'balance bank name',
        'balanceDueDate' => 'balance due date'
    ];

    public function completeSale()
    {
        if (empty($this->cart)) {
            $this->js('swal.fire("Error", "Please add items to the cart.", "error")');
            return;
        }

        $this->validate([
            'customerId' => 'required',
        ]);


        try {
            DB::beginTransaction();

            $invoiceNumber = Sale::generateInvoiceNumber();

            // For staff sales: always set status as 'pending' and payment_status as 'pending'
            $paymentStatus = 'pending';
            $status = 'pending';

            $customer = Customer::find($this->customerId);

            $sale = Sale::create([
                'invoice_number' => $invoiceNumber,
                'customer_id' => $this->customerId,
                'user_id' => auth()->id(),
                'customer_type' => $customer->type,
                'subtotal' => $this->subtotal,
                'discount_amount' => $this->totalDiscount,
                'total_amount' => $this->grandTotal,
                'payment_type' => 'full', // Always set as full for staff sales
                'payment_status' => $paymentStatus,
                'status' => $status,
                'notes' => $this->saleNotes,
                'due_amount' => 0, // Set due amount as total amount since payment is pending
            ]);

            foreach ($this->cart as $productId => $item) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $productId,
                    'product_code' => $item['code'],
                    'product_name' => $item['name'],

                    'quantity' => $this->quantities[$productId],
                    'unit_price' => $item['price'],
                    'discount' => $this->discounts[$productId],
                    'total' => ($item['price'] - $this->discounts[$productId]) * $this->quantities[$productId],
                ]);
            }

            DB::commit();

            $this->lastSaleId = $sale->id;

            $this->js('swal.fire("Success", "Sale submitted successfully! Invoice #' . $invoiceNumber . ' is pending admin approval.", "success")');

            // Reset customer selection
            $this->customerId = null;

            // Clear cart and payment info
            $this->clearCart();
            $this->resetPaymentInfo();
        } catch (Exception $e) {
            DB::rollBack();
            $this->js('swal.fire("Error", "An error occurred while completing the sale: ' . $e->getMessage() . '", "error")');
            Log::error('Sale completion error: ' . $e->getMessage());
        }
    }

    public function resetPaymentInfo()
    {
        $this->paymentType = 'full';
        $this->paymentMethod = '';
        $this->paymentReceiptImage = null;
        $this->paymentReceiptImagePreview = null;
        $this->bankName = '';

        $this->initialPaymentAmount = 0;
        $this->initialPaymentMethod = '';
        $this->initialPaymentReceiptImage = null;
        $this->initialPaymentReceiptImagePreview = null;
        $this->initialBankName = '';

        $this->balanceAmount = 0;
        $this->balancePaymentMethod = '';
        $this->balanceDueDate = date('Y-m-d', strtotime('+7 days'));
        $this->balancePaymentReceiptImage = null;
        $this->balancePaymentReceiptImagePreview = null;
        $this->balanceBankName = '';

        // Add these lines to the resetPaymentInfo method
        $this->duePaymentMethod = '';
        $this->duePaymentAttachment = null;
        $this->duePaymentAttachmentPreview = null;

        $this->saleNotes = '';
    }

    public function viewReceipt($saleId = null)
    {
        if ($saleId) {
            $this->lastSaleId = $saleId;
        }

        if ($this->lastSaleId) {
            $this->showReceipt = true;
            $this->js('$("#receiptModal").modal("show")');
        }
    }

    public function printReceipt()
    {
        $this->dispatch('printReceipt');
    }

    public function downloadReceipt()
    {
        return redirect()->route('receipts.download', $this->lastSaleId);
    }

    /**
     * Get file type icon or preview based on file object and preview URL
     *
     * @param mixed $file The uploaded file object
     * @param string|null $previewUrl The temporary preview URL
     * @return array File information with type, icon, and URL
     */
    public function getFilePreviewInfo($file, $previewUrl = null)
    {
        if (!$file) {
            return null;
        }

        $fileInfo = [
            'name' => $file->getClientOriginalName(),
            'type' => 'unknown',
            'icon' => 'bi-file',
            'url' => null
        ];

        // Determine file type
        $extension = strtolower($file->getClientOriginalExtension());

        // Handle images
        if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
            $fileInfo['type'] = 'image';
            $fileInfo['icon'] = 'bi-file-image';

            // Only set URL if we have a valid preview
            try {
                $fileInfo['url'] = $previewUrl && $previewUrl !== 'pdf' ? $previewUrl : null;
            } catch (\Exception $e) {
                $fileInfo['url'] = null;
            }
        }
        // Handle PDFs
        elseif ($extension === 'pdf') {
            $fileInfo['type'] = 'pdf';
            $fileInfo['icon'] = 'bi-file-earmark-pdf';
        }

        return $fileInfo;
    }

    public function render()
    {
        return view(
            'livewire.staff.billing',
            [
                'receipt' => $this->showReceipt && $this->lastSaleId ? Sale::with(['customer', 'items', 'payments'])->find($this->lastSaleId) : null,
            ]
        );
    }
}
