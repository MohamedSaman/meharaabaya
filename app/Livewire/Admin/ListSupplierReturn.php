<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ReturnSupplier;
use App\Models\ProductSupplier;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Livewire\Concerns\WithDynamicLayout;

#[Title("Supplier Returns List")]
class ListSupplierReturn extends Component
{
    use WithDynamicLayout;

    use WithPagination;

    public $search = '';
    public $supplierFilter = '';
    public $purchaseOrderFilter = '';
    public $dateFrom = '';
    public $dateTo = '';
    public $reasonFilter = '';
    public $perPage = 10;

    public $selectedReturn = null;
    public $showReturnModal = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'supplierFilter' => ['except' => ''],
        'purchaseOrderFilter' => ['except' => ''],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
        'reasonFilter' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingSupplierFilter()
    {
        $this->resetPage();
    }

    public function updatingPurchaseOrderFilter()
    {
        $this->resetPage();
    }

    public function updatingDateFrom()
    {
        $this->resetPage();
    }

    public function updatingDateTo()
    {
        $this->resetPage();
    }

    public function updatingReasonFilter()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->supplierFilter = '';
        $this->purchaseOrderFilter = '';
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->reasonFilter = '';
        $this->resetPage();
    }

    public function viewReturn($returnId)
    {
        $this->selectedReturn = ReturnSupplier::with([
            'purchaseOrder.supplier',
            'product'
        ])->find($returnId);

        if ($this->selectedReturn) {
            $this->showReturnModal = true;
            $this->dispatch('show-return-modal');
        }
    }

    public function closeReturnModal()
    {
        $this->showReturnModal = false;
        $this->selectedReturn = null;
    }

    public function confirmDelete($returnId)
    {
        $this->js("
            Swal.fire({
                title: 'Delete Return Record?',
                text: 'This action cannot be undone! The return record will be permanently removed from the system.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    \$wire.deleteReturn({$returnId});
                }
            });
        ");
    }

    public function deleteReturn($returnId)
    {
        DB::transaction(function () use ($returnId) {
            $return = ReturnSupplier::findOrFail($returnId);
            
            // Restore stock before deleting
            $this->restoreProductStock($return);
            
            $return->delete();
        });

        $this->dispatch('alert', ['message' => 'Return record deleted successfully!', 'type' => 'success']);
    }

    private function restoreProductStock($return)
    {
        $stock = \App\Models\ProductStock::where('product_id', $return->product_id)->first();

        if ($stock) {
            // Restore the stock that was reduced during return
            $stock->available_stock += $return->return_quantity;
            $stock->total_stock += $return->return_quantity;
            
            // Reduce damage stock if the reason was damaged
            if ($return->return_reason === 'damaged') {
                $stock->damage_stock = max(0, $stock->damage_stock - $return->return_quantity);
            }
            
            $stock->save();
        }
    }

    public function downloadPDF($returnId = null)
    {
        if ($returnId) {
            // Download single return PDF
            $return = ReturnSupplier::with(['purchaseOrder.supplier', 'product'])->find($returnId);
            
            if (!$return) {
                $this->dispatch('alert', ['message' => 'Return record not found!', 'type' => 'error']);
                return;
            }

            $pdf = Pdf::loadView('pdf.supplier-return-single', compact('return'));
            return response()->streamDownload(
                fn() => print($pdf->output()),
                "Supplier_Return_{$return->purchaseOrder->order_code}_{$return->product->code}.pdf"
            );
        } else {
            // Download all filtered returns PDF
            $returns = $this->getReturnsQuery()->get();
            $filters = [
                'search' => $this->search,
                'supplier' => $this->supplierFilter ? ProductSupplier::find($this->supplierFilter)->name : 'All',
                'purchase_order' => $this->purchaseOrderFilter ? PurchaseOrder::find($this->purchaseOrderFilter)->order_code : 'All',
                'date_range' => $this->dateFrom && $this->dateTo ? "{$this->dateFrom} to {$this->dateTo}" : 'All',
                'reason' => $this->reasonFilter ? ucfirst($this->reasonFilter) : 'All',
            ];

            $pdf = Pdf::loadView('pdf.supplier-return-list', compact('returns', 'filters'));
            return response()->streamDownload(
                fn() => print($pdf->output()),
                "Supplier_Returns_Report_" . now()->format('Y-m-d') . ".pdf"
            );
        }
    }

    public function exportCSV()
    {
        $returns = $this->getReturnsQuery()->get();
        
        $fileName = "supplier_returns_" . now()->format('Y-m-d') . ".csv";
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function() use ($returns) {
            $file = fopen('php://output', 'w');
            
            // Add headers
            fputcsv($file, [
                'Return ID',
                'Date',
                'Purchase Order',
                'Supplier',
                'Product Code',
                'Product Name',
                'Return Quantity',
                'Unit Price',
                'Total Amount',
                'Return Reason',
                'Notes'
            ]);

            // Add data
            foreach ($returns as $return) {
                fputcsv($file, [
                    $return->id,
                    $return->created_at->format('Y-m-d'),
                    $return->purchaseOrder->order_code,
                    $return->purchaseOrder->supplier->name,
                    $return->product->code,
                    $return->product->name,
                    $return->return_quantity,
                    $return->unit_price,
                    $return->total_amount,
                    ucfirst(str_replace('_', ' ', $return->return_reason)),
                    $return->notes
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function getReturnsQuery()
    {
        return ReturnSupplier::with(['purchaseOrder.supplier', 'product'])
            ->when($this->search, function ($query) {
                $query->whereHas('product', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('code', 'like', '%' . $this->search . '%');
                })->orWhereHas('purchaseOrder.supplier', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->supplierFilter, function ($query) {
                $query->whereHas('purchaseOrder', function ($q) {
                    $q->where('supplier_id', $this->supplierFilter);
                });
            })
            ->when($this->purchaseOrderFilter, function ($query) {
                $query->where('purchase_order_id', $this->purchaseOrderFilter);
            })
            ->when($this->dateFrom, function ($query) {
                $query->whereDate('created_at', '>=', $this->dateFrom);
            })
            ->when($this->dateTo, function ($query) {
                $query->whereDate('created_at', '<=', $this->dateTo);
            })
            ->when($this->reasonFilter, function ($query) {
                $query->where('return_reason', $this->reasonFilter);
            })
            ->orderBy('created_at', 'desc');
    }

    public function getSummaryStats()
    {
        $query = ReturnSupplier::query();

        if ($this->dateFrom) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }
        if ($this->supplierFilter) {
            $query->whereHas('purchaseOrder', function ($q) {
                $q->where('supplier_id', $this->supplierFilter);
            });
        }

        return [
            'total_returns' => $query->count(),
            'total_quantity' => $query->sum('return_quantity'),
            'total_amount' => $query->sum('total_amount'),
            'damaged_returns' => $query->clone()->where('return_reason', 'damaged')->count(),
        ];
    }

    public function render()
    {
        $returns = $this->getReturnsQuery()->paginate($this->perPage);
        $suppliers = ProductSupplier::orderBy('name')->get();
        $purchaseOrders = PurchaseOrder::whereIn('status', ['received', 'complete'])
            ->when($this->supplierFilter, function ($query) {
                $query->where('supplier_id', $this->supplierFilter);
            })
            ->orderBy('order_code')
            ->get();
        $summaryStats = $this->getSummaryStats();

        return view('livewire.admin.list-supplier-return', compact(
            'returns', 
            'suppliers', 
            'purchaseOrders', 
            'summaryStats'
        ))->layout($this->layout);
    }
}
