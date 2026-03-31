<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Models\Customer;
use App\Models\ProductDetail;
use App\Models\Quotation;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Livewire\Concerns\WithDynamicLayout;

#[Title('Create Quotation')]
class QuotationSystem extends Component
{
    use WithDynamicLayout;

    // Basic Properties
    public $search = '';
    public $searchResults = [];
    public $customerId = '';
    public $validUntil;
    public $quotationId = null;
    public $isEditMode = false;

    // Cart Items
    public $cart = [];

    // Customer Properties
    public $customers = [];
    public $selectedCustomer = null;

    // Customer Form (for new customer - only used in modal)
    public $customerName = '';
    public $customerPhone = '';
    public $customerEmail = '';
    public $customerAddress = '';
    public $customerType = 'retail';
    public $businessName = '';

    // Quotation Properties
    public $notes = '';
    public $termsConditions = "1. This quotation is valid for 15 days.\n2. Prices are subject to change.";

    // Discount Properties
    public $additionalDiscount = 0;
    public $additionalDiscountType = 'fixed'; // 'fixed' or 'percentage'


    // Modals
    public $showQuotationModal = false;
    public $showCustomerModal = false;
    public $lastQuotationId = null;
    public $createdQuotation = null;


    // Update Unit Price
    public function updateUnitPrice($index, $price)
    {
        $price = floatval($price);
        if ($price < 0) $price = 0;
        $this->cart[$index]['price'] = $price;
        // Recalculate total for this item
        $this->cart[$index]['total'] = ($price - $this->cart[$index]['discount']) * $this->cart[$index]['quantity'];
    }

    public function mount($quotation = null)
    {
        $this->validUntil = now()->addDays(30)->format('Y-m-d');
        $this->loadCustomers();

        // Check if quotation parameter is provided and is numeric
        if ($quotation && is_numeric($quotation)) {
            $this->loadQuotationForEdit($quotation);
        } else {
            $this->setDefaultCustomer();
        }
    }

    // Load quotation for editing
    public function loadQuotationForEdit($quotationId)
    {
        $quotation = Quotation::find($quotationId);

        if (!$quotation) {
            session()->flash('error', 'Quotation not found.');
            return redirect()->route('admin.quotation-system');
        }

        if ($quotation->status === 'converted') {
            session()->flash('error', 'Cannot edit a converted quotation.');
            return redirect()->route('admin.quotation-list');
        }

        $this->quotationId = $quotation->id;
        $this->isEditMode = true;

        // Load customer data
        $this->customerId = $quotation->customer_id;
        $this->selectedCustomer = Customer::find($quotation->customer_id);

        // Load quotation details
        $this->validUntil = \Carbon\Carbon::parse($quotation->valid_until)->format('Y-m-d');
        $this->termsConditions = $quotation->terms_conditions ?? '';
        $this->notes = $quotation->notes ?? '';

        // Load discount data
        $this->additionalDiscount = $quotation->additional_discount_value ?? 0;
        $this->additionalDiscountType = $quotation->additional_discount_type ?? 'fixed';

        // Load cart items
        $items = is_string($quotation->items) ? json_decode($quotation->items, true) : $quotation->items;

        if (is_array($items) && count($items) > 0) {
            foreach ($items as $item) {
                $product = ProductDetail::with(['stock', 'price'])->find($item['product_id']);

                if ($product) {
                    $this->cart[] = [
                        'id' => $product->id,
                        'name' => $product->name,
                        'code' => $product->code,
                        'model' => $product->model,
                        'quantity' => $item['quantity'],
                        'price' => $item['unit_price'],
                        'discount' => $item['discount_per_unit'] ?? 0,
                        'total' => $item['total'],
                        'stock' => $product->stock->available_stock ?? 0
                    ];
                }
            }

            // Recalculate totals for all cart items to ensure consistency
            foreach ($this->cart as $index => &$cartItem) {
                $cartItem['total'] = ($cartItem['price'] - $cartItem['discount']) * $cartItem['quantity'];
            }
        }

        // Flash success message to indicate edit mode
        session()->flash('message', 'Editing Quotation #' . $quotation->quotation_number);
    }

    // Set default walking customer
    public function setDefaultCustomer()
    {
        // Find or create walking customer (only one)
        $walkingCustomer = Customer::where('name', 'Walking Customer')->first();

        if (!$walkingCustomer) {
            $walkingCustomer = Customer::create([
                'name' => 'Walking Customer',
                'phone' => 'xxxxx', // Empty phone number
                'email' => null,
                'address' => 'xxxxx',
                'type' => 'retail',
                'business_name' => null,
            ]);

            $this->loadCustomers(); // Reload customers after creating new one
        }

        $this->customerId = $walkingCustomer->id;
        $this->selectedCustomer = $walkingCustomer;
    }

