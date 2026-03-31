<?php

namespace App\Livewire\Admin;

use App\Models\Cheque;
use App\Models\Expense;
use App\Models\Investor;
use App\Models\InvestorTransaction;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Investor Ledger')]
class InvestorLedger extends Component
{
    public Investor $investor;

    public array $rows = [];

    public array $descriptionSuggestions = [];

    public array $monthOptions = [];

    // Payment method modal properties
    public bool $showPaymentModal = false;

    public ?int $pendingPaymentIndex = null;
    public bool $isPendingInflow = false;

    public string $selectedPaymentMethod = 'cash';

    public string $chequeSearch = '';

    public ?int $selectedChequeId = null;

    public array $availableCheques = [];

    // Cheque detail viewer
    public bool $showChequeDetail = false;

    public array $chequeDetail = [];

    public function mount(Investor $investor): void
    {
        $this->investor = $investor;
        $this->monthOptions = $this->buildMonthOptions();
        $this->descriptionSuggestions = $this->buildDescriptionSuggestions();
        $this->loadRows();
    }

    protected function newRow(): array
    {
        return [
            'id' => null,
            'transaction_date' => now()->format('Y-m-d'),
            'description' => '',
            'inflow' => '',
            'outflow' => '',
            'outflow_month' => '',
            'payment_method' => '',
            'cheque_id' => null,
            'cheque_number' => '',
            'cheque_bank' => '',
            'cheque_date' => '',
            'cheque_amount' => '',
            'cheque_customer' => '',
        ];
    }

    protected function buildMonthOptions(): array
    {
        $months = [];

        for ($i = 0; $i < 24; $i++) {
            $date = now()->startOfMonth()->subMonths($i);
            $months[] = [
                'value' => $date->format('Y-m'),
                'label' => $date->format('F Y'),
            ];
        }

        return $months;
    }

    protected function buildDescriptionSuggestions(): array
    {
        $base = [
            'Investment',
            'Profit Margin',
            'Refund',
            'Expense',  
            'Withdrawal',
        ];

        $history = $this->investor->transactions()
            ->whereNotNull('description')
            ->where('description', '!=', '')
            ->select('description')
            ->distinct()
            ->orderBy('description')
            ->pluck('description')
            ->map(fn($item) => trim((string) $item))
            ->filter()
            ->values()
            ->toArray();

        return array_values(array_unique(array_merge($base, $history)));
    }

    protected function isProfitMarginDescription(string $description): bool
    {
        return str_contains(strtolower($description), 'profit margin');
    }

    protected function extractMonthFromReference(?string $reference): string
    {
        if (! $reference) {
            return '';
        }

        if (str_starts_with($reference, 'profit-month:')) {
            $month = substr($reference, strlen('profit-month:'));

            return preg_match('/^\d{4}-\d{2}$/', $month) ? $month : '';
        }

        return '';
    }

    protected function calculateProfitMarginOutflow(string $month): float
    {
        if (! preg_match('/^\d{4}-\d{2}$/', $month)) {
            return 0;
        }

        $date = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $year = (int) $date->year;
        $monthNumber = (int) $date->month;

        $salesTotal = (float) Sale::query()
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $monthNumber)
            ->sum('total_amount');

        $totalCostForSaleProducts = (float) DB::table('sale_items as si')
            ->join('sales as s', 's.id', '=', 'si.sale_id')
            ->leftJoin('product_prices as pp', 'pp.product_id', '=', 'si.product_id')
            ->whereYear('s.created_at', $year)
            ->whereMonth('s.created_at', $monthNumber)
            ->selectRaw('COALESCE(SUM(si.quantity * COALESCE(pp.supplier_price, 0)), 0) as total_cost')
            ->value('total_cost');

        $expenses = (float) Expense::query()
            ->whereYear('date', $year)
            ->whereMonth('date', $monthNumber)
            ->sum('amount');

        $saleRevenue = $salesTotal - ($totalCostForSaleProducts + $expenses);
        $investorShareRatio = ((float) $this->investor->profit_share_percentage) / 100;
        $outflow = $saleRevenue * $investorShareRatio;

