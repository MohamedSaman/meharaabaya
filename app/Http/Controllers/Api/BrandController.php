<?php

namespace App\Http\Controllers\Api;

use App\Models\BrandList;
use Illuminate\Http\Request;

class BrandController extends ApiController
{
    /**
     * Get all brands
     */
    public function index(Request $request)
    {
        $query = BrandList::query();

        // Search
        if ($request->has('search')) {
            $query->where('brand_name', 'like', "%{$request->get('search')}%");
        }

        /** @var \Illuminate\Pagination\LengthAwarePaginator $brands */
        $brands = $query->orderBy('brand_name')->paginate(50);

        // Transform for mobile app
        $transformedBrands = collect($brands->items())->map(function ($brand) {
            return [
                'id' => $brand->id,
                'brand_name' => $brand->brand_name,
                'name' => $brand->brand_name, // Alias for compatibility
                'created_at' => $brand->created_at,
                'updated_at' => $brand->updated_at,
            ];
        });

        return $this->paginated($brands->setCollection($transformedBrands));
    }

    /**
     * Get a single brand by ID
     */
    public function show($id)
    {
        $brand = BrandList::find($id);

        if (!$brand) {
            return $this->error('Brand not found', 404);
        }

        return $this->success([
            'id' => $brand->id,
            'name' => $brand->brand_name,
        ]);
    }

    /**
     * Create a new brand
     */
    public function store(Request $request)
    {
        // Accept both 'name' and 'brand_name' from frontend
        $brandName = $request->name ?? $request->brand_name;

        if (empty($brandName)) {
            return $this->error('Brand name is required', 422);
        }

        // Check for existing brand
        if (BrandList::where('brand_name', $brandName)->exists()) {
            return $this->error('Brand name already exists', 422);
        }

        $brand = BrandList::create([
            'brand_name' => $brandName,
        ]);

        return $this->success([
            'id' => $brand->id,
            'brand_name' => $brand->brand_name,
            'name' => $brand->brand_name,
            'description' => $request->description ?? null,
        ], 'Brand created successfully', 201);
    }

    /**
     * Update a brand
     */
    public function update(Request $request, $id)
    {
        $brand = BrandList::find($id);

        if (!$brand) {
            return $this->error('Brand not found', 404);
        }

        // Accept both 'name' and 'brand_name' from frontend
        $brandName = $request->name ?? $request->brand_name ?? $brand->brand_name;

        // Check for existing brand (excluding current)
        if (BrandList::where('brand_name', $brandName)->where('id', '!=', $id)->exists()) {
            return $this->error('Brand name already exists', 422);
        }

        $brand->update([
            'brand_name' => $brandName,
        ]);

        return $this->success([
            'id' => $brand->id,
            'brand_name' => $brand->brand_name,
            'name' => $brand->brand_name,
            'description' => $request->description ?? null,
        ], 'Brand updated successfully');
    }

    /**
     * Delete a brand
     */
    public function destroy($id)
    {
        $brand = BrandList::find($id);

        if (!$brand) {
            return $this->error('Brand not found', 404);
        }

        $brand->delete();
        return $this->success(null, 'Brand deleted successfully');
    }
}
