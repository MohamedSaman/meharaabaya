<?php

namespace App\Livewire\Admin;

use Exception;
use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\CategoryList;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use App\Livewire\Concerns\WithDynamicLayout;

#[Title("Product Category List")]
class ProductCategorylist extends Component
{
    use WithDynamicLayout;

    public $categoryName;
    public function render()
    {
        $categories = CategoryList::orderBy('id', 'desc')->get();
        return view('livewire.admin.Product-categorylist', [
            'categories' => $categories,
        ])->layout($this->layout);
    }

    public function createCategory()
    {
        $this->dispatch('create-category');
    }

    public function resetForm()
    {
        $this->categoryName = '';
    }

    public function saveCategory()
    {
        $this->validate([
            'categoryName' => 'required|unique:category_lists,category_name'
        ]);
        try {
            CategoryList::create([
                'category_name' => $this->categoryName,
            ]);

            $this->reset(['categoryName']);
            $this->js("Swal.fire('Success!', 'Category Created Successfully', 'success')");
            $this->js('$("#createCategoryModal").modal("hide")');
        } catch (Exception $e) {
            $this->js("Swal.fire('Error!', '" . $e->getMessage() . "', 'error')");
        }
    }

    public $editCategoryId;
    public $editCategoryName;

    public function editCategory($id)
    {
        $category = CategoryList::find($id);
        if (!$category) {
            $this->js("Swal.fire('Error!', 'Category not found', 'error')");
            return;
        }

        $this->editCategoryName = $category->category_name;
        $this->editCategoryId = $category->id;
        $this->resetErrorBag();
        $this->resetValidation();
        $this->dispatch('edit-category');
    }

    public function updateCategory()
    {
        $this->validate([
            'editCategoryName' => 'required|unique:category_lists,category_name,' . $this->editCategoryId
        ]);
        try {
            CategoryList::where('id', $this->editCategoryId)->update([
                'category_name' => $this->editCategoryName,
            ]);

            $this->reset(['editCategoryName', 'editCategoryId']);
            $this->js('$("#editCategoryModal").modal("hide")');
            $this->js("Swal.fire('Success!', 'Category Updated Successfully', 'success')");
        } catch (Exception $e) {
            $this->js("Swal.fire('Error!', '" . $e->getMessage() . "', 'error')");
        }
    }

    public $deleteId;
    public function confirmDelete($id)
    {
        $this->deleteId = $id;
        $this->dispatch('confirm-delete');
    }
    #[On('confirmDelete')]
    public function deleteCategory()
    {
        try {
            CategoryList::where('id', $this->deleteId)->delete();
        } catch (Exception $e) {
            // log($e->getMessage());
            $this->js("Swal.fire('Error!', '" . $e->getMessage() . "', 'error')");
        }
    }
}
