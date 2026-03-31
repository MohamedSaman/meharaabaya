<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use App\Models\Sale;
use App\Models\Payment;
use Exception;
use App\Livewire\Concerns\WithDynamicLayout;

#[Title("Due Payments")]
class DuePayments extends Component
{
    use WithDynamicLayout;

    use WithPagination, WithFileUploads;

    public $search = '';
    public $selectedSale = null;
    public $saleDetail = null;
    public $duePaymentAttachment;
    public $saleId;
    public $duePaymentMethod = '';
    public $paymentNote = '';
    public $duePaymentAttachmentPreview;
    public $receivedAmount = '';
    public $filters = [
        'status' => '',
        'dateRange' => '',
    ];

    public $extendDueSaleId;
    public $newDueDate;
    public $extensionReason = '';

    protected $listeners = ['refreshPayments' => '$refresh'];

    public function mount()
    {
        // Initialize component
    }

    public function updatedDuePaymentAttachment()
    {
        $this->validate([
            'duePaymentAttachment' => 'file|mimes:jpg,jpeg,png,gif,pdf|max:2048',
        ]);

        if ($this->duePaymentAttachment) {
            $previewInfo = $this->getFilePreviewInfo($this->duePaymentAttachment);
            $this->duePaymentAttachmentPreview = $previewInfo;
        }
    }

    public function getSaleDetails($saleId)
    {
        $this->saleId = $saleId;
        $this->saleDetail = Sale::with(['customer', 'items', 'payments'])->find($saleId);
        $this->duePaymentMethod = '';
        $this->paymentNote = '';
        $this->duePaymentAttachment = null;
        $this->duePaymentAttachmentPreview = null;
        $this->receivedAmount = '';

        $this->dispatch('openModal', 'payment-detail-modal');
    }

