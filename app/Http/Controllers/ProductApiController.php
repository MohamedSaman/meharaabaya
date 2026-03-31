<?php

namespace App\Http\Controllers;

use App\Models\ProductDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProductApiController extends Controller
{
    /**
     * Get all products with caching (for client-side operations)
     * Cache for 5 minutes, refresh when products change
     */
    public function getAllProducts(Request $request)
    {
        // Cache key
        $cacheKey = 'products_list_all';
        
        // Get from cache or fetch from database
        $products = Cache::remember($cacheKey, now()->addMinutes(5), function () {
            return ProductDetail::join('product_prices', 'product_details.id', '=', 'product_prices.product_id')
                ->join('product_stocks', 'product_details.id', '=', 'product_stocks.product_id')
                ->leftJoin('brand_lists', 'product_details.brand_id', '=', 'brand_lists.id')
                ->leftJoin('category_lists', 'product_details.category_id', '=', 'category_lists.id')
                ->select(
                    'product_details.id',
                    'product_details.code',
                    'product_details.name as product_name',
                    'product_details.model',
                    'product_details.image',
                    'product_details.description',
                    'product_details.barcode',
                    'product_details.status',
                    'product_prices.supplier_price',
                    'product_prices.selling_price',
                    'product_prices.discount_price',
                    'product_stocks.available_stock',
                    'product_stocks.damage_stock',
                    'product_stocks.total_stock',
                    'brand_lists.brand_name as brand',
                    'category_lists.category_name as category'
                )
                ->orderBy('product_details.created_at', 'desc')
                ->get();
        });

        return response()->json([
            'success' => true,
            'data' => $products,
            'count' => $products->count(),
            'cached_at' => now()->toDateTimeString()
        ]);
    }

    /**
     * Clear products cache (call this after CRUD operations)
     */
    public static function clearCache()
    {
        Cache::forget('products_list_all');
    }
}
