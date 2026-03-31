<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\ProductDetail;
use App\Models\ProductPrice;
use App\Models\ProductStock;
use App\Models\BrandList;
use App\Models\CategoryList;
use App\Models\ProductSupplier;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Quotation;
use App\Models\ReturnsProduct;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use App\Imports\ProductsImport;
use App\Exports\ProductsTemplateExport;
use App\Exports\ProductsExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\ProductApiController;
use App\Models\ProductBatch;
use Illuminate\Support\Facades\DB;
use App\Livewire\Concerns\WithDynamicLayout;

#[Title("Product List")]
class Products extends Component
{
    use WithDynamicLayout;

    use WithPagination, WithFileUploads;

    public $search = '';

    // Create form fields
    public $code, $name, $model, $brand, $category, $image, $description, $barcode, $status, $supplier;
    public $supplier_price, $selling_price, $discount_price, $available_stock, $damage_stock;

    // Import file
    public $importFile;

    // Edit form fields
    public $editId, $editCode, $editName, $editModel, $editBrand, $editCategory, $editImage, $existingImage,
        $editDescription, $editBarcode, $editStatus, $editSupplierPrice, $editSellingPrice,
        $editDiscountPrice, $editDamageStock;

    // Stock Adjustment fields
    public $adjustmentProductId, $adjustmentProductName, $adjustmentAvailableStock, $adjustmentDamageStock,
        $damageQuantity, $availableQuantity;

    // View Product
    public $viewProduct;

    // History fields
    public $historyProductId, $historyProductName, $historyTab = 'sales';
    public $salesHistory = [], $purchasesHistory = [], $returnsHistory = [], $quotationsHistory = [];

    // Default IDs for brand, category, and supplier
    public $defaultBrandId, $defaultCategoryId, $defaultSupplierId;
    public $perPage = 10;

    public function mount()
    {
        $this->setDefaultIds();
        $this->setDefaultValues();
    }

    /**
     * Reset component state when pagination changes
     * This fixes the issue where wrong product shows in modal on different pages
     */
    public function updatingSearch()
    {
        $this->resetPage();
    }

    /**
     * Set default IDs for brand, category, and supplier
     */
    private function setDefaultIds()
    {
        // Get or create default brand
        $defaultBrand = BrandList::where('brand_name', 'Default Brand')->first();
        if (!$defaultBrand) {
            $defaultBrand = BrandList::create([
                'brand_name' => 'Default Brand',
                'status' => 'active'
            ]);
        }
        $this->defaultBrandId = $defaultBrand->id;

        // Get or create default category
        $defaultCategory = CategoryList::where('category_name', 'Default Category')->first();
        if (!$defaultCategory) {
            $defaultCategory = CategoryList::create([
                'category_name' => 'Default Category',
                'status' => 'active'
            ]);
        }
        $this->defaultCategoryId = $defaultCategory->id;

        // Get or create default supplier
        $defaultSupplier = ProductSupplier::where('name', 'Default Supplier')->first();
        if (!$defaultSupplier) {
            $defaultSupplier = ProductSupplier::create([
                'name' => 'Default Supplier',
                'phone' => '0000000000',
                'email' => 'default@supplier.com',
                'address' => 'Default Address',
                'status' => 'active'
            ]);
        }
        $this->defaultSupplierId = $defaultSupplier->id;
    }

    /**
     * Set default values for brand, category, and supplier
     */
    private function setDefaultValues()
    {
        // Set default brand
        $this->brand = $this->defaultBrandId;

        // Set default category
        $this->category = $this->defaultCategoryId;

        // Set default supplier
        $this->supplier = $this->defaultSupplierId;

        // Set default status
        $this->status = 'active';

        // Set default stock values
        $this->available_stock = 0;
        $this->damage_stock = 0;

        // Set default prices
        $this->supplier_price = 0;
        $this->selling_price = 0;
        $this->discount_price = 0;
    }

    public function render()
    {
        $brands = BrandList::orderBy('brand_name')->get();
        $categories = CategoryList::orderBy('category_name')->get();
        $suppliers = ProductSupplier::orderBy('name')->get();

        $products = ProductDetail::join('product_prices', 'product_details.id', '=', 'product_prices.product_id')
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
            ->where(function ($query) {
                $query->where('product_details.name', 'like', '%' . $this->search . '%')
                    ->orWhere('product_details.code', 'like', '%' . $this->search . '%')
                    ->orWhere('product_details.model', 'like', '%' . $this->search . '%')
                    ->orWhere('brand_lists.brand_name', 'like', '%' . $this->search . '%')
                    ->orWhere('category_lists.category_name', 'like', '%' . $this->search . '%')
                    ->orWhere('product_details.status', 'like', '%' . $this->search . '%')
                    ->orWhere('product_details.barcode', 'like', '%' . $this->search . '%');
            })
            ->orderByRaw("CASE WHEN product_details.code LIKE 'G-%' THEN 1 ELSE 0 END ASC")
            ->orderBy('product_details.code', 'asc')
            ->paginate($this->perPage);

        return view('livewire.admin.Productes', [
            'products' => $products,
            'brands' => $brands,
            'categories' => $categories,
            'suppliers' => $suppliers,
        ])->layout($this->layout);
    }
    public function updatedPerPage()
    {
        $this->resetPage();
    }