    public function submitPayment()
    {
        $this->validate([
            'receivedAmount' => 'required|numeric|min:0.01',
            'duePaymentMethod' => 'required',
            'duePaymentAttachment' => 'nullable|file|mimes:jpg,jpeg,png,gif,pdf|max:2048',
        ]);

        try {
            DB::beginTransaction();

            $sale = Sale::findOrFail($this->saleId);
            $receivedAmount = floatval($this->receivedAmount);

            // Validate received amount doesn't exceed due amount
            if ($receivedAmount > $sale->due_amount) {
                DB::rollBack();
                $this->dispatch('showToast', [
                    'type' => 'error',
                    'message' => 'Entered amount is too large. Due amount is Rs.' . number_format($sale->due_amount, 2)
                ]);
                return;
            }

            // Store attachment if provided
            $attachmentPath = null;
            if ($this->duePaymentAttachment) {
                $receiptName = time() . '-payment-' . $sale->id . '.' . $this->duePaymentAttachment->getClientOriginalExtension();
                $this->duePaymentAttachment->storeAs('public/due-receipts', $receiptName);
                $attachmentPath = "due-receipts/{$receiptName}";
            }

            // Create a new payment record for this due payment
            $payment = Payment::create([
                'sale_id' => $sale->id,
                'amount' => $receivedAmount,
                'payment_method' => $this->duePaymentMethod,
                'payment_reference' => $attachmentPath,
                'due_payment_attachment' => $this->paymentNote,
                'is_completed' => true,
                'status' => 'paid',
                'payment_date' => now(),
            ]);

            // Update cash in hands if payment method is cash
            if ($this->duePaymentMethod === 'cash') {
                $cashInHandRecord = DB::table('cash_in_hands')->where('key', 'cash_amount')->first();

                if ($cashInHandRecord) {
                    DB::table('cash_in_hands')
                        ->where('key', 'cash_amount')
                        ->update([
                            'value' => $cashInHandRecord->value + $receivedAmount,
                            'updated_at' => now()
                        ]);
                } else {
                    DB::table('cash_in_hands')->insert([
                        'key' => 'cash_amount',
                        'value' => $receivedAmount,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }

            // Update sale's due amount
            $remainingDue = $sale->due_amount - $receivedAmount;
            $sale->due_amount = $remainingDue;

            // Update payment status based on remaining due
            if ($remainingDue <= 0.01) {
                $sale->payment_status = 'paid';
            } else {
                $sale->payment_status = 'partial';
            }

            $sale->save();

            DB::commit();

            $this->dispatch('closeModal', 'payment-detail-modal');
            $this->js("Swal.fire('Success!', 'Payment submitted successfully and sent for admin approval', 'success')");

            $this->reset(['saleDetail', 'duePaymentMethod', 'duePaymentAttachment', 'paymentNote', 'receivedAmount', 'duePaymentAttachmentPreview']);
        } catch (Exception $e) {
            DB::rollback();
            $this->dispatch('showToast', [
                'type' => 'error',
                'message' => 'Failed to submit payment: ' . $e->getMessage()
            ]);
        }
    }

    public function openExtendDueModal($saleId)
    {
        $this->extendDueSaleId = $saleId;
        $sale = Sale::findOrFail($saleId);

        // Set initial new due date to 7 days from today
        $this->newDueDate = now()->addDays(7)->format('Y-m-d');
        $this->extensionReason = '';

        $this->dispatch('openModal', 'extend-due-modal');
    }

    public function extendDueDate()
    {
        $this->validate([
            'newDueDate' => 'required|date|after:today',
            'extensionReason' => 'required|min:5',
        ]);

        try {
            DB::beginTransaction();

            $sale = Sale::findOrFail($this->extendDueSaleId);

            // Add a note to track this extension
            $noteText = "Due date extended on " . now()->format('Y-m-d H:i') . " to {$this->newDueDate}. Reason: {$this->extensionReason}";

            $sale->update([
                'notes' => ($sale->notes ? $sale->notes . "\n" : '') . $noteText
            ]);

            DB::commit();

            $this->dispatch('closeModal', 'extend-due-modal');
            $this->dispatch('showToast', [
                'type' => 'success',
                'message' => 'Due date extended successfully'
            ]);

            $this->reset(['extendDueSaleId', 'newDueDate', 'extensionReason']);
        } catch (Exception $e) {
            DB::rollback();
            $this->dispatch('showToast', [
                'type' => 'error',
                'message' => 'Failed to extend due date: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get file info with appropriate preview or icon
     */
    private function getFilePreviewInfo($file)
    {
        if (!$file) {
            return null;
        }

        $result = [
            'name' => $file->getClientOriginalName(),
            'type' => 'unknown',
            'icon' => 'bi-file-earmark',
            'color' => 'text-secondary',
            'preview' => null
        ];

        $extension = strtolower($file->getClientOriginalExtension());

        if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
            $result['type'] = 'image';
            $result['icon'] = 'bi-file-earmark-image';
            $result['color'] = 'text-primary';

            try {
                $result['preview'] = $file->temporaryUrl();
            } catch (\Exception $e) {
                $result['preview'] = null;
            }
        } elseif ($extension === 'pdf') {
            $result['type'] = 'pdf';
            $result['icon'] = 'bi-file-earmark-pdf';
            $result['color'] = 'text-danger';
        }

        return $result;
    }

    public function resetFilters()
    {
        $this->filters = [
            'status' => '',
            'dateRange' => '',
        ];
    }

    public function render()
    {
        $query = Sale::query()
            ->where('due_amount', '>', 0)
            ->where('payment_status', '!=', 'paid')
            ->with(['customer', 'items', 'payments']);

        // Apply search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('invoice_number', 'like', "%{$this->search}%")
                    ->orWhereHas('customer', function ($customerQuery) {
                        $customerQuery->where('name', 'like', "%{$this->search}%")
                            ->orWhere('phone', 'like', "%{$this->search}%");
                    });
            });
        }

        // Apply payment status filter
        if (!empty($this->filters['status'])) {
            $query->where('payment_status', $this->filters['status']);
        }

        // Apply date range filter (based on created_at since sales table doesn't have due_date)
        if (!empty($this->filters['dateRange'])) {
            if (strpos($this->filters['dateRange'], ' to ') !== false) {
                list($startDate, $endDate) = explode(' to ', $this->filters['dateRange']);
                $query->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59']);
            }
        }

        $dueSales = $query->orderBy('created_at', 'desc')->paginate(10);

        // Calculate stats
        $pendingCount = Sale::where('due_amount', '>', 0)
            ->where('payment_status', 'partial')
            ->count();

        $pendingAmount = Sale::where('due_amount', '>', 0)
            ->where('payment_status', 'partial')
            ->sum('due_amount');

        $awaitingApprovalCount = Payment::where('status', 'pending')
            ->whereHas('sale', function ($q) {
                $q->where('due_amount', '>', 0);
            })
            ->count();

        $totalDueAmount = Sale::where('due_amount', '>', 0)
            ->where('payment_status', '!=', 'paid')
            ->sum('due_amount');

        return view('livewire.admin.due-payments', [
            'dueSales' => $dueSales,
            'pendingCount' => $pendingCount,
            'awaitingApprovalCount' => $awaitingApprovalCount,
            'totalDueAmount' => $totalDueAmount,
            'pendingAmount' => $pendingAmount,
        ])->layout($this->layout);
    }
}
