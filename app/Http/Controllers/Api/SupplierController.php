<?php

namespace App\Http\Controllers\Api;

use App\Models\ProductSupplier;
use Illuminate\Http\Request;

class SupplierController extends ApiController
{
    /**
     * Get all suppliers with optional search
     */
    public function index(Request $request)
    {
        $query = ProductSupplier::query();

        // Search
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('businessname', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }

        /** @var \Illuminate\Pagination\LengthAwarePaginator $suppliers */
        $suppliers = $query->orderBy('created_at', 'desc')->paginate(20);

        // Transform for mobile app
        $transformedSuppliers = collect($suppliers->items())->map(function ($supplier) {
            return [
                'id' => $supplier->id,
                'supplier_name' => $supplier->name,
                'name' => $supplier->name,
                'business_name' => $supplier->businessname,
                'phone' => $supplier->phone,
                'email' => $supplier->email,
                'address' => $supplier->address,
                'status' => $supplier->status,
                'created_at' => $supplier->created_at,
                'updated_at' => $supplier->updated_at,
            ];
        });

        return $this->paginated($suppliers->setCollection($transformedSuppliers));
    }

    /**
     * Get a single supplier by ID
     */
    public function show($id)
    {
        $supplier = ProductSupplier::with('purchaseOrders')->find($id);

        if (!$supplier) {
            return $this->error('Supplier not found', 404);
        }

        return $this->success($supplier);
    }

    /**
     * Create a new supplier
     */
    public function store(Request $request)
    {
        // Accept both 'name' and 'supplier_name' from frontend
        $name = $request->name ?? $request->supplier_name;

        if (empty($name)) {
            return $this->error('Supplier name is required', 422);
        }

        $supplier = ProductSupplier::create([
            'name' => $name,
            'businessname' => $request->businessname ?? $request->business_name ?? $request->company,
            'contact' => $request->contact,
            'address' => $request->address ?? $request->supplier_address,
            'email' => $request->email ?? $request->supplier_email,
            'phone' => $request->phone ?? $request->supplier_phone,
            'status' => $request->status ?? 'active',
            'notes' => $request->notes,
        ]);

        return $this->success($this->transformSupplier($supplier), 'Supplier created successfully', 201);
    }

    /**
     * Update a supplier
     */
    public function update(Request $request, $id)
    {
        $supplier = ProductSupplier::find($id);

        if (!$supplier) {
            return $this->error('Supplier not found', 404);
        }

        // Accept both 'name' and 'supplier_name' from frontend
        $name = $request->name ?? $request->supplier_name ?? $supplier->name;

        $supplier->update([
            'name' => $name,
            'businessname' => $request->businessname ?? $request->business_name ?? $request->company ?? $supplier->businessname,
            'contact' => $request->contact ?? $supplier->contact,
            'address' => $request->address ?? $request->supplier_address ?? $supplier->address,
            'email' => $request->email ?? $request->supplier_email ?? $supplier->email,
            'phone' => $request->phone ?? $request->supplier_phone ?? $supplier->phone,
            'status' => $request->status ?? $supplier->status,
            'notes' => $request->notes ?? $supplier->notes,
        ]);

        return $this->success($this->transformSupplier($supplier), 'Supplier updated successfully');
    }

    /**
     * Delete a supplier
     */
    public function destroy($id)
    {
        $supplier = ProductSupplier::find($id);

        if (!$supplier) {
            return $this->error('Supplier not found', 404);
        }

        // Check for related purchase orders
        if ($supplier->purchaseOrders()->count() > 0) {
            return $this->error('Cannot delete supplier with existing orders', 400);
        }

        $supplier->delete();
        return $this->success(null, 'Supplier deleted successfully');
    }

    /**
     * Transform supplier data for frontend compatibility
     */
    private function transformSupplier($supplier)
    {
        return [
            'id' => $supplier->id,
            'supplier_name' => $supplier->name,
            'name' => $supplier->name,
            'business_name' => $supplier->businessname,
            'company' => $supplier->businessname,
            'phone' => $supplier->phone,
            'email' => $supplier->email,
            'address' => $supplier->address,
            'status' => $supplier->status,
            'notes' => $supplier->notes,
            'created_at' => $supplier->created_at,
            'updated_at' => $supplier->updated_at,
        ];
    }
}
