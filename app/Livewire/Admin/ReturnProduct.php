<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Customer;
use App\Models\ProductDetail;
use App\Models\Sale;
use App\Models\ProductStock;
use App\Models\ReturnsProduct;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Livewire\Concerns\WithDynamicLayout;

#[Title("Product Return")]
class ReturnProduct extends Component
{
    use WithDynamicLayout;

    public $searchCustomer = '';
    public $customers = [];
    public $selectedCustomer = null;

    public $customerInvoices = [];
    public $selectedInvoice = null;
    public $selectedInvoices = [];

    public $invoiceProducts = [];
    public $returnItems = [];
    public $totalReturnValue = 0;
    public $overallDiscountPerItem = 0;

    public $showInvoiceModal = false;
    public $invoiceModalData = null;

    public $showReturnSection = false;
    public $searchReturnProduct = '';
    public $availableProducts = [];
    public $invoiceProductsForSearch = [];
    public $selectedProducts = [];

    public $previousReturns = []; // Track previously returned items

    /** 🔍 Search Customer or Invoice */
    public function updatedSearchCustomer()
    {
        if (strlen($this->searchCustomer) > 2) {
            $this->customers = Customer::query()
                ->where('name', 'like', '%' . $this->searchCustomer . '%')
                ->orWhere('phone', 'like', '%' . $this->searchCustomer . '%')
                ->orWhere('email', 'like', '%' . $this->searchCustomer . '%')
                ->limit(10)
                ->get();

            $this->customerInvoices = Sale::where('invoice_number', 'like', '%' . $this->searchCustomer . '%')
                ->latest()
                ->limit(5)
                ->get();
        } else {
            $this->customers = [];
            $this->customerInvoices = [];
        }
    }

    /** 👤 Select Customer */
    public function selectCustomer($customerId)
    {
        $this->selectedCustomer = Customer::find($customerId);
        $this->searchCustomer = '';
        $this->customers = [];

        $this->resetReturnData();
        $this->loadCustomerInvoices();
    }

    /** 🧾 Load Selected Customer's Invoices */
    public function loadCustomerInvoices()
    {
        if (!$this->selectedCustomer) {
            $this->customerInvoices = [];
            return;
        }

        $this->customerInvoices = Sale::where('customer_id', $this->selectedCustomer->id)
            ->latest()
            ->limit(5)
            ->get();
    }

    /** 🎯 Simple Invoice Selection for Return */
    public function selectInvoiceForReturn($invoiceId)
    {
        $this->resetReturnData();

        $this->selectedInvoice = Sale::with(['items.product', 'customer'])->find($invoiceId);
        $this->selectedInvoices = [$invoiceId];
        $this->showReturnSection = true;

        if ($this->selectedInvoice && $this->selectedInvoice->customer) {
            $this->selectedCustomer = $this->selectedInvoice->customer;
        }

        if ($this->selectedInvoice) {
            // Calculate overall discount per item
            $this->calculateOverallDiscountPerItem();

            // Load previous returns for this invoice
            $this->loadPreviousReturns();

            // Build return items with remaining quantities
            foreach ($this->selectedInvoice->items as $item) {
                $alreadyReturned = $this->getAlreadyReturnedQuantity($item->product->id);
                $remainingQty = $item->quantity - $alreadyReturned;

                if ($remainingQty > 0) {
                    // Apply unit discount first
                    $unitDiscount = $item->discount_per_unit ?? 0;

                    // Apply proportional overall discount per item
                    $proportionalOverallDiscount = $this->overallDiscountPerItem;

                    // Total discount per unit is unit discount + proportional overall discount
                    $totalDiscountPerUnit = $unitDiscount + $proportionalOverallDiscount;

                    // Net price after all discounts
                    $netUnitPrice = $item->unit_price - $totalDiscountPerUnit;

                    $this->returnItems[] = [
                        'product_id' => $item->product->id,
                        'name' => $item->product->name,
                        'unit_price' => $item->unit_price,
                        'discount_per_unit' => $unitDiscount,
                        'overall_discount_per_unit' => $proportionalOverallDiscount,
                        'total_discount_per_unit' => $totalDiscountPerUnit,
                        'net_unit_price' => $netUnitPrice,
                        'original_qty' => $item->quantity,
                        'already_returned' => $alreadyReturned,
                        'max_qty' => $remainingQty,
                        'return_qty' => 0,
                    ];
                }
            }
        }

        $this->loadInvoiceProductsForSearch();
        $this->searchCustomer = '';
    }

