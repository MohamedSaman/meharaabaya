<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\ReturnsProduct;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Livewire\Concerns\WithDynamicLayout;
use Livewire\WithPagination;

#[Title("Product Return")]
class ReturnList extends Component
{
    use WithDynamicLayout, WithPagination;


    // Do not store the full collection/paginator in a public property
    public $returnsCount = 0;
    public $returnSearch = '';
    public $selectedReturn = null;
    public $showReceiptModal = false;
    public $currentReturnId = null;
    public $perPage = 10;

    public function mount()
    {
        // Load lightweight count; actual paginated data is returned from render()
        $this->loadReturns();
    }

    protected function loadReturns()
    {
        $query = ReturnsProduct::with(['sale', 'product']);

        if (!empty($this->returnSearch)) {
            $search = '%' . $this->returnSearch . '%';
            $query->where(function ($q) use ($search) {
                $q->whereHas('sale', function ($sq) use ($search) {
                    $sq->where('invoice_number', 'like', $search);
                })->orWhereHas('product', function ($pq) use ($search) {
                    $pq->where('name', 'like', $search)
                        ->orWhere('code', 'like', $search);
                });
            });
        }
        $this->returnsCount = $query->count();
    }

    public function updatedReturnSearch()
    {
        $this->resetPage();
        $this->loadReturns();
    }

    public function showReturnDetails($id)
    {
        $this->selectedReturn = ReturnsProduct::with(['sale', 'product'])->find($id);
        $this->dispatch('showModal', 'returnDetailsModal');
    }

    public function showReceipt($returnId)
    {
        $this->selectedReturn = ReturnsProduct::with(['sale.customer', 'product'])->find($returnId);
        $this->currentReturnId = $returnId;
        $this->showReceiptModal = true;
        $this->dispatch('showModal', 'receiptModal');
    }

    public function downloadReturn($returnId)
    {
        $return = ReturnsProduct::with(['sale.customer', 'product'])->find($returnId);

        if (!$return) {
            $this->dispatch('showToast', ['type' => 'error', 'message' => 'Return record not found.']);
            return;
        }

        try {
            $pdf = PDF::loadView('admin.returns.return-receipt', compact('return'));

            $pdf->setPaper('a4', 'portrait');
            $pdf->setOption('dpi', 150);
            $pdf->setOption('defaultFont', 'sans-serif');

            return response()->streamDownload(
                function () use ($pdf) {
                    echo $pdf->output();
                },
                'return-receipt-' . $return->id . '-' . now()->format('Y-m-d') . '.pdf'
            );
        } catch (\Exception $e) {
            $this->dispatch('showToast', ['type' => 'error', 'message' => 'Failed to generate PDF: ' . $e->getMessage()]);
        }
    }

    public function printReceipt()
    {
        $this->dispatch('printReceipt');
    }

    public function deleteReturn($returnId)
    {
        $this->selectedReturn = ReturnsProduct::find($returnId);
        $this->currentReturnId = $returnId;
        $this->dispatch('showModal', 'deleteReturnModal');
    }

    public function confirmDeleteReturn()
    {
        try {
            if ($this->selectedReturn) {
                // Restore the stock before deleting the return record
                $this->restoreStock($this->selectedReturn);

                $this->selectedReturn->delete();
                // Refresh lightweight data and reset pagination if needed
                $this->loadReturns();
                $this->resetPage();

                $this->dispatch('hideModal', 'deleteReturnModal');
                $this->dispatch('showToast', ['type' => 'success', 'message' => 'Return record deleted successfully!']);
            }
        } catch (\Exception $e) {
            $this->dispatch('showToast', ['type' => 'error', 'message' => 'Error deleting return: ' . $e->getMessage()]);
        }
    }

    private function restoreStock($return)
    {
        // Decrease the available stock since we're deleting a return
        $productStock = \App\Models\ProductStock::where('product_id', $return->product_id)->first();

        if ($productStock) {
            $productStock->available_stock -= $return->return_quantity;
            if ($productStock->sold_count >= $return->return_quantity) {
                $productStock->sold_count += $return->return_quantity;
            }
            $productStock->save();
        }
    }

    public function closeModal()
    {
        $this->selectedReturn = null;
        $this->currentReturnId = null;
        $this->showReceiptModal = false;
        $this->dispatch('hideModal', 'returnDetailsModal');
        $this->dispatch('hideModal', 'deleteReturnModal');
        $this->dispatch('hideModal', 'receiptModal');
    }

    public function render()
    {
        $query = ReturnsProduct::with(['sale', 'product'])->orderByDesc('created_at');

        if (!empty($this->returnSearch)) {
            $search = '%' . $this->returnSearch . '%';
            $query->where(function ($q) use ($search) {
                $q->whereHas('sale', function ($sq) use ($search) {
                    $sq->where('invoice_number', 'like', $search);
                })->orWhereHas('product', function ($pq) use ($search) {
                    $pq->where('name', 'like', $search)
                        ->orWhere('code', 'like', $search);
                });
            });
        }
        $returns = $query->paginate($this->perPage);

        return view('livewire.admin.return-list', [
            'returns' => $returns,
            'selectedReturn' => $this->selectedReturn,
            'currentReturnId' => $this->currentReturnId,
        ])->layout($this->layout);
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }
}
