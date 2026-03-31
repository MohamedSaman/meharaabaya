<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\ProductDetail;
use App\Models\ProductStock;
use App\Models\ProductBatch;
use App\Models\ProductPrice;
use Illuminate\Support\Str;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Livewire\Concerns\WithDynamicLayout;
use Livewire\WithPagination;

#[Title("Goods Receive Note")]
class GRN extends Component
{
    use WithDynamicLayout, WithPagination;


    public $selectedPO = null;
    public $grnItems = [];
    public $searchProduct = '';
    public $searchResults = [];
    public $search = '';
    public $newItem = ['product_id' => null, 'name' => '', 'qty' => 1, 'unit_price' => 0, 'discount' => 0, 'status' => 'received'];

    protected $listeners = ['deleteGRNItem'];
    public $perPage = 10;

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function mount()
    {

        $this->searchResults = ['unplanned' => []];
    }

    // public function loadPurchaseOrders()
    // {
    //     // Show both complete and received orders in the table
    //     $this->purchaseOrders = PurchaseOrder::whereIn('status', ['complete', 'received'])
    //         ->with(['supplier', 'items.product'])
    //         ->latest()
    //         ->paginate(10);
    // }

    // Add this method to get counts for both statuses
    public function getOrderCounts()
    {
        return [
            'complete' => PurchaseOrder::where('status', 'complete')->count(),
            'received' => PurchaseOrder::where('status', 'received')->count(),
            'total' => PurchaseOrder::whereIn('status', ['complete', 'received'])->count()
        ];
    }
    public function viewGRN($orderId)
    {
        $this->selectedPO = PurchaseOrder::with(['supplier', 'items' => function ($query) {
            $query->where('status', 'received');
        }, 'items.product'])->find($orderId);

        if (!$this->selectedPO) {
            $this->dispatch('alert', ['message' => 'Order not found!', 'type' => 'error']);
            return;
        }

        $this->grnItems = [];
        $this->searchResults = ['unplanned' => []];

        foreach ($this->selectedPO->items as $item) {
            $this->grnItems[] = [
                'id' => $item->id,
                'product_id' => $item->product_id ?? ($item->product->id ?? null),
                'code' => $item->product->code ?? ($item->code ?? ''),
                'name' => $item->product->name ?? ($item->name ?? ''),
                'ordered_qty' => $item->quantity,
                'received_qty' => $item->received_quantity ?? $item->quantity,
                'unit_price' => $item->unit_price,
                'discount' => $item->discount,
                'discount_type' => $item->discount_type ?? 'percent',
                'status' => $item->status ?? 'received',
            ];
        }

        // Dispatch event to open modal after data is loaded
        $this->dispatch('open-view-grn-modal');
    }

    public function openGRN($orderId)
    {
        $this->selectedPO = PurchaseOrder::with(['supplier', 'items.product'])->find($orderId);

        if (!$this->selectedPO) {
            $this->dispatch('alert', ['message' => 'Order not found!', 'type' => 'error']);
            return;
        }

        $this->grnItems = [];
        $this->searchResults = ['unplanned' => []];

        foreach ($this->selectedPO->items as $item) {
            // Get current product price for selling price reference
            $product = ProductDetail::with('price')->find($item->product_id);
            $currentSellingPrice = $product && $product->price ? $product->price->selling_price : 0;

            $this->grnItems[] = [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'code' => $item->product->code ?? ($product?->code ?? ''),
                'name' => $item->product->name ?? ($product?->name ?? ''),
                'ordered_qty' => $item->quantity,
                'received_qty' => $item->quantity,
                'unit_price' => $item->unit_price,
                'discount' => $item->discount,
                'discount_type' => $item->discount_type ?? 'percent',
                'selling_price' => $currentSellingPrice, // Add selling price
                'status' => $item->status,
            ];
        }

        // Dispatch event to open modal after data is loaded
        $this->dispatch('open-grn-modal');
    }

    public function updated($propertyName)
    {
        if (preg_match('/grnItems\.(\d+)\.name/', $propertyName, $matches)) {
            $index = $matches[1];
            $searchTerm = $this->grnItems[$index]['name'];
            if (strlen($searchTerm) > 1) {
                $this->searchResults[$index] = ProductDetail::where('name', 'like', "%{$searchTerm}%")
                    ->orWhere('code', 'like', "%{$searchTerm}%")
                    ->with(['price', 'stock'])
                    ->limit(5)
                    ->get();
            } else {
                $this->searchResults[$index] = [];
            }
        } elseif ($propertyName === 'searchProduct') {
            if (strlen($this->searchProduct) > 1) {
                $this->searchResults['unplanned'] = ProductDetail::where('name', 'like', "%{$this->searchProduct}%")
                    ->orWhere('code', 'like', "%{$this->searchProduct}%")
                    ->with(['price', 'stock'])
                    ->limit(5)
                    ->get();
            } else {
                $this->searchResults['unplanned'] = [];
            }
        }
    }

