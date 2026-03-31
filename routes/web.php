<?php

use App\Http\Controllers\Admin\CashController;
use App\Http\Controllers\Admin\PrintController;
use App\Http\Controllers\ProductApiController;
use Illuminate\Http\Request;
use App\Livewire\CustomLogin;
use App\Livewire\Admin\Products;
use App\Livewire\Staff\Billing;
use App\Livewire\Admin\MadeByList;
use App\Livewire\Admin\ProductTypes;
use App\Livewire\Admin\BillingPage;
use App\Livewire\Admin\ManageAdmin;
use App\Livewire\Admin\ManageStaff;
use App\Livewire\Staff\DuePayments;
use App\Livewire\Admin\SupplierList;
use App\Livewire\Admin\ViewPayments;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use App\Livewire\Admin\AdminDashboard;
use App\Livewire\Admin\ManageCustomer;
use App\Livewire\Admin\ProductBrandlist;
use App\Livewire\Staff\StaffDashboard;
use App\Livewire\Admin\StaffDueDetails;
use App\Livewire\Admin\PaymentApprovals;
use App\Livewire\Admin\StaffSaleDetails;
use App\Livewire\Admin\StaffStockDetails;
use App\Livewire\Admin\ProductCategorylist;
use App\Livewire\Admin\ProductStockDetails;
use App\Livewire\Admin\ProductDialColorlist;
use App\Livewire\Admin\ProductGlassTypeList;
use App\Livewire\Admin\ProductStrapMaterial;
use App\Livewire\Staff\StaffStockOverview;
use App\Http\Controllers\ReceiptController;
use App\Livewire\Admin\CustomerSaleDetails;
use App\Livewire\Admin\ProductStrapColorlist;
use App\Livewire\Staff\CustomerSaleManagement;
use App\Livewire\Admin\StoreBilling;
use App\Livewire\Admin\DuePayments as AdminDuePayments;
use App\Livewire\Admin\StaffStockDetails as StaffStockDetailsExport;
use App\Livewire\Staff\StoreBilling as StaffStoreBilling;
use App\Http\Controllers\ProductsExportController;
use App\Http\Controllers\StaffSaleExportController;
use App\Livewire\Admin\GRN;
use App\Livewire\Admin\StaffAttendance;
use App\Livewire\Admin\StaffSallary;
use App\Livewire\Admin\LoanManage;
use App\Livewire\Admin\Quotation;
use App\Livewire\Admin\SalesApproval;
use App\Livewire\Admin\SupplierManage;
use App\Livewire\Admin\Reports;
use App\Livewire\Admin\Analytics;
use App\Livewire\Admin\QuotationSystem;
use App\Livewire\Admin\QuotationList;
use App\Livewire\Admin\SalesSystem;
use App\Livewire\Admin\SalesList;
use App\Livewire\Admin\PosSales;
use App\Livewire\Admin\PurchaseOrderList;
use App\Models\Setting as ModelsSetting;
use App\Livewire\Admin\Settings;
use App\Livewire\Admin\Expenses;
use App\Livewire\Admin\Income;
use App\Livewire\Admin\ReturnList;
use App\Livewire\Admin\ReturnProduct;
use App\Livewire\Admin\AddCustomerReceipt;
use App\Livewire\Admin\AddSupplierReceipt;
use App\Livewire\Admin\ChequeList;
use App\Livewire\Admin\DaySummary;
use App\Livewire\Admin\DaySummaryDetails;
use App\Livewire\Admin\Deposits;
use App\Livewire\Admin\ListCustomerReceipt;
use App\Livewire\Admin\ListSupplierReceipt;
use App\Livewire\Admin\ReturnCheque;

use App\Livewire\Admin\ReturnSupplier;
use App\Livewire\Admin\ListSupplierReturn;
use App\Livewire\Admin\ProfitLoss;
use App\Livewire\Admin\ProfitShare;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Public routes
Route::get('/', CustomLogin::class)->name('welcome')->middleware('guest');

// Custom logout route
Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
})->name('logout');