    // Load customers for dropdown
    public function loadCustomers()
    {
        $this->customers = Customer::orderBy('name')->get();
    }

    // Computed Properties for Totals
    public function getSubtotalProperty()
    {
        return collect($this->cart)->sum('total');
    }

    public function getTotalDiscountProperty()
    {
        return collect($this->cart)->sum(function ($item) {
            return ($item['discount'] * $item['quantity']);
        });
    }

    public function getSubtotalAfterItemDiscountsProperty()
    {
        return $this->subtotal;
    }

    public function getAdditionalDiscountAmountProperty()
    {
        if (empty($this->additionalDiscount) || $this->additionalDiscount <= 0) {
            return 0;
        }

        if ($this->additionalDiscountType === 'percentage') {
            // Calculate percentage discount from subtotal after item discounts
            return ($this->subtotalAfterItemDiscounts * $this->additionalDiscount) / 100;
        }

        // For fixed discount, ensure it doesn't exceed the subtotal
        return min($this->additionalDiscount, $this->subtotalAfterItemDiscounts);
    }

    public function getGrandTotalProperty()
    {
        return $this->subtotalAfterItemDiscounts - $this->additionalDiscountAmount;
    }

    // When customer is selected from dropdown
    public function updatedCustomerId($value)
    {
        if ($value) {
            $customer = Customer::find($value);
            if ($customer) {
                // Store selected customer data but don't populate form fields
                $this->selectedCustomer = $customer;
            }
        } else {
            // If customer is deselected, set back to walking customer
            $this->setDefaultCustomer();
        }
    }

    // Reset customer fields
    public function resetCustomerFields()
    {
        $this->customerName = '';
        $this->customerPhone = '';
        $this->customerEmail = '';
        $this->customerAddress = '';
        $this->customerType = 'retail';
        $this->businessName = '';
    }

    // Open customer modal
    public function openCustomerModal()
    {
        $this->resetCustomerFields();
        $this->showCustomerModal = true;
    }

    // Close customer modal
    public function closeCustomerModal()
    {
        $this->showCustomerModal = false;
        $this->resetCustomerFields();
    }

    // Create new customer
    public function createCustomer()
    {
        $this->validate([
            'customerName' => 'required|string|max:255',
            'customerPhone' => 'nullable|string|max:20|unique:customers,phone',
            'customerEmail' => 'nullable|email|unique:customers,email',
            'customerAddress' => 'required|string',
            'customerType' => 'required|in:retail,wholesale,business',
        ]);

        try {
            $customer = Customer::create([
                'name' => $this->customerName,
                'phone' => $this->customerPhone ?: null,
                'email' => $this->customerEmail,
                'address' => $this->customerAddress,
                'type' => $this->customerType,
                'business_name' => $this->businessName,
            ]);

            // Reload customers and select the new one
            $this->loadCustomers();
            $this->customerId = $customer->id;
            $this->selectedCustomer = $customer;
            $this->closeCustomerModal();
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to create customer: ' . $e->getMessage());
        }
    }

