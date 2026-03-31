<?php

namespace App\Http\Controllers\Api;

use App\Models\Quotation;
use App\Models\User;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Customer;
use App\Models\ProductDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QuotationController extends ApiController
{
    /**
     * Get all quotations with optional filters
     */
    public function index(Request $request)
    {
        $query = Quotation::with(['customer', 'creator']);

        // Search
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('quotation_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }

        /** @var \Illuminate\Pagination\LengthAwarePaginator $quotations */
        $quotations = $query->orderBy('created_at', 'desc')->paginate(20);

        $quotations->through(function ($quotation) {
            return $this->transformQuotation($quotation);
        });

        return $this->paginated($quotations);
    }

    /**
     * Get a single quotation by ID
     */
    public function show($id)
    {
        $quotation = Quotation::with(['customer', 'creator'])->find($id);

        if (!$quotation) {
            return $this->error('Quotation not found', 404);
        }

        return $this->success($this->transformQuotation($quotation, true));
    }

    /**
     * Create a new quotation
     */
    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
        ]);


        // Calculate totals
        $subtotal = 0;
        foreach ($request->items as $item) {
            $subtotal += ($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0);
        }

        $discount = $request->discount ?? 0;
        $totalAmount = $subtotal - $discount;

        // Get user ID - use authenticated user or find/create API user (Fallback like SaleController)
        $userId = Auth::id();
        if (!$userId) {
            $apiUser = User::where('email', 'api@system.local')->first();
            if (!$apiUser) {
                // Check if User model exists to create
                try {
                    $apiUser = User::create([
                        'name' => 'API System',
                        'email' => 'api@system.local',
                        'password' => Hash::make('api-system-user-qtn'),
                        'contact' => '0000000000',
                        'role' => 'admin',
                    ]);
                } catch (\Exception $e) {
                    // If creation fails (e.g. strict mode), try fallback to ID 1 or logging it
                    \Illuminate\Support\Facades\Log::warning('Failed to create API user: ' . $e->getMessage());
                }
            }
            $userId = $apiUser ? $apiUser->id : 1; // Fallback to ID 1 if all else fails
        }

        $quotation = Quotation::create([
            'reference_number' => $request->reference_number,
            'customer_id' => $request->customer_id,
            'customer_name' => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'customer_email' => $request->customer_email,
            'customer_address' => $request->customer_address,
            'quotation_date' => $request->quotation_date ?? now(),
            'valid_until' => $request->valid_until ?? now()->addDays(30),
            'items' => $request->items,
            'subtotal' => $subtotal,
            'discount_amount' => $discount,
            'total_amount' => $totalAmount,
            'notes' => $request->notes,
            'terms_conditions' => $request->terms,
            'status' => 'draft',
            'created_by' => $userId,
        ]);

        return $this->success($this->transformQuotation($quotation, true), 'Quotation created successfully', 201);
    }

    /**
     * Update a quotation
     */
    public function update(Request $request, $id)
    {
        $quotation = Quotation::find($id);

        if (!$quotation) {
            return $this->error('Quotation not found', 404);
        }

        // Recalculate if items changed
        if ($request->has('items')) {
            $subtotal = 0;
            foreach ($request->items as $item) {
                $subtotal += ($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0);
            }
            $discount = $request->discount ?? $quotation->discount_amount ?? 0;
            $totalAmount = $subtotal - $discount;

            $quotation->items = $request->items;
            $quotation->subtotal = $subtotal;
            $quotation->total_amount = $totalAmount;
        }

        $quotation->update([
            'customer_id' => $request->customer_id ?? $quotation->customer_id,
            'customer_name' => $request->customer_name ?? $quotation->customer_name,
            'customer_phone' => $request->customer_phone ?? $quotation->customer_phone,
            'customer_email' => $request->customer_email ?? $quotation->customer_email,
            'valid_until' => $request->valid_until ?? $quotation->valid_until,
            'notes' => $request->notes ?? $quotation->notes,
            'terms_conditions' => $request->terms ?? $quotation->terms_conditions,
            'status' => $request->status ?? $quotation->status,
        ]);

        return $this->success($this->transformQuotation($quotation, true), 'Quotation updated successfully');
    }

    /**
     * Delete a quotation
     */
    public function destroy($id)
    {
        $quotation = Quotation::find($id);

        if (!$quotation) {
            return $this->error('Quotation not found', 404);
        }

        $quotation->delete();
        return $this->success(null, 'Quotation deleted successfully');
    }

    /**
     * Transform quotation for API response
     */
    private function transformQuotation($quotation, $detailed = false)
    {
        $data = [
            'id' => $quotation->id,
            'quotation_number' => $quotation->quotation_number,
            'reference_number' => $quotation->reference_number,
            'customer' => $quotation->customer ? [
                'id' => $quotation->customer->id,
                'name' => $quotation->customer->name,
            ] : null,
            'customer_name' => $quotation->customer_name,
            'customer_phone' => $quotation->customer_phone,
            'quotation_date' => $quotation->quotation_date,
            'valid_until' => $quotation->valid_until,
            'subtotal' => (float) $quotation->subtotal,
            'discount' => (float) $quotation->discount_amount,
            'total_amount' => (float) $quotation->total_amount,
            'status' => $quotation->status,
            'created_by' => $quotation->creator ? $quotation->creator->name : null,
            'created_at' => $quotation->created_at,
        ];

        if ($detailed) {
            $rawItems = $quotation->items;
            $data['items'] = is_string($rawItems) ? json_decode($rawItems, true) : $rawItems;
            $data['notes'] = $quotation->notes;
            $data['terms'] = $quotation->terms_conditions;
            $data['customer_email'] = $quotation->customer_email;
            $data['customer_address'] = $quotation->customer_address;
        }

        return $data;
    }

    /**
     * Convert quotation to sale
     */
    public function convertToSale($id)
    {
        $quotation = Quotation::find($id);

        if (!$quotation) {
            return $this->error('Quotation not found', 404);
        }

        if ($quotation->status === 'converted') {
            return $this->error('Quotation has already been converted to a sale', 400);
        }

        try {
            DB::beginTransaction();

            // Find or create customer
            // Get user ID - use authenticated user or find/create API user (Fallback)
            $userId = Auth::id();
            if (!$userId) {
                $apiUser = User::where('email', 'api@system.local')->first();
                $userId = $apiUser ? $apiUser->id : 1;
            }

            $customer = null;
            if ($quotation->customer_id) {
                $customer = \App\Models\Customer::find($quotation->customer_id);
            }

            if (!$customer && $quotation->customer_phone) {
                $customer = \App\Models\Customer::where('phone', $quotation->customer_phone)->first();
            }

            if (!$customer) {
                $customer = \App\Models\Customer::create([
                    'name' => $quotation->customer_name ?? 'Walk-in Customer',
                    'phone' => $quotation->customer_phone,
                    'email' => $quotation->customer_email,
                    'address' => $quotation->customer_address,
                    'type' => 'retail',
                    'user_id' => $userId,
                ]);
            }

            // Parse items
            $rawItems = $quotation->items;
            $items = is_string($rawItems) ? json_decode($rawItems, true) : $rawItems;
            $items = is_array($items) ? $items : [];

            // Create Sale
            $sale = Sale::create([
                'invoice_number' => Sale::generateInvoiceNumber(),
                'customer_id' => $customer->id,
                'subtotal' => $quotation->subtotal,
                'discount_amount' => $quotation->discount_amount ?? 0,
                'total_amount' => $quotation->total_amount,
                'payment_type' => 'full',
                'payment_status' => 'pending',
                'due_amount' => $quotation->total_amount,
                'notes' => "Created from Quotation #" . $quotation->quotation_number,
                'user_id' => $userId,
                'status' => 'confirm',
            ]);

            // Create sale items and update stock
            foreach ($items as $item) {
                // Determine discount per unit (check compatibility keys)
                $discountPerUnit = $item['discount_per_unit'] ?? $item['discount'] ?? $item['discount_amount'] ?? 0;
                $quantity = $item['quantity'] ?? 1;
                $unitPrice = $item['unit_price'] ?? 0;

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'] ?? null,
                    'product_code' => $item['product_code'] ?? '',
                    'product_name' => $item['product_name'] ?? $item['name'] ?? 'N/A',
                    'product_model' => $item['product_model'] ?? '',
                    'quantity' => $quantity, // Ensure valid value
                    'unit_price' => $unitPrice,
                    'discount_per_unit' => $discountPerUnit,
                    'total_discount' => $discountPerUnit * $quantity,
                    'total' => ($unitPrice * $quantity) - ($discountPerUnit * $quantity),
                ]);

                // Update product stock
                if (!empty($item['product_id'])) {
                    $product = ProductDetail::find($item['product_id']);
                    if ($product && $product->stock) {
                        $product->stock->available_stock -= $item['quantity'] ?? 0;
                        $product->stock->save();
                    }
                }
            }

            // Mark quotation as converted
            $quotation->update([
                'status' => 'converted',
            ]);

            DB::commit();

            return $this->success([
                'sale_id' => $sale->id,
                'invoice_number' => $sale->invoice_number,
            ], 'Quotation converted to sale successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Conversion failed: ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString());
            return $this->error('Failed to convert quotation: ' . $e->getMessage(), 500);
        }
    }
}

