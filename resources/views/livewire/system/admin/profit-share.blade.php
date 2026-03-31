@php use Illuminate\Support\Facades\Storage; @endphp
<div>
    <div class="container-fluid py-3">

        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h3 class="fw-bold text-dark mb-2">
                    <i class="bi bi-pie-chart-fill text-primary me-2"></i> Profit Share Management
                </h3>
                <p class="text-muted mb-0">Manage investors and their profit share percentages</p>
            </div>
            <div>
                <button class="btn btn-primary" wire:click="openCreateModal" id="createInvestorBtn">
                    <i class="bi bi-plus-lg me-2"></i> Create Investor
                </button>
            </div>
        </div>

        {{-- Flash Messages --}}
        @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-exclamation-circle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        {{-- Investor Grid --}}
        @if($investors->count() > 0)
        <div class="row g-4">
            @foreach($investors as $investor)
            <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="investor-card card h-100" data-url="{{ route('admin.investor-ledger', $investor) }}" onclick="window.location.href=this.dataset.url" style="cursor:pointer;">
                    {{-- Investor Image --}}
                    <div class="investor-img-wrapper">
                        @if($investor->image)
                        <img src="{{ Storage::disk('public')->url($investor->image) }}"
                            alt="{{ $investor->name }}"
                            class="investor-img"
                            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="investor-img-placeholder" style="display:none;">
                            <i class="bi bi-person-circle"></i>
                        </div>
                        @else
                        <div class="investor-img-placeholder">
                            <i class="bi bi-person-circle"></i>
                        </div>
                        @endif

                        {{-- Profit Badge --}}
                        <div class="profit-badge">
                            <i class="bi bi-graph-up-arrow me-1"></i>
                            {{ number_format($investor->profit_share_percentage, 2) }}%
                        </div>
                    </div>

                    {{-- Card Body --}}
                    <div class="card-body pt-3 pb-2">
                        <h5 class="investor-name">{{ $investor->name }}</h5>

                        <div class="investor-details">
                            <div class="detail-row">
                                <i class="bi bi-telephone-fill text-primary"></i>
                                <span>{{ $investor->phone_number }}</span>
                            </div>
                            <div class="detail-row">
                                <i class="bi bi-envelope-fill text-primary"></i>
                                <span class="text-break">{{ $investor->email }}</span>
                            </div>
                            <div class="detail-row">
                                <i class="bi bi-geo-alt-fill text-primary"></i>
                                <span>{{ $investor->address }}</span>
                            </div>
                            @if($investor->notes)
                            <div class="detail-row align-items-start">
                                <i class="bi bi-sticky-fill text-warning mt-1"></i>
                                <span class="text-muted small">{{ $investor->notes }}</span>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Card Footer --}}
                    <div class="card-footer bg-transparent border-top-0 pt-0 pb-3 px-3 d-flex flex-column gap-2">
                        <a href="{{ route('admin.investor-ledger', $investor) }}" class="btn btn-outline-primary btn-sm w-100" onclick="event.stopPropagation();">
                            <i class="bi bi-box-arrow-up-right me-1"></i> View Inflow / Outflow
                        </a>
                        <div class="d-flex gap-2">
                            <button wire:click="openEditModal({{ $investor->id }})" class="btn btn-outline-secondary btn-sm flex-fill" onclick="event.stopPropagation();">
                                <i class="bi bi-pencil-square me-1"></i> Edit
                            </button>
                            <button
                                wire:click="confirmDelete({{ $investor->id }})"
                                onclick="event.stopPropagation();"
                                class="btn btn-outline-danger btn-sm flex-fill"
                                wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="confirmDelete({{ $investor->id }})">
                                    <i class="bi bi-trash me-1"></i> Delete
                                </span>
                                <span wire:loading wire:target="confirmDelete({{ $investor->id }})">
                                    <span class="spinner-border spinner-border-sm"></span>
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        {{-- Empty State --}}
        <div class="card text-center py-5">
            <div class="card-body">
                <div class="empty-state-icon mb-4">
                    <i class="bi bi-people"></i>
                </div>
                <h5 class="fw-bold text-dark mb-2">No Investors Yet</h5>
                <p class="text-muted mb-4">Get started by adding your first investor to begin tracking profit shares.</p>
                <button class="btn btn-primary" wire:click="openCreateModal">
                    <i class="bi bi-plus-lg me-2"></i> Add First Investor
                </button>
            </div>
        </div>
        @endif

    </div>

    {{-- ============================= --}}
    {{-- Create Investor Modal         --}}
    {{-- ============================= --}}
    @if($showCreateModal)
    <div class="modal fade show d-block" tabindex="-1" aria-labelledby="createInvestorModalLabel" aria-hidden="false"
        style="background-color: rgba(0,0,0,0.5);" id="createInvestorModal">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="createInvestorModalLabel">
                        <i class="bi bi-person-{{ $isEditMode ? 'check' : 'plus' }}-fill text-white me-2"></i> {{ $isEditMode ? 'Edit Investor Profile' : 'Create New Investor' }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeModal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="saveInvestor" id="createInvestorForm">
                        <div class="row g-3">

                            {{-- Name --}}
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">
                                    Full Name <span class="text-danger">*</span>
                                </label>
                                <input
                                    type="text"
                                    id="investorName"
                                    wire:model="name"
                                    placeholder="Enter investor name"
                                    class="form-control @error('name') is-invalid @enderror">
                                @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            {{-- Phone --}}
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">
                                    Phone Number <span class="text-danger">*</span>
                                </label>
                                <input
                                    type="text"
                                    id="investorPhone"
                                    wire:model="phone_number"
                                    placeholder="Enter phone number"
                                    class="form-control @error('phone_number') is-invalid @enderror">
                                @error('phone_number') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            {{-- Email --}}
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">
                                    Email Address <span class="text-danger">*</span>
                                </label>
                                <input
                                    type="email"
                                    id="investorEmail"
                                    wire:model="email"
                                    placeholder="Enter email address"
                                    class="form-control @error('email') is-invalid @enderror">
                                @error('email') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            {{-- Profit Share % --}}
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">
                                    Profit Share (%) <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input
                                        type="number"
                                        id="investorProfitShare"
                                        wire:model="profit_share_percentage"
                                        step="0.01"
                                        min="0"
                                        max="100"
                                        placeholder="e.g. 25.00"
                                        class="form-control @error('profit_share_percentage') is-invalid @enderror">
                                    <span class="input-group-text">%</span>
                                </div>
                                @error('profit_share_percentage') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            {{-- Address --}}
                            <div class="col-12">
                                <label class="form-label fw-semibold">
                                    Address <span class="text-danger">*</span>
                                </label>
                                <textarea
                                    id="investorAddress"
                                    wire:model="address"
                                    placeholder="Enter full address"
                                    rows="2"
                                    class="form-control @error('address') is-invalid @enderror"></textarea>
                                @error('address') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            {{-- Image Upload --}}
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Profile Image</label>
                                <input
                                    type="file"
                                    id="investorImage"
                                    wire:model="image"
                                    accept="image/*"
                                    class="form-control @error('image') is-invalid @enderror">
                                @error('image') <span class="text-danger small">{{ $message }}</span> @enderror
                                @if($image)
                                <div class="mt-2">
                                    <img src="{{ $image->temporaryUrl() }}"
                                        alt="Preview"
                                        class="rounded shadow-sm"
                                        style="width: 80px; height: 80px; object-fit: cover; border: 2px solid #4361ee;">
                                </div>
                                @elseif($existingImage)
                                <div class="mt-2">
                                    <img src="{{ Storage::disk('public')->url($existingImage) }}"
                                        alt="Current Image"
                                        class="rounded shadow-sm"
                                        style="width: 80px; height: 80px; object-fit: cover; border: 2px solid #4361ee;">
                                </div>
                                @endif
                            </div>

                            {{-- Notes --}}
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Additional Notes</label>
                                <textarea
                                    id="investorNotes"
                                    wire:model="notes"
                                    placeholder="Any additional information..."
                                    rows="2"
                                    class="form-control"></textarea>
                            </div>

                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="saveInvestor">
                                    <i class="bi bi-check2-circle me-1"></i> {{ $isEditMode ? 'Save Changes' : 'Save Investor' }}
                                </span>
                                <span wire:loading wire:target="saveInvestor">
                                    <span class="spinner-border spinner-border-sm me-2" role="status"></span> Saving...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ============================= --}}
    {{-- Delete Confirmation Modal     --}}
    {{-- ============================= --}}
    @if($showDeleteModal)
    <div class="modal fade show d-block" tabindex="-1" aria-labelledby="deleteInvestorModalLabel" aria-hidden="false"
        style="background-color: rgba(0,0,0,0.5);" id="deleteInvestorModal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-white" id="deleteInvestorModalLabel">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> Confirm Delete
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="cancelDelete" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <i class="bi bi-person-x text-danger mb-3 d-block" style="font-size: 3rem;"></i>
                    <h5 class="fw-bold mb-2">Are you sure?</h5>
                    <p class="text-muted mb-0">You are about to delete this investor. This action cannot be undone.</p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary" wire:click="cancelDelete">Cancel</button>
                    <button type="button" class="btn btn-danger" wire:click="deleteInvestor" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="deleteInvestor">
                            <i class="bi bi-trash me-1"></i> Delete Investor
                        </span>
                        <span wire:loading wire:target="deleteInvestor">
                            <span class="spinner-border spinner-border-sm me-1"></span> Deleting...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>