    // Search Products
    public function updatedSearch()
    {
        if (strlen($this->search) >= 2) {
            $this->searchResults = ProductDetail::with(['stock', 'price'])
                ->where('name', 'like', '%' . $this->search . '%')
                ->orWhere('code', 'like', '%' . $this->search . '%')
                ->orWhere('model', 'like', '%' . $this->search . '%')
                ->take(10)
                ->get()
                ->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'code' => $product->code,
                        'model' => $product->model,
                        'price' => $product->price->selling_price ?? 0,
                        'stock' => $product->stock->available_stock ?? 0,
                        'image' => $product->image
                    ];
                });
        } else {
            $this->searchResults = [];
        }
    }

    // Add to Cart
    public function addToCart($product)
    {
        $existing = collect($this->cart)->firstWhere('id', $product['id']);

        if ($existing) {
            // Increase quantity if already in cart
            $this->cart = collect($this->cart)->map(function ($item) use ($product) {
                if ($item['id'] == $product['id']) {
                    $item['quantity'] += 1;
                    $item['total'] = ($item['price'] - $item['discount']) * $item['quantity'];
                }
                return $item;
            })->toArray();
        } else {
            // Add new item - use discount_price if available, otherwise 0
            $discountPrice = ProductDetail::find($product['id'])->price->discount_price ?? 0;

            array_unshift($this->cart, [
                'id' => $product['id'],
                'name' => $product['name'],
                'code' => $product['code'],
                'model' => $product['model'],
                'price' => $product['price'], // Unit price from selling_price
                'quantity' => 1,
                'discount' => $discountPrice, // Pre-fill with discount_price from database
                'total' => $product['price'] - $discountPrice // Initial total with discount applied
            ]);
        }

        $this->search = '';
        $this->searchResults = [];
    }

    // Update Quantity
    public function updateQuantity($index, $quantity)
    {
        if ($quantity < 1) $quantity = 1;

        $this->cart[$index]['quantity'] = $quantity;
        $this->cart[$index]['total'] = ($this->cart[$index]['price'] - $this->cart[$index]['discount']) * $quantity;
    }

    // Increment Quantity
    public function incrementQuantity($index)
    {
        $this->cart[$index]['quantity'] += 1;
        $this->cart[$index]['total'] = ($this->cart[$index]['price'] - $this->cart[$index]['discount']) * $this->cart[$index]['quantity'];
    }

    // Decrement Quantity
    public function decrementQuantity($index)
    {
        if ($this->cart[$index]['quantity'] > 1) {
            $this->cart[$index]['quantity'] -= 1;
            $this->cart[$index]['total'] = ($this->cart[$index]['price'] - $this->cart[$index]['discount']) * $this->cart[$index]['quantity'];
        }
    }

    // Update Discount (only discount is editable now)
    public function updateDiscount($index, $discount)
    {
        if ($discount < 0) $discount = 0;
        // Ensure discount doesn't exceed price
        if ($discount > $this->cart[$index]['price']) {
            $discount = $this->cart[$index]['price'];
        }

        $this->cart[$index]['discount'] = $discount;
        $this->cart[$index]['total'] = ($this->cart[$index]['price'] - $discount) * $this->cart[$index]['quantity'];
    }

    // Remove from Cart
    public function removeFromCart($index)
    {
        unset($this->cart[$index]);
        $this->cart = array_values($this->cart); // Reindex array
    }

    // Clear Cart
    public function clearCart()
    {
        $this->cart = [];
        $this->additionalDiscount = 0;
        $this->additionalDiscountType = 'fixed';
    }

    // Update additional discount with real-time validation
    public function updatedAdditionalDiscount($value)
    {
        // Convert empty string to 0
        if ($value === '') {
            $this->additionalDiscount = 0;
            return;
        }

        // Ensure it's a positive number
        if ($value < 0) {
            $this->additionalDiscount = 0;
            return;
        }

        // If percentage discount, ensure it doesn't exceed 100%
        if ($this->additionalDiscountType === 'percentage' && $value > 100) {
            $this->additionalDiscount = 100;
            return;
        }

        // For fixed discount, ensure it doesn't exceed the subtotal after item discounts
        if ($this->additionalDiscountType === 'fixed' && $value > $this->subtotalAfterItemDiscounts) {
            $this->additionalDiscount = $this->subtotalAfterItemDiscounts;
            return;
        }
    }

    // Update additional discount type with better handling
    public function updatedAdditionalDiscountType($type)
    {
        // Reset discount when type changes to avoid confusion
        $this->additionalDiscount = 0;
    }

    public function toggleDiscountType()
    {
        // Toggle between percentage and fixed
        $this->additionalDiscountType = $this->additionalDiscountType === 'percentage' ? 'fixed' : 'percentage';

        // Reset value after switch
        $this->additionalDiscount = 0;
    }

    // Apply percentage discount
    public function applyPercentageDiscount($percentage)
    {
        if ($percentage >= 0 && $percentage <= 100) {
            $this->additionalDiscountType = 'percentage';
            $this->additionalDiscount = $percentage;
        }
    }

    // Apply fixed discount
    public function applyFixedDiscount($amount)
    {
        if ($amount >= 0) {
            $this->additionalDiscountType = 'fixed';
            $this->additionalDiscount = min($amount, $this->subtotalAfterItemDiscounts);
        }
    }

    // Remove additional discount
    public function removeAdditionalDiscount()
    {
        $this->additionalDiscount = 0;
    }

    // Create Quotation
    public function createQuotation()
    {
        // Validate required fields
        if (empty($this->cart)) {
            session()->flash('error', 'Please add at least one product to the quotation.');
            return;
        }

        // If no customer selected, use walking customer
        if (!$this->selectedCustomer && !$this->customerId) {
            $this->setDefaultCustomer();
        }

        try {
            DB::beginTransaction();

            // Get customer data
            if ($this->selectedCustomer) {
                $customer = $this->selectedCustomer;
            } else {
                $customer = Customer::find($this->customerId);
            }

            if (!$customer) {
                session()->flash('error', 'Customer not found.');
                return;
            }

            // Prepare items for JSON storage
            $items = collect($this->cart)->map(function ($item, $index) {
                return [
                    'id' => $index + 1,
                    'product_id' => $item['id'],
                    'product_code' => $item['code'],
                    'product_name' => $item['name'],
                    'product_model' => $item['model'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'discount_per_unit' => $item['discount'],
                    'total_discount' => $item['discount'] * $item['quantity'],
                    'total' => $item['total']
                ];
            })->toArray();

            // ✅ FIX: Calculate total discount (item discounts + additional discount)
            $totalItemDiscount = $this->totalDiscount;
            $totalDiscount = $totalItemDiscount + $this->additionalDiscountAmount;

            $quotationData = [
                'customer_id' => $customer->id,
                'customer_type' => $customer->type,
                'customer_name' => $customer->name,
                'customer_phone' => $customer->phone,
                'customer_email' => $customer->email,
                'customer_address' => $customer->address,
                'valid_until' => $this->validUntil,
                'subtotal' => $this->subtotal,
                'discount_amount' => $totalDiscount,
                'additional_discount' => $this->additionalDiscountAmount,
                'additional_discount_type' => $this->additionalDiscountType,
                'additional_discount_value' => $this->additionalDiscount,
                'tax_amount' => 0,
                'shipping_charges' => 0,
                'total_amount' => $this->grandTotal,
                'items' => $items,
                'terms_conditions' => $this->termsConditions,
                'notes' => $this->notes,
            ];

            if ($this->isEditMode && $this->quotationId) {
                // Update existing quotation
                $quotation = Quotation::find($this->quotationId);
                $quotation->update($quotationData);
                session()->flash('success', 'Quotation updated successfully!');
            } else {
                // Create new quotation
                $quotationData['quotation_number'] = Quotation::generateQuotationNumber();
                $quotationData['quotation_date'] = now();
                $quotationData['status'] = 'draft';
                $quotation = Quotation::create($quotationData);
                session()->flash('success', 'Quotation created successfully!');
            }

            DB::commit();

            // Store quotation data and show modal WITHOUT resetting the page
            $this->lastQuotationId = $quotation->id;
            $this->createdQuotation = $quotation;
            $this->showQuotationModal = true;
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to ' . ($this->isEditMode ? 'update' : 'create') . ' quotation: ' . $e->getMessage());
        }
    }

    // Download Quotation
    public function downloadQuotation()
    {
        if (!$this->lastQuotationId) {
            session()->flash('error', 'No quotation found to download.');
            return;
        }

        $quotation = Quotation::find($this->lastQuotationId);

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

    // Print Quotation
    public function printQuotation()
    {
        if (!$this->lastQuotationId) {
            session()->flash('error', 'No quotation found to print.');
            return;
        }

        $quotation = Quotation::find($this->lastQuotationId);

        if (!$quotation) {
            session()->flash('error', 'Quotation not found.');
            return;
        }

        $pdf = PDF::loadView('admin.quotations.print', compact('quotation'));

        return $pdf->download('quotation-' . $quotation->quotation_number . '.pdf');
    }

    // Close Modal and reset only necessary fields
    public function closeModal()
    {

        $this->showQuotationModal = false;
        $this->lastQuotationId = null;
        $this->createdQuotation = null;
    }

    // Continue creating new quotation (reset everything)
    public function createNewQuotation()
    {
        $this->resetExcept(['customers', 'validUntil']);
        $this->validUntil = now()->addDays(30)->format('Y-m-d');
        $this->setDefaultCustomer(); // Set walking customer again for new quotation
        $this->showQuotationModal = false;
    }

    public function render()
    {
        return view('livewire.admin.quotation-system', [
            'subtotal' => $this->subtotal,
            'totalDiscount' => $this->totalDiscount,
            'subtotalAfterItemDiscounts' => $this->subtotalAfterItemDiscounts,
            'additionalDiscountAmount' => $this->additionalDiscountAmount,
            'grandTotal' => $this->grandTotal
        ])->layout($this->layout);
    }
}
