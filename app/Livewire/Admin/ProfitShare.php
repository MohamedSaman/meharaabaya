<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Investor;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Title;

#[Title("Profit Share Management")]
class ProfitShare extends Component
{
    use WithFileUploads;

    public $showCreateModal = false;
    public $showDeleteModal = false;
    public $deleteInvestorId = null;
    
    public $isEditMode = false;
    public $investorId = null;
    public $existingImage = null;

    // Form fields
    public $name = '';
    public $phone_number = '';
    public $address = '';
    public $email = '';
    public $profit_share_percentage = '';
    public $image;
    public $notes = '';

    public function openCreateModal()
    {
        $this->resetForm();
        $this->isEditMode = false;
        $this->showCreateModal = true;
    }

    public function openEditModal($id)
    {
        $this->resetForm();
        $investor = Investor::findOrFail($id);
        $this->investorId = $investor->id;
        $this->name = $investor->name;
        $this->phone_number = $investor->phone_number;
        $this->address = $investor->address;
        $this->email = $investor->email;
        $this->profit_share_percentage = $investor->profit_share_percentage;
        $this->existingImage = $investor->image;
        $this->notes = $investor->notes;
        $this->isEditMode = true;
        
        $this->showCreateModal = true;
    }

    public function closeModal()
    {
        $this->showCreateModal = false;
        $this->showDeleteModal = false;
        $this->deleteInvestorId = null;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->name = '';
        $this->phone_number = '';
        $this->address = '';
        $this->email = '';
        $this->profit_share_percentage = '';
        $this->image = null;
        $this->existingImage = null;
        $this->notes = '';
        $this->isEditMode = false;
        $this->investorId = null;
        $this->resetValidation();
    }

    public function saveInvestor()
    {
        $rules = [
            'name'                    => 'required|string|max:255',
            'phone_number'            => 'required|string|max:20',
            'address'                 => 'required|string',
            'profit_share_percentage' => 'required|numeric|min:0|max:100',
            'image'                   => 'nullable|image|max:2048',
            'notes'                   => 'nullable|string',
        ];

        if ($this->isEditMode) {
            $rules['email'] = 'required|email|unique:investors,email,' . $this->investorId;
        } else {
            $rules['email'] = 'required|email|unique:investors,email';
        }

        $this->validate($rules);

        $imagePath = $this->existingImage;
        if ($this->image) {
            // Delete old image if uploading new one
            if ($this->isEditMode && $this->existingImage && Storage::disk('public')->exists($this->existingImage)) {
                Storage::disk('public')->delete($this->existingImage);
            }
            $imagePath = $this->image->store('investors', 'public');
        }

        if ($this->isEditMode) {
            $investor = Investor::findOrFail($this->investorId);
            $investor->update([
                'name'                    => $this->name,
                'phone_number'            => $this->phone_number,
                'address'                 => $this->address,
                'email'                   => $this->email,
                'profit_share_percentage' => $this->profit_share_percentage,
                'image'                   => $imagePath,
                'notes'                   => $this->notes,
            ]);
            session()->flash('success', 'Investor updated successfully!');
        } else {
            Investor::create([
                'name'                    => $this->name,
                'phone_number'            => $this->phone_number,
                'address'                 => $this->address,
                'email'                   => $this->email,
                'profit_share_percentage' => $this->profit_share_percentage,
                'image'                   => $imagePath,
                'notes'                   => $this->notes,
            ]);
            session()->flash('success', 'Investor created successfully!');
        }

        $this->closeModal();
    }

    public function confirmDelete($id)
    {
        $this->deleteInvestorId = $id;
        $this->showDeleteModal = true;
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->deleteInvestorId = null;
    }

    public function deleteInvestor()
    {
        if ($this->deleteInvestorId) {
            $investor = Investor::findOrFail($this->deleteInvestorId);

            // Delete the image file from storage when investor is deleted
            if ($investor->image && Storage::disk('public')->exists($investor->image)) {
                Storage::disk('public')->delete($investor->image);
            }

            $investor->delete();
            $this->showDeleteModal = false;
            $this->deleteInvestorId = null;
            session()->flash('success', 'Investor deleted successfully!');
        }
    }

    public function render()
    {
        $investors = Investor::latest()->get();
        return view('livewire.admin.profit-share', compact('investors'))->layout('components.layouts.admin');
    }
}
