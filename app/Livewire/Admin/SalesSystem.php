<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Title;
use App\Models\Customer;
use App\Models\ProductDetail;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\POSSession;
use App\Services\FIFOStockService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Livewire\Concerns\WithDynamicLayout;



#[Title('Create Sale')]
class SalesSystem extends Component
{
    use WithDynamicLayout;
    // Basic Properties
    public $search = '';
    public $searchResults = [];
    public $customerId = '';

    // Cart Items
    public $cart = [];

    // Customer Properties
    public $customers = [];
    public $selectedCustomer = null;

    // Customer Form (for new customer - only used in modal)
    public $customerName = '';
    public $customerPhone = '';
    public $customerEmail = '';
    public $customerAddress = '';
    public $customerType = 'retail';
    public $businessName = '';

    // Sale Properties
    public $notes = '';

    // Discount Properties
    public $additionalDiscount = 0;
    public $additionalDiscountType = 'fixed'; // 'fixed' or 'percentage'

    // Modals
    public $showSaleModal = false;
    public $showCustomerModal = false;
    public $lastSaleId = null;
    public $createdSale = null;
    // POS Session (to track/update daily totals)
    public $currentSession = null;

    public function mount()
    {
        $this->loadCustomers();
        $this->setDefaultCustomer();
    }

    // Set default walking customer
    public function setDefaultCustomer()
    {
        // Find or create walking customer (only one)
        $walkingCustomer = Customer::where('name', 'Walking Customer')->first();

        if (!$walkingCustomer) {
            $walkingCustomer = Customer::create([
                'name' => 'Walking Customer',
                'phone' => 'xxxxx', // Empty phone number
                'email' => null,
                'address' => 'xxxxx',
                'type' => 'retail',
                'business_name' => null,
            ]);

            $this->loadCustomers(); // Reload customers after creating new one
        }

        $this->customerId = $walkingCustomer->id;
        $this->selectedCustomer = $walkingCustomer;
    }

    // Load customers for dropdown
    public function loadCustomers()
    {
        $this->customers = Customer::orderBy('name')->get();
    }

    // Computed Properties for Totals
    public function getSubtotalProperty()
    {
        return collect($this->cart)->sum('total');
    }

    public function getTotalDiscountProperty()
    {
        return collect($this->cart)->sum(function ($item) {
            return ($item['discount'] * $item['quantity']);
        });
    }

    public function getSubtotalAfterItemDiscountsProperty()
    {
        return $this->subtotal;
    }

    public function getAdditionalDiscountAmountProperty()
    {
        if (empty($this->additionalDiscount) || $this->additionalDiscount <= 0) {
            return 0;
        }

        if ($this->additionalDiscountType === 'percentage') {
            return ($this->subtotalAfterItemDiscounts * $this->additionalDiscount) / 100;
        }

        return min($this->additionalDiscount, $this->subtotalAfterItemDiscounts);
    }

    public function getGrandTotalProperty()
    {
        return $this->subtotalAfterItemDiscounts - $this->additionalDiscountAmount;
    }

    // When customer is selected from dropdown
    public function updatedCustomerId($value)
    {
        if ($value) {
            $customer = Customer::find($value);
            if ($customer) {
                $this->selectedCustomer = $customer;
            }
        } else {
            // If customer is deselected, set back to walking customer
            $this->setDefaultCustomer();
        }
    }

    // Reset customer fields
    public function resetCustomerFields()
    {
        $this->customerName = '';
        $this->customerPhone = '';
        $this->customerEmail = '';
        $this->customerAddress = '';
        $this->customerType = 'retail';
        $this->businessName = '';
    }

    // Open customer modal
    public function openCustomerModal()
    {
        $this->resetCustomerFields();
        $this->showCustomerModal = true;
    }

    // Close customer modal
    public function closeCustomerModal()
    {
        $this->showCustomerModal = false;
        $this->resetCustomerFields();
    }

