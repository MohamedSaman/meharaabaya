<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\Payment;
use App\Models\ReturnsProduct;

class CustomerTransactionHistory extends Component
{
    public Customer $customer;

    public function mount(Customer $customer)
    {
        $this->customer = $customer;
    }

    public function render()
    {
        // 1. Get Sales (Increases Balance / Debit)
        $sales = Sale::where('customer_id', $this->customer->id)
            ->get()
            ->map(function ($sale) {
                return [
                    'type' => 'Sale',
                    'id' => $sale->id,
                    'reference' => 'INV-' . str_pad($sale->id, 5, '0', STR_PAD_LEFT),
                    'date' => $sale->created_at,
                    'debit' => $sale->total_amount,
                    'credit' => 0,
                    'details' => 'Sale Invoice: ' . ($sale->invoice_number ?? ''),
                    'cheque_count' => null,
                    'due_days' => $sale->due_date
                        ? (int) \Carbon\Carbon::parse($sale->created_at)->startOfDay()->diffInDays(\Carbon\Carbon::parse($sale->due_date)->startOfDay())
                        : null,
                ];
            });

        // 2. Get Payments (Decreases Balance / Credit)
        $payments = Payment::where(function ($query) {
            $query->where('customer_id', $this->customer->id)
                ->orWhereHas('sale', function ($q) {
                    $q->where('customer_id', $this->customer->id);
                });
        })
            ->withCount('cheques')
            ->get()
            ->map(function ($payment) {
                // Some live environments store payment_date as date-only, which renders 12:00 AM.
                // Prefer created_at when payment_date has no meaningful time component.
                $transactionDate = $payment->payment_date;
                if (
                    !$transactionDate ||
                    (
                        $payment->created_at &&
                        $transactionDate->format('H:i:s') === '00:00:00' &&
                        $payment->created_at->format('H:i:s') !== '00:00:00'
                    )
                ) {
                    $transactionDate = $payment->created_at;
                }

                return [
                    'type' => 'Payment',
                    'id' => $payment->id,
                    'reference' => $payment->payment_reference ?? 'PAY-' . str_pad($payment->id, 5, '0', STR_PAD_LEFT),
                    'date' => $transactionDate,
                    'debit' => 0,
                    'credit' => $payment->amount,
                    'details' => 'Payment via ' . ucfirst(str_replace('_', ' ', $payment->payment_method)),
                    'cheque_count' => $payment->payment_method === 'cheque' ? $payment->cheques_count : null,
                    'due_days' => null,
                ];
            });

        // 3. Get Returns (Decreases Balance / Credit)
        $returns = ReturnsProduct::whereHas('sale', function ($q) {
            $q->where('customer_id', $this->customer->id);
        })
            ->get()
            ->map(function ($return) {
                return [
                    'type' => 'Return',
                    'id' => $return->id,
                    'reference' => 'RET-' . str_pad($return->id, 5, '0', STR_PAD_LEFT),
                    'date' => $return->created_at,
                    'debit' => 0,
                    'credit' => $return->total_amount,
                    'details' => 'Product Return',
                    'cheque_count' => null,
                    'due_days' => null,
                ];
            });

        // Combine, sort by date ascending, and reset keys
        $transactions = collect()
            ->concat($sales)
            ->concat($payments)
            ->concat($returns)
            ->sortBy(function ($transaction) {
                // Ensure strictly stable sorting if timestamps are exactly identical.
                // Sales (type A) come first, then returns (type B), then payments (type C).
                $typeOrder = ['Sale' => 1, 'Return' => 2, 'Payment' => 3];
                $timestamp = \Carbon\Carbon::parse($transaction['date'])->timestamp;
                return $timestamp . '_' . $typeOrder[$transaction['type']] . '_' . $transaction['id'];
            })
            ->values();

        // Calculate running balance
        $balance = 0;
        $processedTransactions = $transactions->map(function ($transaction) use (&$balance) {
            $balance += $transaction['debit'];
            $balance -= $transaction['credit'];
            $transaction['cheque_count'] = $transaction['cheque_count'] ?? null;
            $transaction['due_days'] = $transaction['due_days'] ?? null;
            $transaction['balance'] = $balance;
            return $transaction;
        });

        // Return transactions in standard chronological order (oldest first, newest last)
        // This ensures chronological reading top-to-bottom makes logical sense to users.
        return view('livewire.admin.customer-transaction-history', [
            'transactions' => $processedTransactions
        ])->layout('components.layouts.admin');
    }
}