    /** 📊 Calculate Overall Discount Per Item */
    private function calculateOverallDiscountPerItem()
    {
        if (!$this->selectedInvoice) {
            $this->overallDiscountPerItem = 0;
            return;
        }

        $totalQuantity = $this->selectedInvoice->items->sum('quantity');
        $totalDiscountAmount = $this->selectedInvoice->discount_amount ?? 0;

        // Calculate total unit discounts from all sale items
        $totalUnitDiscounts = $this->selectedInvoice->items->sum(function ($item) {
            return ($item->discount_per_unit ?? 0) * $item->quantity;
        });

        // Calculate remaining overall discount after unit discounts
        $remainingOverallDiscount = $totalDiscountAmount - $totalUnitDiscounts;

        // Distribute remaining overall discount per item
        $this->overallDiscountPerItem = $totalQuantity > 0 ? ($remainingOverallDiscount / $totalQuantity) : 0;
    }

    /** 📜 Load Previous Returns */
    private function loadPreviousReturns()
    {
        if (!$this->selectedInvoice) {
            $this->previousReturns = [];
            return;
        }

        $this->previousReturns = ReturnsProduct::where('sale_id', $this->selectedInvoice->id)
            ->with('product')
            ->get()
            ->groupBy('product_id')
            ->map(function ($returns) {
                return [
                    'product_name' => $returns->first()->product->name ?? 'Unknown',
                    'total_returned' => $returns->sum('return_quantity'),
                    'total_amount' => $returns->sum('total_amount'),
                    'returns' => $returns->map(function ($return) {
                        return [
                            'quantity' => $return->return_quantity,
                            'amount' => $return->total_amount,
                            'date' => $return->created_at->format('Y-m-d H:i'),
                        ];
                    })->toArray()
                ];
            })
            ->toArray();
    }

    /** 🔢 Get Already Returned Quantity */
    private function getAlreadyReturnedQuantity($productId)
    {
        if (!$this->selectedInvoice) return 0;

        return ReturnsProduct::where('sale_id', $this->selectedInvoice->id)
            ->where('product_id', $productId)
            ->sum('return_quantity');
    }

    /** 👁️ View Invoice Details in Modal */
    public function viewInvoice($invoiceId)
    {
        $invoice = Sale::with(['items.product', 'customer'])->find($invoiceId);

        if ($invoice) {
            $totalDiscountAmount = $invoice->discount_amount ?? 0;
            $totalQty = $invoice->items->sum('quantity');

            // Calculate total unit discounts
            $totalUnitDiscounts = $invoice->items->sum(function ($item) {
                return ($item->discount_per_unit ?? 0) * $item->quantity;
            });

            // Calculate remaining overall discount per item
            $remainingOverallDiscount = $totalDiscountAmount - $totalUnitDiscounts;
            $overallDiscountPerItem = $totalQty > 0 ? ($remainingOverallDiscount / $totalQty) : 0;

            $this->invoiceModalData = [
                'invoice_number' => $invoice->invoice_number,
                'customer_name' => $invoice->customer->name,
                'date' => $invoice->created_at->format('Y-m-d H:i:s'),
                'total_amount' => $invoice->total_amount,
                'overall_discount' => $totalDiscountAmount,
                'items' => $invoice->items->map(function ($item) use ($overallDiscountPerItem) {
                    $itemDiscount = $item->discount_per_unit ?? 0;
                    $totalDiscountPerUnit = $itemDiscount + $overallDiscountPerItem;
                    $netPrice = $item->unit_price - $totalDiscountPerUnit;

                    return [
                        'product_name' => $item->product->name,
                        'product_code' => $item->product->code,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'item_discount' => $itemDiscount,
                        'overall_discount' => $overallDiscountPerItem,
                        'net_price' => $netPrice,
                        'total' => $item->quantity * $netPrice,
                    ];
                })->toArray()
            ];
            $this->showInvoiceModal = true;
            $this->dispatch('show-invoice-modal');
        }
    }

    /** ❌ Close Invoice Modal */
    public function closeInvoiceModal()
    {
        $this->showInvoiceModal = false;
        $this->invoiceModalData = null;
    }

    /** 📦 Load Products from Selected Invoice for Search */
    private function loadInvoiceProductsForSearch()
    {
        if (empty($this->selectedInvoices)) {
            $this->invoiceProductsForSearch = [];
            return;
        }

        $allProducts = collect();

        foreach ($this->selectedInvoices as $invoiceId) {
            $invoice = Sale::with(['items.product.price'])->find($invoiceId);
            if ($invoice) {
                $products = $invoice->items->map(function ($item) use ($invoice) {
                    $alreadyReturned = $this->getAlreadyReturnedQuantity($item->product->id);
                    $remainingQty = $item->quantity - $alreadyReturned;

                    return [
                        'id' => $item->product->id,
                        'name' => $item->product->name,
                        'code' => $item->product->code,
                        'image' => $item->product->image,
                        'selling_price' => $item->unit_price,
                        'invoice_id' => $invoice->id,
                        'invoice_number' => $invoice->invoice_number,
                        'max_qty' => $remainingQty,
                    ];
                });
                $allProducts = $allProducts->merge($products);
            }
        }

        $this->invoiceProductsForSearch = $allProducts->unique('id')->values()->toArray();
    }

