<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Quotation extends Model
{
    use HasFactory;

    protected $fillable = [
        'quotation_number',
        'reference_number',
        'customer_id',
        'customer_type',
        'customer_name',
        'customer_phone',
        'customer_email',
        'customer_address',
        'quotation_date',
        'valid_until',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'shipping_charges',
        'total_amount',
        'items',
        'terms_conditions',
        'notes',
        'status',
        'rejection_reason',
        'sent_at',
        'accepted_at',
        'rejected_at',
        'converted_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'quotation_date' => 'date',
        'valid_until' => 'date',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'shipping_charges' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'items' => 'array',
        'sent_at' => 'datetime',
        'accepted_at' => 'datetime',
        'rejected_at' => 'datetime',
        'converted_at' => 'datetime',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::creating(function ($quotation) {
            if (empty($quotation->quotation_number)) {
                $quotation->quotation_number = static::generateQuotationNumber();
            }
            if (Auth::check()) {
                $quotation->created_by = Auth::id();
            }
        });

        static::updating(function ($quotation) {
            if (Auth::check()) {
                $quotation->updated_by = Auth::id();
            }
            
            // Auto update status to expired when valid_until date passes
            if ($quotation->valid_until->lt(now()) && 
                in_array($quotation->status, ['draft', 'sent'])) {
                $quotation->status = 'expired';
            }
        });
    }

    /**
     * Get the customer that owns the quotation.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the user who created the quotation.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the quotation.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Generate a unique quotation number.
     */
    public static function generateQuotationNumber(): string
    {
        $prefix = 'QTN';
        $year = date('Y');
        $month = date('m');
        
        $lastQuotation = static::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('id', 'desc')
            ->first();
            
        $sequence = $lastQuotation ? intval(substr($lastQuotation->quotation_number, -4)) + 1 : 1;
        
        return $prefix . $year . $month . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Calculate totals from items.
     */
    public function calculateTotals(): void
    {
        $items = $this->items ?? [];
        
        $this->subtotal = 0;
        $this->discount_amount = 0;
        
        foreach ($items as $item) {
            $quantity = $item['quantity'] ?? 1;
            $unitPrice = $item['unit_price'] ?? 0;
            $discount = $item['discount'] ?? 0;
            
            $this->subtotal += $unitPrice * $quantity;
            $this->discount_amount += $discount * $quantity;
        }
        
        $this->total_amount = $this->subtotal - $this->discount_amount + $this->tax_amount + $this->shipping_charges;
    }

    /**
     * Add item to quotation.
     */
    public function addItem(array $itemData): void
    {
        $items = $this->items ?? [];
        
        $items[] = [
            'id' => count($items) + 1,
            'product_id' => $itemData['product_id'],
            'product_code' => $itemData['product_code'],
            'product_name' => $itemData['product_name'],
            'product_model' => $itemData['product_model'] ?? null,
            'product_brand' => $itemData['product_brand'] ?? null,
            'quantity' => $itemData['quantity'] ?? 1,
            'unit_price' => $itemData['unit_price'] ?? 0,
            'discount' => $itemData['discount'] ?? 0,
            'total' => (($itemData['unit_price'] ?? 0) * ($itemData['quantity'] ?? 1)) - (($itemData['discount'] ?? 0) * ($itemData['quantity'] ?? 1)),
            'description' => $itemData['description'] ?? null,
        ];
        
        $this->items = $items;
        $this->calculateTotals();
    }

    /**
     * Remove item from quotation.
     */
    public function removeItem(int $itemId): void
    {
        $items = $this->items ?? [];
        $items = array_filter($items, fn($item) => $item['id'] !== $itemId);
        
        // Reindex items
        $items = array_values($items);
        foreach ($items as $index => &$item) {
            $item['id'] = $index + 1;
        }
        
        $this->items = $items;
        $this->calculateTotals();
    }

    /**
     * Update item in quotation.
     */
    public function updateItem(int $itemId, array $itemData): void
    {
        $items = $this->items ?? [];
        
        foreach ($items as &$item) {
            if ($item['id'] === $itemId) {
                $item = array_merge($item, $itemData);
                $item['total'] = ($item['unit_price'] * $item['quantity']) - ($item['discount'] * $item['quantity']);
                break;
            }
        }
        
        $this->items = $items;
        $this->calculateTotals();
    }

    /**
     * Get all items.
     */
    public function getItems(): array
    {
        return $this->items ?? [];
    }

    /**
     * Get item by ID.
     */
    public function getItem(int $itemId): ?array
    {
        $items = $this->items ?? [];
        
        foreach ($items as $item) {
            if ($item['id'] === $itemId) {
                return $item;
            }
        }
        
        return null;
    }

    /**
     * Check if quotation is expired.
     */
    public function getIsExpiredAttribute(): bool
    {
        return $this->valid_until->lt(now()) && !in_array($this->status, ['accepted', 'rejected', 'converted']);
    }

    /**
     * Check if quotation can be edited.
     */
    public function getCanEditAttribute(): bool
    {
        return in_array($this->status, ['draft']);
    }

    /**
     * Check if quotation can be converted to sale.
     */
    public function getCanConvertAttribute(): bool
    {
        return in_array($this->status, ['accepted']);
    }

    /**
     * Mark quotation as sent.
     */
    public function markAsSent(): bool
    {
        if ($this->status === 'draft') {
            $this->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);
            return true;
        }
        return false;
    }

    /**
     * Mark quotation as accepted.
     */
    public function markAsAccepted(): bool
    {
        if (in_array($this->status, ['sent', 'draft'])) {
            $this->update([
                'status' => 'accepted',
                'accepted_at' => now(),
            ]);
            return true;
        }
        return false;
    }

    /**
     * Mark quotation as rejected.
     */
    public function markAsRejected(string $reason = null): bool
    {
        if (in_array($this->status, ['sent', 'draft'])) {
            $this->update([
                'status' => 'rejected',
                'rejection_reason' => $reason,
                'rejected_at' => now(),
            ]);
            return true;
        }
        return false;
    }

    /**
     * Mark quotation as converted to sale.
     */
    public function markAsConverted(): bool
    {
        if ($this->status === 'accepted') {
            $this->update([
                'status' => 'converted',
                'converted_at' => now(),
            ]);
            return true;
        }
        return false;
    }

    /**
     * Scope a query to only include draft quotations.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope a query to only include sent quotations.
     */
    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    /**
     * Scope a query to only include accepted quotations.
     */
    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    /**
     * Scope a query to only include rejected quotations.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope a query to only include expired quotations.
     */
    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    /**
     * Scope a query to only include converted quotations.
     */
    public function scopeConverted($query)
    {
        return $query->where('status', 'converted');
    }

    /**
     * Scope a query to only include active quotations.
     */
    public function scopeActive($query)
    {
        return $query->where('valid_until', '>=', now())
                    ->whereIn('status', ['draft', 'sent']);
    }

    /**
     * Scope a query to only include quotations for a specific customer.
     */
    public function scopeForCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    /**
     * Scope a query to only include quotations within a date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('quotation_date', [$startDate, $endDate]);
    }

    /**
     * Get the formatted total amount.
     */
    public function getFormattedTotalAttribute(): string
    {
        return 'Rs. ' . number_format($this->total_amount, 2);
    }

    /**
     * Get the formatted subtotal amount.
     */
    public function getFormattedSubtotalAttribute(): string
    {
        return 'Rs. ' . number_format($this->subtotal, 2);
    }

    /**
     * Get the formatted discount amount.
     */
    public function getFormattedDiscountAttribute(): string
    {
        return 'Rs. ' . number_format($this->discount_amount, 2);
    }

    /**
     * Get the days remaining until expiration.
     */
    public function getDaysRemainingAttribute(): int
    {
        return max(0, now()->diffInDays($this->valid_until, false));
    }

    /**
     * Get the status badge class.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'draft' => 'bg-secondary',
            'sent' => 'bg-info',
            'accepted' => 'bg-success',
            'rejected' => 'bg-danger',
            'expired' => 'bg-warning',
            'converted' => 'bg-primary',
            default => 'bg-secondary'
        };
    }
}