    // Create new customer
    public function createCustomer()
    {
        $this->validate([
            'customerName' => 'required|string|max:255',
            'customerPhone' => 'nullable|string|max:10|unique:customers,phone',
            'customerEmail' => 'nullable|email|unique:customers,email',
            'customerAddress' => 'required|string',
            'customerType' => 'required|in:retail,wholesale',
        ]);

        try {
            $customer = Customer::create([
                'name' => $this->customerName,
                'phone' => $this->customerPhone ?: null,
                'email' => $this->customerEmail,
                'address' => $this->customerAddress,
                'type' => $this->customerType,
                'business_name' => $this->businessName,
            ]);

            $this->loadCustomers();
            $this->customerId = $customer->id;
            $this->selectedCustomer = $customer;
            $this->closeCustomerModal();

            $this->js("Swal.fire('success', 'Customer created successfully!', 'success')");
        } catch (\Exception $e) {
            $this->js("Swal.fire('error', 'Failed to create customer: " . addslashes($e->getMessage()) . "', 'error')");
        }
    }

    // Search Products
    public function updatedSearch()
    {
        if (strlen($this->search) >= 2) {
            $this->searchResults = ProductDetail::with(['stock', 'price'])
                ->where('name', 'like', '%' . $this->search . '%')
                ->orWhere('code', 'like', '%' . $this->search . '%')
                ->orWhere('model', 'like', '%' . $this->search . '%')
                ->take(10)
                ->get()
                ->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'code' => $product->code,
                        'model' => $product->model,
                        'price' => $product->price->selling_price ?? 0,
                        'stock' => $product->stock->available_stock ?? 0,
                        'sold' => $product->stock->sold_count ?? 0,
                        'image' => $product->image
                    ];
                });
        } else {
            $this->searchResults = [];
        }
    }

    // Add to Cart
    public function addToCart($product)
    {
        // Check stock availability
        if (($product['stock'] ?? 0) <= 0) {
            $this->js("Swal.fire('error', 'Not enough stock available!', 'error')");
            return;
        }

        $existing = collect($this->cart)->firstWhere('id', $product['id']);

        if ($existing) {
            // Check if adding more exceeds stock
            if (($existing['quantity'] + 1) > $product['stock']) {
                $this->js("Swal.fire('error', 'Not enough stock available!', 'error')");
                return;
            }

            $this->cart = collect($this->cart)->map(function ($item) use ($product) {
                if ($item['id'] == $product['id']) {
                    $item['quantity'] += 1;
                    $item['total'] = ($item['price'] - $item['discount']) * $item['quantity'];
                    // Ensure key exists
                    if (!isset($item['key'])) {
                        $item['key'] = uniqid('cart_');
                    }
                }
                return $item;
            })->toArray();
        } else {
            $discountPrice = ProductDetail::find($product['id'])->price->discount_price ?? 0;

            $newItem = [
                'key' => uniqid('cart_'),  // Add unique key to maintain state
                'id' => $product['id'],
                'name' => $product['name'],
                'code' => $product['code'],
                'model' => $product['model'],
                'price' => $product['price'],
                'quantity' => 1,
                'discount' => $discountPrice,
                'total' => $product['price'] - $discountPrice,
                'stock' => $product['stock']
            ];

            // Prepend new item to the beginning of the cart so it appears at the top
            array_unshift($this->cart, $newItem);
        }

        $this->search = '';
        $this->searchResults = [];

        // Check if quantity spans multiple batch prices and split if needed
        $this->checkAndSplitCartByBatchPrices($product['id']);
    }

    // Update Quantity
    public function updateQuantity($index, $quantity)
    {
        if ($quantity < 1) $quantity = 1;

        // Check stock availability
        $productStock = $this->cart[$index]['stock'];
        if ($quantity > $productStock) {
            $this->js("Swal.fire('error', 'Not enough stock available! Maximum: ' . $productStock, 'error')");
            return;
        }

        $this->cart[$index]['quantity'] = $quantity;
        $this->cart[$index]['total'] = ($this->cart[$index]['price'] - $this->cart[$index]['discount']) * $quantity;

        // Check if quantity spans multiple batch prices and split if needed
        $this->checkAndSplitCartByBatchPrices($this->cart[$index]['id']);
    }

    // Increment Quantity
    public function incrementQuantity($index)
    {
        $currentQuantity = $this->cart[$index]['quantity'];
        $productStock = $this->cart[$index]['stock'];

        if (($currentQuantity + 1) > $productStock) {
            $this->js("Swal.fire('error', 'Not enough stock available! Maximum: ' . $productStock, 'error')");
            return;
        }

        $this->cart[$index]['quantity'] += 1;
        $this->cart[$index]['total'] = ($this->cart[$index]['price'] - $this->cart[$index]['discount']) * $this->cart[$index]['quantity'];

        // Check if quantity spans multiple batch prices and split if needed
        $this->checkAndSplitCartByBatchPrices($this->cart[$index]['id']);
    }

    // Decrement Quantity
    public function decrementQuantity($index)
    {
        if ($this->cart[$index]['quantity'] > 1) {
            $this->cart[$index]['quantity'] -= 1;
            $this->cart[$index]['total'] = ($this->cart[$index]['price'] - $this->cart[$index]['discount']) * $this->cart[$index]['quantity'];
        }
    }

    // Update Price
    public function updatePrice($index, $price)
    {
        if ($price < 0) $price = 0;

        $this->cart[$index]['price'] = $price;
        $this->cart[$index]['total'] = ($price - $this->cart[$index]['discount']) * $this->cart[$index]['quantity'];
    }

    // Update Discount
    public function updateDiscount($index, $discount)
    {
        if ($discount < 0) $discount = 0;
        if ($discount > $this->cart[$index]['price']) {
            $discount = $this->cart[$index]['price'];
        }

        $this->cart[$index]['discount'] = $discount;
        $this->cart[$index]['total'] = ($this->cart[$index]['price'] - $discount) * $this->cart[$index]['quantity'];
    }

    // Remove from Cart
    public function removeFromCart($index)
    {
        unset($this->cart[$index]);
        $this->cart = array_values($this->cart);
        $this->js("Swal.fire('success', 'Product removed from sale!', 'success')");
    }

    // Clear Cart
    public function clearCart()
    {
        $this->cart = [];
        $this->additionalDiscount = 0;
        $this->additionalDiscountType = 'fixed';
        $this->js("Swal.fire('success', 'Cart cleared!', 'success')");
    }

    // Update additional discount
    public function updatedAdditionalDiscount($value)
    {
        if ($value === '') {
            $this->additionalDiscount = 0;
            return;
        }

        if ($value < 0) {
            $this->additionalDiscount = 0;
            return;
        }

        if ($this->additionalDiscountType === 'percentage' && $value > 100) {
            $this->additionalDiscount = 100;
            return;
        }

        if ($this->additionalDiscountType === 'fixed' && $value > $this->subtotalAfterItemDiscounts) {
            $this->additionalDiscount = $this->subtotalAfterItemDiscounts;
            return;
        }
    }

    public function toggleDiscountType()
    {
        $this->additionalDiscountType = $this->additionalDiscountType === 'percentage' ? 'fixed' : 'percentage';
        $this->additionalDiscount = 0;
    }

    public function removeAdditionalDiscount()
    {
        $this->additionalDiscount = 0;
        $this->js("Swal.fire('success', 'Additional discount removed!', 'success')");
    }

    // Create Sale
    public function createSale()
    {
        if (empty($this->cart)) {
            $this->js("Swal.fire('error', 'Please add at least one product to the sale.', 'error')");
            return;
        }

        // If no customer selected, use walking customer
        if (!$this->selectedCustomer && !$this->customerId) {
            $this->setDefaultCustomer();
        }

        try {
            DB::beginTransaction();

            // Get customer data - now guaranteed to have a customer
            if ($this->selectedCustomer) {
                $customer = $this->selectedCustomer;
            } else {
                $customer = Customer::find($this->customerId);
            }

            if (!$customer) {
                throw new \Exception('Customer not found.');
            }

            // Create sale
            $sale = Sale::create([
                'sale_id' => Sale::generateSaleId(),
                'invoice_number' => Sale::generateInvoiceNumber(),
                'customer_id' => $customer->id,
                'customer_type' => $customer->type,
                'subtotal' => $this->subtotal,
                'discount_amount' => $this->additionalDiscountAmount,
                'total_amount' => $this->grandTotal,
                'payment_type' => 'full',
                'payment_status' => 'pending',
                'due_amount' => $this->grandTotal,
                'notes' => $this->notes,
                'user_id' => Auth::id(),
                'status' => 'confirm',
                'sale_type' => 'admin'
            ]);

            // Create sale items and update stock using FIFO
            foreach ($this->cart as $item) {
                // Update product stock using FIFO method
                try {
                    $fifoResult = FIFOStockService::deductStock($item['id'], $item['quantity']);

                    // Group deductions by selling price to combine same-price batches
                    $groupedByPrice = [];
                    foreach ($fifoResult['deductions'] as $deduction) {
                        $price = (string) ($deduction['selling_price'] ?? '0');
                        if (!isset($groupedByPrice[$price])) {
                            $groupedByPrice[$price] = [
                                'quantity' => 0,
                                'selling_price' => (float) $price,
                            ];
                        }
                        $groupedByPrice[$price]['quantity'] += $deduction['quantity'];
                    }

                    // Create sale items - one per unique selling price
                    foreach ($groupedByPrice as $priceGroup) {
                        SaleItem::create([
                            'sale_id' => $sale->id,
                            'product_id' => $item['id'],
                            'product_code' => $item['code'],
                            'product_name' => $item['name'],
                            'product_model' => $item['model'],
                            'quantity' => $priceGroup['quantity'],
                            'unit_price' => $item['price'], // Use edited cart price, not batch selling price
                            'discount_per_unit' => $item['discount'],
                            'total_discount' => $item['discount'] * $priceGroup['quantity'],
                            'total' => ($item['price'] - $item['discount']) * $priceGroup['quantity']
                        ]);
                    }

                    // Log FIFO deduction details
                    Log::info("FIFO Stock Deduction for Product {$item['id']}", [
                        'quantity' => $item['quantity'],
                        'batches_used' => count($fifoResult['deductions']),
                        'average_cost' => $fifoResult['average_cost'],
                        'deductions' => $fifoResult['deductions']
                    ]);
                } catch (\Exception $e) {
                    // FIFO can fail when no product batches exist. Fall back to direct stock deduction.
                    Log::warning("FIFO deduction failed in SalesSystem, using fallback", [
                        'product_id' => $item['id'],
                        'product_name' => $item['name'],
                        'error' => $e->getMessage(),
                    ]);

                    $product = ProductDetail::with('stock')->find($item['id']);
                    if (! $product || ! $product->stock) {
                        throw new \Exception("Stock record not found for {$item['name']}");
                    }

                    if ($product->stock->available_stock < $item['quantity']) {
                        throw new \Exception("Insufficient stock for {$item['name']}. Required: {$item['quantity']}, Available: {$product->stock->available_stock}");
                    }

                    // Deduct stock directly from product stock table
                    $product->stock->available_stock -= $item['quantity'];
                    $product->stock->sold_count += $item['quantity'];
                    $product->stock->save();

                    // Create a single sale item with current cart values
                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $item['id'],
                        'product_code' => $item['code'],
                        'product_name' => $item['name'],
                        'product_model' => $item['model'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['price'],
                        'discount_per_unit' => $item['discount'],
                        'total_discount' => $item['discount'] * $item['quantity'],
                        'total' => ($item['price'] - $item['discount']) * $item['quantity'],
                    ]);
                }
            }

            DB::commit();

            // Ensure there is an open POS session for this user and update its totals
            try {
                $this->currentSession = POSSession::getTodaySession(Auth::id());
                if (! $this->currentSession) {
                    // Create a session with zero opening cash so admin sales are still tracked
                    $this->currentSession = POSSession::openSession(Auth::id(), 0);
                }

                // Update session totals from today's sales/payments
                $this->currentSession->updateFromSales();
                // Recalculate expected cash (cash_difference remains until close)
                $this->currentSession->calculateDifference();
            } catch (\Exception $e) {
                Log::error('Failed to update POS session after admin sale: ' . $e->getMessage());
            }

            $this->lastSaleId = $sale->id;
            $this->createdSale = Sale::with(['customer', 'items'])->find($sale->id);
            $this->showSaleModal = true;

            session()->flash('success', 'Sale created successfully! Payment status: Pending');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('SalesSystem createSale failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            $this->js("Swal.fire('error', 'Failed to create sale: " . addslashes($e->getMessage()) . "', 'error')");
        }
    }

    // Download Invoice
    public function downloadInvoice()
    {
        if (!$this->lastSaleId) {
            $this->js("Swal.fire('error', 'No sale found to download.', 'error')");
            return;
        }

        $sale = Sale::with(['customer', 'items', 'returns' => function ($q) {
            $q->with('product');
        }])->find($this->lastSaleId);

        if (!$sale) {
            $this->js("Swal.fire('error', 'Sale not found.', 'error')");
            return;
        }

        $pdf = Pdf::loadView('admin.sales.invoice', compact('sale'));
        $pdf->setPaper('a5', 'landscape');
        $pdf->setOption('dpi', 96);
        $pdf->setOption('defaultFont', 'sans-serif');

        return response()->streamDownload(
            function () use ($pdf) {
                echo $pdf->output();
            },
            'invoice-' . $sale->invoice_number . '.pdf'
        );
    }

    // Close Modal
    public function closeModal()
    {
        $this->showSaleModal = false;
        $this->lastSaleId = null;
        $this->dispatch('refreshPage');
    }

    // Continue creating new sale
    public function createNewSale()
    {
        $this->resetExcept(['customers']);
        $this->loadCustomers();
        $this->setDefaultCustomer(); // Set walking customer again for new sale
        $this->showSaleModal = false;
    }

    /**
     * Check if product quantity spans multiple batches with different prices
     * and automatically split cart items accordingly
     */
    private function checkAndSplitCartByBatchPrices($productId)
    {
        // Get all cart items for this product
        $productItems = collect($this->cart)->filter(function ($item) use ($productId) {
            return $item['id'] == $productId;
        });

        if ($productItems->isEmpty()) {
            return;
        }

        // Calculate total quantity for this product in cart
        $totalQuantity = $productItems->sum('quantity');

        // Get active batches for this product
        $batches = \App\Models\ProductBatch::getActiveBatches($productId);

        if ($batches->isEmpty() || $batches->count() == 1) {
            // No need to split if only one batch or no batches
            return;
        }

        // Simulate FIFO deduction to see which batches would be used
        $simulatedDeductions = [];
        $remainingQty = $totalQuantity;

        foreach ($batches as $batch) {
            if ($remainingQty <= 0) break;

            $deductQty = min($remainingQty, $batch->remaining_quantity);
            $simulatedDeductions[] = [
                'quantity' => $deductQty,
                'selling_price' => $batch->selling_price,
            ];
            $remainingQty -= $deductQty;
        }

        // Group by selling price to see if we need to split
        $groupedByPrice = [];
        foreach ($simulatedDeductions as $deduction) {
            $price = (string) ($deduction['selling_price'] ?? '0');
            if (!isset($groupedByPrice[$price])) {
                $groupedByPrice[$price] = 0;
            }
            $groupedByPrice[$price] += $deduction['quantity'];
        }

        // Only split if there are multiple different prices
        if (count($groupedByPrice) <= 1) {
            return;
        }

        // Remove all existing cart items for this product
        $firstItem = $productItems->first();
        $this->cart = collect($this->cart)->filter(function ($item) use ($productId) {
            return $item['id'] != $productId;
        })->values()->toArray();

        // Add new cart items - one per unique price
        foreach ($groupedByPrice as $price => $qty) {
            $this->cart[] = [
                'key' => uniqid('cart_'),
                'id' => $firstItem['id'],
                'name' => $firstItem['name'],
                'code' => $firstItem['code'],
                'model' => $firstItem['model'],
                'price' => (float) $price,
                'quantity' => $qty,
                'discount' => $firstItem['discount'],
                'total' => (((float) $price) - $firstItem['discount']) * $qty,
                'stock' => $firstItem['stock']
            ];
        }
    }

    public function render()
    {
        $layoutPath = $this->layout;

        return view('livewire.admin.sales-system', [
            'subtotal' => $this->subtotal,
            'totalDiscount' => $this->totalDiscount,
            'subtotalAfterItemDiscounts' => $this->subtotalAfterItemDiscounts,
            'additionalDiscountAmount' => $this->additionalDiscountAmount,
            'grandTotal' => $this->grandTotal
        ])->layout($layoutPath);
    }
}
