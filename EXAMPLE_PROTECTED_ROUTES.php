<?php

/**
 * Example: Staff Routes with Permission Middleware
 * 
 * This file shows how to protect staff routes with the permission middleware.
 * Replace the existing staff routes section in routes/web.php with this code.
 */

// BEFORE (Without Permission Protection):
/*
Route::middleware('role:staff')->prefix('staff')->name('staff.')->group(function () {
    Route::get('/dashboard', StaffDashboard::class)->name('dashboard');
    Route::get('/billing', Billing::class)->name('billing');
    Route::get('/customer-sale-management', CustomerSaleManagement::class)->name('customer-sale-management');
    Route::get('/staff-stock-overview', StaffStockOverview::class)->name('staff-stock-overview');
    Route::get('/due-payments', DuePayments::class)->name('due-payments');
});
*/

// AFTER (With Permission Protection):
Route::middleware('role:staff')->prefix('staff')->name('staff.')->group(function () {
    
    // Dashboard - requires view_dashboard permission
    Route::get('/dashboard', StaffDashboard::class)
        ->name('dashboard')
        ->middleware('permission:view_dashboard');
    
    // Billing - requires view_billing permission
    Route::get('/billing', Billing::class)
        ->name('billing')
        ->middleware('permission:view_billing');
    
    // Customer Sale Management - requires view_customer_sales permission
    Route::get('/customer-sale-management', CustomerSaleManagement::class)
        ->name('customer-sale-management')
        ->middleware('permission:view_customer_sales');
    
    // Staff Stock Overview - requires view_stock_details permission
    Route::get('/staff-stock-overview', StaffStockOverview::class)
        ->name('staff-stock-overview')
        ->middleware('permission:view_stock_details');
    
    // Due Payments - requires view_due_payments permission
    Route::get('/due-payments', DuePayments::class)
        ->name('due-payments')
        ->middleware('permission:view_due_payments');
});

/**
 * Alternative: Group routes by permission level
 */
Route::middleware(['role:staff', 'permission:view_dashboard'])->prefix('staff')->name('staff.')->group(function () {
    // All routes in this group require view_dashboard permission
    Route::get('/dashboard', StaffDashboard::class)->name('dashboard');
});

Route::middleware(['role:staff', 'permission:view_billing'])->prefix('staff')->name('staff.')->group(function () {
    // All routes in this group require view_billing permission
    Route::get('/billing', Billing::class)->name('billing');
    Route::post('/billing/create', [BillingController::class, 'store'])->name('billing.store');
});

/**
 * Alternative: Multiple permissions using middleware array
 */
Route::middleware(['role:staff', 'permission:view_sales,view_customer_sales'])
    ->prefix('staff')
    ->name('staff.')
    ->group(function () {
        Route::get('/customer-sale-management', CustomerSaleManagement::class)
            ->name('customer-sale-management');
    });

/**
 * To implement this in your routes/web.php:
 * 
 * 1. Find the staff routes section (around line 164)
 * 2. Replace the existing staff routes with the protected version above
 * 3. Ensure the CheckStaffPermission middleware is registered in Kernel.php
 * 4. Test with staff accounts that have different permission levels
 */
