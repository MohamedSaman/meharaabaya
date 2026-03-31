<?php

namespace App\Http\Controllers\Api;

use App\Models\ReturnsProduct;
use App\Models\ReturnSupplier;
use App\Models\Sale;
use App\Models\PurchaseOrder;
use App\Models\ProductDetail;
use App\Models\ProductStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReturnController extends ApiController
{
    // ============================================================================================
    // CUSTOMER RETURNS
    // ============================================================================================

    /**
     * Get list of customer returns
     */
    public function getCustomerReturns(Request $request)
    {
        $limit = $request->query('limit', 20);
        $search = $request->query('search', '');

        $query = ReturnsProduct::with(['sale.customer', 'product'])
            ->orderBy('created_at', 'desc');

        if ($search) {
            $query->whereHas('sale', function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($cq) use ($search) {
                        $cq->where('name', 'like', "%{$search}%");
                    });
            })->orWhereHas('product', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        $returns = $query->paginate($limit);

        return $this->success([
            'results' => $returns->items(),
            'count' => $returns->total(),
            'total_pages' => $returns->lastPage(),
        ]);
    }

    /**
     * Store a new customer return
     */
    public function storeCustomerReturn(Request $request)
    {
        $request->validate([
            'sale_id' => 'required|exists:sales,id',
            'product_id' => 'required|exists:product_details,id',
            'return_quantity' => 'required|numeric|min:1',
            'reason' => 'required|in:damage,sale_return', // damage or sale_return
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $sale = Sale::findOrFail($request->sale_id);
            // Verify product was in sale (optional but recommended)
            // For now assuming frontend filters correctly

            // Get product selling price from the return request or fetch from sale items if implemented
            // Assuming we take current selling price or passed price
            $product = ProductDetail::findOrFail($request->product_id);

            // Calculate refund amount based on sold price? 
            // Ideally we should find the sale item and get the price it was sold at.
            // For simplicity, we might accept unit_price from frontend or use product's current selling price 
            // but for returns, using the original sold price is best.
            // Let's assume the frontend passes the price or we fetch it. 
            // Since the model ReturnsProduct has 'selling_price', lets use that.

            $sellingPrice = $request->input('unit_price', $product->selling_price);
            $totalAmount = $sellingPrice * $request->return_quantity;

            $return = ReturnsProduct::create([
                'sale_id' => $request->sale_id,
                'product_id' => $request->product_id,
                'return_quantity' => $request->return_quantity,
                'selling_price' => $sellingPrice,
                'total_amount' => $totalAmount,
                'notes' => $request->notes,
                // We might need to store the reason in the model if it supports it, 
                // currently ReturnsProduct fillable doesn't show 'reason', but we need it for logic.
                // Assuming we just use it for stock logic for now.
            ]);

            // Stock Logic
            $stock = ProductStock::where('product_id', $request->product_id)->first();

            if ($stock) {
                if ($request->reason === 'damage') {
                    // Add to damage_stock (using correct column name from migration)
                    $stock->damage_stock += $request->return_quantity;
                } else {
                    // Sale return -> Add to available stock
                    $stock->available_stock += $request->return_quantity;
                }

                // Update totals (total_stock = available + damage) using the model method if available, or manually
                if (method_exists($stock, 'updateTotals')) {
                    $stock->updateTotals();
                } else {
                    $stock->total_stock = $stock->available_stock + $stock->damage_stock;
                    $stock->save();
                }
            } else {
                // If stock record doesn't exist, create it (though it should usually exist for a sold product)
                $stock = new ProductStock();
                $stock->product_id = $request->product_id;
                if ($request->reason === 'damage') {
                    $stock->damage_stock = $request->return_quantity;
                } else {
                    $stock->available_stock = $request->return_quantity;
                }
                $stock->total_stock = $stock->available_stock + $stock->damage_stock;
                $stock->save();
            }

            DB::commit();
            return $this->success($return, 'Customer return recorded successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Customer Return Error: ' . $e->getMessage());
            return $this->error('Failed to record return: ' . $e->getMessage(), 500);
        }
    }

    // ============================================================================================
    // SUPPLIER RETURNS
    // ============================================================================================

    /**
     * Get list of supplier returns
     */
    public function getSupplierReturns(Request $request)
    {
        $limit = $request->query('limit', 20);
        $search = $request->query('search', '');

        $query = ReturnSupplier::with(['purchaseOrder.supplier', 'product'])
            ->orderBy('created_at', 'desc');

        if ($search) {
            $query->whereHas('purchaseOrder', function ($q) use ($search) {
                $q->where('po_number', 'like', "%{$search}%")
                    ->orWhereHas('supplier', function ($sq) use ($search) {
                        $sq->where('supplier_name', 'like', "%{$search}%");
                    });
            })->orWhereHas('product', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        $returns = $query->paginate($limit);

        return $this->success([
            'results' => $returns->items(),
            'count' => $returns->total(),
            'total_pages' => $returns->lastPage(),
        ]);
    }

    /**
     * Store a new supplier return
     */
    /**
     * Store a new supplier return
     */
    public function storeSupplierReturn(Request $request)
    {
        $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'product_id' => 'required|exists:product_details,id',
            'return_quantity' => 'required|numeric|min:1',
            'return_reason' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $po = PurchaseOrder::findOrFail($request->purchase_order_id);
            $product = ProductDetail::findOrFail($request->product_id);

            // Get unit price from PO Item if possible, otherwise use current cost
            // Ideally we find the exact PO item to get the price we bought it at
            $poItem = $po->items()->where('product_id', $request->product_id)->first();
            $unitPrice = $poItem ? $poItem->unit_price : ($request->input('unit_price', $product->cost_price));

            $totalAmount = $unitPrice * $request->return_quantity;

            $return = ReturnSupplier::create([
                'purchase_order_id' => $request->purchase_order_id,
                'product_id' => $request->product_id,
                'return_quantity' => $request->return_quantity,
                'unit_price' => $unitPrice,
                'total_amount' => $totalAmount,
                'return_reason' => $request->return_reason,
                'notes' => $request->notes,
            ]);

            // Stock Logic: Reduce available stock because we returned it to supplier
            $stock = ProductStock::where('product_id', $request->product_id)->first();

            if ($stock) {
                // Decrement available stock
                $stock->available_stock -= $request->return_quantity;

                // Update totals
                if (method_exists($stock, 'updateTotals')) {
                    $stock->updateTotals();
                } else {
                    $stock->total_stock = $stock->available_stock + $stock->damage_stock;
                    $stock->save();
                }
            }

            // Financial Logic: Deduct from Purchase Order Due Amount
            // "if i return i need to pay after return product dedect no ?" -> Yes, reduce debt.
            $po->due_amount -= $totalAmount;
            // Also reduce total amount since the order size effectively shrank? 
            // Usually valid to keep total_amount as historical "what we ordered" and use "refund_amount" or similar.
            // But for simple "pay what you owe", reducing due_amount is critical. 
            // For accounting correctness, often one creates a "Debit Note" or "Credit Note". 
            // Here we simply adjust the balance.

            // Prevent negative due amount if we already paid (means they owe US money, negative debt)
            // But we allow it to track that Supplier owes us.

            $po->save();

            DB::commit();
            return $this->success($return, 'Supplier return recorded successfully. Stock and PO Balance updated.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Supplier Return Error: ' . $e->getMessage());
            return $this->error('Failed to record return: ' . $e->getMessage(), 500);
        }
    }
}