        return round(max($outflow, 0), 2);
    }

    protected function applyRowRules(int $index): void
    {
        if (! isset($this->rows[$index])) {
            return;
        }

        $description = trim((string) ($this->rows[$index]['description'] ?? ''));
        $isProfitMargin = $this->isProfitMarginDescription($description);

        if ($isProfitMargin) {
            $this->rows[$index]['inflow'] = '';

            $month = (string) ($this->rows[$index]['outflow_month'] ?? '');
            if ($month !== '') {
                $this->rows[$index]['outflow'] = (string) $this->calculateProfitMarginOutflow($month);
            } else {
                $this->rows[$index]['outflow'] = '';
            }
        } else {
            $this->rows[$index]['outflow_month'] = '';
        }
    }

    public function onDescriptionChanged(int $index): void
    {
        $this->applyRowRules($index);
    }

    public function onOutflowMonthChanged(int $index): void
    {
        $this->applyRowRules($index);
    }

    protected function loadRows(): void
    {
        $transactions = $this->investor->transactions()
            ->with('cheque')
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get();

        $this->rows = $transactions->map(function ($item) {
            return [
                'id' => $item->id,
                'transaction_date' => $item->transaction_date ? Carbon::parse($item->transaction_date)->format('Y-m-d') : now()->format('Y-m-d'),
                'description' => (string) ($item->description ?? ''),
                'inflow' => $item->type === 'inflow' ? (string) $item->amount : '',
                'outflow' => $item->type === 'outflow' ? (string) $item->amount : '',
                'outflow_month' => $item->type === 'outflow' ? $this->extractMonthFromReference($item->reference) : '',
                'payment_method' => (string) ($item->payment_method ?? 'cash'),
                'cheque_id' => $item->cheque_id,
                'cheque_number' => $item->cheque ? $item->cheque->cheque_number : '',
                'cheque_bank' => $item->cheque ? $item->cheque->bank_name : '',
                'cheque_date' => $item->cheque && $item->cheque->cheque_date ? Carbon::parse($item->cheque->cheque_date)->format('d M Y') : '',
                'cheque_amount' => $item->cheque ? (string) $item->cheque->cheque_amount : '',
                'cheque_customer' => $item->cheque && $item->cheque->customer ? $item->cheque->customer->name : '',
            ];
        })->values()->toArray();

        $this->ensureTrailingBlankRow();
    }

    public function addRow(): void
    {
        $this->rows[] = $this->newRow();
    }

    protected function isRowBlank(array $row): bool
    {
        $description = trim((string) ($row['description'] ?? ''));
        $inflow = is_numeric($row['inflow'] ?? null) ? (float) $row['inflow'] : 0;
        $outflow = is_numeric($row['outflow'] ?? null) ? (float) $row['outflow'] : 0;
        $outflowMonth = trim((string) ($row['outflow_month'] ?? ''));

        return $description === '' && $inflow <= 0 && $outflow <= 0 && $outflowMonth === '';
    }

    protected function ensureTrailingBlankRow(): void
    {
        $normalizedRows = [];

        foreach ($this->rows as $row) {
            if (! $this->isRowBlank($row)) {
                $normalizedRows[] = $row;
            }
        }

        $normalizedRows[] = $this->newRow();
        $this->rows = array_values($normalizedRows);
    }


    public function deleteRow(int $index): void
    {
        if (! isset($this->rows[$index])) {
            return;
        }

        $row = $this->rows[$index];

        if (! empty($row['id'])) {
            $transaction = $this->investor->transactions()->find($row['id']);
            
            if ($transaction) {
                // Return cheque status to pending if it's being deleted
                if ($transaction->cheque_id) {
                    Cheque::where('id', $transaction->cheque_id)->update(['status' => 'pending']);
                }
                
                $transaction->delete();
            }
            session()->flash('success', 'Line deleted successfully.');
        } else {
            // Unsaved row
            session()->flash('success', 'Draft line removed.');
        }

        unset($this->rows[$index]);
        $this->rows = array_values($this->rows);
        
        $this->resetErrorBag();
        $this->ensureTrailingBlankRow();
    }

    // ─── Helpers ──────────────────────────────────────────────────────

    // ─── Payment Method Modal ─────────────────────────────────────────

    /**
     * Called when user clicks on inflow/outflow cell or blurs input.
     * Open modal to select payment method.
     */
    public function requestPaymentSave(int $index): void
    {
        if (! isset($this->rows[$index])) {
            return;
        }

        $outflow = is_numeric($this->rows[$index]['outflow'] ?? null) ? (float) $this->rows[$index]['outflow'] : 0;
        $inflow = is_numeric($this->rows[$index]['inflow'] ?? null) ? (float) $this->rows[$index]['inflow'] : 0;
        $description = trim((string) ($this->rows[$index]['description'] ?? ''));

        // If blank row, skip
        if ($description === '' && $outflow <= 0 && $inflow <= 0) {
            return;
        }

        if ($inflow > 0 || $outflow > 0) {
            $this->pendingPaymentIndex = $index;
            $this->isPendingInflow = $inflow > 0;
            $this->selectedPaymentMethod = $this->rows[$index]['payment_method'] ?: 'cash';
            $this->chequeSearch = '';
            $this->selectedChequeId = $this->rows[$index]['cheque_id'];
            
            if (!$this->isPendingInflow) {
                $this->loadAvailableCheques();
            }
            
            $this->showPaymentModal = true;
            return;
        }

        // Otherwise save normally
        $this->saveRow($index);
    }

    public function loadAvailableCheques(): void
    {
        $query = Cheque::query()
            ->with('customer')
            ->where('status', 'pending')
            ->orderBy('id', 'desc');

        if ($this->chequeSearch !== '') {
            $query->where(function ($q) {
                $q->where('cheque_number', 'like', '%' . $this->chequeSearch . '%')
                  ->orWhere('bank_name', 'like', '%' . $this->chequeSearch . '%')
                  ->orWhereHas('customer', function ($customerQuery) {
                      $customerQuery->where('name', 'like', '%' . $this->chequeSearch . '%');
                  });
            });
        }

        $this->availableCheques = $query->limit(20)->get()->map(function ($cheque) {
            return [
                'id' => $cheque->id,
                'cheque_number' => $cheque->cheque_number,
                'bank_name' => $cheque->bank_name,
                'cheque_amount' => (float) $cheque->cheque_amount,
                'cheque_date' => $cheque->cheque_date ? Carbon::parse($cheque->cheque_date)->format('d M Y') : '',
                'customer_name' => $cheque->customer ? $cheque->customer->name : 'N/A',
            ];
        })->toArray();
    }

    public function updatedChequeSearch(): void
    {
        $this->loadAvailableCheques();
    }

    public function selectCheque(int $chequeId): void
    {
        $this->selectedChequeId = $chequeId;

        // Automatically update the outflow amount in the UI when a cheque is selected
        if (!$this->isPendingInflow && $this->pendingPaymentIndex !== null && isset($this->rows[$this->pendingPaymentIndex])) {
            $cheque = Cheque::find($chequeId);
            if ($cheque) {
                $this->rows[$this->pendingPaymentIndex]['outflow'] = (string) $cheque->cheque_amount;
            }
        }
    }

    public function confirmPaymentMethod(): void
    {
        if ($this->pendingPaymentIndex === null) {
            return;
        }

        $index = $this->pendingPaymentIndex;

        if (! isset($this->rows[$index])) {
            $this->closePaymentModal();
            return;
        }

        // Validate cheque selection if cheque method chosen, and ONLY if it's an outflow
        if (!$this->isPendingInflow && $this->selectedPaymentMethod === 'cheque' && ! $this->selectedChequeId) {
            $this->addError('chequeSelection', 'Please select a cheque.');
            return;
        }

        // Set payment method on row
        $this->rows[$index]['payment_method'] = $this->selectedPaymentMethod;

        if (!$this->isPendingInflow && $this->selectedPaymentMethod === 'cheque' && $this->selectedChequeId) {
            $this->rows[$index]['cheque_id'] = $this->selectedChequeId;
            $cheque = Cheque::find($this->selectedChequeId);
            $this->rows[$index]['cheque_number'] = $cheque ? $cheque->cheque_number : '';
            if ($cheque) {
                // Automatically set the row outflow amount to the exact cheque amount
                $this->rows[$index]['outflow'] = (string) $cheque->cheque_amount;
            }
        } else {
            $this->rows[$index]['cheque_id'] = null;
            $this->rows[$index]['cheque_number'] = '';
        }

        // Save the row
        $this->saveRow($index);

        // If cheque was selected for outflow, mark it as used (complete)
        if (!$this->isPendingInflow && $this->selectedPaymentMethod === 'cheque' && $this->selectedChequeId) {
            Cheque::where('id', $this->selectedChequeId)->update(['status' => 'complete']);
        }

        $this->closePaymentModal();
    }

    public function closePaymentModal(): void
    {
        $this->showPaymentModal = false;
        $this->pendingPaymentIndex = null;
        $this->isPendingInflow = false;
        $this->selectedPaymentMethod = 'cash';
        $this->chequeSearch = '';
        $this->selectedChequeId = null;
        $this->availableCheques = [];
        $this->resetErrorBag('chequeSelection');
    }

    // ─── Cheque Detail Viewer ─────────────────────────────────────────

    public function viewChequeDetails(int $index): void
    {
        if (! isset($this->rows[$index])) {
            return;
        }

        $row = $this->rows[$index];
        $chequeId = $row['cheque_id'] ?? null;

        if (! $chequeId) {
            return;
        }

        $cheque = Cheque::with('customer')->find($chequeId);

        if (! $cheque) {
            return;
        }

        $this->chequeDetail = [
            'cheque_number' => $cheque->cheque_number,
            'bank_name' => $cheque->bank_name,
            'cheque_date' => $cheque->cheque_date ? Carbon::parse($cheque->cheque_date)->format('d M Y') : 'N/A',
            'cheque_amount' => number_format((float) $cheque->cheque_amount, 2),
            'status' => ucfirst($cheque->status),
            'customer_name' => $cheque->customer ? $cheque->customer->name : 'N/A',
            'description' => $row['description'] ?? '',
        ];

        $this->showChequeDetail = true;
    }

    public function closeChequeDetailModal(): void
    {
        $this->showChequeDetail = false;
        $this->chequeDetail = [];
    }

    // ─── Validation & Save ────────────────────────────────────────────

    protected function validateRow(int $index): ?array
    {
        if (! isset($this->rows[$index])) {
            return null;
        }

        $this->applyRowRules($index);

        $row = $this->rows[$index];
        $description = trim((string) ($row['description'] ?? ''));
        $isProfitMargin = $this->isProfitMarginDescription($description);
        $inflow = is_numeric($row['inflow'] ?? null) ? (float) $row['inflow'] : 0;
        $outflow = is_numeric($row['outflow'] ?? null) ? (float) $row['outflow'] : 0;
        $outflowMonth = (string) ($row['outflow_month'] ?? '');

        if ($description === '' && $inflow <= 0 && $outflow <= 0) {
            return null;
        }

        if ($description === '') {
            $this->addError("rows.$index.description", 'Description is required.');
        }

        if ($inflow < 0) {
            $this->addError("rows.$index.inflow", 'Inflow must be 0 or higher.');
        }

        if ($outflow < 0) {
            $this->addError("rows.$index.outflow", 'Outflow must be 0 or higher.');
        }

        if ($inflow > 0 && $outflow > 0) {
            $this->addError("rows.$index.inflow", 'Use either inflow or outflow in one line.');
            $this->addError("rows.$index.outflow", 'Use either inflow or outflow in one line.');
        }

        if ($inflow <= 0 && $outflow <= 0) {
            $this->addError("rows.$index.inflow", 'Enter inflow or outflow amount.');
        }

        if ($isProfitMargin && $outflow > 0) {
            if (! preg_match('/^\d{4}-\d{2}$/', $outflowMonth)) {
                $this->addError("rows.$index.outflow_month", 'Select month for Profit Margin outflow.');
            } else {
                $reference = 'profit-month:' . $outflowMonth;

                // 1. Check DB for duplicate
                $existsInDb = $this->investor->transactions()
                    ->where('reference', $reference)
                    ->when($row['id'] ?? null, function ($query, $id) {
                        $query->where('id', '!=', $id);
                    })
                    ->exists();

                if ($existsInDb) {
                    $this->addError("rows.$index.outflow_month", 'Profit for this month is already given inside the ledger.');
                } else {
                    // 2. Check UI rows for duplicate
                    foreach ($this->rows as $rIndex => $r) {
                        if ($rIndex !== $index) {
                            $isOtherProfit = $this->isProfitMarginDescription(trim((string) ($r['description'] ?? '')));
                            if ($isOtherProfit && ($r['outflow_month'] ?? '') === $outflowMonth) {
                                $this->addError("rows.$index.outflow_month", 'You have selected this month in another row.');
                                break;
                            }
                        }
                    }
                }
            }
        }

        if ($this->getErrorBag()->isNotEmpty()) {
            return null;
        }

        $type = $inflow > 0 ? 'inflow' : 'outflow';

        $rowDate = trim((string) ($row['transaction_date'] ?? ''));

        return [
            'description' => $description,
            'type' => $type,
            'amount' => $inflow > 0 ? $inflow : $outflow,
            'reference' => $isProfitMargin && $outflowMonth !== '' ? 'profit-month:' . $outflowMonth : null,
            'transaction_date' => $isProfitMargin && $outflowMonth !== ''
                ? Carbon::createFromFormat('Y-m', $outflowMonth)->startOfMonth()->toDateString()
                : ($rowDate !== '' ? $rowDate : now()->toDateString()),
            'payment_method' => $row['payment_method'] ?: 'cash',
            'cheque_id' => $type === 'outflow' ? ($row['cheque_id'] ?? null) : null,
        ];
    }

    public function saveRow(int $index): void
    {
        $this->resetErrorBag();
        $validated = $this->validateRow($index);

        if ($this->getErrorBag()->isNotEmpty()) {
            return;
        }

        if ($validated === null) {
            return;
        }

        $row = $this->rows[$index];

        if (! empty($row['id'])) {
            $transaction = $this->investor->transactions()->findOrFail($row['id']);
            $transaction->update([
                'type' => $validated['type'],
                'amount' => $validated['amount'],
                'transaction_date' => $validated['transaction_date'],
                'reference' => $validated['reference'],
                'description' => $validated['description'],
                'payment_method' => $validated['payment_method'],
                'cheque_id' => $validated['cheque_id'],
            ]);
        } else {
            $transaction = InvestorTransaction::create([
                'investor_id' => $this->investor->id,
                'type' => $validated['type'],
                'amount' => $validated['amount'],
                'transaction_date' => $validated['transaction_date'],
                'reference' => $validated['reference'],
                'description' => $validated['description'],
                'payment_method' => $validated['payment_method'],
                'cheque_id' => $validated['cheque_id'],
            ]);

            $this->rows[$index]['id'] = $transaction->id;
        }

        $this->ensureTrailingBlankRow();

        session()->flash('success', 'Line saved successfully.');
    }

    public function saveRows(): void
    {
        $this->resetErrorBag();

        // Check if any outflow rows are missing payment method
        foreach (array_keys($this->rows) as $index) {
            $row = $this->rows[$index];
            $outflow = is_numeric($row['outflow'] ?? null) ? (float) $row['outflow'] : 0;
            $description = trim((string) ($row['description'] ?? ''));
            $paymentMethod = trim((string) ($row['payment_method'] ?? ''));

            if ($outflow > 0 && $description !== '' && $paymentMethod === '') {
                // Open payment modal for this row
                $this->pendingOutflowIndex = $index;
                $this->selectedPaymentMethod = 'cash';
                $this->chequeSearch = '';
                $this->selectedChequeId = null;
                $this->loadAvailableCheques();
                $this->showPaymentModal = true;
                return;
            }
        }

        foreach (array_keys($this->rows) as $index) {
            $validated = $this->validateRow($index);

            if ($this->getErrorBag()->isNotEmpty()) {
                return;
            }

            if ($validated === null) {
                continue;
            }

            $row = $this->rows[$index];

            if (! empty($row['id'])) {
                $transaction = $this->investor->transactions()->findOrFail($row['id']);
                $transaction->update([
                    'type' => $validated['type'],
                    'amount' => $validated['amount'],
                    'transaction_date' => $validated['transaction_date'],
                    'reference' => $validated['reference'],
                    'description' => $validated['description'],
                    'payment_method' => $validated['payment_method'],
                    'cheque_id' => $validated['cheque_id'],
                ]);
            } else {
                $transaction = InvestorTransaction::create([
                    'investor_id' => $this->investor->id,
                    'type' => $validated['type'],
                    'amount' => $validated['amount'],
                    'transaction_date' => $validated['transaction_date'],
                    'reference' => $validated['reference'],
                    'description' => $validated['description'],
                    'payment_method' => $validated['payment_method'],
                    'cheque_id' => $validated['cheque_id'],
                ]);

                $this->rows[$index]['id'] = $transaction->id;
            }
        }

        $this->ensureTrailingBlankRow();

        session()->flash('success', 'All lines saved successfully.');
    }

    public function getTotalsProperty(): array
    {
        $row = $this->investor->transactions()
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'inflow' THEN amount ELSE 0 END), 0) as total_inflow")
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'outflow' THEN amount ELSE 0 END), 0) as total_outflow")
            ->first();

        $inflow = (float) ($row->total_inflow ?? 0);
        $outflow = (float) ($row->total_outflow ?? 0);

        return [
            'inflow' => $inflow,
            'outflow' => $outflow,
            'balance' => $inflow - $outflow,
        ];
    }

    public function render()
    {
        return view('livewire.admin.investor-ledger', [
            'totals' => $this->totals,
            'descriptionSuggestions' => $this->descriptionSuggestions,
            'monthOptions' => $this->monthOptions,
        ])->layout('components.layouts.admin');
    }
}
