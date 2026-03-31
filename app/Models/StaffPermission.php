<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffPermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'permission_key',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the user that owns the permission.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Available permission keys and their descriptions
     */
    public static function availablePermissions()
    {
        return [
            // Dashboard
            'menu_dashboard' => 'Dashboard/Overview Menu',
            
            // Products Menu
            'menu_products' => 'Products Menu',
            'menu_products_list' => 'List Products',
            'menu_products_brand' => 'Product Brands',
            'menu_products_category' => 'Product Categories',
            
            // Sales Menu
            'menu_sales' => 'Sales Menu',
            'menu_sales_add' => 'Add Sales',
            'menu_sales_list' => 'List Sales',
            'menu_sales_pos' => 'POS Sales',
            
            // Quotation Menu
            'menu_quotation' => 'Quotation Menu',
            'menu_quotation_add' => 'Add Quotation',
            'menu_quotation_list' => 'List Quotation',
            
            // Purchase Menu
            'menu_purchase' => 'Purchase Menu',
            'menu_purchase_order' => 'Purchase Order',
            'menu_purchase_grn' => 'GRN (Goods Received Note)',
            
            // Return Menu
            'menu_return' => 'Return Menu',
            'menu_return_customer_add' => 'Add Customer Return',
            'menu_return_customer_list' => 'List Customer Return',
            'menu_return_supplier_add' => 'Add Supplier Return',
            'menu_return_supplier_list' => 'List Supplier Return',
            
            // Cheque/Banks Menu
            'menu_banks' => 'Cheque/Banks Menu',
            'menu_banks_deposit' => 'Deposit By Cash',
            'menu_banks_cheque_list' => 'Cheque List',
            'menu_banks_return_cheque' => 'Return Cheque',
            
            // Expenses Menu
            'menu_expenses' => 'Expenses Menu',
            'menu_expenses_list' => 'List Expenses',
            
            // Payment Management Menu
            'menu_payment' => 'Payment Management Menu',
            'menu_payment_customer_receipt_add' => 'Add Customer Receipt',
            'menu_payment_customer_receipt_list' => 'List Customer Receipt',
            'menu_payment_supplier_add' => 'Add Supplier Payment',
            'menu_payment_supplier_list' => 'List Supplier Payment',
            
            // People Menu
            'menu_people' => 'People Menu',
            'menu_people_suppliers' => 'List Suppliers',
            'menu_people_customers' => 'List Customers',
            'menu_people_staff' => 'List Staff',
            
            // POS
            'menu_pos' => 'POS (Point of Sale)',
            
            // Reports
            'menu_reports' => 'Reports',
            
            // Analytics
            'menu_analytics' => 'Analytics',
        ];
    }

    /**
     * Permission categories for organized display
     */
    public static function permissionCategories()
    {
        return [
            'Dashboard' => [
                'menu_dashboard',
            ],
            'Products Management' => [
                'menu_products',
                'menu_products_list',
                'menu_products_brand',
                'menu_products_category',
            ],
            'Sales Management' => [
                'menu_sales',
                'menu_sales_add',
                'menu_sales_list',
                'menu_sales_pos',
            ],
            'Quotation Management' => [
                'menu_quotation',
                'menu_quotation_add',
                'menu_quotation_list',
            ],
            'Purchase Management' => [
                'menu_purchase',
                'menu_purchase_order',
                'menu_purchase_grn',
            ],
            'Return Management' => [
                'menu_return',
                'menu_return_customer_add',
                'menu_return_customer_list',
                'menu_return_supplier_add',
                'menu_return_supplier_list',
            ],
            'Cheque & Banks' => [
                'menu_banks',
                'menu_banks_deposit',
                'menu_banks_cheque_list',
                'menu_banks_return_cheque',
            ],
            'Expenses Management' => [
                'menu_expenses',
                'menu_expenses_list',
            ],
            'Payment Management' => [
                'menu_payment',
                'menu_payment_customer_receipt_add',
                'menu_payment_customer_receipt_list',
                'menu_payment_supplier_add',
                'menu_payment_supplier_list',
            ],
            'People Management' => [
                'menu_people',
                'menu_people_suppliers',
                'menu_people_customers',
                'menu_people_staff',
            ],
            'Point of Sale' => [
                'menu_pos',
            ],
            'Reports & Analytics' => [
                'menu_reports',
                'menu_analytics',
            ],
        ];
    }

    /**
     * Check if a user has a specific permission
     */
    public static function hasPermission($userId, $permissionKey)
    {
        return self::where('user_id', $userId)
            ->where('permission_key', $permissionKey)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Get all permissions for a user
     */
    public static function getUserPermissions($userId)
    {
        return self::where('user_id', $userId)
            ->where('is_active', true)
            ->pluck('permission_key')
            ->toArray();
    }

    /**
     * Sync permissions for a user
     */
    public static function syncPermissions($userId, array $permissions)
    {
        // Delete existing permissions
        self::where('user_id', $userId)->delete();

        // Create new permissions
        foreach ($permissions as $permission) {
            self::create([
                'user_id' => $userId,
                'permission_key' => $permission,
                'is_active' => true,
            ]);
        }
    }
}
