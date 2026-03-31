<?php

namespace App\Http\Controllers\Api;

use App\Models\ProductDetail;
use App\Models\ProductPrice;
use App\Models\ProductStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ProductController extends ApiController
{
    /**
     * Get all products with optional search and filters
     */
    public function index(Request $request)
    {
        $query = ProductDetail::with(['price', 'stock', 'brand', 'category', 'supplier'])
            ->select('product_details.*');

        // Search
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->has('category_id')) {
            $query->where('category_id', $request->get('category_id'));
        }

        // Filter by brand
        if ($request->has('brand_id')) {
            $query->where('brand_id', $request->get('brand_id'));
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }

        /** @var \Illuminate\Pagination\LengthAwarePaginator $products */
        $products = $query->orderBy('created_at', 'desc')->paginate(20);

        // Transform for mobile app
        $transformedProducts = collect($products->items())->map(function ($product) {
            return $this->transformProduct($product);
        });

        return $this->paginated($products->setCollection($transformedProducts));
    }

    /**
     * Get a single product by ID
     */
    public function show($id)
    {
        $product = ProductDetail::with(['price', 'stock', 'brand', 'category', 'supplier'])
            ->find($id);

        if (!$product) {
            return $this->error('Product not found', 404);
        }

        return $this->success($this->transformProduct($product));
    }

    /**
     * Create a new product
     */
    public function store(Request $request)
    {
        // Map mobile app field names to Laravel model fields
        $name = $request->name ?? $request->product_name;
        $code = $request->code ?? $request->product_sku ?? $request->product_code;
        $description = $request->description ?? $request->product_description;
        $sellingPrice = $request->selling_price ?? $request->product_selling_price ?? 0;
        $supplierPrice = $request->supplier_price ?? $request->product_price ?? $request->cost_price ?? 0;
        $brandId = $request->brand_id ?? $request->brand;
        $categoryId = $request->category_id ?? $request->category;
        $supplierId = $request->supplier_id ?? $request->supplier;
        $status = $request->status ?? ($request->is_active ? 'active' : 'inactive');
        $availableStock = $request->available_stock ?? 0;
        $damageStock = $request->damage_stock ?? $request->damaged_stock ?? 0;
        $image = $request->image ?? $request->image_url;

        // Validate required fields
        if (empty($name)) {
            return $this->error('Product name is required', 422);
        }
        if (empty($code)) {
            return $this->error('Product code/SKU is required', 422);
        }

        // Check if code already exists
        if (ProductDetail::where('code', $code)->exists()) {
            return $this->error('Product code already exists', 422);
        }

        try {
            DB::beginTransaction();

            // Create product
            $product = ProductDetail::create([
                'name' => $name,
                'code' => $code,
                'model' => $request->model,
                'image' => $image,
                'description' => $description,
                'barcode' => $request->barcode ?? $code,
                'status' => $status ?? 'active',
                'brand_id' => $brandId,
                'category_id' => $categoryId,
                'supplier_id' => $supplierId,
            ]);

            // Create price
            ProductPrice::create([
                'product_id' => $product->id,
                'supplier_price' => $supplierPrice,
                'selling_price' => $sellingPrice,
                'discount_price' => $request->discount_price ?? $request->product_wholesale_price ?? 0,
            ]);

            // Create stock
            ProductStock::create([
                'product_id' => $product->id,
                'available_stock' => $availableStock,
                'damage_stock' => $damageStock,
                'total_stock' => $availableStock,
            ]);

            DB::commit();

            $product->load(['price', 'stock', 'brand', 'category', 'supplier']);
            return $this->success($this->transformProduct($product), 'Product created successfully', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('Failed to create product: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update a product
     */
    public function update(Request $request, $id)
    {
        $product = ProductDetail::find($id);

        if (!$product) {
            return $this->error('Product not found', 404);
        }

        try {
            DB::beginTransaction();

            // Map mobile app field names to Laravel model fields
            $name = $request->name ?? $request->product_name;
            $code = $request->code ?? $request->product_sku ?? $request->product_code;
            $description = $request->description ?? $request->product_description;
            $sellingPrice = $request->selling_price ?? $request->product_selling_price;
            $supplierPrice = $request->supplier_price ?? $request->product_price ?? $request->cost_price;
            $brandId = $request->brand_id ?? $request->brand;
            $categoryId = $request->category_id ?? $request->category;
            $supplierId = $request->supplier_id ?? $request->supplier;
            // Handle status/is_active: if is_active provided, use it to determine status, else fallback to status field
            $status = $request->has('is_active')
                ? ($request->is_active ? 'active' : 'inactive')
                : ($request->status ?? $product->status);
            $availableStock = $request->available_stock;
            $damageStock = $request->damage_stock ?? $request->damaged_stock;
            $image = $request->image ?? $request->image_url;

            // Update product
            $product->update([
                'name' => $name ?? $product->name,
                'code' => $code ?? $product->code,
                'model' => $request->model ?? $product->model,
                'image' => $image ?? $product->image,
                'description' => $description ?? $product->description,
                'barcode' => $request->barcode ?? $product->barcode,
                'status' => $status,
                'brand_id' => $brandId ?? $product->brand_id,
                'category_id' => $categoryId ?? $product->category_id,
                'supplier_id' => $supplierId ?? $product->supplier_id,
            ]);

            // Update price if provided
            if ($product->price) {
                $product->price->update([
                    'supplier_price' => $supplierPrice ?? $product->price->supplier_price,
                    'selling_price' => $sellingPrice ?? $product->price->selling_price,
                    'discount_price' => $request->discount_price ?? $request->product_wholesale_price ?? $product->price->discount_price,
                ]);
            }

            // Update stock if provided
            if ($product->stock) {
                // If available_stock is null, don't update it (keep existing). if 0 is passed, update it.
                $stockUpdate = [];
                if ($availableStock !== null)
                    $stockUpdate['available_stock'] = $availableStock;
                if ($damageStock !== null)
                    $stockUpdate['damage_stock'] = $damageStock;

                if (!empty($stockUpdate)) {
                    $product->stock->update($stockUpdate);
                }
            }

            DB::commit();

            $product->load(['price', 'stock', 'brand', 'category', 'supplier']);
            return $this->success($this->transformProduct($product), 'Product updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('Failed to update product: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete a product
     */
    public function destroy($id)
    {
        $product = ProductDetail::find($id);

        if (!$product) {
            return $this->error('Product not found', 404);
        }

        try {
            DB::beginTransaction();

            // Delete related records
            ProductPrice::where('product_id', $id)->delete();
            ProductStock::where('product_id', $id)->delete();
            $product->delete();

            DB::commit();
            return $this->success(null, 'Product deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('Failed to delete product: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Transform product for API response
     */
    private function transformProduct($product)
    {
        return [
            'id' => $product->id,
            'product_name' => $product->name,
            'product_sku' => $product->code,
            'product_code' => $product->code,
            'model' => $product->model,
            'image' => $product->image,
            'product_description' => $product->description,
            'barcode' => $product->barcode,
            'product_status' => $product->status,
            'is_active' => $product->status === 'active',

            // Relations identifiers
            'brand_id' => $product->brand_id,
            'category_id' => $product->category_id,
            'supplier_id' => $product->supplier_id,

            // Nested relations data (optional, but keep for completeness if needed)
            'brand' => $product->brand ? [
                'id' => $product->brand->id,
                'name' => $product->brand->brand_name,
            ] : null,
            'category' => $product->category ? [
                'id' => $product->category->id,
                'name' => $product->category->category_name,
            ] : null,
            'supplier' => $product->supplier ? [
                'id' => $product->supplier->id,
                'name' => $product->supplier->name,
            ] : null,

            // Flat fields for frontend display
            'brand_name' => $product->brand ? $product->brand->brand_name : null,
            'category_name' => $product->category ? $product->category->category_name : null,
            'supplier_name' => $product->supplier ? $product->supplier->name : null,

            // Flattened attributes
            'product_price' => $product->price ? (float) $product->price->supplier_price : 0,
            'product_selling_price' => $product->price ? (float) $product->price->selling_price : 0,
            'product_wholesale_price' => $product->price ? (float) $product->price->discount_price : 0,

            'available_stock' => $product->stock ? (int) $product->stock->available_stock : 0,
            'damaged_stock' => $product->stock ? (int) $product->stock->damage_stock : 0,
            'total_stock' => $product->stock ? (int) $product->stock->total_stock : 0,

            'created_at' => $product->created_at,
            'updated_at' => $product->updated_at,
        ];
    }
}
