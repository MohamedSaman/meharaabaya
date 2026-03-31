<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\ProductSupplier;
use App\Models\ProductDetail;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\ProductStock;
use App\Models\ReturnSupplier as ReturnSupplierModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Livewire\Concerns\WithDynamicLayout;

#[Title("Supplier Return")]
class ReturnSupplier extends Component
{
    use WithDynamicLayout;

    public $searchSupplier = '';
    public $suppliers = [];
    public $selectedSupplier = null;

    public $supplierPurchaseOrders = [];
    public $selectedPurchaseOrder = null;
    public $selectedPurchaseOrders = [];

    public $purchaseOrderProducts = [];
    public $returnItems = [];
    public $totalReturnValue = 0;
    public $overallDiscountPerItem = 0;

    public $showPurchaseOrderModal = false;
    public $purchaseOrderModalData = null;

    public $showReturnSection = false;
    public $searchReturnProduct = '';
    public $availableProducts = [];
    public $purchaseOrderProductsForSearch = [];
    public $selectedProducts = [];

    public $previousReturns = []; // Track previously returned items
    public $returnReason = 'damaged'; // Default return reason

    /** 🔍 Search Supplier or Purchase Order */
    public function updatedSearchSupplier()
    {
        if (strlen($this->searchSupplier) > 2) {
            $this->suppliers = ProductSupplier::query()
                ->where('name', 'like', '%' . $this->searchSupplier . '%')
                ->orWhere('phone', 'like', '%' . $this->searchSupplier . '%')
                ->orWhere('email', 'like', '%' . $this->searchSupplier . '%')
                ->limit(10)
                ->get();

            $this->supplierPurchaseOrders = PurchaseOrder::where('order_code', 'like', '%' . $this->searchSupplier . '%')
                ->latest()
                ->limit(5)
                ->get();
        } else {
            $this->suppliers = [];
            $this->supplierPurchaseOrders = [];
        }
    }

    /** 👤 Select Supplier */
    public function selectSupplier($supplierId)
    {
        $this->selectedSupplier = ProductSupplier::find($supplierId);
        $this->searchSupplier = '';
        $this->suppliers = [];

        $this->resetReturnData();
        $this->loadSupplierPurchaseOrders();
    }

    /** 🧾 Load Selected Supplier's Purchase Orders */
    public function loadSupplierPurchaseOrders()
    {
        if (!$this->selectedSupplier) {
            $this->supplierPurchaseOrders = [];
            return;
        }

        $this->supplierPurchaseOrders = PurchaseOrder::where('supplier_id', $this->selectedSupplier->id)
            ->whereIn('status', ['received', 'complete'])
            ->latest()
            ->limit(5)
            ->get();
    }

    /** 🎯 Simple Purchase Order Selection for Return */
    public function selectPurchaseOrderForReturn($purchaseOrderId)
    {
        $this->resetReturnData();

        $this->selectedPurchaseOrder = PurchaseOrder::with(['items.product', 'supplier'])->find($purchaseOrderId);
        $this->selectedPurchaseOrders = [$purchaseOrderId];
        $this->showReturnSection = true;

        if ($this->selectedPurchaseOrder && $this->selectedPurchaseOrder->supplier) {
            $this->selectedSupplier = $this->selectedPurchaseOrder->supplier;
        }

        if ($this->selectedPurchaseOrder) {
            // Calculate overall discount per item
            $this->calculateOverallDiscountPerItem();

            // Load previous returns for this purchase order
            $this->loadPreviousReturns();

            // Build return items with remaining quantities
            foreach ($this->selectedPurchaseOrder->items as $item) {
                $alreadyReturned = $this->getAlreadyReturnedQuantity($item->product->id);
                $remainingQty = $item->received_quantity - $alreadyReturned;

                if ($remainingQty > 0) {
                    // Use total available stock across ALL batches (available + damaged)
                    $stock = ProductStock::where('product_id', $item->product->id)->first();
                    $totalAvailableStock = $stock ? ($stock->available_stock + $stock->damage_stock) : 0;

                    // Max returnable = min(what we received on this PO minus already returned, total stock on hand)
                    $maxReturnQty = max(0, min($remainingQty, $totalAvailableStock));

                    // Calculate per-unit discount (item discount is percentage)
                    $unitPrice = $item->unit_price;
                    $itemDiscountPercentage = $item->discount ?? 0;
                    $itemDiscountAmount = ($unitPrice * $itemDiscountPercentage) / 100;
                    $proportionalOverallDiscount = $this->overallDiscountPerItem;
                    $totalDiscountPerUnit = $itemDiscountAmount + $proportionalOverallDiscount;

                    $this->returnItems[] = [
                        'purchase_order_item_id' => $item->id,
                        'product_id' => $item->product->id,
                        'name' => $item->product->name,
                        'code' => $item->product->code,
                        'unit_price' => $unitPrice,
                        'discount_percentage' => $itemDiscountPercentage,
                        'discount_per_unit' => $itemDiscountAmount,
                        'overall_discount_per_unit' => $proportionalOverallDiscount,
                        'total_discount_per_unit' => $totalDiscountPerUnit,
                        'net_unit_price' => $unitPrice - $totalDiscountPerUnit,
                        'original_qty' => $item->received_quantity,
                        'already_returned' => $alreadyReturned,
                        'total_stock' => $totalAvailableStock,
                        'max_qty' => $maxReturnQty,
                        'return_qty' => 0,
                        'return_reason' => 'damaged',
                    ];
                }
            }
        }

        $this->loadPurchaseOrderProductsForSearch();
        $this->searchSupplier = '';
    }

