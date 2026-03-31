<?php

namespace App\Http\Controllers\Api;

use App\Models\CategoryList;
use Illuminate\Http\Request;

class CategoryController extends ApiController
{
    /**
     * Get all categories
     */
    public function index(Request $request)
    {
        $query = CategoryList::withCount('products');

        // Search
        if ($request->has('search')) {
            $query->where('category_name', 'like', "%{$request->get('search')}%");
        }

        /** @var \Illuminate\Pagination\LengthAwarePaginator $categories */
        $categories = $query->orderBy('category_name')->paginate(50);

        // Transform for mobile app
        $transformedCategories = collect($categories->items())->map(function ($category) {
            return [
                'id' => $category->id,
                'category_name' => $category->category_name,
                'name' => $category->category_name, // Alias for compatibility
                'product_count' => $category->products_count ?? 0,
                'created_at' => $category->created_at,
                'updated_at' => $category->updated_at,
            ];
        });
        //test

        return $this->paginated($categories->setCollection($transformedCategories));
    }

    /**
     * Get a single category by ID
     */
    public function show($id)
    {
        $category = CategoryList::withCount('products')->find($id);

        if (!$category) {
            return $this->error('Category not found', 404);
        }

        return $this->success([
            'id' => $category->id,
            'category_name' => $category->category_name,
            'name' => $category->category_name,
            'product_count' => $category->products_count ?? 0,
            'created_at' => $category->created_at,
            'updated_at' => $category->updated_at,
        ]);
    }

    /**
     * Create a new category
     */
    public function store(Request $request)
    {
        // Accept both 'name' and 'category_name' from frontend
        $categoryName = $request->name ?? $request->category_name;

        if (empty($categoryName)) {
            return $this->error('Category name is required', 422);
        }

        // Check for existing category
        if (CategoryList::where('category_name', $categoryName)->exists()) {
            return $this->error('Category name already exists', 422);
        }

        $category = CategoryList::create([
            'category_name' => $categoryName,
        ]);

        return $this->success([
            'id' => $category->id,
            'category_name' => $category->category_name,
            'name' => $category->category_name,
            'description' => $request->description ?? null,
        ], 'Category created successfully', 201);
    }

    /**
     * Update a category
     */
    public function update(Request $request, $id)
    {
        $category = CategoryList::find($id);

        if (!$category) {
            return $this->error('Category not found', 404);
        }

        // Accept both 'name' and 'category_name' from frontend
        $categoryName = $request->name ?? $request->category_name ?? $category->category_name;

        // Check for existing category (excluding current)
        if (CategoryList::where('category_name', $categoryName)->where('id', '!=', $id)->exists()) {
            return $this->error('Category name already exists', 422);
        }

        $category->update([
            'category_name' => $categoryName,
        ]);

        return $this->success([
            'id' => $category->id,
            'category_name' => $category->category_name,
            'name' => $category->category_name,
            'description' => $request->description ?? null,
        ], 'Category updated successfully');
    }

    /**
     * Delete a category
     */
    public function destroy($id)
    {
        $category = CategoryList::find($id);

        if (!$category) {
            return $this->error('Category not found', 404);
        }

        $category->delete();
        return $this->success(null, 'Category deleted successfully');
    }
}
