<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Quotation;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Customer;
use App\Models\ProductDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Livewire\Concerns\WithDynamicLayout;
use Barryvdh\DomPDF\Facade\Pdf;

#[\Livewire\Attributes\Title('Quotation Management')]
class QuotationList extends Component
{
    use WithDynamicLayout;
    use WithPagination;
    // Do not store paginator in a public property (Livewire cannot serialize it)
    public $quotationsCount = 0;
    public $search = '';
    public $selectedQuotation = null;
    public $createSaleModal = false;
    public $editableItems = [];
    public $saleData = [
        'notes' => '',
        'additional_discount' => 0,
        'additional_discount_type' => 'fixed',
        'additional_discount_amount' => 0
    ];

    // Public properties for calculations
    public $totalAmount = 0;
    public $totalDiscount = 0;
    public $subtotal = 0;
    public $grandTotal = 0;
    public $additionalDiscountAmount = '';

    public $quotationToDelete = null;
    public $searchTerms = [];
    public $showSearchResults = [];
    public $perPage = 10;

    public function mount()
    {
        // initial count only; the paginated list is returned from render()
        $this->loadQuotations();
    }

    public function loadQuotations()
    {
        // Build base query and update only a lightweight property (count)
        $query = Quotation::with('customer');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('quotation_number', 'like', '%' . $this->search . '%')
                    ->orWhere('customer_name', 'like', '%' . $this->search . '%')
                    ->orWhere('customer_phone', 'like', '%' . $this->search . '%');
            });
        }

        // Keep a simple count for lightweight state (avoids serializing paginator)
        $this->quotationsCount = $query->count();
    }

    public function updatedSearch()
    {
        // Reset to first page when search changes and refresh lightweight data
        $this->resetPage();
        $this->loadQuotations();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function viewQuotation($id)
    {
        $this->selectedQuotation = Quotation::find($id);

        if ($this->selectedQuotation && is_string($this->selectedQuotation->items)) {
            $this->selectedQuotation->items = json_decode($this->selectedQuotation->items, true);
        }

        $this->dispatch('showViewModal');
    }

    public function openCreateSaleModal($quotationId)
    {
        $this->selectedQuotation = Quotation::find($quotationId);

        if ($this->selectedQuotation) {
            // Decode items if stored as JSON
            $items = $this->selectedQuotation->items;
            if (is_string($items)) {
                $items = json_decode($items, true);
            }

            // Initialize editable items with quotation data and current stock
            $this->editableItems = collect($items)->map(function ($item) {
                $product = ProductDetail::find($item['product_id']);
                $currentStock = $product->stock->available_stock ?? 0;
                $discountPrice = $product->price->discount_price ?? 0;

                return [
                    'product_id' => $item['product_id'] ?? null,
                    'product_name' => $item['product_name'] ?? $item['name'] ?? 'N/A',
                    'product_code' => $item['product_code'] ?? '',
                    'product_model' => $item['product_model'] ?? '',
                    'quantity' => $item['quantity'] ?? 1,
                    'unit_price' => $item['unit_price'] ?? 0,
                    'discount_per_unit' => $discountPrice,
                    'total_discount' => $discountPrice * ($item['quantity'] ?? 1),
                    'total' => (($item['unit_price'] ?? 0) - $discountPrice) * ($item['quantity'] ?? 1),
                    'current_stock' => $currentStock,
                    'original_quantity' => $item['quantity'] ?? 1
                ];
            })->toArray();

            // Initialize search terms and search results arrays
            $this->searchTerms = array_fill(0, count($this->editableItems), '');
            $this->showSearchResults = array_fill(0, count($this->editableItems), false);

            // Calculate initial totals to get subtotal first
            $this->calculateInitialTotals();

            // Fetch additional discount data from quotation
            $additionalDiscountValue = $this->selectedQuotation->additional_discount_value ?? 0;
            $additionalDiscountType = $this->selectedQuotation->additional_discount_type ?? 'fixed';
            $additionalDiscountAmount = $this->selectedQuotation->additional_discount ?? 0;

            // Set the discount data
            $this->saleData['additional_discount'] = $additionalDiscountValue;
            $this->saleData['additional_discount_type'] = $additionalDiscountType;
            $this->saleData['additional_discount_amount'] = $additionalDiscountAmount;

            // Recalculate totals with proper discount amounts
            $this->calculateTotals();

            $this->saleData['notes'] = "Created from Quotation #" . $this->selectedQuotation->quotation_number;

            $this->createSaleModal = true;
            $this->dispatch('showCreateSaleModal');
        }
    }

    // Calculate initial totals without discount for proper initialization
    private function calculateInitialTotals()
    {
        foreach ($this->editableItems as $index => $item) {
            $quantity = $item['quantity'] ?? 1;
            $unitPrice = $item['unit_price'] ?? 0;
            $discountPerUnit = $item['discount_per_unit'] ?? 0;

            $totalDiscount = $discountPerUnit * $quantity;
            $total = ($unitPrice * $quantity) - $totalDiscount;

            $this->editableItems[$index]['total_discount'] = $totalDiscount;
            $this->editableItems[$index]['total'] = max(0, $total);
        }

        $this->totalDiscount = collect($this->editableItems)->sum('total_discount');
        $this->totalAmount = collect($this->editableItems)->sum('total');
        $this->subtotal = $this->totalAmount + $this->totalDiscount;
    }

    public function closeCreateSaleModal()
    {
        $this->createSaleModal = false;
        $this->editableItems = [];
        $this->searchTerms = [];
        $this->showSearchResults = [];
        $this->saleData = [
            'notes' => '',
            'additional_discount' => 0,
            'additional_discount_type' => 'fixed',
            'additional_discount_amount' => 0
        ];
        $this->selectedQuotation = null;
        $this->totalAmount = 0;
        $this->totalDiscount = 0;
        $this->subtotal = 0;
        $this->grandTotal = 0;
        $this->additionalDiscountAmount = 0;
    }

    // Calculate all totals
    public function calculateTotals()
    {
        // Calculate item-level totals first
        foreach ($this->editableItems as $index => $item) {
            $quantity = $item['quantity'] ?? 1;
            $unitPrice = $item['unit_price'] ?? 0;
            $discountPerUnit = $item['discount_per_unit'] ?? 0;

            $totalDiscount = $discountPerUnit * $quantity;
            $total = ($unitPrice * $quantity) - $totalDiscount;

            $this->editableItems[$index]['total_discount'] = $totalDiscount;
            $this->editableItems[$index]['total'] = max(0, $total);
        }

        // Calculate summary totals
        $this->totalDiscount = collect($this->editableItems)->sum('total_discount');
        $this->totalAmount = collect($this->editableItems)->sum('total');
        $this->subtotal = $this->totalAmount + $this->totalDiscount;

        // Calculate additional discount
        $this->calculateAdditionalDiscount();

        // Total discount = items discount + additional discount
        $totalCombinedDiscount = $this->totalDiscount + $this->additionalDiscountAmount;

        // Grand total = subtotal - total combined discount
        $this->grandTotal = $this->subtotal - $totalCombinedDiscount;
    }

    // Calculate additional discount
    private function calculateAdditionalDiscount()
    {
        // If we have a direct amount set, use that
        if (isset($this->saleData['additional_discount_amount']) && $this->saleData['additional_discount_amount'] > 0) {
            $this->additionalDiscountAmount = min($this->saleData['additional_discount_amount'], $this->subtotal);
            return;
        }

        // Otherwise calculate from percentage/fixed
        $additionalDiscount = floatval($this->saleData['additional_discount'] ?? 0);

        if ($additionalDiscount <= 0) {
            $this->additionalDiscountAmount = 0;
            return;
        }

        if ($this->saleData['additional_discount_type'] === 'percentage') {
            $this->additionalDiscountAmount = ($this->subtotal * $additionalDiscount) / 100;
        } else {
            $this->additionalDiscountAmount = min($additionalDiscount, $this->subtotal);
        }
    }

    // Update discount type and recalculate
    public function updatedSaleDataAdditionalDiscountType()
    {
        $this->saleData['additional_discount'] = 0;
        $this->saleData['additional_discount_amount'] = 0;
        $this->calculateTotals();
    }

    // Update additional discount with validation
    public function updatedSaleDataAdditionalDiscount($value)
    {
        if ($value === '') {
            $this->saleData['additional_discount'] = 0;
        } else {
            $value = floatval($value);
            if ($value < 0) {
                $this->saleData['additional_discount'] = 0;
            } elseif ($this->saleData['additional_discount_type'] === 'percentage' && $value > 100) {
                $this->saleData['additional_discount'] = 100;
            } else {
                $this->saleData['additional_discount'] = $value;
            }
        }

        // Update the direct discount amount when percentage/fixed changes
        if ($this->saleData['additional_discount_type'] === 'percentage') {
            $this->saleData['additional_discount_amount'] = ($this->subtotal * $this->saleData['additional_discount']) / 100;
        } else {
            $this->saleData['additional_discount_amount'] = $this->saleData['additional_discount'];
        }

        $this->calculateTotals();
    }

    // Update additional discount amount directly
    public function updateAdditionalDiscountAmount($amount)
    {
        $amount = floatval($amount);

        if ($amount < 0) {
            $amount = 0;
        }

        // Ensure discount doesn't exceed subtotal
        if ($amount > $this->subtotal) {
            $amount = $this->subtotal;
        }

        $this->saleData['additional_discount_amount'] = $amount;

        // If we're in percentage mode, calculate what percentage this amount represents
        if ($this->saleData['additional_discount_type'] === 'percentage' && $this->subtotal > 0) {
            $percentage = ($amount / $this->subtotal) * 100;
            $this->saleData['additional_discount'] = min(100, $percentage);
        } else {
            // In fixed mode, just set the discount amount
            $this->saleData['additional_discount'] = $amount;
        }

        $this->calculateTotals();
    }

    // Update item quantity with stock validation
    public function updateItemQuantity($index, $quantity)
    {
        if (isset($this->editableItems[$index])) {
            $quantity = max(1, intval($quantity));
            $maxStock = $this->editableItems[$index]['current_stock'];

            if ($quantity > $maxStock) {
                $this->dispatch('show-error', "Not enough stock! Maximum available: {$maxStock}");
                $quantity = $maxStock;
            }

            $this->editableItems[$index]['quantity'] = $quantity;
            $this->calculateTotals();
        }
    }

    // Update discount per unit
    public function updateItemDiscount($index, $discount)
    {
        if (isset($this->editableItems[$index])) {
            $this->editableItems[$index]['discount_per_unit'] = max(0, floatval($discount));
            $this->calculateTotals();
        }
    }

    // Add new empty item
    public function addNewItem()
    {
        $this->editableItems[] = [
            'product_id' => null,
            'product_name' => '',
            'product_code' => '',
            'product_model' => '',
            'quantity' => 1,
            'unit_price' => 0,
            'discount_per_unit' => 0,
            'total_discount' => 0,
            'total' => 0,
            'current_stock' => 0,
            'original_quantity' => 1
        ];

        $this->searchTerms[] = '';
        $this->showSearchResults[] = false;
        $this->calculateTotals();
    }

    // Remove item
    public function removeItem($index)
    {
        if (isset($this->editableItems[$index]) && count($this->editableItems) > 1) {
            unset($this->editableItems[$index]);
            unset($this->searchTerms[$index]);
            unset($this->showSearchResults[$index]);

            $this->editableItems = array_values($this->editableItems);
            $this->searchTerms = array_values($this->searchTerms);
            $this->showSearchResults = array_values($this->showSearchResults);

            $this->calculateTotals();
        } else {
            $this->dispatch('show-error', 'Cannot remove the only item. Sale must have at least one product.');
        }
    }

    // Search products
    public function searchProducts($searchTerm)
    {
        if (strlen($searchTerm) >= 2) {
            return ProductDetail::with(['stock', 'price'])
                ->where('name', 'like', '%' . $searchTerm . '%')
                ->orWhere('code', 'like', '%' . $searchTerm . '%')
                ->orWhere('model', 'like', '%' . $searchTerm . '%')
                ->take(10)
                ->get()
                ->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'code' => $product->code,
                        'model' => $product->model,
                        'price' => $product->price->selling_price ?? 0,
                        'discount_price' => $product->price->discount_price ?? 0,
                        'stock' => $product->stock->available_stock ?? 0
                    ];
                })
                ->toArray();
        }
        return [];
    }

    // Show search results for specific index
    public function showSearchResult($index, $searchTerm)
    {
        if (strlen($searchTerm) >= 2) {
            $this->showSearchResults[$index] = true;
            $this->searchTerms[$index] = $searchTerm;
        } else {
            $this->showSearchResults[$index] = false;
        }
    }

    // Select product for item
    public function selectProduct($index, $productId)
    {
        $product = ProductDetail::with(['stock', 'price'])->find($productId);

        if ($product && isset($this->editableItems[$index])) {
            $this->editableItems[$index]['product_id'] = $product->id;
            $this->editableItems[$index]['product_name'] = $product->name;
            $this->editableItems[$index]['product_code'] = $product->code;
            $this->editableItems[$index]['product_model'] = $product->model;
            $this->editableItems[$index]['unit_price'] = $product->price->selling_price ?? 0;
            $this->editableItems[$index]['discount_per_unit'] = $product->price->discount_price ?? 0;
            $this->editableItems[$index]['current_stock'] = $product->stock->available_stock ?? 0;

            // Clear search term and hide results for this index
            $this->searchTerms[$index] = '';
            $this->showSearchResults[$index] = false;

            $this->calculateTotals();
            $this->dispatch('product-selected');
        }
    }

    public function createSale()
    {
        // Validate items
        if (empty($this->editableItems)) {
            $this->dispatch('show-error', 'Please add at least one product to the sale.');
            return;
        }

        // Validate all items have products selected
        foreach ($this->editableItems as $index => $item) {
            if (!$item['product_id']) {
                $this->dispatch('show-error', "Please select a product for item #" . ($index + 1));
                return;
            }

            if ($item['quantity'] > $item['current_stock']) {
                $this->dispatch('show-error', "Not enough stock for {$item['product_name']}. Available: {$item['current_stock']}");
                return;
            }
        }

        try {
            DB::transaction(function () {
                // Find or create customer
                $customer = Customer::where('phone', $this->selectedQuotation->customer_phone)->first();

                if (!$customer) {
                    $customer = Customer::create([
                        'name' => $this->selectedQuotation->customer_name,
                        'phone' => $this->selectedQuotation->customer_phone,
                        'email' => $this->selectedQuotation->customer_email,
                        'address' => $this->selectedQuotation->customer_address,
                        'type' => $this->selectedQuotation->customer_type,
                    ]);
                }

                // Total discount = items discount + additional discount
                $totalCombinedDiscount = $this->totalDiscount + $this->additionalDiscountAmount;

                // Create sale
                $sale = Sale::create([
                    'sale_id' => Sale::generateSaleId(),
                    'invoice_number' => Sale::generateInvoiceNumber(),
                    'customer_id' => $customer->id,
                    'customer_type' => $customer->type,
                    'subtotal' => $this->subtotal,
                    'discount_amount' => $totalCombinedDiscount,
                    'total_amount' => $this->grandTotal,
                    'payment_type' => 'full',
                    'payment_status' => 'pending',
                    'due_amount' => $this->grandTotal,
                    'notes' => $this->saleData['notes'],
                    'user_id' => Auth::id(),
                    'status' => 'confirm',
                    'sale_type' => 'admin'
                ]);

                // Create sale items and update stock
                foreach ($this->editableItems as $item) {
                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $item['product_id'],
                        'product_code' => $item['product_code'],
                        'product_name' => $item['product_name'],
                        'product_model' => $item['product_model'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'discount_per_unit' => $item['discount_per_unit'],
                        'total_discount' => $item['total_discount'],
                        'total' => $item['total']
                    ]);

                    // Update product stock
                    $product = ProductDetail::find($item['product_id']);
                    if ($product && $product->stock) {
                        $product->stock->available_stock -= $item['quantity'];
                        $product->stock->save();
                    }
                }

                // Mark quotation as converted to sale
                $this->selectedQuotation->update([
                    'status' => 'converted',
                    'converted_at' => now()
                ]);

                $this->dispatch('show-success', 'Sale created successfully from quotation!');

                $this->loadQuotations();
                $this->dispatch('refreshPage');
                // close model using js

                $this->dispatch('close-modal.create-sale-modal');
                $this->js('window.location.reload();');
            });
        } catch (\Exception $e) {
            $this->dispatch('show-error', 'Failed to create sale: ' . $e->getMessage());
        }
        $this->dispatch('refreshPage');
    }

    public function confirmDeleteQuotation($id)
    {
        $this->quotationToDelete = $id;
        $this->dispatch('show-delete-confirmation');
    }

    public function deleteQuotation()
    {
        try {
            if (!$this->quotationToDelete) {
                $this->dispatch('show-error', 'No quotation selected for deletion!');
                return;
            }

            $quotation = Quotation::find($this->quotationToDelete);

            if ($quotation) {
                $quotationNumber = $quotation->quotation_number;

                // Check if quotation is converted to sale
                if ($quotation->status === 'converted') {
                    $this->dispatch('show-error', "Cannot delete quotation #{$quotationNumber} because it has been converted to a sale!");
                    $this->quotationToDelete = null;
                    return;
                }

                $quotation->delete();
                $this->loadQuotations();
                $this->quotationToDelete = null;

                $this->dispatch('show-success', "Quotation #{$quotationNumber} deleted successfully!");
            } else {
                $this->dispatch('show-error', 'Quotation not found!');
            }
        } catch (\Exception $e) {
            $this->dispatch('show-error', 'Failed to delete quotation: ' . $e->getMessage());
        }
    }

    // Download Quotation from View Modal
    public function downloadQuotation()
    {
        if (!$this->selectedQuotation) {
            session()->flash('error', 'No quotation selected.');
            return;
        }

        $quotation = Quotation::find($this->selectedQuotation->id);

        if (!$quotation) {
            session()->flash('error', 'Quotation not found.');
            return;
        }

        $pdf = PDF::loadView('admin.quotations.print', compact('quotation'));

        return response()->streamDownload(
            function () use ($pdf) {
                echo $pdf->output();
            },
            'quotation-' . $quotation->quotation_number . '.pdf'
        );
    }

    public function render()
    {
        // Build the paginated query here instead of storing paginator in public property
        $query = Quotation::with('customer');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('quotation_number', 'like', '%' . $this->search . '%')
                    ->orWhere('customer_name', 'like', '%' . $this->search . '%')
                    ->orWhere('customer_phone', 'like', '%' . $this->search . '%');
            });
        }

        $quotations = $query->orderBy('created_at', 'desc')->paginate($this->perPage);

        return view('livewire.admin.quotation-list', [
            'quotations' => $quotations,
        ])->layout($this->layout);
    }
}