// Routes that require authentication
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {

    // Profile route - accessible to all authenticated users
    Route::get('/user/profile', function () {
        return view('profile.show');
    })->name('profile.show');

    Route::get('/user/profile-photo/{user}', function (\App\Models\User $user) {
        if (!$user->profile_photo_path || !Storage::disk('public')->exists($user->profile_photo_path)) {
            abort(404);
        }

        $fileContent = Storage::disk('public')->get($user->profile_photo_path);
        $mimeType = (new \finfo(FILEINFO_MIME_TYPE))->buffer($fileContent) ?: 'application/octet-stream';

        return response($fileContent, 200, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=86400',
        ]);
    })->name('profile.photo.show');

    // Generic dashboard route: redirect authenticated users to their role-specific dashboard
    Route::get('/dashboard', function () {
        $user = Auth::user();

        // If for some reason there's no authenticated user, send them to the welcome page
        if (!$user) {
            return redirect()->route('welcome');
        }

        // If the User model uses a role helper (eg. spatie/laravel-permission)
        // prefer calling hasRole() on the model. Otherwise fall back to
        // checking a string `role` attribute.
        if (method_exists($user, 'hasRole')) {
            if ($user->hasRole('admin')) {
                return redirect()->route('admin.dashboard');
            }
            if ($user->hasRole('staff')) {
                return redirect()->route('staff.dashboard');
            }
        } else {
            $role = strtolower($user->role ?? '');
            if ($role === 'admin') {
                return redirect()->route('admin.dashboard');
            }
            if ($role === 'staff') {
                return redirect()->route('staff.dashboard');
            }
        }

        // Fallback: redirect to a safe page (profile) instead of looping back to dashboard
        return redirect()->route('profile.show');
    })->name('dashboard');

    // Settings route - accessible to all authenticated users

    // API route for products (client-side caching)
    Route::get('/api/products/all', [ProductApiController::class, 'getAllProducts'])->name('api.products.all');

    // !! Admin routes
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', AdminDashboard::class)->name('dashboard');
        Route::get('/Product-list', Products::class)->name('Productes');
        Route::get('/products/export', [App\Http\Controllers\ProductExportController::class, 'export'])->name('products.export');
        Route::get('/add-Product-brand', ProductBrandlist::class)->name('Product-brand');
        Route::get('/Product-category', ProductCategorylist::class)->name('Product-category');
        Route::get('/billing-page', BillingPage::class)->name('billing-page');
        Route::get('/manage-admin', ManageAdmin::class)->name('manage-admin');
        Route::get('/manage-staff', ManageStaff::class)->name('manage-staff');
        Route::get('/manage-customer', ManageCustomer::class)->name('manage-customer');
        Route::get('/customer/{customer}/transactions', \App\Livewire\Admin\CustomerTransactionHistory::class)->name('customer-transactions');
        Route::get('/Product-stock-details', ProductStockDetails::class)->name('Product-stock-details');
        Route::get('/staff-stock-details', StaffStockDetails::class)->name('staff-stock-details');


        Route::get('/staff-due-details', StaffDueDetails::class)->name('staff-due-details');
        Route::get('/customer-sale-details', CustomerSaleDetails::class)->name('customer-sale-details');

        Route::get('/view-payments', ViewPayments::class)->name('view-payments');
        Route::get('/admin/staff/{staffId}/reentry', \App\Livewire\Admin\StockReentry::class)->name('staff.reentry');
        Route::get('/store-billing', StoreBilling::class)->name('store-billing');
        Route::get('/print/sale/{id}', [PrintController::class, 'printSale'])->name('print.sale');
        Route::get('/due-payments', AdminDuePayments::class)->name('due-payments');
        Route::get('/staff-attendance', StaffAttendance::class)->name('staff-attendance');
        Route::get('/staff-salary', StaffSallary::class)->name('staff-salary');
        Route::get('/loan-management', LoanManage::class)->name('loan-management');
        Route::get('/sales-system', SalesSystem::class)->name('sales-system');
        Route::get('/pos-sales', PosSales::class)->name('pos-sales');

        Route::get('/supplier-management', SupplierManage::class)->name('supplier-management');
        Route::get('/quotation', Quotation::class)->name('quotation');
        Route::get('/goods-receive-note', GRN::class)->name('grn');
        Route::get('/expenses', Expenses::class)->name('expenses');
        Route::get('/day-summary', Income::class)->name('income');

        Route::get('/systemsetting', Settings::class)->name('systemsetting');
        Route::get('/reports', Reports::class)->name('reports');
        Route::get('/analytics', Analytics::class)->name('analytics');
        Route::get('/profit-loss', \App\Livewire\Admin\ProfitLoss::class)->name('profit-loss');
        Route::get('/profit-share', ProfitShare::class)->name('profit-share');
        Route::get('/profit-share/investor/{investor}', \App\Livewire\Admin\InvestorLedger::class)->name('investor-ledger');
        Route::get('/quotation-system/{quotation?}', QuotationSystem::class)->name('quotation-system');
        Route::get('/quotation-list', QuotationList::class)->name('quotation-list');
        Route::get('/sales-list', SalesList::class)->name('sales-list');
        Route::get('/settings', Settings::class)->name('settings');
        Route::get('/return-product', ReturnProduct::class)->name('return-product');
        Route::get('/purchase-order-list', PurchaseOrderList::class)->name('purchase-order-list');
        Route::get('/return-list', ReturnList::class)->name('return-list');
        Route::get('/add-customer-receipt', AddCustomerReceipt::class)->name('add-customer-receipt');
        Route::get('/cheque-list', ChequeList::class)->name('cheque-list');
        Route::get('/return-cheque', ReturnCheque::class)->name('return-cheque');
        Route::get('/list-customer-receipt', ListCustomerReceipt::class)->name('list-customer-receipt');
        Route::get('/add-supplier-receipt', AddSupplierReceipt::class)->name('add-supplier-receipt');
        Route::get('/list-supplier-receipt', ListSupplierReceipt::class)->name('list-supplier-receipt');
        Route::get('/return-supplier', ReturnSupplier::class)->name('return-supplier');
        Route::get('/list-supplier-return', ListSupplierReturn::class)->name('list-supplier-return');
        Route::get('/cash-deposit', DaySummary::class)->name('day-summary');
        Route::get('/deposits', Deposits::class)->name('deposits');
        Route::get('/register-report/{sessionId}', DaySummaryDetails::class)->name('day-summary-details');
        // Route to reopen today's closed POS session (AJAX)
        Route::post('/reopen-pos-session', [StoreBilling::class, 'reopenPOSSession'])->name('reopen-pos-session');
    });
    Route::post('/admin/update-cash', [CashController::class, 'updateCashInHand'])
        ->name('admin.updateCashInHand')
        ->middleware(['auth', 'role:admin']);

    Route::get('/admin/check-pos-session', [CashController::class, 'checkPOSSession'])
        ->name('admin.check-pos-session')
        ->middleware(['auth', 'role:admin']);

    //!! Staff routes - All admin routes available to staff (permissions control access)
    Route::middleware('role:staff')->prefix('staff')->name('staff.')->group(function () {
        // Dashboard
        Route::get('/dashboard', StaffDashboard::class)->name('dashboard');

        // Products
        Route::get('/Product-list', Products::class)->name('Productes');
        Route::get('/add-Product-brand', ProductBrandlist::class)->name('Product-brand');
        Route::get('/Product-category', ProductCategorylist::class)->name('Product-category');
        Route::get('/Product-stock-details', ProductStockDetails::class)->name('Product-stock-details');

        // Sales
        Route::get('/billing', Billing::class)->name('billing');
        Route::get('/billing-page', BillingPage::class)->name('billing-page');
        Route::get('/sales-system', SalesSystem::class)->name('sales-system');
        Route::get('/pos-sales', PosSales::class)->name('pos-sales');
        Route::get('/sales-list', SalesList::class)->name('sales-list');
        Route::get('/store-billing', StoreBilling::class)->name('store-billing');

        // Customers
        Route::get('/manage-customer', ManageCustomer::class)->name('manage-customer');
        Route::get('/customer/{customer}/transactions', \App\Livewire\Admin\CustomerTransactionHistory::class)->name('customer-transactions');
        Route::get('/customer-sale-details', CustomerSaleDetails::class)->name('customer-sale-details');
        Route::get('/customer-sale-management', CustomerSaleManagement::class)->name('customer-sale-management');

        // Stock/Inventory
        Route::get('/staff-stock-overview', StaffStockOverview::class)->name('staff-stock-overview');
        Route::get('/staff-stock-details', StaffStockDetails::class)->name('staff-stock-details');

        // Purchases
        Route::get('/goods-receive-note', GRN::class)->name('grn');
        Route::get('/purchase-order-list', PurchaseOrderList::class)->name('purchase-order-list');
        Route::get('/supplier-management', SupplierManage::class)->name('supplier-management');

        // Quotations
        Route::get('/quotation', Quotation::class)->name('quotation');
        Route::get('/quotation-system/{quotation?}', QuotationSystem::class)->name('quotation-system');
        Route::get('/quotation-list', QuotationList::class)->name('quotation-list');

        // Returns
        Route::get('/return-product', ReturnProduct::class)->name('return-product');
        Route::get('/return-list', ReturnList::class)->name('return-list');
        Route::get('/return-supplier', ReturnSupplier::class)->name('return-supplier');
        Route::get('/list-supplier-return', ListSupplierReturn::class)->name('list-supplier-return');

        // Payments
        Route::get('/due-payments', DuePayments::class)->name('due-payments');
        Route::get('/view-payments', ViewPayments::class)->name('view-payments');
        Route::get('/add-customer-receipt', AddCustomerReceipt::class)->name('add-customer-receipt');
        Route::get('/list-customer-receipt', ListCustomerReceipt::class)->name('list-customer-receipt');
        Route::get('/add-supplier-receipt', AddSupplierReceipt::class)->name('add-supplier-receipt');
        Route::get('/list-supplier-receipt', ListSupplierReceipt::class)->name('list-supplier-receipt');

        // Cheques/Banks
        Route::get('/cheque-list', ChequeList::class)->name('cheque-list');
        Route::get('/return-cheque', ReturnCheque::class)->name('return-cheque');

        // Finance
        Route::get('/expenses', Expenses::class)->name('expenses');
        Route::get('/income', Income::class)->name('income');
        Route::get('/loan-management', LoanManage::class)->name('loan-management');

        // HR/Staff Management
        Route::get('/manage-staff', ManageStaff::class)->name('manage-staff');
        Route::get('/staff-attendance', StaffAttendance::class)->name('staff-attendance');
        Route::get('/staff-salary', StaffSallary::class)->name('staff-salary');
        Route::get('/staff-due-details', StaffDueDetails::class)->name('staff-due-details');

        // Reports & Analytics
        Route::get('/reports', Reports::class)->name('reports');
        Route::get('/analytics', Analytics::class)->name('analytics');

        // Settings
        Route::get('/settings', Settings::class)->name('settings');
    });

    // !! Export routes (accessible to authenticated users)
    Route::get('/Productes/export', [ProductsExportController::class, 'export'])->name('Productes.export');
    Route::get('/staff-sales/export', [StaffSaleExportController::class, 'export'])->name('staff-sales.export');

    // Receipt download (accessible to authenticated users)
    Route::get('/receipts/{id}/download', [ReceiptController::class, 'download'])->name('receipts.download');

    // Export staff stock details
    Route::get('/export/staff-stock', function () {
        return app(StaffStockDetails::class)->exportToCSV();
    })->name('export.staff-stock');

    // Test route for product history
    Route::get('/test/product-history/{id}', function ($id) {
        $product = \App\Models\ProductDetail::with(['price', 'stock'])->findOrFail($id);

        // Debug: Check raw sale items count
        $rawCount = \App\Models\SaleItem::where('product_id', $id)->count();

        // Debug: Get raw sale items
        $rawSaleItems = \App\Models\SaleItem::where('product_id', $id)->get();

        // Load sales history with join
        $salesItems = \App\Models\SaleItem::with(['sale.customer', 'sale.user'])
            ->where('sale_items.product_id', $id)
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->select(
                'sale_items.*',
                'sales.invoice_number',
                'sales.sale_type',
                'sales.customer_type',
                'sales.payment_type',
                'sales.payment_status',
                'sales.status as sale_status',
                'sales.created_at as sale_date'
            )
            ->orderBy('sales.created_at', 'desc')
            ->get();

        $salesHistory = $salesItems->map(function ($sale) {
            return [
                'id' => $sale->id,
                'invoice_number' => $sale->invoice_number,
                'sale_type' => $sale->sale_type ?? 'regular',
                'customer_type' => $sale->customer_type ?? 'walk-in',
                'quantity' => $sale->quantity,
                'unit_price' => $sale->unit_price,
                'discount_per_unit' => $sale->discount_per_unit ?? 0,
                'total_discount' => $sale->total_discount ?? 0,
                'total' => $sale->total,
                'payment_type' => $sale->payment_type ?? 'cash',
                'payment_status' => $sale->payment_status ?? 'unpaid',
                'sale_status' => $sale->sale_status ?? 'completed',
                'sale_date' => $sale->sale_date,
                'customer_name' => $sale->sale && $sale->sale->customer ? $sale->sale->customer->name : 'Walk-in Customer',
                'customer_phone' => $sale->sale && $sale->sale->customer ? $sale->sale->customer->phone : 'N/A',
                'user_name' => $sale->sale && $sale->sale->user ? $sale->sale->user->name : 'N/A'
            ];
        })->toArray();

        // Load purchases history
        $purchaseItems = \App\Models\PurchaseOrderItem::with(['order.supplier'])
            ->where('purchase_order_items.product_id', $id)
            ->join('purchase_orders', 'purchase_order_items.order_id', '=', 'purchase_orders.id')
            ->select(
                'purchase_order_items.*',
                'purchase_orders.order_code',
                'purchase_orders.order_date',
                'purchase_orders.received_date',
                'purchase_orders.status as order_status'
            )
            ->orderBy('purchase_orders.order_date', 'desc')
            ->get();

        $purchasesHistory = $purchaseItems->map(function ($purchase) {
            $subtotal = $purchase->received_quantity * $purchase->unit_price;
            $discountAmount = 0;

            // Calculate discount as percentage
            if (isset($purchase->discount) && $purchase->discount > 0) {
                $discountAmount = ($subtotal * $purchase->discount) / 100;
            }

            $total = $subtotal - $discountAmount;

            return [
                'id' => $purchase->id,
                'order_code' => $purchase->order_code,
                'order_date' => $purchase->order_date,
                'received_date' => $purchase->received_date ?? 'Pending',
                'quantity' => $purchase->quantity,
                'received_quantity' => $purchase->received_quantity,
                'unit_price' => $purchase->unit_price,
                'discount' => $purchase->discount ?? 0,
                'total' => $total,
                'order_status' => $purchase->order_status ?? 'pending',
                'supplier_name' => $purchase->order && $purchase->order->supplier ? $purchase->order->supplier->name : 'N/A',
                'supplier_phone' => $purchase->order && $purchase->order->supplier ? $purchase->order->supplier->phone : 'N/A'
            ];
        })->toArray();

        // Load returns history
        $returns = \App\Models\ReturnsProduct::with(['sale.customer', 'product'])
            ->where('returns_products.product_id', $id)
            ->join('sales', 'returns_products.sale_id', '=', 'sales.id')
            ->select(
                'returns_products.*',
                'sales.invoice_number'
            )
            ->orderBy('returns_products.created_at', 'desc')
            ->get();

        $returnsHistory = $returns->map(function ($return) {
            return [
                'id' => $return->id,
                'invoice_number' => $return->invoice_number,
                'return_quantity' => $return->return_quantity,
                'selling_price' => $return->selling_price ?? 0,
                'total_amount' => $return->total_amount ?? 0,
                'notes' => $return->notes ?? 'No notes provided',
                'return_date' => $return->created_at,
                'customer_name' => $return->sale && $return->sale->customer ? $return->sale->customer->name : 'Walk-in Customer',
                'customer_phone' => $return->sale && $return->sale->customer ? $return->sale->customer->phone : 'N/A'
            ];
        })->toArray();

        // Load quotations history
        $quotations = \App\Models\Quotation::with(['creator', 'customer'])
            ->where('status', '!=', 'draft')
            ->orderBy('quotation_date', 'desc')
            ->get();

        $quotationsHistory = [];

        foreach ($quotations as $quotation) {
            $items = is_array($quotation->items) ? $quotation->items : json_decode($quotation->items, true);

            if (!empty($items)) {
                foreach ($items as $item) {
                    if (isset($item['product_id']) && $item['product_id'] == $id) {
                        $quotationsHistory[] = [
                            'id' => $quotation->id,
                            'quotation_number' => $quotation->quotation_number,
                            'reference_number' => $quotation->reference_number ?? 'N/A',
                            'customer_name' => $quotation->customer_name ?? ($quotation->customer->name ?? 'N/A'),
                            'customer_phone' => $quotation->customer_phone ?? ($quotation->customer->phone ?? 'N/A'),
                            'customer_email' => $quotation->customer_email ?? 'N/A',
                            'quotation_date' => $quotation->quotation_date,
                            'valid_until' => $quotation->valid_until,
                            'status' => $quotation->status,
                            'quantity' => $item['quantity'] ?? 0,
                            'unit_price' => $item['unit_price'] ?? 0,
                            'discount' => $item['discount'] ?? 0,
                            'total' => $item['total'] ?? 0,
                            'product_name' => $item['product_name'] ?? 'N/A',
                            'product_code' => $item['product_code'] ?? 'N/A',
                            'created_by_name' => $quotation->creator->name ?? 'N/A'
                        ];
                    }
                }
            }
        }

        return view('test.product-history', [
            'product' => $product,
            'salesHistory' => $salesHistory,
            'purchasesHistory' => $purchasesHistory,
            'returnsHistory' => $returnsHistory,
            'quotationsHistory' => $quotationsHistory,
            'rawCount' => $rawCount,
            'rawSaleItems' => $rawSaleItems,
            'salesItems' => $salesItems,
            'historyTab' => 'sales'
        ]);
    })->name('test.product-history');
});