    public function selectProduct($index, $productId)
    {
        if (!is_numeric($productId)) return;

        $product = ProductDetail::with('price')->find($productId);
        if ($product) {
            $unitPrice = $product->price ? $product->price->supplier_price : 0;
            if ($index === -1) {
                $this->newItem['product_id'] = $product->id;
                $this->newItem['name'] = $product->name;
                $this->newItem['unit_price'] = $unitPrice;
                $this->newItem['status'] = 'received';
                $this->searchProduct = $product->name;
                $this->searchResults['unplanned'] = [];
            } else {
                $this->grnItems[$index]['product_id'] = $product->id;
                $this->grnItems[$index]['name'] = $product->name;
                $this->grnItems[$index]['unit_price'] = $unitPrice;
                $this->grnItems[$index]['status'] = 'received';
                $this->searchResults[$index] = [];
            }
        }
    }

    public function addUnplannedItem()
    {
        // Validate input fields
        if (!$this->newItem['name']) {
            $this->dispatch('alert', ['message' => 'Product name is required!', 'type' => 'error']);
            return;
        }

        $qty = (int) $this->newItem['qty'];
        $unitPrice = (float) $this->newItem['unit_price'];
        $discount = (float) $this->newItem['discount'];

        if ($qty < 1) {
            $this->dispatch('alert', ['message' => 'Quantity must be at least 1!', 'type' => 'error']);
            return;
        }

        if ($unitPrice < 0) {
            $this->dispatch('alert', ['message' => 'Unit price cannot be negative!', 'type' => 'error']);
            return;
        }

        if ($discount < 0) {
            $this->dispatch('alert', ['message' => 'Discount cannot be negative!', 'type' => 'error']);
            return;
        }

        $this->grnItems[] = [
            'product_id' => $this->newItem['product_id'],
            'name' => $this->newItem['name'],
            'ordered_qty' => 0,
            'received_qty' => $qty,
            'unit_price' => $unitPrice,
            'discount' => $discount,
            'discount_type' => 'percent',
            'status' => 'received',
        ];

        $this->newItem = ['product_id' => null, 'name' => '', 'qty' => 1, 'unit_price' => 0, 'discount' => 0, 'status' => 'received'];
        $this->searchProduct = '';
        $this->searchResults['unplanned'] = [];
    }

    public function addNewRow()
    {
        $this->grnItems[] = [
            'product_id' => null,
            'name' => '',
            'ordered_qty' => 0,
            'received_qty' => 1,
            'unit_price' => 0,
            'discount' => 0,
            'discount_type' => 'percent',
            'status' => 'received',
        ];

        // Initialize search results for the new row
        $newIndex = count($this->grnItems) - 1;
        $this->searchResults[$newIndex] = [];
    }

    public function deleteGRNItem($index)
    {
        if (isset($this->grnItems[$index]['id'])) {
            $orderItem = PurchaseOrderItem::find($this->grnItems[$index]['id']);
            if ($orderItem) {
                $orderItem->status = 'notreceived';
                $orderItem->save();
            }
        }
        $this->grnItems[$index]['status'] = 'notreceived';
        $this->searchResults[$index] = [];
    }

    public function correctGRNItem($index)
    {
        $item = $this->grnItems[$index];
        $productId = $item['product_id'];
        $receivedQty = (int) ($item['received_qty'] ?? 0);

        // Mark the item as received in the UI
        $this->grnItems[$index]['status'] = 'received';

        // Update stock immediately if we have a valid product and quantity
        if ($productId && $receivedQty > 0) {
            // Calculate prices - Cast to proper types
            $unitPrice = (float) ($item['unit_price'] ?? 0);
            $discount = (float) ($item['discount'] ?? 0);

            // Always treat discount as percentage
            $supplierPrice = $unitPrice - ($unitPrice * $discount / 100);
            $supplierPrice = max(0, $supplierPrice);

            // Get selling price
            $product = ProductDetail::with('price')->find($productId);
            $sellingPrice = $supplierPrice;
            if ($product && $product->price) {
                $currentSupplierPrice = (float) $product->price->supplier_price;
                $currentSellingPrice = (float) $product->price->selling_price;
                if ($currentSupplierPrice > 0) {
                    $ratio = $currentSellingPrice / $currentSupplierPrice;
                    $sellingPrice = $supplierPrice * $ratio;
                } else {
                    $sellingPrice = $currentSellingPrice;
                }
            }

            $this->updateProductStock($productId, $receivedQty, $supplierPrice, $sellingPrice, $this->selectedPO ? $this->selectedPO->id : null);
        }
    }

