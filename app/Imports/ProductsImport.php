<?php

namespace App\Imports;

use App\Models\ProductDetail;
use App\Models\ProductPrice;
use App\Models\ProductStock;
use App\Models\BrandList;
use App\Models\CategoryList;
use App\Models\ProductSupplier;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Illuminate\Support\Facades\DB;

class ProductsImport implements ToModel, WithHeadingRow, WithValidation, SkipsEmptyRows, SkipsOnFailure
{
    use SkipsFailures;

    private $defaultBrandId;
    private $defaultCategoryId;
    private $defaultSupplierId;
    private $successCount = 0;
    private $skipCount = 0;

    public function __construct()
    {
        // Set default IDs
        $this->setDefaultIds();
    }

    /**
     * Set default IDs for brand, category, and supplier
     */
    private function setDefaultIds()
    {
        // Get or create default brand
        $defaultBrand = BrandList::firstOrCreate(
            ['brand_name' => 'Default Brand'],
            ['status' => 'active']
        );
        $this->defaultBrandId = $defaultBrand->id;

        // Get or create default category
        $defaultCategory = CategoryList::firstOrCreate(
            ['category_name' => 'Default Category'],
            ['status' => 'active']
        );
        $this->defaultCategoryId = $defaultCategory->id;

        // Get or create default supplier
        $defaultSupplier = ProductSupplier::firstOrCreate(
            ['name' => 'Default Supplier'],
            [
                'phone' => '0000000000',
                'email' => 'default@supplier.com',
                'address' => 'Default Address',
                'status' => 'active'
            ]
        );
        $this->defaultSupplierId = $defaultSupplier->id;
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Check if product with same code already exists
        $existingProduct = ProductDetail::where('code', $row['code'])->first();
        
        if ($existingProduct) {
            $this->skipCount++;
            return null; // Skip duplicate products
        }

        try {
            DB::beginTransaction();

            // Create product detail with only CODE and NAME from Excel
            // All other fields get default/null values
            $product = ProductDetail::create([
                'code' => $row['code'],
                'name' => $row['name'],
                'model' => null, // Default null
                'image' => null, // Default null
                'description' => null, // Default null
                'barcode' => null, // Default null
                'status' => 'active', // Default active status
                'brand_id' => $this->defaultBrandId, // Default brand
                'category_id' => $this->defaultCategoryId, // Default category
                'supplier_id' => $this->defaultSupplierId, // Default supplier
            ]);

            // Create default price record
            ProductPrice::create([
                'product_id' => $product->id,
                'supplier_price' => 0.00, // Default 0
                'selling_price' => 0.00, // Default 0
                'discount_price' => 0.00, // Default 0
            ]);

            // Create default stock record
            ProductStock::create([
                'product_id' => $product->id,
                'available_stock' => 0, // Default 0
                'damage_stock' => 0, // Default 0
            ]);

            DB::commit();
            $this->successCount++;

            return $product;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->skipCount++;
            return null;
        }
    }

    /**
     * Validation rules for each row
     */
    public function rules(): array
    {
        return [
            'code' => 'required|string|max:255',
            'name' => 'required|string|max:255',
        ];
    }

    /**
     * Custom validation messages
     */
    public function customValidationMessages()
    {
        return [
            'code.required' => 'Product code is required',
            'name.required' => 'Product name is required',
        ];
    }

    /**
     * Get the count of successfully imported products
     */
    public function getSuccessCount(): int
    {
        return $this->successCount;
    }

    /**
     * Get the count of skipped products
     */
    public function getSkipCount(): int
    {
        return $this->skipCount;
    }

    /**
     * Get heading row configuration
     */
    public function headingRow(): int
    {
        return 1; // First row contains headers
    }
}
