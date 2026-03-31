<?php

namespace App\Livewire\Staff;

use Exception;
use App\Models\Payment;
use App\Models\Sale;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Carbon\Carbon;
#[Layout('components.layouts.staff')]
#[Title('Due Payments')]
class DuePayments extends Component
{
    use WithPagination, WithFileUploads;
    

    /** -----------------------------
     * UI / State
     * ------------------------------*/
    public string $search = '';
    public ?int $saleId = null;
    public ?Sale $saleDetail = null;

    public ?string $duePaymentMethod = '';
    public ?string $paymentNote = '';
    public $duePaymentAttachment = null;               // Livewire tmp uploaded file
    public ?array $duePaymentAttachmentPreview = null;  // preview metadata
    public string $receivedAmount = '';

    public array $filters = [
        'status' => '',
        'dateRange' => '',
    ];

    // Extend-due-date modal state
    public ?int $extendDueSaleId = null;
    public ?string $newDueDate = null;
    public string $extensionReason = '';

    protected $listeners = ['refreshSales' => '$refresh'];

    /** Max upload size in KB (2_048 = 2MB). Increase if you want bigger receipts. */
    public int $maxAttachmentKb = 2048;

    /** Allowed mime/extension pairs */
    private array $allowedMimes = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];

    /** -----------------------------
     * Validation
     * ------------------------------*/
    protected function rules(): array
    {
        return [
            'receivedAmount'        => ['required', 'numeric', 'min:0.01'],
            'duePaymentMethod'      => ['required', 'string', 'max:100'],
            'paymentNote'           => ['nullable', 'string', 'max:2000'],
            'duePaymentAttachment'  => ['nullable', 'file', 'mimes:' . implode(',', $this->allowedMimes), 'max:' . $this->maxAttachmentKb],

            // Extend due date
            'newDueDate'            => ['nullable', 'date', 'after:today'],
            'extensionReason'       => ['nullable', 'string', 'min:5', 'max:500'],
        ];
    }

    protected function messages(): array
    {
        return [
            'duePaymentAttachment.max' => "The due payment attachment must not be greater than {$this->maxAttachmentKb} kilobytes.",
            'duePaymentAttachment.mimes' => 'Supported file types: jpg, jpeg, png, gif, pdf.',
        ];
    }

    /** -----------------------------
     * Lifecycle
     * ------------------------------*/
    public function mount(): void
    {
        // no-op for now
    }

    /** -----------------------------
     * File handling
     * ------------------------------*/
    public function updatedDuePaymentAttachment(): void
    {
        $this->validateOnly('duePaymentAttachment');

        if ($this->duePaymentAttachment) {
            $this->duePaymentAttachmentPreview = $this->getFilePreviewInfo($this->duePaymentAttachment);
        } else {
            $this->duePaymentAttachmentPreview = null;
        }
    }

    private function getFilePreviewInfo($file): ?array
    {
        if (!$file) {
            return null;
        }

        $ext = strtolower($file->getClientOriginalExtension());

        $info = [
            'name'   => $file->getClientOriginalName(),
            'type'   => 'unknown',
            'icon'   => 'bi-file-earmark',
            'color'  => 'text-secondary',
            'preview'=> null,
        ];

        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
            $info['type']  = 'image';
            $info['icon']  = 'bi-file-earmark-image';
            $info['color'] = 'text-primary';
            try {
                $info['preview'] = $file->temporaryUrl();
            } catch (\Throwable $e) {
                $info['preview'] = null;
            }
        } elseif ($ext === 'pdf') {
            $info['type']  = 'pdf';
            $info['icon']  = 'bi-file-earmark-pdf';
            $info['color'] = 'text-danger';
        }

        return $info;
    }

    /** -----------------------------
     * Modals / Actions
     * ------------------------------*/
    public function getSaleDetails(int $saleId): void
    {
        $this->resetValidation();

        $this->saleId   = $saleId;
        $this->saleDetail = Sale::with(['customer', 'items'])->findOrFail($saleId);

        $this->duePaymentMethod = '';
        $this->paymentNote      = '';
        $this->receivedAmount   = (string) $this->saleDetail->due_amount;
        $this->duePaymentAttachment = null;
        $this->duePaymentAttachmentPreview = null;

        $this->dispatch('openModal', 'payment-detail-modal');
    }

    public function submitPayment(): void
    {
        $this->validate([
            'receivedAmount'       => ['required', 'numeric', 'min:0.01'],
            'duePaymentMethod'     => ['required', 'string', 'max:100'],
            'duePaymentAttachment' => ['nullable', 'file', 'mimes:' . implode(',', $this->allowedMimes), 'max:' . $this->maxAttachmentKb],
            'paymentNote'          => ['nullable', 'string', 'max:2000'],
        ]);

        DB::beginTransaction();

        try {
            $sale = Sale::lockForUpdate()->findOrFail($this->saleId);
            $payment = Payment::where('sale_id', $this->saleId)
                ->where('is_completed', false)
                ->whereNull('status')
                ->firstOrFail();

            $due = (float) $sale->due_amount;
            $received = (float) $this->receivedAmount;

            if ($received > $due) {
                DB::rollBack();
                $this->dispatch('showToast', [
                    'type' => 'error',
                    'message' => 'Entered amount is too large. Please enter an amount less than or equal to the due amount.'
                ]);
                return;
            }

            // Handle optional attachment
            $attachmentPath = $payment->due_payment_attachment; // Keep old one if not updated
            if ($this->duePaymentAttachment) {
                // Delete old attachment if it exists
                if ($attachmentPath && Storage::disk('public')->exists($attachmentPath)) {
                    Storage::disk('public')->delete($attachmentPath);
                }
                $fileExt = $this->duePaymentAttachment->getClientOriginalExtension();
                $receiptName = now()->timestamp . '-sale-' . $sale->id . '-' . Str::random(6) . '.' . $fileExt;
                $this->duePaymentAttachment->storeAs('public/due-receipts', $receiptName);
                $attachmentPath = 'due-receipts/' . $receiptName;
            }

            // Update the existing payment record for this submission
            $payment->update([
                'amount'                => $received,
                'due_payment_method'    => $this->duePaymentMethod,
                'due_payment_attachment'=> $attachmentPath,
                'status'                => 'pending', // Pending admin approval
                'payment_date'          => now(),
            ]);

            // Update the sale's due amount
            $remaining = round($due - $received, 2);
            $sale->update([
                'due_amount' => $remaining,
            ]);

            // Append note on the Sale (optional)
            if (!empty($this->paymentNote)) {
                $existing = (string)($sale->notes ?? '');
                $noteLine = "Payment of {$received} submitted on " . now()->format('Y-m-d H:i') . ": " . $this->paymentNote;
                $sale->update(['notes' => trim($existing . "\n" . $noteLine)]);
            }

            DB::commit();

            $this->dispatch('closeModal', 'payment-detail-modal');
            $this->dispatch('showToast', [
                'type' => 'success',
                'message' => 'Payment submitted and sent for admin approval.'
            ]);

            // Reset form state
            $this->reset([
                'saleDetail',
                'duePaymentMethod',
                'duePaymentAttachment',
                'duePaymentAttachmentPreview',
                'paymentNote',
                'receivedAmount',
                'saleId',
            ]);

            // refresh list
            $this->dispatch('refreshSales');

        } catch (Exception $e) {
            DB::rollBack();
            $this->dispatch('showToast', [
                'type' => 'error',
                'message' => 'Failed to submit payment: ' . $e->getMessage(),
            ]);
        }
    }

    public function openExtendDueModal(int $saleId): void
    {
        $this->resetValidation();

        $this->extendDueSaleId = $saleId;
        $sale = Sale::findOrFail($saleId);

        $dueDate = $sale->due_date instanceof Carbon
            ? $sale->due_date
            : Carbon::parse($sale->due_date);

        $this->newDueDate = $dueDate->copy()->addDays(7)->format('Y-m-d');
        $this->extensionReason = '';

        $this->dispatch('openModal', 'extend-due-modal');
    }

    public function extendDueDate(): void
    {
        $this->validate([
            'newDueDate'      => ['required', 'date', 'after:today'],
            'extensionReason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        DB::beginTransaction();

        try {
            $sale = Sale::lockForUpdate()->findOrFail($this->extendDueSaleId);

            $oldDue = ($sale->due_date instanceof Carbon)
                ? $sale->due_date->format('Y-m-d')
                : Carbon::parse($sale->due_date)->format('Y-m-d');

            $sale->update([
                'due_date' => $this->newDueDate,
            ]);

            $existing = (string)($sale->notes ?? '');
            $noteLine = "Due date extended on " . now()->format('Y-m-d H:i') .
                        " from {$oldDue} to {$this->newDueDate}. Reason: {$this->extensionReason}";
            $sale->update(['notes' => trim($existing . "\n" . $noteLine)]);

            DB::commit();

            $this->dispatch('closeModal', 'extend-due-modal');
            $this->dispatch('showToast', [
                'type' => 'success',
                'message' => 'Due date extended successfully.',
            ]);

            $this->reset(['extendDueSaleId', 'newDueDate', 'extensionReason']);
            $this->dispatch('refreshSales');

        } catch (Exception $e) {
            DB::rollBack();
            $this->dispatch('showToast', [
                'type' => 'error',
                'message' => 'Failed to extend due date: ' . $e->getMessage(),
            ]);
        }
    }

    /** -----------------------------
     * Listing / Render
     * ------------------------------*/
    public function render()
    {
        $query = Payment::query()
            ->where('is_completed', false)
            ->whereNull('status')
            ->with(['sale.customer'])
            ->whereHas('sale', function ($saleQuery) {
                $saleQuery->where('user_id', auth()->id());
            });

        // Apply search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->whereHas('sale', function ($saleQuery) {
                    $saleQuery->where('invoice_number', 'like', "%{$this->search}%")
                        ->orWhereHas('customer', function ($customerQuery) {
                            $customerQuery->where('name', 'like', "%{$this->search}%")
                                ->orWhere('phone', 'like', "%{$this->search}%");
                        });
                });
            });
        }

        // Apply date range filter on the related sale's due_date
        if (!empty($this->filters['dateRange'])) {
            if (strpos($this->filters['dateRange'], ' to ') !== false) {
                list($startDate, $endDate) = explode(' to ', $this->filters['dateRange']);
                $query->whereHas('sale', function ($saleQuery) use ($startDate, $endDate) {
                    $saleQuery->whereBetween('due_date', [$startDate, $endDate . ' 23:59:59']);
                });
            }
        }

        $duePayments = $query->orderBy(
            \App\Models\Sale::select('due_date')
                ->whereColumn('sales.id', 'payments.sale_id')
                ->latest()
                ->limit(1)
        , 'asc')->paginate(10);

        return view('livewire.staff.due-payments', [
            'duePayments' => $duePayments,
        ]);
    }
}
