<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\ProductSupplier;
use App\Models\ProductDetail;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Livewire\Concerns\WithDynamicLayout;

#[Title("Purchase Order")]
class Quotation extends Component
{
    use WithDynamicLayout;

    public $suppliers = [];
    public $supplier_id = '';
    public $search = '';
    public $products = [];
    public $selectedProduct = null;
    public $quantity = 1;
    public $orderItems = [];
    public $orders;
    public $selectedOrder;
    // Edit flow
    public $editOrderId = null;
    public $editOrderItems = [];

    public function mount()
    {
        $this->suppliers = ProductSupplier::all();
        $this->loadOrders();
    }

    public function updatedSearch()
    {
        if ($this->search != '') {
            $this->products = ProductDetail::where('name', 'like', '%' . $this->search . '%')
                ->orWhere('code', 'like', '%' . $this->search . '%')
                ->with(['stock'])
                ->limit(5)
                ->get();
        } else {
            $this->products = [];
        }
    }

    public function selectProduct($id)
    {
        $this->selectedProduct = ProductDetail::with(['stock'])->find($id);
        $this->products = [];
        $this->search = ''; // Clear search after selection
    }

    public function addItem()
    {
        if (!$this->selectedProduct || $this->quantity < 1) return;

        $this->orderItems[] = [
            'product_id' => $this->selectedProduct->id,
            'name' => $this->selectedProduct->name,
            'quantity' => $this->quantity,
        ];

        $this->selectedProduct = null;
        $this->search = '';
        $this->quantity = 1;
    }

    public function removeItem($index)
    {
        unset($this->orderItems[$index]);
        $this->orderItems = array_values($this->orderItems);
    }

    public function saveOrder()
    {
        if (!$this->supplier_id || count($this->orderItems) == 0) return;


        // Generate unique order code for the year
        $year = date('Y');
        $lastOrder = PurchaseOrder::where('order_code', 'like', 'ORD-' . $year . '-%')
            ->orderByDesc('order_code')
            ->first();
        if ($lastOrder && preg_match('/ORD-' . $year . '-(\d+)/', $lastOrder->order_code, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        } else {
            $nextNumber = 1;
        }
        $orderCode = 'ORD-' . $year . '-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        $order = PurchaseOrder::create([
            'order_code' => $orderCode,
            'supplier_id' => $this->supplier_id,
            'order_date' => now(),
            'status' => 'pending',
        ]);

        foreach ($this->orderItems as $item) {
            PurchaseOrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => 0,
                'discount' => 0,
            ]);
        }

        // Refresh table
        $this->loadOrders();

        $this->reset(['supplier_id', 'search', 'selectedProduct', 'quantity', 'orderItems']);
        $this->js("bootstrap.Modal.getInstance(document.getElementById('addPurchaseOrderModal')).hide();");
        $this->js("Swal.fire('Success', 'Purchase Order created successfully!', 'success');");
    }

    public function loadOrders()
    {
        $this->orders = PurchaseOrder::whereIn('status', ['pending', 'complete', 'cancelled'])
            ->with(['supplier', 'items.product'])
            ->latest()
            ->orderBy('id', 'desc')
            ->get();
    }

    public function viewOrder($id)
    {
        $this->selectedOrder = PurchaseOrder::with(['supplier', 'items.product'])->find($id);

        if (!$this->selectedOrder) {
            $this->js("Swal.fire('Error', 'Order not found!', 'error');");
            return;
        }

        // Open modal using JavaScript
        $this->js("new bootstrap.Modal(document.getElementById('viewOrderModal')).show();");
    }

    /** ✏️ Load order for editing */
    public function editOrder($id)
    {
        $order = PurchaseOrder::with(['supplier', 'items.product'])->find($id);
        if (!$order) {
            $this->js("Swal.fire('Error', 'Order not found!', 'error');");
            return;
        }

        $this->editOrderId = $order->id;
        $this->editOrderItems = [];
        foreach ($order->items as $item) {
            $this->editOrderItems[] = [
                'item_id' => $item->id,
                'product_id' => $item->product_id,
                'name' => $item->product->name ?? 'N/A',
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'discount' => $item->discount,
            ];
        }

        // Open modal using JavaScript
        $this->js("new bootstrap.Modal(document.getElementById('editOrderModal')).show();");
    }

    public function removeEditItem($index)
    {
        if (isset($this->editOrderItems[$index])) {
            unset($this->editOrderItems[$index]);
            $this->editOrderItems = array_values($this->editOrderItems);
        }
    }

    /** 💾 Persist edits to order and items (create/update/delete) */
    public function updateOrder()
    {
        if (!$this->editOrderId) return;

        $order = PurchaseOrder::find($this->editOrderId);
        if (!$order) {
            $this->js("Swal.fire('Error', 'Order not found!', 'error');");
            return;
        }

        // Track current item ids to keep
        $keepIds = [];

        foreach ($this->editOrderItems as $item) {
            // Validation: quantity must be >=1
            $qty = isset($item['quantity']) ? intval($item['quantity']) : 0;
            if ($qty < 1) continue; // skip invalid rows

            if (!empty($item['item_id'])) {
                // update existing
                $orderItem = PurchaseOrderItem::find($item['item_id']);
                if ($orderItem) {
                    $orderItem->quantity = $qty;
                    $orderItem->unit_price = $item['unit_price'] ?? 0;
                    $orderItem->discount = $item['discount'] ?? 0;
                    $orderItem->save();
                    $keepIds[] = $orderItem->id;
                }
            } else {
                // create new
                $new = PurchaseOrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $qty,
                    'unit_price' => $item['unit_price'] ?? 0,
                    'discount' => $item['discount'] ?? 0,
                ]);
                $keepIds[] = $new->id;
            }
        }

        // Delete any order items not present in keepIds
        PurchaseOrderItem::where('order_id', $order->id)
            ->whereNotIn('id', $keepIds ?: [0])
            ->delete();

        $this->loadOrders();
        $this->editOrderId = null;
        $this->editOrderItems = [];
        $this->js("bootstrap.Modal.getInstance(document.getElementById('editOrderModal')).hide();");
        $this->js("Swal.fire('Success', 'Order updated successfully!', 'success');");
    }

    public function confirmDelete($id)
    {
        $this->js("Swal.fire({
                title: 'Delete order?',
                text: 'This will remove the purchase order and its items. Continue?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    \$wire.deleteOrderConfirmed({$id});
                }
            });
        ");
    }

    public function deleteOrderConfirmed($id)
    {
        $order = PurchaseOrder::find($id);
        if ($order) {
            $order->status = 'cancelled';
            $order->save();
            $this->loadOrders();
            $this->js("Swal.fire('Cancelled', 'Order status changed to cancelled.', 'success');");
        }
    }

    public function confirmComplete($id)
    {
        $this->js("Swal.fire({
                title: 'Mark order as complete?',
                text: 'Are you sure you want to complete this order?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, complete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    \$wire.completeOrderConfirmed({$id});
                }
            });
        ");
    }

    public function completeOrderConfirmed($id)
    {
        $order = PurchaseOrder::find($id);
        if ($order) {
            $order->status = 'complete';
            $order->save();
            $this->loadOrders();
            $this->js("Swal.fire('Success', 'Order marked as completed!', 'success');");
        }
    }

    public function render()
    {
        $pendingCount = PurchaseOrder::where('status', 'pending')->count();
        $completedCount = PurchaseOrder::where('status', 'complete')->count();

        return view('livewire.admin.quotation', compact('pendingCount', 'completedCount'))->layout($this->layout);
    }
}