    /**
     * Store product image on public disk while keeping existing URL structure.
     */
    private function storeProductImage($uploadedImage)
    {
        $extension = strtolower($uploadedImage->getClientOriginalExtension() ?: 'jpg');
        $filename = uniqid() . '_' . time() . '.' . $extension;

        $storedPath = $uploadedImage->storeAs('images/ProductImages', $filename, 'public');

        if (!$storedPath) {
            throw new \Exception('Image upload failed. Please check storage permissions.');
        }

        // If public/storage is a real folder (not symlink), keep a synced copy for direct web access.
        $this->syncImageToPublicStorage($storedPath);

        // Keep saved DB path unchanged: storage/images/ProductImages/filename.ext
        return 'storage/' . ltrim($storedPath, '/');
    }

    /**
     * Sync file to public/storage for servers where storage symlink is unavailable.
     */
    private function syncImageToPublicStorage(string $storedPath): void
    {
        try {
            $publicStoragePath = public_path('storage');

            // When symlink exists, disk('public') files are already web-accessible.
            if (is_link($publicStoragePath)) {
                return;
            }

            $relativePath = ltrim(str_replace('\\', '/', $storedPath), '/');
            $source = Storage::disk('public')->path($relativePath);
            $destination = $publicStoragePath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
            $destinationDir = dirname($destination);

            if (!is_dir($destinationDir)) {
                mkdir($destinationDir, 0755, true);
            }

            if (is_file($source)) {
                copy($source, $destination);
            }
        } catch (\Throwable $e) {
            Log::warning('Image public sync skipped: ' . $e->getMessage());
        }
    }

    /**
     * Delete image from public disk when path belongs to local storage.
     */
    private function deleteProductImageIfExists($imagePath)
    {
        if (!$imagePath || !str_starts_with($imagePath, 'storage/')) {
            return;
        }

        $diskPath = ltrim(Str::after($imagePath, 'storage/'), '/');
        if ($diskPath !== '') {
            Storage::disk('public')->delete($diskPath);

            // Also clean up non-symlink public/storage fallback copy if present.
            $publicCopy = public_path('storage/' . str_replace('/', DIRECTORY_SEPARATOR, $diskPath));
            if (is_file($publicCopy)) {
                @unlink($publicCopy);
            }
        }
    }

