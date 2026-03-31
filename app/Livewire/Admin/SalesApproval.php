<?php

namespace App\Livewire\Admin;

use App\Models\Sale;
use App\Models\Payment;
use App\Models\ProductStock;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Livewire\Concerns\WithDynamicLayout;

#[Title('Sales Approvals')]
class SalesApproval extends Component
{
    use WithDynamicLayout;

    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $dateFilter = '';
    public $selectedSale = null;
    public $rejectionReason = '';

    // Edit properties
    public $editingSale = null;
    public $editItems = [];
    public $editQuantities = [];
    public $editPrices = [];
    public $editDiscounts = [];
    public $editSubtotal = 0;
    public $editTotalDiscount = 0;
    public $editGrandTotal = 0;

    // Stats properties
    public $pendingCount = 0;
    public $approvedCount = 0;
    public $rejectedCount = 0;
    public $todayCount = 0;

    protected $listeners = ['refreshStats' => 'loadStats', 'refreshComponent' => 'refreshComponent'];

    public function mount()
    {
        $this->loadStats();
    }

    public function refreshComponent()
    {
        $this->selectedSale = null;
        $this->editingSale = null;
        $this->editItems = [];
        $this->editQuantities = [];
        $this->editPrices = [];
        $this->editDiscounts = [];
        $this->editSubtotal = 0;
        $this->editTotalDiscount = 0;
        $this->editGrandTotal = 0;
        $this->rejectionReason = '';
        $this->loadStats();
        $this->resetPage();
    }