    /** ❌ Remove Product from Return Cart */
    public function removeFromReturn($index)
    {
        unset($this->returnItems[$index]);
        $this->returnItems = array_values($this->returnItems);
        $this->calculateTotalReturnValue();
    }

    /** 🧹 Clear Cart */
    public function clearReturnCart()
    {
        $this->returnItems = [];
        $this->totalReturnValue = 0;
    }

    /** ♻️ Auto-update total when quantities change */
    public function updatedReturnItems()
    {
        $this->calculateTotalReturnValue();
    }

    /** 💰 Calculate Total Return Value */
    private function calculateTotalReturnValue()
    {
        $this->totalReturnValue = collect($this->returnItems)->sum(
            fn($item) => $item['return_qty'] * $item['net_unit_price']
        );
    }

    /** ✅ Validate before showing confirmation */
    public function processReturn()
    {
        $this->calculateTotalReturnValue();

        if (empty($this->returnItems) || !$this->selectedInvoice) {
            $this->js("Swal.fire('Error!', 'Please select items for return.', 'error')");
            return;
        }

        $hasReturnItems = false;
        foreach ($this->returnItems as $item) {
            if ($item['return_qty'] < 0) {
                $this->js("Swal.fire('Error!', 'Return quantity cannot be negative for " . $item['name'] . "', 'error')");
                return;
            }

            if (isset($item['return_qty']) && $item['return_qty'] > 0) {
                if ($item['return_qty'] > $item['max_qty']) {
                    $this->js("Swal.fire('Error!', 'Invalid return quantity for " . $item['name'] . ". Maximum available: " . $item['max_qty'] . "', 'error')");
                    return;
                }
                $hasReturnItems = true;
            }
        }

        if (!$hasReturnItems) {
            $this->dispatch('alert', ['message' => 'Please enter at least one return quantity.']);
            return;
        }

        $this->dispatch('show-return-modal');
    }

    /** 💾 Confirm Return & Save to Database */
    public function confirmReturn()
    {
        $this->calculateTotalReturnValue();

        if (empty($this->returnItems) || !$this->selectedCustomer || !$this->selectedInvoice) return;

        $itemsToReturn = array_filter($this->returnItems, function ($item) {
            return isset($item['return_qty']) && $item['return_qty'] > 0;
        });

        if (empty($itemsToReturn)) {
            $this->dispatch('alert', ['message' => 'No valid return quantities entered.']);
            return;
        }

        DB::transaction(function () use ($itemsToReturn) {
            foreach ($itemsToReturn as $item) {
                ReturnsProduct::create([
                    'sale_id' => $this->selectedInvoice->id,
                    'product_id' => $item['product_id'],
                    'return_quantity' => $item['return_qty'],
                    'selling_price' => $item['net_unit_price'],
                    'total_amount' => $item['return_qty'] * $item['net_unit_price'],
                    'notes' => 'Customer return processed via system',
                ]);

                $this->updateProductStock($item['product_id'], $item['return_qty']);
            }
        });

        $this->clearReturnCart();
        $this->dispatch('alert', ['message' => 'Return processed successfully!']);
        $this->dispatch('close-return-modal');
        $this->dispatch('reload-page');
    }

    /** 📈 Update Product Stock */
    private function updateProductStock($productId, $quantity)
    {
        $stock = ProductStock::where('product_id', $productId)->first();

        if ($stock) {
            $stock->available_stock += $quantity;
            $stock->total_stock += $quantity;
            $stock->save();
        } else {
            ProductStock::create([
                'product_id' => $productId,
                'available_stock' => $quantity,
                'damage_stock' => 0,
                'total_stock' => $quantity,
                'sold_count' => 0,
                'restocked_quantity' => 0,
            ]);
        }
    }

    /** 🔄 Reset Return Data */
    private function resetReturnData()
    {
        $this->selectedInvoice = null;
        $this->selectedInvoices = [];
        $this->invoiceProducts = [];
        $this->returnItems = [];
        $this->selectedProducts = [];
        $this->showReturnSection = false;
        $this->searchReturnProduct = '';
        $this->availableProducts = [];
        $this->invoiceProductsForSearch = [];
        $this->totalReturnValue = 0;
        $this->overallDiscountPerItem = 0;
        $this->previousReturns = [];
    }

    public function render()
    {
        return view('livewire.admin.return-product')->layout($this->layout);
    }
}