    // 🔹 Validation Rules for Create
    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:100|unique:product_details,code',
            'model' => 'nullable|string|max:255',
            'brand' => 'required|exists:brand_lists,id',
            'category' => 'required|exists:category_lists,id',
            'supplier' => 'nullable|exists:product_suppliers,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'description' => 'nullable|string|max:1000',
            'barcode' => 'nullable|string|max:255|unique:product_details,barcode',
            'supplier_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0|gte:supplier_price',
            'discount_price' => 'nullable|numeric|min:0|lte:selling_price',
            'available_stock' => 'required|integer|min:0',
            'damage_stock' => 'nullable|integer|min:0',
        ];
    }

    // 🔹 Validation Messages
    protected function messages()
    {
        return [
            'name.required' => 'Product name is required.',
            'name.max' => 'Product name must not exceed 255 characters.',
            'code.required' => 'Product code is required.',
            'code.unique' => 'This product code already exists.',
            'brand.required' => 'Please select a brand.',
            'brand.exists' => 'Selected brand is invalid.',
            'category.required' => 'Please select a category.',
            'category.exists' => 'Selected category is invalid.',
            'supplier_price.required' => 'Supplier price is required.',
            'supplier_price.numeric' => 'Supplier price must be a number.',
            'supplier_price.min' => 'Supplier price cannot be negative.',
            'selling_price.required' => 'Selling price is required.',
            'selling_price.numeric' => 'Selling price must be a number.',
            'selling_price.min' => 'Selling price cannot be negative.',
            'selling_price.gte' => 'Selling price must be greater than or equal to supplier price.',
            'discount_price.lte' => 'Discount price cannot be greater than selling price.',
            'available_stock.required' => 'Available stock is required.',
            'available_stock.integer' => 'Available stock must be a whole number.',
            'available_stock.min' => 'Available stock cannot be negative.',
            'damage_stock.integer' => 'Damage stock must be a whole number.',
            'damage_stock.min' => 'Damage stock cannot be negative.',
            'image.image' => 'The file must be an image.',
            'image.mimes' => 'Accepted formats: jpeg, png, jpg, gif, webp.',
            'image.max' => 'Image size must not exceed 2MB.',
            'editImage.image' => 'The file must be an image.',
            'editImage.mimes' => 'Accepted formats: jpeg, png, jpg, gif, webp.',
            'editImage.max' => 'Image size must not exceed 2MB.',
            'barcode.unique' => 'This barcode already exists.',
        ];
    }

    // 🔹 Open Create Modal
    public function openCreateModal()
    {
        $this->resetForm();
        $this->resetValidation();

        // Set default values (like walking customer in sales system)
        $this->setDefaultValues();

        $this->js("$('#createProductModal').modal('show')");
    }

    // 🔹 Create Product
    public function createProduct()
    {
        // Validate the form data
        $validatedData = $this->validate();

        try {
            // Generate product code if not provided
            $productCode = $this->code ?: 'PROD-' . strtoupper(Str::random(8));

            // Handle image upload
            $imagePath = null;
            if ($this->image) {
                $imagePath = $this->storeProductImage($this->image);
            }

            $product = ProductDetail::create([
                'code' => $productCode,
                'name' => $this->name,
                'model' => $this->model,
                'image' => $imagePath,
                'description' => $this->description,
                'barcode' => $this->barcode,
                'status' => 'active',
                'brand_id' => $this->brand,
                'category_id' => $this->category,
            ]);

            ProductPrice::create([
                'product_id' => $product->id,
                'supplier_price' => $this->supplier_price,
                'selling_price' => $this->selling_price,
                'discount_price' => $this->discount_price,
            ]);

            ProductStock::create([
                'product_id' => $product->id,
                'available_stock' => $this->available_stock ?? 0,
                'damage_stock' => $this->damage_stock ?? 0,
                'total_stock' => ($this->available_stock ?? 0) + ($this->damage_stock ?? 0),
                'sold_count' => 0,
                'restocked_quantity' => 0,
            ]);

            $this->resetForm();
            $this->js("$('#createProductModal').modal('hide')");
            $this->js("Swal.fire('Success!', 'Product created successfully!', 'success')");

            // Clear cache for client-side refresh
            ProductApiController::clearCache();

            $this->dispatch('refreshPage');
        } catch (\Exception $e) {
            Log::error("Create product failed: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            $this->js("Swal.fire('Error!', 'Failed to create product. Please try again.', 'error')");
        }
    }

    // 🔹 Import Products from Excel
    public function importProducts()
    {
        // Validate file
        $this->validate([
            'importFile' => 'required|mimes:xlsx,xls,csv|max:10240', // Max 10MB
        ], [
            'importFile.required' => 'Please select an Excel file to import.',
            'importFile.mimes' => 'File must be an Excel file (xlsx, xls, or csv).',
            'importFile.max' => 'File size must not exceed 10MB.',
        ]);

        try {
            // Create import instance
            $import = new ProductsImport();

            // Import the file
            Excel::import($import, $this->importFile->getRealPath());

            // Get import statistics
            $successCount = $import->getSuccessCount();
            $skipCount = $import->getSkipCount();
            $failures = $import->failures();

            // Build success message
            $message = "Import completed! ";
            $message .= "✅ {$successCount} product(s) imported successfully. ";

            if ($skipCount > 0) {
                $message .= "⚠️ {$skipCount} product(s) skipped (duplicates or errors). ";
            }

            // Reset file input
            $this->reset(['importFile']);

            // Clear cache for client-side refresh
            ProductApiController::clearCache();

            // Close modal and show success
            $this->js("$('#importProductsModal').modal('hide')");
            $this->js("Swal.fire('Import Complete!', '{$message}', 'success')");

            $this->dispatch('refreshPage');
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errorMessage = "Import failed due to validation errors: <br>";

            foreach ($failures as $failure) {
                $errorMessage .= "Row {$failure->row()}: " . implode(', ', $failure->errors()) . "<br>";
            }

            $this->js("Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                html: '{$errorMessage}',
                confirmButtonText: 'OK'
            })");
        } catch (\Exception $e) {
            $this->js("Swal.fire('Error!', 'Failed to import products: {$e->getMessage()}', 'error')");
        }
    }

    // 🔹 Download Excel Template
    public function downloadTemplate()
    {
        return Excel::download(new ProductsTemplateExport(), 'products_import_template.xlsx');
    }

    // 🔹 Export Products to Excel
    public function exportProducts()
    {
        try {
            // Store search parameter in session for the download
            session(['products_export_search' => $this->search]);

            // Use JavaScript to navigate directly to export URL
            $this->js("window.location.href = '/admin/products/export'");
        } catch (\Exception $e) {
            // Log the error
            \Illuminate\Support\Facades\Log::error('Export preparation failed: ' . $e->getMessage());

            // Show error message to user
            $this->js("Swal.fire('Error!', 'Failed to prepare export: {$e->getMessage()}', 'error')");
        }
    }

    // 🔹 Reset form fields
    private function resetForm()
    {
        $this->reset([
            'code',
            'name',
            'model',
            'brand',
            'category',
            'image',
            'editImage',
            'existingImage',
            'description',
            'barcode',
            'status',
            'supplier',
            'supplier_price',
            'selling_price',
            'discount_price',
            'available_stock',
            'damage_stock'
        ]);
        $this->resetValidation();
    }

    // 🔹 Edit Product
    public function editProduct($id)
    {
        $product = ProductDetail::with(['price', 'stock'])->findOrFail($id);

        $this->editId = $product->id;
        $this->editCode = $product->code;
        $this->editName = $product->name;
        $this->editModel = $product->model;
        $this->editBrand = $product->brand_id;
        $this->editCategory = $product->category_id;
        $this->existingImage = $product->image;
        $this->editDescription = $product->description;
        $this->editBarcode = $product->barcode;
        $this->editStatus = $product->status;
        $this->editSupplierPrice = $product->price->supplier_price ?? 0;
        $this->editSellingPrice = $product->price->selling_price ?? 0;
        $this->editDiscountPrice = $product->price->discount_price ?? 0;
        $this->editDamageStock = $product->stock->damage_stock ?? 0;

        $this->resetValidation();

        $this->js("
            setTimeout(() => {
                const modal = new bootstrap.Modal(document.getElementById('editProductModal'));
                modal.show();
            }, 100);
        ");
    }

    // 🔹 Validation Rules for Update
    protected function updateRules()
    {
        return [
            'editName' => 'required|string|max:255',
            'editCode' => 'required|string|max:100|unique:product_details,code,' . $this->editId,
            'editModel' => 'nullable|string|max:255',
            'editBrand' => 'required|exists:brand_lists,id',
            'editCategory' => 'required|exists:category_lists,id',
            'editImage' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'editDescription' => 'nullable|string|max:1000',
            'editBarcode' => 'nullable|string|max:255|unique:product_details,barcode,' . $this->editId,
            'editStatus' => 'required|in:active,inactive',
            'editSupplierPrice' => 'required|numeric|min:0',
            'editSellingPrice' => 'required|numeric|min:0|gte:editSupplierPrice',
            'editDiscountPrice' => 'nullable|numeric|min:0|lte:editSellingPrice',
            'editDamageStock' => 'required|integer|min:0',
        ];
    }

    // 🔹 Update Product
    public function updateProduct()
    {
        // Validate the form data
        $validatedData = $this->validate($this->updateRules());

        try {
            $product = ProductDetail::findOrFail($this->editId);

            // Handle image upload
            $imagePath = $this->existingImage;
            if ($this->editImage) {
                $this->deleteProductImageIfExists($this->existingImage);
                $imagePath = $this->storeProductImage($this->editImage);
            }

            $product->update([
                'code' => $this->editCode,
                'name' => $this->editName,
                'model' => $this->editModel,
                'brand_id' => $this->editBrand,
                'category_id' => $this->editCategory,
                'image' => $imagePath,
                'description' => $this->editDescription,
                'barcode' => $this->editBarcode,
                'status' => $this->editStatus,
            ]);

            $product->price()->updateOrCreate(
                ['product_id' => $product->id],
                [
                    'supplier_price' => $this->editSupplierPrice,
                    'selling_price' => $this->editSellingPrice,
                    'discount_price' => $this->editDiscountPrice,
                ]
            );

            $product->stock()->updateOrCreate(
                ['product_id' => $product->id],
                [
                    'damage_stock' => $this->editDamageStock,
                ]
            );

            // Clear cache for client-side refresh
            ProductApiController::clearCache();

            $this->js("$('#editProductModal').modal('hide')");
            $this->js("Swal.fire('Success!', 'Product updated successfully!', 'success')");
            $this->dispatch('refreshPage');
        } catch (\Exception $e) {
            Log::error("Update product failed: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            $this->js("Swal.fire('Error!', 'Failed to update product. Please try again.', 'error')");
        }
    }

    // 🔹 Confirm Delete Product
    public function confirmDeleteProduct($id)
    {
        $this->js("
            Swal.fire({
                title: 'Are you sure?',
                text: 'You won\\'t be able to revert this!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    \$wire.deleteProduct($id);
                }
            });
        ");
    }

    // 🔹 Delete Product
    public function deleteProduct($id)
    {
        try {
            $product = ProductDetail::findOrFail($id);

            // Check if product is being used in sales items
            $salesItemCount = SaleItem::where('product_id', $id)->count();

            if ($salesItemCount > 0) {
                $this->js("Swal.fire('Warning!', 'This product is being used in {$salesItemCount} sale(s). You cannot delete it while it\\'s in use.', 'warning')");
                return;
            }

            // Delete related records first
            ProductPrice::where('product_id', $id)->delete();
            ProductStock::where('product_id', $id)->delete();

            // Delete image if exists
            $this->deleteProductImageIfExists($product->image);

            // Delete the product
            $product->delete();

            // Clear cache for client-side refresh
            ProductApiController::clearCache();

            $this->js("Swal.fire('Success!', 'Product deleted successfully!', 'success')");
            $this->dispatch('refreshPage');
        } catch (\Exception $e) {
            $this->js("Swal.fire('Error!', 'Failed to delete product. Please try again.', 'error')");
        }
    }

    // 🔹 View Product Details
    public function viewProductDetails($id)
    {
        $this->viewProduct = ProductDetail::with(['price', 'stock'])
            ->leftJoin('brand_lists', 'product_details.brand_id', '=', 'brand_lists.id')
            ->leftJoin('category_lists', 'product_details.category_id', '=', 'category_lists.id')
            ->select(
                'product_details.*',
                'brand_lists.brand_name as brand',
                'category_lists.category_name as category'
            )
            ->where('product_details.id', $id)
            ->first();

        // Load current batch prices and batch details
        if ($this->viewProduct) {
            $this->viewProduct->currentBatchInfo = \App\Services\FIFOStockService::getCurrentBatchPrices($id);
            $this->viewProduct->allBatches = \App\Services\FIFOStockService::getBatchDetails($id);
        }

        $this->js("$('#viewProductModal').modal('show')");
    }

    // 🔹 Open Stock Adjustment Modal
    public function openStockAdjustment($id)
    {
        $product = ProductDetail::with(['stock'])->findOrFail($id);

        $this->adjustmentProductId = $product->id;
        $this->adjustmentProductName = $product->name;
        $this->adjustmentAvailableStock = $product->stock->available_stock ?? 0;
        $this->adjustmentDamageStock = $product->stock->damage_stock ?? 0;
        $this->damageQuantity = null; // Clear damage input
        $this->availableQuantity = null; // Clear available input

        $this->resetValidation();
        $this->js("$('#stockAdjustmentModal').modal('show')");
    }

    // 🔹 Stock Adjustment Validation Rules
    protected function adjustmentRules()
    {
        return [
            'adjustmentQuantity' => 'required|integer|min:1',
        ];
    }


    // 🔹 Add Damage Stock (Deduct from Available Stock and Batches using FIFO)
    public function addDamageStock()
    {
        $this->validate([
            'damageQuantity' => 'required|integer|min:1',
        ], [
            'damageQuantity.required' => 'Please enter damage quantity.',
            'damageQuantity.min' => 'Damage quantity must be at least 1.',
        ]);

        DB::beginTransaction();
        try {
            $product = ProductDetail::with(['stock', 'price'])->findOrFail($this->adjustmentProductId);
            $stock = $product->stock;

            if (!$stock) {
                $stock = ProductStock::create([
                    'product_id' => $product->id,
                    'available_stock' => 0,
                    'damage_stock' => 0,
                    'total_stock' => 0,
                    'sold_count' => 0,
                ]);
            }

            $damageQty = (int)$this->damageQuantity;
            $currentAvailable = $stock->available_stock;
            $currentDamage = $stock->damage_stock;

            // 🔹 Deduct from batches using FIFO (First In, First Out)
            // Include default batch which is always active
            $remainingDamage = $damageQty;

            $batches = ProductBatch::where('product_id', $product->id)
                ->where('status', 'active')
                ->where('remaining_quantity', '>', 0)
                ->orderBy('received_date', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            // If no batches with stock, ensure default batch exists and use it
            if ($batches->isEmpty()) {
                $defaultBatch = ProductBatch::getOrCreateDefaultBatch($product->id);

                // If default batch exists, add it to batches
                if ($defaultBatch) {
                    $batches = collect([$defaultBatch]);
                } else {
                    DB::rollBack();
                    $this->js("Swal.fire('Error!', 'No active batches found for this product.', 'error')");
                    return;
                }
            }

            foreach ($batches as $batch) {
                if ($remainingDamage <= 0) break;

                $deductQty = min($remainingDamage, $batch->remaining_quantity);
                $batch->remaining_quantity -= $deductQty;

                // Default batches should never be marked as depleted
                if ($batch->remaining_quantity == 0 && !$batch->isDefaultBatch()) {
                    $batch->status = 'depleted';
                }

                $batch->save();
                $remainingDamage -= $deductQty;

                Log::info("Damage added: Deducted {$deductQty} from batch {$batch->batch_number}");
            }

            // Check if we have enough stock in batches
            if ($remainingDamage > 0) {
                DB::rollBack();
                $availableInBatches = $batches->sum('remaining_quantity');
                $this->js("Swal.fire('Error!', 'Not enough stock in batches! Available: {$availableInBatches}, Required: {$damageQty}', 'error')");
                return;
            }

            // Update stock table
            $newAvailableStock = max(0, $currentAvailable - $damageQty);
            $newDamageStock = $currentDamage + $damageQty;

            $stock->available_stock = $newAvailableStock;
            $stock->damage_stock = $newDamageStock;
            $stock->total_stock = $newAvailableStock + $newDamageStock;
            $stock->save();

            // 🔹 Update product prices based on the oldest active batch with stock
            $oldestActiveBatch = ProductBatch::where('product_id', $product->id)
                ->where('status', 'active')
                ->where('remaining_quantity', '>', 0)
                ->orderBy('received_date', 'asc')
                ->orderBy('id', 'asc')
                ->first();

            if ($oldestActiveBatch && $product->price) {
                // Update the product_prices table with the batch prices
                $product->price->supplier_price = $oldestActiveBatch->supplier_price;
                $product->price->selling_price = $oldestActiveBatch->selling_price;
                $product->price->save();

                Log::info("Prices updated: Supplier={$oldestActiveBatch->supplier_price}, Selling={$oldestActiveBatch->selling_price} from batch {$oldestActiveBatch->batch_number}");
            }

            DB::commit();

            // Clear cache for client-side refresh
            ProductApiController::clearCache();

            // Reset and refresh
            $this->damageQuantity = null;
            $this->adjustmentAvailableStock = $newAvailableStock;
            $this->adjustmentDamageStock = $newDamageStock;

            $this->js("Swal.fire('Success!', 'Damage stock added successfully! {$damageQty} units marked as damaged.', 'success')");
            $this->dispatch('refreshPage');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Add damage stock failed: " . $e->getMessage());
            $this->js("Swal.fire('Error!', 'Failed to add damage stock: " . addslashes($e->getMessage()) . "', 'error')");
        }
    }

    // 🔹 Adjust Available Stock (Increase Stock and Add to Oldest Batch)
    public function adjustAvailableStock()
    {
        $this->validate([
            'availableQuantity' => 'required|integer|min:1',
        ], [
            'availableQuantity.required' => 'Please enter quantity to add.',
            'availableQuantity.min' => 'Quantity must be at least 1.',
        ]);

        DB::beginTransaction();
        try {
            $product = ProductDetail::with(['stock'])->findOrFail($this->adjustmentProductId);
            $stock = $product->stock;

            if (!$stock) {
                $stock = ProductStock::create([
                    'product_id' => $product->id,
                    'available_stock' => 0,
                    'damage_stock' => 0,
                    'total_stock' => 0,
                    'sold_count' => 0,
                ]);
            }

            $addQty = (int)$this->availableQuantity;
            $currentAvailable = $stock->available_stock;

            // 🔹 Always add adjustments to default batch (create if not exists)
            try {
                $defaultBatch = ProductBatch::getOrCreateDefaultBatch($product->id);

                $defaultBatch->remaining_quantity += $addQty;
                $defaultBatch->quantity += $addQty;
                $defaultBatch->save();

                Log::info("Available stock increased: Added {$addQty} to default batch for product #{$product->id}");
            } catch (\Exception $e) {
                Log::error("Failed to add to default batch for product #{$product->id}: " . $e->getMessage());
                throw new \Exception("Failed to adjust stock. Please try again.");
            }

            // Update stock table - only increase available stock
            $newAvailableStock = $currentAvailable + $addQty;

            $stock->available_stock = $newAvailableStock;
            $stock->total_stock = $newAvailableStock + $stock->damage_stock;
            $stock->save();

            DB::commit();

            // Clear cache for client-side refresh
            ProductApiController::clearCache();

            // Reset and refresh
            $this->availableQuantity = null;
            $this->adjustmentAvailableStock = $newAvailableStock;

            $this->js("Swal.fire('Success!', 'Available stock increased successfully! Added {$addQty} units.', 'success')");
            $this->dispatch('refreshPage');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Adjust available stock failed: " . $e->getMessage());
            $this->js("Swal.fire('Error!', 'Failed to adjust available stock: " . addslashes($e->getMessage()) . "', 'error')");
        }
    }

    /**
     * Open Product History Modal
     */
    public function openProductHistory($id)
    {
        try {
            $product = ProductDetail::findOrFail($id);

            $this->historyProductId = $product->id;
            $this->historyProductName = $product->name;

            // Set default tab
            $this->historyTab = 'sales';

            // Load ALL history data at once
            $this->loadSalesHistory();
            $this->loadPurchasesHistory();
            $this->loadReturnsHistory();
            $this->loadQuotationsHistory();

            // Log for debugging
            Log::info('Product History Loaded', [
                'product_id' => $this->historyProductId,
                'sales' => count($this->salesHistory),
                'purchases' => count($this->purchasesHistory),
                'returns' => count($this->returnsHistory),
                'quotations' => count($this->quotationsHistory)
            ]);

            // Show modal using Bootstrap JavaScript
            $this->js("
                setTimeout(() => {
                    const modal = new bootstrap.Modal(document.getElementById('productHistoryModal'));
                    modal.show();
                }, 100);
            ");
        } catch (\Exception $e) {
            $this->js("Swal.fire('Error!', 'Failed to load product history: " . addslashes($e->getMessage()) . "', 'error')");
        }
    }

    /**
     * Switch History Tab
     */
    public function switchHistoryTab($tab)
    {
        // Validate tab name
        $validTabs = ['sales', 'purchases', 'returns', 'quotations'];
        if (!in_array($tab, $validTabs)) {
            $tab = 'sales';
        }

        // Simply update the active tab
        $this->historyTab = $tab;

        // Log for debugging
        Log::info('Tab switched', [
            'tab' => $tab,
            'sales_count' => count($this->salesHistory),
            'purchases_count' => count($this->purchasesHistory),
            'returns_count' => count($this->returnsHistory),
            'quotations_count' => count($this->quotationsHistory)
        ]);

        // Dispatch event for debugging
        $this->dispatch('historyTabSwitched', ['tab' => $tab]);
    }

    /**
     * Load Sales History
     */
    private function loadSalesHistory()
    {
        try {
            $salesItems = SaleItem::with(['sale.customer', 'sale.user'])
                ->where('sale_items.product_id', $this->historyProductId)
                ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->select(
                    'sale_items.*',
                    'sales.invoice_number',
                    'sales.sale_type',
                    'sales.customer_type',
                    'sales.payment_type',
                    'sales.payment_status',
                    'sales.status as sale_status',
                    'sales.created_at as sale_date'
                )
                ->orderBy('sales.created_at', 'desc')
                ->get();

            $this->salesHistory = $salesItems->map(function ($sale) {
                return [
                    'id' => $sale->id,
                    'invoice_number' => $sale->invoice_number,
                    'sale_type' => $sale->sale_type ?? 'regular',
                    'customer_type' => $sale->customer_type ?? 'walk-in',
                    'quantity' => $sale->quantity,
                    'unit_price' => $sale->unit_price,
                    'discount_per_unit' => $sale->discount_per_unit ?? 0,
                    'total_discount' => $sale->total_discount ?? 0,
                    'total' => $sale->total,
                    'payment_type' => $sale->payment_type ?? 'cash',
                    'payment_status' => $sale->payment_status ?? 'unpaid',
                    'sale_status' => $sale->sale_status ?? 'completed',
                    'sale_date' => $sale->sale_date,
                    'customer_name' => $sale->sale && $sale->sale->customer ? $sale->sale->customer->name : 'Walk-in Customer',
                    'customer_phone' => $sale->sale && $sale->sale->customer ? $sale->sale->customer->phone : 'N/A',
                    'user_name' => $sale->sale && $sale->sale->user ? $sale->sale->user->name : 'N/A'
                ];
            })->toArray();
            // dd(array_column($this->salesHistory, 'sale_status'));

        } catch (\Exception $e) {
            $this->salesHistory = [];
        }
    }

    /**
     * Load Purchases History
     */
    private function loadPurchasesHistory()
    {
        try {
            $purchaseItems = PurchaseOrderItem::with(['order.supplier'])
                ->where('purchase_order_items.product_id', $this->historyProductId)
                ->join('purchase_orders', 'purchase_order_items.order_id', '=', 'purchase_orders.id')
                ->select(
                    'purchase_order_items.*',
                    'purchase_orders.order_code',
                    'purchase_orders.order_date',
                    'purchase_orders.received_date',
                    'purchase_orders.status as order_status'
                )
                ->orderBy('purchase_orders.order_date', 'desc')
                ->get();

            $this->purchasesHistory = $purchaseItems->map(function ($purchase) {
                $subtotal = $purchase->received_quantity * $purchase->unit_price;
                $discountAmount = 0;

                // Calculate discount as percentage
                if (isset($purchase->discount) && $purchase->discount > 0) {
                    $discountAmount = ($subtotal * $purchase->discount) / 100;
                }

                $total = $subtotal - $discountAmount;

                return [
                    'id' => $purchase->id,
                    'order_code' => $purchase->order_code,
                    'order_date' => $purchase->order_date,
                    'received_date' => $purchase->received_date ?? 'Pending',
                    'quantity' => $purchase->quantity,
                    'received_quantity' => $purchase->received_quantity,
                    'unit_price' => $purchase->unit_price,
                    'discount' => $purchase->discount ?? 0,
                    'total' => $total,
                    'order_status' => $purchase->order_status ?? 'pending',
                    'supplier_name' => $purchase->order && $purchase->order->supplier ? $purchase->order->supplier->name : 'N/A',
                    'supplier_phone' => $purchase->order && $purchase->order->supplier ? $purchase->order->supplier->phone : 'N/A'
                ];
            })->toArray();
        } catch (\Exception $e) {
            $this->purchasesHistory = [];
        }
    }

    /**
     * Load Returns History
     */
    private function loadReturnsHistory()
    {
        try {
            $returns = ReturnsProduct::with(['sale.customer', 'product'])
                ->where('returns_products.product_id', $this->historyProductId)
                ->join('sales', 'returns_products.sale_id', '=', 'sales.id')
                ->select(
                    'returns_products.*',
                    'sales.invoice_number'
                )
                ->orderBy('returns_products.created_at', 'desc')
                ->get();

            $this->returnsHistory = $returns->map(function ($return) {
                return [
                    'id' => $return->id,
                    'invoice_number' => $return->invoice_number,
                    'return_quantity' => $return->return_quantity,
                    'selling_price' => $return->selling_price ?? 0,
                    'total_amount' => $return->total_amount ?? 0,
                    'notes' => $return->notes ?? 'No notes provided',
                    'return_date' => $return->created_at,
                    'customer_name' => $return->sale && $return->sale->customer ? $return->sale->customer->name : 'Walk-in Customer',
                    'customer_phone' => $return->sale && $return->sale->customer ? $return->sale->customer->phone : 'N/A'
                ];
            })->toArray();
        } catch (\Exception $e) {
            $this->returnsHistory = [];
        }
    }

    /**
     * Load Quotations History
     */
    private function loadQuotationsHistory()
    {
        try {
            $quotations = Quotation::with(['creator', 'customer'])
                ->where('status', '!=', 'draft')
                ->orderBy('quotation_date', 'desc')
                ->get();

            $this->quotationsHistory = [];

            foreach ($quotations as $quotation) {
                $items = is_array($quotation->items) ? $quotation->items : json_decode($quotation->items, true);

                if (!empty($items)) {
                    foreach ($items as $item) {
                        if (isset($item['product_id']) && $item['product_id'] == $this->historyProductId) {
                            $this->quotationsHistory[] = [
                                'id' => $quotation->id,
                                'quotation_number' => $quotation->quotation_number,
                                'reference_number' => $quotation->reference_number ?? 'N/A',
                                'customer_name' => $quotation->customer_name ?? ($quotation->customer->name ?? 'N/A'),
                                'customer_phone' => $quotation->customer_phone ?? ($quotation->customer->phone ?? 'N/A'),
                                'customer_email' => $quotation->customer_email ?? 'N/A',
                                'quotation_date' => $quotation->quotation_date,
                                'valid_until' => $quotation->valid_until,
                                'status' => $quotation->status,
                                'quantity' => $item['quantity'] ?? 0,
                                'unit_price' => $item['unit_price'] ?? 0,
                                'discount' => $item['discount'] ?? 0,
                                'total' => $item['total'] ?? 0,
                                'product_name' => $item['product_name'] ?? 'N/A',
                                'product_code' => $item['product_code'] ?? 'N/A',
                                'created_by_name' => $quotation->creator->name ?? 'N/A'
                            ];
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $this->quotationsHistory = [];
            // Log error for debugging
        }
    }

    // 🔹 Real-time validation for specific fields
    public function updated($propertyName)
    {
        // Clear view/edit state when page changes to fix modal showing wrong product
        if ($propertyName === 'page' || $propertyName === 'search') {
            $this->viewProduct = null;
            $this->editId = null;
            $this->historyProductId = null;
            $this->adjustmentProductId = null;
        }

        // Only validate specific fields in real-time to improve performance
        if (in_array($propertyName, [
            'name',
            'code',
            'brand',
            'category',
            'supplier_price',
            'selling_price',
            'available_stock',
            'editName',
            'editCode',
            'editBrand',
            'editCategory',
            'editSupplierPrice',
            'editSellingPrice',
            'damageQuantity',
            'availableQuantity'
        ])) {
            $this->validateOnly($propertyName);
        }
    }
}