    /** 📊 Calculate Overall Discount Per Item */
    private function calculateOverallDiscountPerItem()
    {
        if (!$this->selectedPurchaseOrder) {
            $this->overallDiscountPerItem = 0;
            return;
        }

        $totalQuantity = $this->selectedPurchaseOrder->items->sum('received_quantity');
        $overallDiscount = $this->selectedPurchaseOrder->discount_amount ?? 0;

        $this->overallDiscountPerItem = $totalQuantity > 0 ? ($overallDiscount / $totalQuantity) : 0;
    }

    /** 📜 Load Previous Returns */
    private function loadPreviousReturns()
    {
        if (!$this->selectedPurchaseOrder) {
            $this->previousReturns = [];
            return;
        }

        $this->previousReturns = ReturnSupplierModel::where('purchase_order_id', $this->selectedPurchaseOrder->id)
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
                            'reason' => $return->return_reason,
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
        if (!$this->selectedPurchaseOrder) return 0;

        return ReturnSupplierModel::where('purchase_order_id', $this->selectedPurchaseOrder->id)
            ->where('product_id', $productId)
            ->sum('return_quantity');
    }

    /** 👁️ View Purchase Order Details in Modal */
    public function viewPurchaseOrder($purchaseOrderId)
    {
        $purchaseOrder = PurchaseOrder::with(['items.product', 'supplier'])->find($purchaseOrderId);

        if ($purchaseOrder) {
            $overallDiscount = $purchaseOrder->discount_amount ?? 0;
            $totalQty = $purchaseOrder->items->sum('quantity');
            $discountPerItem = $totalQty > 0 ? ($overallDiscount / $totalQty) : 0;

            $this->purchaseOrderModalData = [
                'order_code' => $purchaseOrder->order_code,
                'supplier_name' => $purchaseOrder->supplier->name,
                'date' => $purchaseOrder->created_at->format('Y-m-d H:i:s'),
                'total_amount' => $purchaseOrder->total_amount,
                'overall_discount' => $overallDiscount,
                'items' => $purchaseOrder->items->map(function ($item) use ($discountPerItem) {
                    $unitPrice = $item->unit_price;
                    $itemDiscountPercentage = $item->discount ?? 0;
                    $itemDiscountAmount = ($unitPrice * $itemDiscountPercentage) / 100;
                    $totalDiscountPerUnit = $itemDiscountAmount + $discountPerItem;
                    $netPrice = $unitPrice - $totalDiscountPerUnit;

                    return [
                        'product_name' => $item->product->name,
                        'product_code' => $item->product->code,
                        'quantity' => $item->received_quantity,
                        'unit_price' => $unitPrice,
                        'item_discount_percentage' => $itemDiscountPercentage,
                        'item_discount_amount' => $itemDiscountAmount,
                        'overall_discount' => $discountPerItem,
                        'net_price' => $netPrice,
                        'total' => $item->received_quantity * $netPrice,
                    ];
                })->toArray()
            ];
            $this->showPurchaseOrderModal = true;
            $this->dispatch('show-purchase-order-modal');
        }
    }

    /** ❌ Close Purchase Order Modal */
    public function closePurchaseOrderModal()
    {
        $this->showPurchaseOrderModal = false;
        $this->purchaseOrderModalData = null;
    }