    public function saveGRN()
    {
        if (!$this->selectedPO || empty($this->grnItems)) return;

        $receivedItemsCount = 0;
        $totalItemsCount = 0;

        foreach ($this->grnItems as $item) {
            // Skip items that are marked as not received
            if (strtolower($item['status'] ?? '') === 'notreceived') {
                $totalItemsCount++;
                continue;
            }

            $productId = $item['product_id'];
            $receivedQty = (int) ($item['received_qty'] ?? 0);

            // Skip items without a valid product_id (empty rows)
            if (!$productId) {
                continue;
            }

            $totalItemsCount++;

            // Calculate selling price based on unit price and discount - Cast to numeric types
            $unitPrice = (float) ($item['unit_price'] ?? 0);
            $discount = (float) ($item['discount'] ?? 0);

            // Always treat discount as percentage
            $supplierPrice = $unitPrice - ($unitPrice * $discount / 100);
            $supplierPrice = max(0, $supplierPrice); // Ensure non-negative

            // Use selling price from the form if provided, otherwise calculate
            $sellingPrice = (float) ($item['selling_price'] ?? 0);

            if ($sellingPrice <= 0) {
                // Calculate selling price based on markup ratio if not provided
                $product = ProductDetail::with('price')->find($productId);
                if ($product && $product->price) {
                    $currentSupplierPrice = (float) $product->price->supplier_price;
                    $currentSellingPrice = (float) $product->price->selling_price;
                    if ($currentSupplierPrice > 0) {
                        $ratio = $currentSellingPrice / $currentSupplierPrice;
                        $sellingPrice = $supplierPrice * $ratio;
                    } else {
                        $sellingPrice = $currentSellingPrice;
                    }
                } else {
                    // Default markup of 20% if no existing price
                    $sellingPrice = $supplierPrice * 1.2;
                }
            }

            if (isset($item['id'])) {
                // Update existing order item
                $orderItem = PurchaseOrderItem::find($item['id']);
                if ($orderItem) {
                    // Calculate delta: new received qty minus previously recorded qty
                    $previousQty = $orderItem->quantity ?? 0;
                    $orderItem->quantity = $receivedQty;
                    $orderItem->unit_price = $item['unit_price'];
                    $orderItem->discount = $item['discount'];
                    $orderItem->discount_type = 'percent';
                    $orderItem->status = $item['status'];
                    $orderItem->save();

                    // Update stock only with the delta (received now)
                    if (strtolower($item['status'] ?? '') === 'received' && $receivedQty > 0) {
                        $delta = $receivedQty - $previousQty;
                        if ($delta > 0) {
                            $this->updateProductStock($productId, $delta, $supplierPrice, $sellingPrice, $this->selectedPO->id);
                        }
                        $receivedItemsCount++;
                    }
                }
            } else {
                // Always save new GRN items as 'received' status
                $newOrderItem = PurchaseOrderItem::create([
                    'order_id' => $this->selectedPO->id,
                    'product_id' => $productId,
                    'quantity' => $receivedQty,
                    'unit_price' => $item['unit_price'] ?? 0,
                    'discount' => $item['discount'] ?? 0,
                    'discount_type' => 'percent',
                    'status' => 'received',
                ]);

                // Update stock for new received item
                if ($receivedQty > 0) {
                    $this->updateProductStock($productId, $receivedQty, $supplierPrice, $sellingPrice, $this->selectedPO->id);
                    $receivedItemsCount++;
                }
            }
        }

        // Update order received date and status based on received items
        $this->selectedPO->received_date = now();

        // Determine overall order status
        if ($receivedItemsCount > 0 && $receivedItemsCount === $totalItemsCount) {
            // All items received - mark as fully received
            $this->selectedPO->status = 'received';
        } elseif ($receivedItemsCount > 0) {
            // Some items received but not all - keep as complete (partial receipt)
            $this->selectedPO->status = 'complete';
        }
        // If no items received, status remains as it was

        $this->selectedPO->save();

        $this->dispatch('alert', ['message' => 'GRN processed successfully! Stock updated.']);
        $this->selectedPO = null;
        $this->grnItems = [];
        $this->searchResults = ['unplanned' => []];
        $this->loadPurchaseOrders();
    }

