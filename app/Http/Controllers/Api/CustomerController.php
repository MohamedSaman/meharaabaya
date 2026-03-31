<?php

namespace App\Http\Controllers\Api;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerController extends ApiController
{
    /**
     * Get all customers with optional search
     */
    public function index(Request $request)
    {
        $query = Customer::query();

        // Search
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('business_name', 'like', "%{$search}%");
            });
        }

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->get('type'));
        }

        /** @var \Illuminate\Pagination\LengthAwarePaginator $customers */
        $customers = $query->orderBy('created_at', 'desc')->paginate(20);

        // Transform for mobile app
        $transformedCustomers = collect($customers->items())->map(function ($customer) {
            return $this->transformCustomer($customer);
        });

        return $this->paginated($customers->setCollection($transformedCustomers));
    }

    /**
     * Get a single customer by ID
     */
    public function show($id)
    {
        $customer = Customer::with('sales')->find($id);

        if (!$customer) {
            return $this->error('Customer not found', 404);
        }

        return $this->success($this->transformCustomer($customer));
    }

    /**
     * Create a new customer
     */
    public function store(Request $request)
    {
        // Accept both 'name' and 'customer_name' from frontend
        $name = $request->name ?? $request->customer_name;

        if (empty($name)) {
            return $this->error('Customer name is required', 422);
        }

        // Accept both 'type' and 'customer_type' from frontend
        // Map frontend values (individual/business) to database values (retail/wholesale)
        $frontendType = $request->type ?? $request->customer_type ?? 'individual';
        $typeMap = [
            'individual' => 'retail',
            'business' => 'wholesale',
            'retail' => 'retail',
            'wholesale' => 'wholesale',
        ];
        $type = $typeMap[$frontendType] ?? 'retail';

        try {
            $customer = Customer::create([
                'name' => $name,
                'phone' => $request->phone ?? $request->mobile,
                'email' => $request->email,
                'type' => $type,
                'address' => $request->address,
                'notes' => $request->notes,
                'business_name' => $request->business_name ?? $request->company,
                'created_by' => Auth::id() ?? null,
                'user_id' => Auth::id() ?? null,
            ]);

            return $this->success($this->transformCustomer($customer), 'Customer created successfully', 201);
        } catch (\Exception $e) {
            return $this->error('Failed to create customer: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update a customer
     */
    public function update(Request $request, $id)
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return $this->error('Customer not found', 404);
        }

        // Accept both 'name' and 'customer_name' from frontend
        $name = $request->name ?? $request->customer_name ?? $customer->name;

        // Map frontend types to database types if provided
        $frontendType = $request->type ?? $request->customer_type;
        $type = $customer->type; // Keep existing by default
        if ($frontendType) {
            $typeMap = [
                'individual' => 'retail',
                'business' => 'wholesale',
                'retail' => 'retail',
                'wholesale' => 'wholesale',
            ];
            $type = $typeMap[$frontendType] ?? $customer->type;
        }

        try {
            $customer->update([
                'name' => $name,
                'phone' => $request->phone ?? $request->mobile ?? $customer->phone,
                'email' => $request->email ?? $customer->email,
                'type' => $type,
                'address' => $request->address ?? $customer->address,
                'notes' => $request->notes ?? $customer->notes,
                'business_name' => $request->business_name ?? $request->company ?? $customer->business_name,
            ]);

            return $this->success($this->transformCustomer($customer), 'Customer updated successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to update customer: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete a customer
     */
    public function destroy($id)
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return $this->error('Customer not found', 404);
        }

        // Check for related sales
        if ($customer->sales()->count() > 0) {
            return $this->error('Cannot delete customer with existing sales', 400);
        }

        $customer->delete();
        return $this->success(null, 'Customer deleted successfully');
    }

    /**
     * Transform customer data for frontend compatibility
     */
    private function transformCustomer($customer)
    {
        // Map database types to frontend types
        $typeMap = [
            'retail' => 'individual',
            'wholesale' => 'business',
        ];
        $frontendType = $typeMap[$customer->type] ?? 'individual';

        return [
            'id' => $customer->id,
            'customer_name' => $customer->name,
            'name' => $customer->name,
            'email' => $customer->email,
            'phone' => $customer->phone,
            'mobile' => $customer->phone,
            'address' => $customer->address,
            'customer_type' => $frontendType,
            'type' => $frontendType,
            'business_name' => $customer->business_name,
            'company' => $customer->business_name,
            'notes' => $customer->notes,
            'credit_limit' => 0,
            'current_balance' => 0,
            'created_at' => $customer->created_at,
            'updated_at' => $customer->updated_at,
        ];
    }
}
