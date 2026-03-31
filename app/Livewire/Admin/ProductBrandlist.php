<?php

namespace App\Livewire\Admin;

use Exception;
use Livewire\Component;

use App\Models\BrandList;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use App\Livewire\Concerns\WithDynamicLayout;

#[Title("Product Brand List")]
class ProductBrandlist extends Component
{
    use WithDynamicLayout;

    public $brandName;
    public $editBrandId;
    public $editBrandName;
    public $deleteId;

    public function render()
    {
        $brands = BrandList::orderBy('id', 'desc')->get();
        return view('livewire.admin.Product-brandlist', [
            'brands' => $brands,
        ])->layout($this->layout);
    }

    public function createBrand()
    {
        $this->resetBrand();
        $this->dispatch('create-brand-modal');
    }

    public function resetBrand()
    {
        $this->reset(['brandName']);
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function saveBrand()
    {
        $this->validate([
            'brandName' => 'required|unique:brand_lists,brand_name'
        ]);

        try {
            BrandList::create([
                'brand_name' => $this->brandName,
            ]);

            $this->reset(['brandName']);
            $this->dispatch('brands-updated');
            $this->js("Swal.fire('Success!', 'Brand Created Successfully', 'success')");
        } catch (Exception $e) {
            $this->js("Swal.fire('Error!', '" . $e->getMessage() . "', 'error')");
        }
    }

    public function editBrand($id)
    {
        $brand = BrandList::find($id);
        if (!$brand) {
            $this->js("Swal.fire('Error!', 'Brand not found', 'error')");
            return;
        }

        $this->editBrandName = $brand->brand_name;
        $this->editBrandId = $brand->id;
        $this->resetErrorBag();
        $this->resetValidation();
        $this->dispatch('edit-brand');
    }

    public function updateBrand()
    {
        $this->validate([
            'editBrandName' => 'required|unique:brand_lists,brand_name,' . $this->editBrandId
        ]);

        try {
            BrandList::where('id', $this->editBrandId)->update([
                'brand_name' => $this->editBrandName,
            ]);

            $this->reset(['editBrandName', 'editBrandId']);
            $this->dispatch('brands-updated');
            $this->js("Swal.fire('Success!', 'Brand Updated Successfully', 'success')");
        } catch (Exception $e) {
            $this->js("Swal.fire('Error!', '" . $e->getMessage() . "', 'error')");
        }
    }

    public function confirmDelete($id)
    {
        $this->deleteId = $id;
        $this->dispatch('confirm-delete');
    }

    #[On('confirmDelete')]
    public function deleteBrand()
    {
        try {
            BrandList::where('id', $this->deleteId)->delete();
            $this->js("Swal.fire('Deleted!', 'Brand has been deleted successfully', 'success')");
            $this->reset(['deleteId']);
            $this->dispatch('brands-updated');
        } catch (Exception $e) {
            $this->js("Swal.fire('Error!', '" . $e->getMessage() . "', 'error')");
        }
    }
}