@push('styles')
<style>
    /* ─── Cards ─── */
    .card {
        border: none;
        border-radius: 14px;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 24px rgba(0, 0, 0, 0.13);
    }

    /* ─── Investor Card ─── */
    .investor-card {
        overflow: hidden;
    }

    /* ─── Image Wrapper ─── */
    .investor-img-wrapper {
        position: relative;
        height: 220px;
        overflow: hidden;
        background: linear-gradient(135deg, #4361ee 0%, #7b2ff7 100%);
    }

    .investor-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .investor-img-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #4361ee 0%, #7b2ff7 100%);
    }

    .investor-img-placeholder i {
        font-size: 6rem;
        color: rgba(255, 255, 255, 0.6);
    }

    /* ─── Profit Badge ─── */
    .profit-badge {
        position: absolute;
        bottom: 12px;
        right: 12px;
        background: rgba(255, 255, 255, 0.95);
        color: #4361ee;
        font-weight: 700;
        font-size: 0.85rem;
        padding: 5px 12px;
        border-radius: 50px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    }

    /* ─── Investor Name ─── */
    .investor-name {
        font-weight: 700;
        font-size: 1.05rem;
        color: #1a1a2e;
        margin-bottom: 12px;
    }

    /* ─── Detail Rows ─── */
    .investor-details {
        display: flex;
        flex-direction: column;
        gap: 7px;
    }

    .detail-row {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.875rem;
        color: #555;
    }

    .detail-row i {
        font-size: 0.85rem;
        flex-shrink: 0;
        width: 16px;
    }

    /* ─── Empty State ─── */
    .empty-state-icon {
        width: 90px;
        height: 90px;
        background: linear-gradient(135deg, #4361ee20, #7b2ff720);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
    }

    .empty-state-icon i {
        font-size: 3rem;
        color: #4361ee;
    }

    /* ─── Modal ─── */
    .modal-content {
        border: none;
        border-radius: 14px;
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.18);
        overflow: hidden;
    }

    .modal-header {
        background: linear-gradient(135deg, #4361ee, #7b2ff7);
        color: white;
        border-bottom: none;
        padding: 1.2rem 1.5rem;
    }

    .modal-title {
        color: white !important;
    }

    .modal-body {
        padding: 1.5rem;
    }

    /* ─── Form Controls ─── */
    .form-control,
    .form-select,
    .input-group-text {
        border-radius: 8px;
        padding: 0.7rem 1rem;
        border: 1px solid #e2e8f0;
        font-size: 0.9rem;
    }

    .input-group .form-control {
        border-radius: 8px 0 0 8px;
    }

    .input-group .input-group-text {
        border-radius: 0 8px 8px 0;
        background: #f8f9fa;
        font-weight: 600;
        color: #4361ee;
    }

    .form-control:focus,
    .form-select:focus {
        box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
        border-color: #4361ee;
    }

    /* ─── Buttons ─── */
    .btn {
        border-radius: 8px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-primary {
        background: linear-gradient(135deg, #4361ee, #7b2ff7);
        border: none;
        padding: 0.75rem 1.5rem;
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #3a53d6, #6a24e0);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(67, 97, 238, 0.35);
    }

    .btn-outline-danger {
        border-radius: 8px;
        padding: 0.55rem 1rem;
    }

    .btn-danger {
        background-color: #e63946;
        border-color: #e63946;
    }

    .btn-danger:hover {
        background-color: #d00000;
        border-color: #d00000;
        transform: translateY(-2px);
    }

    /* ─── Card Footer ─── */
    .card-footer {
        background: transparent;
    }

    /* ─── Alerts ─── */
    .alert {
        border-radius: 10px;
        border: none;
    }
</style>
@endpush