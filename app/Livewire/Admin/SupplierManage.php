<?php

namespace App\Livewire\Admin;

use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use App\Models\ProductSupplier;
use Livewire\WithPagination;
use App\Livewire\Concerns\WithDynamicLayout;

#[Title("Supplier Management")]
class SupplierManage extends Component
{
    use WithDynamicLayout;

    use WithPagination;

    public $supplierId;
    public $name;
    public $businessname;
    public $contact;
    public $address;
    public $email;
    public $phone;
    public $status = 'active';
    public $notes;
    
    public $showCreateModal = false;
    public $showEditModal = false;
    public $showViewModal = false;
    public $perPage= 10;

    protected $rules = [
        'name' => 'required|string|max:255',
        'businessname' => 'nullable|string|max:255',
        'contact' => 'nullable|string|max:10',
        'address' => 'nullable|string|max:255',
        'email' => 'nullable|email|max:255',
        'phone' => 'nullable|string|max:10',
        'status' => 'required|in:active,inactive',
        'notes' => 'nullable|string|max:500',
    ];

    protected $messages = [
        'name.required' => 'The supplier name field is required.',
        'name.string' => 'The supplier name must be a valid string.',
        'name.max' => 'The supplier name may not be greater than 255 characters.',
        'businessname.string' => 'The business name must be a valid string.',
        'businessname.max' => 'The business name may not be greater than 255 characters.',
        'contact.string' => 'The contact number must be a valid string.',
        'contact.max' => 'The contact number may not be greater than 10 characters.',
        'address.string' => 'The address must be a valid string.',
        'address.max' => 'The address may not be greater than 255 characters.',
        'email.email' => 'Please enter a valid email address.',
        'email.max' => 'The email may not be greater than 255 characters.',
        'phone.string' => 'The phone number must be a valid string.',
        'phone.max' => 'The phone number may not be greater than 10 characters.',
        'status.required' => 'The status field is required.',
        'status.in' => 'The selected status is invalid.',
        'notes.string' => 'The notes must be a valid string.',
        'notes.max' => 'The notes may not be greater than 500 characters.',
    ];

    // -------------------- CREATE MODAL --------------------
    public function createSupplier()
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }

    // -------------------- CREATE --------------------
    public function save()
    {
        $this->validate();

        ProductSupplier::create([
            'name' => $this->name,
            'businessname' => $this->businessname,
            'contact' => $this->contact,
            'address' => $this->address,
            'email' => $this->email,
            'phone' => $this->phone,
            'status' => $this->status,
            'notes' => $this->notes,
        ]);

        $this->resetForm();
        $this->showCreateModal = false;

        $this->dispatch('show-toast', 'success', 'Supplier created successfully!');
        $this->dispatch('refreshPage');
    }

    // -------------------- VIEW --------------------
    public function view($id)
    {
        $supplier = ProductSupplier::findOrFail($id);

        $this->supplierId = $supplier->id;
        $this->name = $supplier->name;
        $this->businessname = $supplier->businessname;
        $this->contact = $supplier->contact;
        $this->address = $supplier->address;
        $this->email = $supplier->email;
        $this->phone = $supplier->phone;
        $this->status = $supplier->status;
        $this->notes = $supplier->notes;

        $this->showViewModal = true;
    }

    // -------------------- EDIT --------------------
    public function edit($id)
    {
        $supplier = ProductSupplier::findOrFail($id);

        $this->supplierId = $supplier->id;
        $this->name = $supplier->name;
        $this->businessname = $supplier->businessname;
        $this->contact = $supplier->contact;
        $this->address = $supplier->address;
        $this->email = $supplier->email;
        $this->phone = $supplier->phone;
        $this->status = $supplier->status;
        $this->notes = $supplier->notes;

        $this->showEditModal = true;
    }

    // -------------------- UPDATE --------------------
    public function updateSupplier()
    {
        $this->validate();

        if (!$this->supplierId) {
            $this->dispatch('show-toast', 'error', 'No supplier selected.');
            return;
        }

        $supplier = ProductSupplier::findOrFail($this->supplierId);

        $supplier->update([
            'name' => $this->name,
            'businessname' => $this->businessname,
            'contact' => $this->contact,
            'address' => $this->address,
            'email' => $this->email,
            'phone' => $this->phone,
            'status' => $this->status,
            'notes' => $this->notes,
        ]);

        $this->resetForm();
        $this->showEditModal = false;

        $this->dispatch('show-toast', 'success', 'Supplier updated successfully!');
        $this->dispatch('refreshPage');
    }

    // -------------------- CONFIRM DELETE --------------------
    public function confirmDelete($id)
    {
        $this->supplierId = $id;
        
        $this->dispatch('swal:confirm', [
            'title' => 'Are you sure?',
            'text' => 'You won\'t be able to revert this!',
            'icon' => 'warning',
            'id' => $id,
        ]);
    }

    // -------------------- DELETE --------------------
    #[On('delete-supplier')]
    public function deleteSupplier($id)
    {
        $supplier = ProductSupplier::find($id);

        if ($supplier) {
            $supplier->delete();
            $this->dispatch('show-toast', 'success', 'Supplier has been deleted.');
        } else {
            $this->dispatch('show-toast', 'error', 'Supplier not found.');
        }
        $this->dispatch('refreshPage');
    }

    public function closeModal()
    {
        $this->showCreateModal = false;
        $this->showEditModal = false;
        $this->showViewModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->reset(['supplierId', 'name', 'businessname', 'contact', 'address', 'email', 'phone', 'status', 'notes']);
        $this->status = 'active';
        $this->resetValidation();
    }
    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function render()
    {
        $suppliers = ProductSupplier::latest()->paginate($this->perPage);
        return view('livewire.admin.supplier-manage', compact('suppliers'))->layout($this->layout);
    }
}