    private function updateProductStock($productId, $quantity, $supplierPrice = 0, $sellingPrice = 0, $purchaseOrderId = null)
    {
        $stock = ProductStock::where('product_id', $productId)->first();

        // Get product details to check prices
        $product = ProductDetail::with('price')->find($productId);
        $productPrice = $product->price;

        // If prices not provided, get from product
        if ($supplierPrice == 0 && $productPrice) {
            $supplierPrice = $productPrice->supplier_price;
        }
        if ($sellingPrice == 0 && $productPrice) {
            $sellingPrice = $productPrice->selling_price;
        }

        // Check if product already has stock
        $hasExistingStock = $stock && $stock->available_stock > 0;

        // Ensure default batch exists (created on first purchase)
        $defaultBatch = ProductBatch::getOrCreateDefaultBatch($productId);

        // Check if this is the first purchase (no batches except default)
        $existingBatches = ProductBatch::where('product_id', $productId)
            ->where('batch_number', 'not like', 'DEFAULT-%')
            ->count();

        if ($existingBatches == 0) {
            // First purchase - add to default batch instead of creating new batch
            $defaultBatch->supplier_price = $supplierPrice;
            $defaultBatch->selling_price = $sellingPrice;
            $defaultBatch->quantity += $quantity;
            $defaultBatch->remaining_quantity += $quantity;
            $defaultBatch->purchase_order_id = $purchaseOrderId;
            $defaultBatch->save();

            $batch = $defaultBatch;
            Log::info("First purchase: Added {$quantity} units to default batch for product #{$productId}");
        } else {
            // Subsequent purchases with different prices - create new batch
            $batchNumber = ProductBatch::generateBatchNumber($productId);
            $batch = ProductBatch::create([
                'product_id' => $productId,
                'batch_number' => $batchNumber,
                'purchase_order_id' => $purchaseOrderId,
                'supplier_price' => $supplierPrice,
                'selling_price' => $sellingPrice,
                'quantity' => $quantity,
                'remaining_quantity' => $quantity,
                'received_date' => now(),
                'status' => 'active',
            ]);
            Log::info("Created new batch {$batchNumber} for product #{$productId}");
        }

        // Update product stock totals
        if ($stock) {
            // Update existing stock
            $stock->available_stock += $quantity;
            $stock->total_stock += $quantity;
            $stock->restocked_quantity += $quantity;
            $stock->save();
        } else {
            // Create new stock record
            $stock = ProductStock::create([
                'product_id' => $productId,
                'available_stock' => $quantity,
                'damage_stock' => 0,
                'total_stock' => $quantity,
                'sold_count' => 0,
                'restocked_quantity' => $quantity,
            ]);
        }

        // Update main product prices if no existing stock (FIFO logic)
        // When old stock reaches 0, the new batch prices become the main prices
        if (!$hasExistingStock) {
            if ($productPrice) {
                $productPrice->supplier_price = $supplierPrice;
                $productPrice->selling_price = $sellingPrice;
                $productPrice->save();
            } else {
                // Create price record if doesn't exist
                ProductPrice::create([
                    'product_id' => $productId,
                    'supplier_price' => $supplierPrice,
                    'selling_price' => $sellingPrice,
                    'discount_price' => 0,
                ]);
            }
        }

        return $batch;
    }

    // Calculate discount amount in rupees (always as percentage)
    public function calculateDiscountAmount($item)
    {
        $discount = floatval($item['discount'] ?? 0);
        $unitPrice = floatval($item['unit_price'] ?? 0);
        $receivedQty = floatval($item['received_qty'] ?? 0);

        // Always treat discount as percentage
        $subtotal = $receivedQty * $unitPrice;
        return ($subtotal * $discount) / 100;
    }

    // Calculate total for an item
    public function calculateItemTotal($item)
    {
        $receivedQty = floatval($item['received_qty'] ?? 0);
        $unitPrice = floatval($item['unit_price'] ?? 0);
        $subtotal = $receivedQty * $unitPrice;
        $discountAmount = $this->calculateDiscountAmount($item);

        return max(0, $subtotal - $discountAmount);
    }

    public function render()
    {
        $query = PurchaseOrder::whereIn('status', ['complete', 'received'])
            ->with(['supplier', 'items.product']);

        // Apply search filter if search term exists
        if (!empty($this->search)) {
            $searchTerm = '%' . $this->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('order_code', 'like', $searchTerm)
                    ->orWhereHas('supplier', function ($supplierQuery) use ($searchTerm) {
                        $supplierQuery->where('name', 'like', $searchTerm);
                    });
            });
        }

        $purchaseOrders = $query->latest()->paginate($this->perPage);

        return view('livewire.admin.g-r-n', [
            'purchaseOrders' => $purchaseOrders,
        ])->layout($this->layout);
    }
}
