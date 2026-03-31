<?php

namespace App\Livewire\Admin;

use Exception;
use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Hash;
use App\Livewire\Concerns\WithDynamicLayout;

#[Title('Manage Admin')]
class ManageAdmin extends Component
{
    use WithDynamicLayout;

    public $name;
    public $contactNumber;
    public $email;
    public $password;
    public $confirmPassword;

    public $editAdminId;
    public $editName;
    public $editContactNumber;
    public $editEmail;
    public $editPassword;
    public $editConfirmPassword;

    public $deleteId;
    public $showEditModal = false;
    public $showCreateModal = false;
    public $showDeleteModal = false;

    public function render()
    {
        $admins = User::where('role', 'admin')->get();
        return view('livewire.admin.manage-admin', [
            'admins' => $admins,
        ])->layout($this->layout);
    }

    /** ----------------------------
     * Create Admin
     * ---------------------------- */
    public function createAdmin()
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function resetForm()
    {
        $this->reset([
            'name', 'contactNumber', 'email', 'password', 'confirmPassword',
            'editAdminId', 'editName', 'editContactNumber', 'editEmail', 
            'editPassword', 'editConfirmPassword'
        ]);
        $this->resetErrorBag();
    }

    public function closeModal()
    {
        $this->showCreateModal = false;
        $this->showEditModal = false;
        $this->showDeleteModal = false;
        $this->resetForm();
    }

    public function saveAdmin()
    {
        $this->validate([
            'name' => 'required',
            'contactNumber' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'confirmPassword' => 'required|min:8|same:password',
        ]);

        try {
            User::create([
                'name' => $this->name,
                'contact' => $this->contactNumber,
                'email' => $this->email,
                'password' => Hash::make($this->password),
                'role' => 'admin',
            ]);

            $this->js("Swal.fire('Success!', 'Admin Created Successfully', 'success')");
            $this->closeModal();
        } catch (Exception $e) {
            $this->js("Swal.fire('Error!', '".$e->getMessage()."', 'error')");
        }
    }

    /** ----------------------------
     * Edit Admin
     * ---------------------------- */
    public function editAdmin($id)
    {
        $user = User::find($id);

        if (!$user) {
            $this->js("Swal.fire('Error!', 'Admin not found.', 'error')");
            return;
        }

        $this->editAdminId = $user->id;
        $this->editName = $user->name;
        $this->editContactNumber = $user->contact;
        $this->editEmail = $user->email;
        $this->editPassword = '';
        $this->editConfirmPassword = '';

        $this->showEditModal = true;
    }

    public function updateAdmin()
    {
        $validationRules = [
            'editName' => 'required',
            'editContactNumber' => 'required',
            'editEmail' => 'required|email|unique:users,email,' . $this->editAdminId,
        ];
        
        // Only validate password if it's provided
        if (!empty($this->editPassword)) {
            $validationRules['editPassword'] = 'required|min:8';
            $validationRules['editConfirmPassword'] = 'required|same:editPassword';
        }
        
        $this->validate($validationRules);

        try {
            $user = User::find($this->editAdminId);
            if ($user) {
                $user->name = $this->editName;
                $user->contact = $this->editContactNumber;
                $user->email = $this->editEmail;
                
                // Only update password if a new one was provided
                if (!empty($this->editPassword)) {
                    $user->password = Hash::make($this->editPassword);
                }
                
                $user->save();
                $this->js("Swal.fire('Success!', 'Admin Updated Successfully', 'success')");
                $this->closeModal();
            } else {
                $this->js("Swal.fire('Error!', 'Admin Not Found', 'error')");
            }
        } catch (Exception $e) {
            $this->js("Swal.fire('Error!', '" . $e->getMessage() . "', 'error')");
        }
    }

    /** ----------------------------
     * Delete Admin
     * ---------------------------- */
    public function confirmDelete($id)
    {
        $this->deleteId = $id;
        $this->showDeleteModal = true;
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->deleteId = null;
    }

    public function deleteAdmin()
    {
        try {
            User::where('id', $this->deleteId)->delete();
            $this->js("Swal.fire('Success!', 'Admin deleted successfully.', 'success')");
            $this->cancelDelete();
        } catch (Exception $e) {
            $this->js("Swal.fire('Error!', '".$e->getMessage()."', 'error')");
        }
    }
}
