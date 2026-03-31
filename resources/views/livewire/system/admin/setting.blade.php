<div class="container-fluid py-3">

    {{-- Page Header --}}
    <div class="d-flex align-items-center mb-4">
        <i class="bi bi-gear-fill text-success fs-2"></i>
        <div class="ms-3">
            <h1 class="h3 fw-bold mb-0">System Settings</h1>
            <p class="text-muted mb-0">Manage all system configurations.</p>
        </div>
    </div>

    {{-- Accordion --}}
    <div class="accordion" id="settingsAccordion">
        <div class="accordion-item border-0 mb-4 shadow-sm rounded-4">
            <h2 class="accordion-header" id="headingSystemConfigs">
                <button class="accordion-button fw-semibold bg-white text-dark rounded-4"
                        type="button" data-bs-toggle="collapse"
                        data-bs-target="#collapseSystemConfigs" aria-expanded="true"
                        aria-controls="collapseSystemConfigs">
                    <i class="bi bi-sliders fs-5 me-3 text-success"></i>
                    System Configurations
                </button>
            </h2>

            <div id="collapseSystemConfigs" class="accordion-collapse collapse show"
                 aria-labelledby="headingSystemConfigs" data-bs-parent="#settingsAccordion">
                <div class="accordion-body">

                    {{-- Add Button --}}
                    @if(!$showAddForm)
                        <div class="mb-3 d-flex justify-content-end">
                            <button class="btn btn-primary rounded-pill px-4 py-2 fw-semibold shadow-sm" 
                                    wire:click="$set('showAddForm', true)">
                                <i class="bi bi-plus-circle me-2"></i> Add Configuration
                            </button>
                        </div>
                    @endif

                    {{-- Add Form --}}
                    @if($showAddForm)
                        <div class="card mb-4 p-3 shadow-sm border-0 rounded-4 border-top border-primary">
                            <form wire:submit.prevent="saveConfiguration" id="configForm" class="row g-3 align-items-end">
                                <div class="col-md-5">
                                    <label class="form-label fw-semibold">Key</label>
                                    <input type="text" wire:model="key"
                                           class="form-control @error('key') is-invalid @enderror"
                                           placeholder="Enter key">
                                    @error('key') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label fw-semibold">Value</label>
                                    <input type="text" wire:model="value"
                                           class="form-control @error('value') is-invalid @enderror"
                                           placeholder="Enter value">
                                    @error('value') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-2 text-end">
                                    <button type="submit" class="btn btn-success w-100 rounded-pill py-2 shadow-sm" wire:loading.attr="disabled">
                                        <span wire:loading.remove>Save</span>
                                        <span wire:loading>Saving...</span>
                                    </button>
                                </div>
                            </form>
                            <div class="mt-2 text-end">
                                <button class="btn btn-outline-secondary btn-sm rounded-pill px-3"
                                        wire:click="cancelAdd" wire:loading.attr="disabled">
                                    Cancel
                                </button>
                            </div>
                        </div>
                    @endif

                    {{-- Existing Configurations with Headings --}}
                    @if($settings->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="fw-semibold text-dark">Key</th>
                                        <th class="fw-semibold text-dark">Value</th>
                                        <th class="text-end fw-semibold text-dark">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($settings as $setting)
                                        <tr>
                                            <td class="fw-medium text-primary">{{ $setting->key }}</td>
                                            <td class="text-muted">{{ $setting->value }}</td>
                                            <td class="text-end">
                                                {{-- Modern Buttons --}}
                                                @if($editingId !== $setting->id)
                                                    <div class="d-flex gap-2 justify-content-end">
                                                        <button class="btn btn-action btn-edit"
                                                                wire:click="editConfiguration({{ $setting->id }})" 
                                                                wire:loading.attr="disabled"
                                                                title="Edit Configuration">
                                                            <i class="bi bi-pencil-square"></i>
                                                            <span>Edit</span>
                                                        </button>
                                                        <button class="btn btn-action btn-delete"
                                                                wire:click="deleteConfiguration({{ $setting->id }})"
                                                                wire:loading.attr="disabled"
                                                                title="Delete Configuration">
                                                            <i class="bi bi-trash"></i>
                                                            <span>Delete</span>
                                                        </button>
                                                    </div>
                                                @else
                                                    {{-- Inline Edit Form --}}
                                                    <div class="w-100">
                                                        <div class="row g-2">
                                                            <div class="col-md-4">
                                                                <input type="text" wire:model="editKey" 
                                                                       class="form-control form-control-sm @error('editKey') is-invalid @enderror" 
                                                                       placeholder="Key">
                                                                @error('editKey') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                            </div>
                                                            <div class="col-md-4">
                                                                <input type="text" wire:model="editValue" 
                                                                       class="form-control form-control-sm @error('editValue') is-invalid @enderror" 
                                                                       placeholder="Value">
                                                                @error('editValue') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                            </div>
                                                            <div class="col-md-2">
                                                                <div class="d-flex gap-1">
                                                                    <button class="btn btn-success btn-sm rounded-pill px-3" 
                                                                            wire:click="updateConfiguration" 
                                                                            wire:loading.attr="disabled">
                                                                        <span wire:loading.remove>Update</span>
                                                                        <span wire:loading>...</span>
                                                                    </button>
                                                                    <button class="btn btn-secondary btn-sm rounded-pill px-3" 
                                                                            wire:click="cancelEdit"
                                                                            wire:loading.attr="disabled">
                                                                        Cancel
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-muted text-center py-4">
                            <i class="bi bi-inbox display-4 d-block mb-2"></i>
                            No configurations found.
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .accordion-button {
        box-shadow: none;
        border: 1px solid #e0e0e0;
        transition: background-color 0.3s, box-shadow 0.3s;
    }

    .accordion-button:not(.collapsed) {
        background-color: #f8f9fa;
        color: #212529;
    }

    .accordion-button:hover {
        background-color: #f1f3f5;
    }

    .accordion-body {
        padding: 1.5rem;
    }

    /* Table styling */
    .table {
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 0 0 1px rgba(0,0,0,0.1);
    }

    .table th {
        border-bottom: 2px solid #e9ecef;
        background-color: #f8f9fa;
        padding: 1rem 0.75rem;
        font-weight: 600;
    }

    .table td {
        padding: 1rem 0.75rem;
        vertical-align: middle;
        border-bottom: 1px solid #f0f0f0;
    }

    .table tbody tr:hover {
        background-color: #f8f9fa;
    }

    /* Modern Button Styles */
    .btn-action {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        border: none;
        border-radius: 8px;
        font-size: 0.875rem;
        font-weight: 500;
        transition: all 0.3s ease;
        text-decoration: none;
        cursor: pointer;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .btn-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }

    .btn-action:active {
        transform: translateY(0);
    }

    .btn-edit {
        background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
        color: white;
    }

    .btn-edit:hover {
        background: linear-gradient(135deg, #0a58ca 0%, #084298 100%);
        color: white;
    }

    .btn-delete {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        color: white;
    }

    .btn-delete:hover {
        background: linear-gradient(135deg, #c82333 0%, #a71e2a 100%);
        color: white;
    }

    /* Loading state for modern buttons */
    .btn-action[wire\\:loading] {
        opacity: 0.7;
        pointer-events: none;
    }

    /* Improved rounded buttons */
    .rounded-pill {
        transition: all 0.3s ease;
    }

    .rounded-pill:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
</style>
@endpush

@script
<script>
    // Success messages
    Livewire.on('config-saved', () => {
        Swal.fire({
            title: 'Success!',
            text: 'Configuration added successfully.',
            icon: 'success',
            confirmButtonColor: '#3085d6',
        });
    });

    Livewire.on('config-updated', () => {
        Swal.fire({
            title: 'Updated!',
            text: 'Configuration updated successfully.',
            icon: 'success',
            confirmButtonColor: '#3085d6',
        });
    });

    Livewire.on('config-deleted', () => {
        Swal.fire({
            title: 'Deleted!',
            text: 'Configuration deleted successfully.',
            icon: 'success',
            confirmButtonColor: '#3085d6',
        });
    });

    // Error messages
    Livewire.on('config-error', (event) => {
        let message = 'An error occurred.';
        
        // Handle different Livewire versions and parameter formats
        if (typeof event === 'string') {
            message = event;
        } else if (event.detail && event.detail.message) {
            message = event.detail.message;
        } else if (event.message) {
            message = event.message;
        } else if (event.detail && typeof event.detail[0] === 'string') {
            message = event.detail[0];
        }
        
        Swal.fire({
            title: 'Error!',
            text: message,
            icon: 'error',
            confirmButtonColor: '#d33',
        });
    });

    // Delete confirmation
    Livewire.on('confirm-config-delete', (event) => {
        let configId;
        
        // Handle different parameter formats
        if (typeof event === 'number') {
            configId = event;
        } else if (event.detail && event.detail.id) {
            configId = event.detail.id;
        } else if (event.id) {
            configId = event.id;
        } else {
            console.error('Could not find configuration ID');
            return;
        }

        Swal.fire({
            title: "Are you sure?",
            text: "This configuration will be deleted permanently.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, delete it!"
        }).then((result) => {
            if (result.isConfirmed) {
                Livewire.dispatch('confirmConfigDelete', { id: configId });
            }
        });
    });

    // Reset form
    Livewire.on('reset-config-form', () => {
        const form = document.getElementById('configForm');
        if (form) form.reset();
    });
</script>
@endscript