    public function loadStats()
    {
        $this->pendingCount = Sale::where('status', 'pending')->count();
        $this->approvedCount = Sale::where('status', 'confirm')->count();
        $this->rejectedCount = Sale::where('status', 'rejected')->count();
        $this->todayCount = Sale::whereDate('created_at', today())
            ->where('status', '!=', 'pending')
            ->count();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedDateFilter()
    {
        $this->resetPage();
    }

    public function viewSale($saleId)
    {
        $this->selectedSale = Sale::with(['customer', 'items.product', 'user'])
            ->find($saleId);

        if ($this->selectedSale) {
            $this->dispatch('openModal', 'sale-approval-modal');
        } else {
            $this->dispatch('showToast', ['type' => 'error', 'message' => 'Sale not found or has been removed.']);
        }
    }

    public function editSale($saleId)
    {
        $this->editingSale = Sale::with(['customer', 'items.product', 'user'])
            ->find($saleId);

        if ($this->editingSale) {
            // Initialize edit arrays
            $this->editItems = [];
            $this->editQuantities = [];
            $this->editPrices = [];
            $this->editDiscounts = [];

            foreach ($this->editingSale->items as $item) {
                $this->editItems[$item->id] = $item;
                $this->editQuantities[$item->id] = $item->quantity;
                $this->editPrices[$item->id] = $item->unit_price;
                $this->editDiscounts[$item->id] = $item->discount;
            }

            $this->calculateEditTotals();
            $this->dispatch('openModal', 'sale-edit-modal');
        }
    }

    public function calculateEditTotals()
    {
        $this->editSubtotal = 0;
        $this->editTotalDiscount = 0;

        foreach ($this->editItems as $itemId => $item) {
            $quantity = $this->editQuantities[$itemId] ?? 0;
            $price = $this->editPrices[$itemId] ?? 0;
            $discount = $this->editDiscounts[$itemId] ?? 0;

            $this->editSubtotal += ($price * $quantity);
            $this->editTotalDiscount += ($discount * $quantity);
        }

        $this->editGrandTotal = $this->editSubtotal - $this->editTotalDiscount;
    }

    public function updateEditItem($itemId)
    {
        $this->calculateEditTotals();
    }

    public function removeEditItem($itemId)
    {
        unset($this->editItems[$itemId]);
        unset($this->editQuantities[$itemId]);
        unset($this->editPrices[$itemId]);
        unset($this->editDiscounts[$itemId]);

        $this->calculateEditTotals();
    }

    public function saveSaleEdit()
    {
        if (!$this->editingSale) {
            $this->dispatch('showToast', ['type' => 'error', 'message' => 'No sale selected for editing']);
            return;
        }

        try {
            DB::beginTransaction();

            // First, restore stock for all original items
            foreach ($this->editingSale->items as $originalItem) {
                $productStock = ProductStock::where('product_id', $originalItem->product_id)->first();
                if ($productStock) {
                    $productStock->available_stock += $originalItem->quantity;
                    $productStock->sold_count -= $originalItem->quantity;
                    $productStock->save();
                }
            }

            // Delete all original sale items
            $this->editingSale->items()->delete();

            // Create new sale items and update stock
            foreach ($this->editItems as $itemId => $item) {
                $quantity = $this->editQuantities[$itemId];
                $price = $this->editPrices[$itemId];
                $discount = $this->editDiscounts[$itemId];

                // Check stock availability
                $productStock = ProductStock::where('product_id', $item->product_id)->first();
                if (!$productStock || $quantity > $productStock->available_stock) {
                    throw new Exception("Insufficient stock for product: {$item->product_name}");
                }

                // Create new sale item
                $this->editingSale->items()->create([
                    'product_id' => $item->product_id,
                    'product_code' => $item->product_code,
                    'product_name' => $item->product_name,
                    'quantity' => $quantity,
                    'unit_price' => $price,
                    'discount' => $discount,
                    'total' => ($price * $quantity) - ($discount * $quantity),
                ]);

                // Update stock
                $productStock->available_stock -= $quantity;
                $productStock->sold_count += $quantity;
                $productStock->save();
            }

            // Update sale totals
            $this->editingSale->update([
                'subtotal' => $this->editSubtotal,
                'discount_amount' => $this->editTotalDiscount,
                'total_amount' => $this->editGrandTotal,
                'due_amount' => $this->editGrandTotal, // Update due amount for pending sales
            ]);

            DB::commit();

            $this->dispatch('closeModal', 'sale-edit-modal');
            $this->dispatch('showToast', ['type' => 'success', 'message' => 'Sale updated successfully']);

            // Reset edit properties
            $this->editingSale = null;
            $this->editItems = [];
            $this->editQuantities = [];
            $this->editPrices = [];
            $this->editDiscounts = [];
            $this->editSubtotal = 0;
            $this->editTotalDiscount = 0;
            $this->editGrandTotal = 0;

            // Reset selected sale to avoid 404 issues
            $this->selectedSale = null;

            $this->loadStats();

            // Force component refresh
            $this->dispatch('$refresh');
        } catch (Exception $e) {
            DB::rollBack();
            $this->dispatch('showToast', ['type' => 'error', 'message' => 'Error updating sale: ' . $e->getMessage()]);
        }
    }

    public function approveSale()
    {
        if (!$this->selectedSale) {
            $this->dispatch('showToast', ['type' => 'error', 'message' => 'No sale selected']);
            return;
        }

        // Refresh the sale data to ensure it's current
        $this->selectedSale = Sale::with(['customer', 'items.product', 'user'])
            ->find($this->selectedSale->id);

        if (!$this->selectedSale) {
            $this->dispatch('showToast', ['type' => 'error', 'message' => 'Sale not found or has been removed.']);
            $this->dispatch('closeModal', 'sale-approval-modal');
            return;
        }

        try {
            DB::beginTransaction();

            // Update sale status
            $this->selectedSale->update([
                'status' => 'confirm',
                'payment_status' => 'pending',
                'due_amount' => $this->selectedSale->total_amount,
            ]);

            // Create payment record
            Payment::create([
                'sale_id' => $this->selectedSale->id,
                'amount' => $this->selectedSale->total_amount,
                'payment_method' => 'cash', // Default for approved sales
                'is_completed' => false,
                'payment_date' => now(),
                'status' => null,
            ]);

            DB::commit();

            $this->dispatch('showToast', ['type' => 'success', 'message' => 'Sale approved successfully']);
            $this->dispatch('closeModal', 'sale-approval-modal');
            $this->selectedSale = null;
            $this->loadStats();
        } catch (Exception $e) {
            DB::rollBack();
            $this->dispatch('showToast', ['type' => 'error', 'message' => 'Error approving sale: ' . $e->getMessage()]);
        }
    }

    public function rejectSale()
    {
        $this->validate([
            'rejectionReason' => 'required|min:10'
        ]);

        if (!$this->selectedSale) {
            $this->dispatch('showToast', ['type' => 'error', 'message' => 'No sale selected']);
            return;
        }

        // Refresh the sale data to ensure it's current
        $this->selectedSale = Sale::with(['customer', 'items.product', 'user'])
            ->find($this->selectedSale->id);

        if (!$this->selectedSale) {
            $this->dispatch('showToast', ['type' => 'error', 'message' => 'Sale not found or has been removed.']);
            $this->dispatch('closeModal', 'sale-approval-modal');
            return;
        }

        try {
            DB::beginTransaction();

            // Update sale status
            $this->selectedSale->update([
                'status' => 'rejected',
                'notes' => $this->selectedSale->notes . "\n\nRejection Reason: " . $this->rejectionReason
            ]);

            // Restore stock for rejected sale
            foreach ($this->selectedSale->items as $item) {
                $productStock = ProductStock::where('product_id', $item->product_id)->first();
                if ($productStock) {
                    $productStock->available_stock += $item->quantity;
                    $productStock->sold_count -= $item->quantity;
                    $productStock->save();
                }
            }

            DB::commit();

            $this->dispatch('showToast', ['type' => 'success', 'message' => 'Sale rejected successfully']);
            $this->dispatch('closeModal', 'sale-approval-modal');
            $this->selectedSale = null;
            $this->rejectionReason = '';
            $this->loadStats();
        } catch (Exception $e) {
            DB::rollBack();
            $this->dispatch('showToast', ['type' => 'error', 'message' => 'Error rejecting sale: ' . $e->getMessage()]);
        }
    }

    public function render()
    {
        $query = Sale::with(['customer', 'user', 'items'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('invoice_number', 'like', '%' . $this->search . '%')
                    ->orWhereHas('customer', function ($q) {
                        $q->where('name', 'like', '%' . $this->search . '%')
                            ->orWhere('phone', 'like', '%' . $this->search . '%');
                    });
            });
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->dateFilter) {
            switch ($this->dateFilter) {
                case 'today':
                    $query->whereDate('created_at', today());
                    break;
                case 'yesterday':
                    $query->whereDate('created_at', now()->subDay());
                    break;
                case 'this_week':
                    $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'this_month':
                    $query->whereMonth('created_at', now()->month);
                    break;
            }
        }

        $sales = $query->paginate(10);

        return view('livewire.admin.sales-approval', compact('sales'))->layout($this->layout);
    }
}
