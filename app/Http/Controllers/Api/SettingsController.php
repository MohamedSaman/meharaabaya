<?php

namespace App\Http\Controllers\Api;

use App\Models\Setting;
use App\Models\User;
use App\Models\StaffPermission;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SettingsController extends ApiController
{
    /**
     * Get all settings
     */
    public function index(Request $request)
    {
        $settings = Setting::orderBy('created_at', 'desc')->get();
        
        return $this->success([
            'results' => $settings->map(function ($setting) {
                return [
                    'id' => $setting->id,
                    'key' => $setting->key,
                    'value' => $setting->value,
                    'created_at' => $setting->created_at,
                    'updated_at' => $setting->updated_at,
                ];
            }),
            'count' => $settings->count(),
        ]);
    }

    /**
     * Create a new setting
     */
    public function store(Request $request)
    {
        $request->validate([
            'key' => 'required|string|max:255|unique:settings,key',
            'value' => 'required|string|max:255',
        ]);

        $setting = Setting::create([
            'key' => $request->key,
            'value' => $request->value,
            'date' => now(),
        ]);

        return $this->success([
            'id' => $setting->id,
            'key' => $setting->key,
            'value' => $setting->value,
        ], 'Setting created successfully', 201);
    }

    /**
     * Update a setting
     */
    public function update(Request $request, $id)
    {
        $setting = Setting::find($id);

        if (!$setting) {
            return $this->error('Setting not found', 404);
        }

        $request->validate([
            'key' => 'sometimes|string|max:255|unique:settings,key,' . $id,
            'value' => 'sometimes|string|max:255',
        ]);

        $setting->update([
            'key' => $request->key ?? $setting->key,
            'value' => $request->value ?? $setting->value,
        ]);

        return $this->success([
            'id' => $setting->id,
            'key' => $setting->key,
            'value' => $setting->value,
        ], 'Setting updated successfully');
    }

    /**
     * Delete a setting
     */
    public function destroy($id)
    {
        $setting = Setting::find($id);

        if (!$setting) {
            return $this->error('Setting not found', 404);
        }

        $setting->delete();
        return $this->success(null, 'Setting deleted successfully');
    }

    /**
     * Get staff members list
     */
    public function getStaff(Request $request)
    {
        $staff = User::where('role', 'staff')
            ->orderBy('name')
            ->get();

        return $this->success([
            'results' => $staff->map(function ($user) {
                $permissions = [];
                if (class_exists(StaffPermission::class)) {
                    try {
                        $permissions = StaffPermission::getUserPermissions($user->id);
                    } catch (\Exception $e) {
                        $permissions = [];
                    }
                }
                
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'contact' => $user->contact,
                    'permissions' => $permissions,
                    'created_at' => $user->created_at,
                ];
            }),
            'count' => $staff->count(),
        ]);
    }

    /**
     * Create a new staff member (sales rep)
     */
    public function storeStaff(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'contact' => 'nullable|string|max:20',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'contact' => $request->contact,
            'role' => 'staff',
        ]);

        return $this->success([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'contact' => $user->contact,
            'permissions' => [],
            'created_at' => $user->created_at,
        ], 'Staff member created successfully', 201);
    }

    /**
     * Update a staff member
     */
    public function updateStaff(Request $request, $id)
    {
        $user = User::where('id', $id)->where('role', 'staff')->first();

        if (!$user) {
            return $this->error('Staff member not found', 404);
        }

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'password' => 'sometimes|string|min:6',
            'contact' => 'nullable|string|max:20',
        ]);

        $user->update([
            'name' => $request->name ?? $user->name,
            'email' => $request->email ?? $user->email,
            'contact' => $request->contact ?? $user->contact,
        ]);

        if ($request->password) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        return $this->success([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'contact' => $user->contact,
        ], 'Staff member updated successfully');
    }

    /**
     * Delete a staff member
     */
    public function destroyStaff($id)
    {
        $user = User::where('id', $id)->where('role', 'staff')->first();

        if (!$user) {
            return $this->error('Staff member not found', 404);
        }

        // Delete associated permissions
        if (class_exists(StaffPermission::class)) {
            try {
                StaffPermission::where('user_id', $id)->delete();
            } catch (\Exception $e) {
                // Ignore if table doesn't exist
            }
        }

        $user->delete();
        return $this->success(null, 'Staff member deleted successfully');
    }

    /**
     * Update staff permissions
     */
    public function updateStaffPermissions(Request $request, $staffId)
    {
        $staff = User::find($staffId);

        if (!$staff) {
            return $this->error('Staff member not found', 404);
        }

        $request->validate([
            'permissions' => 'required|array',
        ]);

        if (class_exists(StaffPermission::class)) {
            try {
                StaffPermission::syncPermissions($staffId, $request->permissions);
            } catch (\Exception $e) {
                return $this->error('Failed to update permissions: ' . $e->getMessage(), 500);
            }
        }

        return $this->success(null, 'Permissions updated successfully');
    }

    /**
     * Get available permissions
     */
    public function getAvailablePermissions()
    {
        $permissions = [];
        $categories = [];

        if (class_exists(StaffPermission::class)) {
            try {
                $permissions = StaffPermission::availablePermissions();
                $categories = StaffPermission::permissionCategories();
            } catch (\Exception $e) {
                // Return default permissions
                $permissions = [
                    'view_dashboard' => 'View Dashboard',
                    'manage_products' => 'Manage Products',
                    'manage_sales' => 'Manage Sales',
                    'manage_purchases' => 'Manage Purchases',
                    'manage_customers' => 'Manage Customers',
                    'manage_suppliers' => 'Manage Suppliers',
                    'view_reports' => 'View Reports',
                    'manage_expenses' => 'Manage Expenses',
                    'manage_settings' => 'Manage Settings',
                ];
                $categories = [
                    'Dashboard' => ['view_dashboard'],
                    'Products' => ['manage_products'],
                    'Sales' => ['manage_sales'],
                    'Purchases' => ['manage_purchases'],
                    'People' => ['manage_customers', 'manage_suppliers'],
                    'Reports' => ['view_reports'],
                    'Finance' => ['manage_expenses'],
                    'Settings' => ['manage_settings'],
                ];
            }
        }

        return $this->success([
            'permissions' => $permissions,
            'categories' => $categories,
        ]);
    }

    /**
     * Get expense categories
     */
    public function getExpenseCategories()
    {
        if (!class_exists(ExpenseCategory::class)) {
            return $this->success([
                'results' => [],
                'count' => 0,
            ]);
        }

        $categories = ExpenseCategory::orderBy('expense_category')->get();

        return $this->success([
            'results' => $categories->map(function ($cat) {
                return [
                    'id' => $cat->id,
                    'category' => $cat->expense_category,
                    'type' => $cat->type,
                    'created_at' => $cat->created_at,
                ];
            }),
            'count' => $categories->count(),
        ]);
    }

    /**
     * Create expense category
     */
    public function storeExpenseCategory(Request $request)
    {
        $request->validate([
            'category' => 'required|string|max:255',
            'type' => 'required|string|max:255',
        ]);

        if (!class_exists(ExpenseCategory::class)) {
            return $this->error('ExpenseCategory model not found', 500);
        }

        // Check if exists
        $exists = ExpenseCategory::where('expense_category', $request->category)
            ->where('type', $request->type)
            ->exists();

        if ($exists) {
            return $this->error('This category/type combination already exists', 422);
        }

        $category = ExpenseCategory::create([
            'expense_category' => $request->category,
            'type' => $request->type,
        ]);

        return $this->success([
            'id' => $category->id,
            'category' => $category->expense_category,
            'type' => $category->type,
        ], 'Expense category created successfully', 201);
    }

    /**
     * Delete expense category
     */
    public function destroyExpenseCategory($id)
    {
        if (!class_exists(ExpenseCategory::class)) {
            return $this->error('ExpenseCategory model not found', 500);
        }

        $category = ExpenseCategory::find($id);

        if (!$category) {
            return $this->error('Expense category not found', 404);
        }

        $category->delete();
        return $this->success(null, 'Expense category deleted successfully');
    }
}
