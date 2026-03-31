<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Sale;
use App\Models\Payment;
use App\Models\Cheque;
use App\Models\ProductDetail;
use App\Models\ProductStock;
use App\Models\StaffProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StaffManagementController extends ApiController
{
    /**
     * Get staff sales summary (StaffSalesView)
     */
    public function getSalesSummary(Request $request)
    {
        $staffMembers = User::where('role', 'staff')->orderBy('name')->get();

        $summary = $staffMembers->map(function ($staff) {
            $sales = Sale::where('user_id', $staff->id)
                ->where('sale_type', 'staff')
                ->with('payments')
                ->get();

            $totalSales = $sales->count();
            $totalAmount = $sales->sum('total_amount');

            // Paid Amount: Sum of amounts from payments with status 'approved'
            $paidAmount = 0;
            $pendingApprovalAmount = 0;
            foreach ($sales as $sale) {
                foreach ($sale->payments as $payment) {
                    if ($payment->status === 'approved') {
                        $paidAmount += $payment->amount;
                    } elseif ($payment->status === 'pending') {
                        $pendingApprovalAmount += $payment->amount;
                    }
                }
            }

            $dueAmount = $sales->sum('due_amount');

            return [
                'staff_id' => $staff->id,
                'staff_name' => $staff->name,
                'staff_email' => $staff->email,
                'staff_contact' => $staff->contact,
                'total_sales' => $totalSales,
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'pending_approval_amount' => $pendingApprovalAmount,
                'due_amount' => $dueAmount,
            ];
        });

        return $this->success([
            'results' => $summary,
            'count' => $summary->count(),
        ]);
    }

    /**
     * Get staff sale details (StaffSaleDetails)
     */
    public function getStaffSaleDetails(Request $request, $staffId)
    {
        $staff = User::find($staffId);
        if (!$staff || $staff->role !== 'staff') {
            return $this->error('Staff member not found', 404);
        }

        // Get summary statistics
        $summaryStats = null;
        if (class_exists(StaffProduct::class)) {
            try {
                $summaryStats = StaffProduct::where('staff_id', $staffId)
                    ->select([
                        DB::raw('SUM(quantity) as total_quantity'),
                        DB::raw('SUM(sold_quantity) as sold_quantity'),
                        DB::raw('SUM(quantity) - SUM(sold_quantity) as available_quantity'),
                        DB::raw('SUM(total_value) as total_value'),
                        DB::raw('SUM(sold_value) as sold_value'),
                        DB::raw('SUM(total_value) - SUM(sold_value) as available_value')
                    ])
                    ->first();
            } catch (\Exception $e) {
                $summaryStats = null;
            }
        }

        // Get product details
        $productDetails = [];
        if (class_exists(StaffProduct::class)) {
            try {
                $productDetails = StaffProduct::join('product_details', 'staff_products.product_id', '=', 'product_details.id')
                    ->leftJoin('brand_lists', 'brand_lists.id', '=', 'product_details.brand_id')
                    ->where('staff_products.staff_id', $staffId)
                    ->select(
                        'staff_products.*',
                        'product_details.name as product_name',
                        'brand_lists.brand_name as product_brand',
                        'product_details.model as product_model',
                        'product_details.code as product_code',
                        'product_details.image as product_image'
                    )
                    ->get();
            } catch (\Exception $e) {
                $productDetails = [];
            }
        }

        // Get recent sales
        $sales = Sale::with(['customer', 'items', 'payments'])
            ->where('user_id', $staffId)
            ->where('sale_type', 'staff')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        return $this->success([
            'staff' => [
                'id' => $staff->id,
                'name' => $staff->name,
                'email' => $staff->email,
                'contact' => $staff->contact,
            ],
            'summary' => $summaryStats,
            'products' => $productDetails,
            'sales' => $sales,
        ]);
    }

    /**
     * Get staff stock details (StaffStockDetails)
     */
    public function getStockDetails(Request $request)
    {
        $staffStocks = [];

        if (class_exists(StaffProduct::class)) {
            try {
                $staffStocks = StaffProduct::join('users', 'staff_products.staff_id', '=', 'users.id')
                    ->select(
                        'users.id as user_id',
                        'users.name',
                        'users.email',
                        'users.contact',
                        DB::raw('SUM(staff_products.quantity) as total_quantity'),
                        DB::raw('SUM(staff_products.sold_quantity) as sold_quantity'),
                        DB::raw('SUM(staff_products.quantity) - SUM(staff_products.sold_quantity) as available_quantity')
                    )
                    ->groupBy('users.id', 'users.name', 'users.email', 'users.contact')
                    ->get();
            } catch (\Exception $e) {
                $staffStocks = [];
            }
        }

        return $this->success([
            'results' => $staffStocks,
            'count' => count($staffStocks),
        ]);
    }

    /**
     * Get stock details for a specific staff member
     */
    public function getStaffStockProducts(Request $request, $staffId)
    {
        $stockDetails = [];

        if (class_exists(StaffProduct::class)) {
            try {
                $stockDetails = StaffProduct::join('users', 'staff_products.staff_id', '=', 'users.id')
                    ->join('product_details', 'staff_products.product_id', '=', 'product_details.id')
                    ->leftJoin('brand_lists', 'brand_lists.id', '=', 'product_details.brand_id')
                    ->where('staff_products.staff_id', $staffId)
                    ->select(
                        'staff_products.*',
                        'users.name as staff_name',
                        'users.email as staff_email',
                        'product_details.name as product_name',
                        'brand_lists.brand_name as product_brand',
                        'product_details.model as product_model',
                        'product_details.code as product_code',
                        'product_details.image as product_image'
                    )
                    ->get();
            } catch (\Exception $e) {
                $stockDetails = [];
            }
        }

        return $this->success([
            'results' => $stockDetails,
            'count' => count($stockDetails),
        ]);
    }

    /**
     * Get payments for approval (StaffPaymentApproval)
     */
    public function getPaymentsForApproval(Request $request)
    {
        $status = $request->query('status', 'pending');
        $paymentMethod = $request->query('payment_method', 'all');
        $search = $request->query('search', '');

        $query = Payment::with(['sale.customer', 'sale.user'])
            ->whereNotNull('sale_id');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($paymentMethod !== 'all') {
            $query->where('payment_method', $paymentMethod);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', '%' . $search . '%')
                    ->orWhere('payment_reference', 'like', '%' . $search . '%')
                    ->orWhereHas('sale', function ($q) use ($search) {
                        $q->where('invoice_number', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('sale.customer', function ($q) use ($search) {
                        $q->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        $payments = $query->orderByDesc('created_at')->paginate(20);

        // Get counts
        $pendingCount = Payment::where('status', 'pending')->whereNotNull('sale_id')->count();
        $approvedCount = Payment::where('status', 'approved')->whereNotNull('sale_id')->count();
        $rejectedCount = Payment::where('status', 'rejected')->whereNotNull('sale_id')->count();

        return $this->success([
            'results' => $payments->items(),
            'count' => $payments->total(),
            'current_page' => $payments->currentPage(),
            'last_page' => $payments->lastPage(),
            'counts' => [
                'pending' => $pendingCount,
                'approved' => $approvedCount,
                'rejected' => $rejectedCount,
            ],
        ]);
    }

    /**
     * Approve a payment
     */
    public function approvePayment(Request $request, $paymentId)
    {
        try {
            DB::beginTransaction();

            $payment = Payment::findOrFail($paymentId);
            $payment->update([
                'status' => 'approved',
                'is_completed' => true,
            ]);

            // If payment method is cheque, approve cheques too
            if ($payment->payment_method === 'cheque') {
                Cheque::where('payment_id', $paymentId)->update([
                    'status' => 'complete',
                ]);
            }

            // Update sale payment status
            if ($payment->sale) {
                $totalPaid = Payment::where('sale_id', $payment->sale_id)
                    ->where('status', 'approved')
                    ->sum('amount');

                $sale = $payment->sale;
                $newDue = max(0, $sale->total_amount - $totalPaid);

                $sale->update([
                    'due_amount' => $newDue,
                    'payment_status' => $newDue <= 0.01 ? 'paid' : 'partial'
                ]);
            }

            DB::commit();

            return $this->success(null, 'Payment approved successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment approval error: ' . $e->getMessage());
            return $this->error('Failed to approve payment: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Reject a payment
     */
    public function rejectPayment(Request $request, $paymentId)
    {
        try {
            DB::beginTransaction();

            $payment = Payment::findOrFail($paymentId);
            $payment->update([
                'status' => 'rejected',
                'is_completed' => false,
            ]);

            // If payment method is cheque, reject cheques too
            if ($payment->payment_method === 'cheque') {
                Cheque::where('payment_id', $paymentId)->update([
                    'status' => 'cancelled',
                ]);
            }

            DB::commit();

            return $this->success(null, 'Payment rejected successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment rejection error: ' . $e->getMessage());
            return $this->error('Failed to reject payment: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get products for stock allocation
     */
    public function getProductsForAllocation(Request $request)
    {
        $search = $request->query('search', '');

        if (strlen($search) < 2) {
            return $this->success(['results' => [], 'count' => 0]);
        }

        $products = ProductDetail::where(function ($q) use ($search) {
            $q->where('name', 'like', '%' . $search . '%')
                ->orWhere('code', 'like', '%' . $search . '%')
                ->orWhere('model', 'like', '%' . $search . '%');
        })
            ->with(['brand', 'category'])
            ->limit(20)
            ->get()
            ->map(function ($product) {
                // Get available stock
                $stock = 0;
                if (class_exists(ProductStock::class)) {
                    try {
                        $stock = ProductStock::where('product_id', $product->id)->sum('available_stock');
                    } catch (\Exception $e) {
                        $stock = 0;
                    }
                }

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'code' => $product->code,
                    'model' => $product->model,
                    'selling_price' => $product->selling_price,
                    'cost_price' => $product->cost_price,
                    'brand' => $product->brand?->brand_name,
                    'category' => $product->category?->category_name,
                    'image' => $product->image,
                    'available_stock' => $stock,
                ];
            });

        return $this->success([
            'results' => $products,
            'count' => $products->count(),
        ]);
    }

    /**
     * Allocate products to staff
     */
    public function allocateProducts(Request $request)
    {
        $request->validate([
            'staff_id' => 'required|exists:users,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:product_details,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $staff = User::find($request->staff_id);
        if (!$staff || $staff->role !== 'staff') {
            return $this->error('Invalid staff member', 400);
        }

        try {
            DB::beginTransaction();

            foreach ($request->items as $item) {
                // Check stock availability
                $availableStock = 0;
                if (class_exists(ProductStock::class)) {
                    $availableStock = ProductStock::where('product_id', $item['product_id'])->sum('available_stock');
                }

                if ($availableStock < $item['quantity']) {
                    throw new \Exception("Insufficient stock for product ID: {$item['product_id']}");
                }

                $product = ProductDetail::find($item['product_id']);
                $total = ($item['quantity'] * $item['unit_price']) - ($item['discount'] ?? 0);

                // Create or update staff product allocation
                if (class_exists(StaffProduct::class)) {
                    $existingAllocation = StaffProduct::where('staff_id', $request->staff_id)
                        ->where('product_id', $item['product_id'])
                        ->first();

                    if ($existingAllocation) {
                        $existingAllocation->increment('quantity', $item['quantity']);
                        $existingAllocation->increment('total_value', $total);
                        if (isset($item['discount']) && $item['discount'] > 0) {
                            $existingAllocation->increment('total_discount', $item['discount']);
                        }
                    } else {
                        StaffProduct::create([
                            'staff_id' => $request->staff_id,
                            'product_id' => $item['product_id'],
                            'quantity' => $item['quantity'],
                            'sold_quantity' => 0,
                            'unit_price' => $item['unit_price'],
                            'total_value' => $total,
                            'sold_value' => 0,
                            'total_discount' => $item['discount'] ?? 0,
                            'discount_per_unit' => ($item['discount'] ?? 0) / $item['quantity'],
                        ]);
                    }
                }

                // Reduce product stock (FIFO)
                $this->reduceProductStock($item['product_id'], $item['quantity']);
            }

            DB::commit();

            return $this->success(null, 'Products allocated successfully', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Stock allocation error: ' . $e->getMessage());
            return $this->error('Failed to allocate products: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Reduce product stock using FIFO method
     */
    private function reduceProductStock($productId, $quantity)
    {
        if (!class_exists(ProductStock::class)) {
            return;
        }

        $batches = ProductStock::where('product_id', $productId)
            ->where('available_stock', '>', 0)
            ->orderBy('created_at')
            ->get();

        $remaining = $quantity;

        foreach ($batches as $batch) {
            if ($remaining <= 0)
                break;

            $reduceQty = min($batch->available_stock, $remaining);
            $batch->decrement('available_stock', $reduceQty);
            $remaining -= $reduceQty;
        }
    }

    /**
     * Get staff due details
     */
    public function getDueDetails(Request $request)
    {
        $staffDues = DB::table('sales')
            ->join('users', 'sales.user_id', '=', 'users.id')
            ->where('users.role', 'staff')
            ->select(
                'users.id as user_id',
                'users.name',
                'users.email',
                'users.contact',
                DB::raw('SUM(sales.total_amount) as total_amount'),
                DB::raw('SUM(sales.due_amount) as due_amount'),
                DB::raw('SUM(sales.total_amount) - SUM(sales.due_amount) as collected_amount')
            )
            ->groupBy('users.id', 'users.name', 'users.email', 'users.contact')
            ->orderBy('total_amount', 'desc')
            ->get();

        return $this->success([
            'results' => $staffDues,
            'count' => $staffDues->count(),
        ]);
    }

    /**
     * Get expenses grouped by staff
     */
    public function getStaffExpenses(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $query = DB::table('expenses')
            ->join('users', 'expenses.user_id', '=', 'users.id')
            ->where('users.role', 'staff')
            ->select(
                'users.id as user_id',
                'users.name',
                'users.email',
                'users.contact',
                DB::raw('COUNT(expenses.id) as expense_count'),
                DB::raw('SUM(expenses.amount) as total_amount')
            );

        if ($startDate) {
            $query->whereDate('expenses.created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('expenses.created_at', '<=', $endDate);
        }

        $staffExpenses = $query->groupBy('users.id', 'users.name', 'users.email', 'users.contact')
            ->orderBy('total_amount', 'desc')
            ->get();

        return $this->success([
            'results' => $staffExpenses,
            'count' => $staffExpenses->count(),
        ]);
    }

    /**
     * Get expenses for a specific staff member
     */
    public function getStaffExpenseDetails(Request $request, $staffId)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $query = DB::table('expenses')
            ->leftJoin('expense_categories', 'expenses.category_id', '=', 'expense_categories.id')
            ->where('expenses.user_id', $staffId)
            ->select(
                'expenses.*',
                'expense_categories.name as category_name'
            );

        if ($startDate) {
            $query->whereDate('expenses.created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('expenses.created_at', '<=', $endDate);
        }

        $expenses = $query->orderByDesc('expenses.created_at')->get();

        // Get staff info
        $staff = User::find($staffId);

        return $this->success([
            'staff' => $staff ? [
                'id' => $staff->id,
                'name' => $staff->name,
                'email' => $staff->email,
            ] : null,
            'results' => $expenses,
            'count' => $expenses->count(),
            'total' => $expenses->sum('amount'),
        ]);
    }
}