    /** 📦 Load Products from Selected Purchase Order for Search */
    private function loadPurchaseOrderProductsForSearch()
    {
        if (empty($this->selectedPurchaseOrders)) {
            $this->purchaseOrderProductsForSearch = [];
            return;
        }

        $allProducts = collect();

        foreach ($this->selectedPurchaseOrders as $purchaseOrderId) {
            $purchaseOrder = PurchaseOrder::with(['items.product'])->find($purchaseOrderId);
            if ($purchaseOrder) {
                $products = $purchaseOrder->items->map(function ($item) use ($purchaseOrder) {
                    $alreadyReturned = $this->getAlreadyReturnedQuantity($item->product->id);
                    $remainingQty = $item->received_quantity - $alreadyReturned;

                    return [
                        'id' => $item->product->id,
                        'name' => $item->product->name,
                        'code' => $item->product->code,
                        'image' => $item->product->image,
                        'unit_price' => $item->unit_price,
                        'purchase_order_id' => $purchaseOrder->id,
                        'order_code' => $purchaseOrder->order_code,
                        'max_qty' => $remainingQty,
                    ];
                });
                $allProducts = $allProducts->merge($products);
            }
        }

        $this->purchaseOrderProductsForSearch = $allProducts->unique('id')->values()->toArray();
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

        if (empty($this->returnItems) || !$this->selectedPurchaseOrder) {
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

                // Check if product has any stock available (damaged or available)
                $stock = ProductStock::where('product_id', $item['product_id'])->first();
                if (!$stock || ($stock->damage_stock == 0 && $stock->available_stock == 0)) {
                    $this->js("Swal.fire('Error!', 'Cannot return " . $item['name'] . ". No stock available (both damaged and available stock are 0).', 'error')");
                    return;
                }

                // Check if there's enough stock to return
                $totalAvailableForReturn = $stock->damage_stock + $stock->available_stock;
                if ($item['return_qty'] > $totalAvailableForReturn) {
                    $this->js("Swal.fire('Error!', 'Cannot return {$item['return_qty']} units of {$item['name']}.<br><br>📦 Purchased on this PO: {$item['original_qty']} units<br>↩️ Already returned: {$item['already_returned']} units<br>🏪 Total in stock: {$totalAvailableForReturn} units<br>⚠️ Only {$item['max_qty']} units can be returned.', 'error')");
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

        if (empty($this->returnItems) || !$this->selectedSupplier || !$this->selectedPurchaseOrder) return;

        $itemsToReturn = array_filter($this->returnItems, function ($item) {
            return isset($item['return_qty']) && $item['return_qty'] > 0;
        });

        if (empty($itemsToReturn)) {
            $this->dispatch('alert', ['message' => 'No valid return quantities entered.']);
            return;
        }

        DB::transaction(function () use ($itemsToReturn) {
            $totalReturnAmount = 0;

            foreach ($itemsToReturn as $item) {
                $returnAmount = $item['return_qty'] * $item['net_unit_price'];
                $totalReturnAmount += $returnAmount;

                ReturnSupplierModel::create([
                    'purchase_order_id' => $this->selectedPurchaseOrder->id,
                    'product_id' => $item['product_id'],
                    'return_quantity' => $item['return_qty'],
                    'unit_price' => $item['net_unit_price'],
                    'total_amount' => $returnAmount,
                    'return_reason' => $item['return_reason'] ?? 'damaged',
                    'notes' => 'Supplier return processed via system',
                ]);

                // 🔹 Add returned quantity back to matching batch
                $this->addReturnToBatch(
                    $item['product_id'],
                    $item['return_qty'],
                    $this->selectedPurchaseOrder->id
                );

                // Reduce stock
                $this->updateProductStock($item['product_id'], $item['return_qty'], $item['return_reason']);
            }

            // Handle return amount - reduce dues or add to overpayment
            $this->processReturnPaymentAdjustment($totalReturnAmount);
        });

        $this->clearReturnCart();
        $this->dispatch('alert', ['message' => 'Supplier return processed successfully!']);
        $this->dispatch('close-return-modal');
        $this->dispatch('reload-page');
    }

    /** 💰 Process Return Payment Adjustment - Reduce dues or add overpayment */
    private function processReturnPaymentAdjustment($totalReturnAmount)
    {
        if (!$this->selectedPurchaseOrder || $totalReturnAmount <= 0) return;

        $order = PurchaseOrder::find($this->selectedPurchaseOrder->id);
        if (!$order) return;

        $dueAmount = $order->due_amount ?? 0;
        $remainingReturnAmount = $totalReturnAmount;

        // First, reduce the due amount of this order
        if ($dueAmount > 0) {
            $reduction = min($dueAmount, $remainingReturnAmount);
            $order->due_amount = max(0, $dueAmount - $reduction);
            $order->save();
            $remainingReturnAmount -= $reduction;
        }

        // If there's still remaining return amount, add it as supplier overpayment
        if ($remainingReturnAmount > 0) {
            $supplier = ProductSupplier::find($order->supplier_id);
            if ($supplier) {
                $supplier->addOverpayment($remainingReturnAmount);
            }
        }
    }

    /** 📈 Update Product Stock (Reduce stock for supplier returns) */
    private function updateProductStock($productId, $quantity, $reason)
    {
        $stock = ProductStock::where('product_id', $productId)->first();

        if ($stock) {
            // First, try to return from damaged stock
            $returnFromDamaged = min($quantity, $stock->damage_stock);
            $stock->damage_stock = max(0, $stock->damage_stock - $returnFromDamaged);

            // Then, return remaining quantity from available stock
            $returnFromAvailable = $quantity - $returnFromDamaged;
            $stock->available_stock = max(0, $stock->available_stock - $returnFromAvailable);

            // Always reduce total stock by the full quantity (we're removing from inventory)
            $stock->total_stock = max(0, $stock->total_stock - $quantity);

            $stock->save();
        }
    }

    /** 🔄 Add Returned Quantity to Matching Batch */
    private function addReturnToBatch($productId, $quantity, $purchaseOrderId)
    {
        // 🎯 First, try to find the batch from THIS specific purchase order
        $matchingBatch = \App\Models\ProductBatch::where('product_id', $productId)
            ->where('purchase_order_id', $purchaseOrderId)
            ->first();

        if ($matchingBatch) {
            // Found the exact batch from this PO, add quantity back
            $matchingBatch->remaining_quantity += $quantity;
            $matchingBatch->quantity += $quantity;

            // Reactivate if it was depleted
            if ($matchingBatch->status === 'depleted') {
                $matchingBatch->status = 'active';
                Log::info("Batch {$matchingBatch->batch_number} reactivated with {$quantity} returned units");
            }

            $matchingBatch->save();
            Log::info("✅ Returned {$quantity} units to batch {$matchingBatch->batch_number} (PO: #{$purchaseOrderId})");
            return;
        }

        // 🔄 Fallback: If no batch from this PO, find by selling price
        $productPrice = \App\Models\ProductPrice::where('product_id', $productId)->first();

        if ($productPrice) {
            $fallbackBatch = \App\Models\ProductBatch::where('product_id', $productId)
                ->where('selling_price', $productPrice->selling_price)
                ->where('status', 'depleted') // Prefer depleted batches for reactivation
                ->orderBy('received_date', 'desc')
                ->first();

            if ($fallbackBatch) {
                $fallbackBatch->remaining_quantity += $quantity;
                $fallbackBatch->quantity += $quantity;
                $fallbackBatch->status = 'active';
                $fallbackBatch->save();
                Log::info("✅ Fallback: Returned {$quantity} units to batch {$fallbackBatch->batch_number} (matched by selling price)");
                return;
            }
        }

        // 📦 Last fallback: Add to default batch
        $defaultBatch = \App\Models\ProductBatch::getOrCreateDefaultBatch($productId);
        $defaultBatch->remaining_quantity += $quantity;
        $defaultBatch->quantity += $quantity;
        $defaultBatch->save();
        Log::info("⚠️  Added {$quantity} returned units to default batch (no matching batch found)");
    }

    /** 🔄 Reset Return Data */
    private function resetReturnData()
    {
        $this->selectedPurchaseOrder = null;
        $this->selectedPurchaseOrders = [];
        $this->purchaseOrderProducts = [];
        $this->returnItems = [];
        $this->selectedProducts = [];
        $this->showReturnSection = false;
        $this->searchReturnProduct = '';
        $this->availableProducts = [];
        $this->purchaseOrderProductsForSearch = [];
        $this->totalReturnValue = 0;
        $this->overallDiscountPerItem = 0;
        $this->previousReturns = [];
        $this->returnReason = 'damaged';
    }

    public function render()
    {
        return view('livewire.admin.return-supplier')->layout($this->layout);
    }
}
