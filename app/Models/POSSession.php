<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class POSSession extends Model
{
    use HasFactory;

    protected $table = 'pos_sessions';

    protected $fillable = [
        'user_id',
        'session_date',
        'opening_cash',
        'closing_cash',
        'total_sales',
        'cash_sales',
        'cheque_payment',
        'credit_card_payment',
        'bank_transfer',
        'late_payment_bulk',
        'refunds',
        'expenses',
        'cash_deposit_bank',
        'expected_cash',
        'cash_difference',
        'notes',
        'status',
        'opened_at',
        'closed_at',
    ];

    protected $casts = [
        'session_date' => 'date',
        'opening_cash' => 'decimal:2',
        'closing_cash' => 'decimal:2',
        'total_sales' => 'decimal:2',
        'cash_sales' => 'decimal:2',
        'cheque_payment' => 'decimal:2',
        'credit_card_payment' => 'decimal:2',
        'bank_transfer' => 'decimal:2',
        'late_payment_bulk' => 'decimal:2',
        'refunds' => 'decimal:2',
        'expenses' => 'decimal:2',
        'cash_deposit_bank' => 'decimal:2',
        'expected_cash' => 'decimal:2',
        'cash_difference' => 'decimal:2',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    /**
     * Get the user that owns the session
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get or create today's open session for a user
     */
    public static function getTodaySession($userId)
    {
        return self::where('user_id', $userId)
            ->where('session_date', Carbon::today())
            ->where('status', 'open')
            ->first();
    }

    /**
     * Create a new session
     */
    public static function openSession($userId, $openingCash, $notes = null)
    {
        // Check if there's already an open session for today
        $existingSession = self::getTodaySession($userId);
        if ($existingSession) {
            return $existingSession;
        }

        return self::create([
            'user_id' => $userId,
            'session_date' => now()->toDateString(),
            'opening_cash' => $openingCash,
            'status' => 'open',
            'notes' => $notes,
        ]);
    }

    /**
     * Close the session
     */
    public function closeSession($closingCash, $notes = null)
    {
        $this->update([
            'closing_cash' => $closingCash,
            'status' => 'closed',
            'closed_at' => now(),
            'notes' => $notes,
        ]);

        $this->calculateDifference();
    }

    /**
     * Calculate expected cash and difference
     */
    public function calculateDifference()
    {
        $expectedCash = $this->opening_cash + $this->cash_sales - $this->refunds - $this->expenses - $this->cash_deposit_bank;
        $difference = $this->closing_cash - $expectedCash;

        $this->update([
            'expected_cash' => $expectedCash,
            'cash_difference' => $difference,
        ]);
    }

    /**
     * Update session totals from sales
     */
    public function updateFromSales()
    {
        // Get all POS sales for this user on this date
        $sales = Sale::where('user_id', $this->user_id)
            ->where('sale_type', 'pos')
            ->whereDate('created_at', $this->session_date)
            ->with('payments')
            ->get();

        // Total sales amount
        $this->total_sales = $sales->sum('total_amount');

        // Get payment details from payments table
        $payments = Payment::whereIn('sale_id', $sales->pluck('id'))
            ->whereDate('payment_date', $this->session_date)
            ->get();

        // Group payments by method
        $this->cash_sales = $payments->where('payment_method', 'cash')->sum('amount');
        $this->cheque_payment = $payments->where('payment_method', 'cheque')->sum('amount');
        $this->credit_card_payment = $payments->where('payment_method', 'card')->sum('amount');
        $this->bank_transfer = $payments->where('payment_method', 'bank_transfer')->sum('amount');

        // Get refunds (returns) for this session
        $this->refunds = 0; // You can add returns logic here if needed

        $this->save();
    }

    /**
     * Check if session is closed
     */
    public function isClosed()
    {
        return $this->status === 'closed';
    }

    /**
     * Check if session is open
     */
    public function isOpen()
    {
        return $this->status === 'open';
    }
}
