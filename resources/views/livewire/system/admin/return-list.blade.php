<div class="container-fluid py-3">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h3 class="fw-bold text-dark mb-2">
                <i class="bi bi-arrow-return-left text-success me-2"></i> Product Returns List
            </h3>
            <p class="text-muted mb-0">View and manage all product returns</p>
        </div>
    </div>

    <!-- Returns Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold mb-0">
                    <i class="bi bi-list-ul text-primary me-2"></i> Returns List
                </h5>
                <span class="badge bg-primary">{{ count($returns) }} records</span>
            </div>
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3" style="width: 60%; margin: auto">
                <!-- 🔍 Search Bar -->
                <div class="search-bar flex-grow-1">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" class="form-control border-start-0" wire:model.live="returnSearch"
                            placeholder="Search by invoice number or product name...">
                    </div>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <label class="text-sm text-muted fw-medium">Show</label>
                <select wire:model.live="perPage" class="form-select form-select-sm" style="width: 80px;">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                    <option value="200">200</option>
                    <option value="500">500</option>
                </select>
                <span class="text-sm text-muted">entries</span>
            </div>
        </div>
        <div class="card-body p-0 overflow-auto">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4" style="width: 50px;">
                                <input type="checkbox" class="form-check-input" id="selectAll" onclick="toggleAllRows(this)">
                            </th>
                            <th class="ps-4">#</th>
                            <th>Invoice Number</th>
                            <th>Product</th>
                            <th>Return Qty</th>
                            <th>Unit Price</th>
                            <th>Total</th>
                            <th>Date</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($returns as $index => $return)
                        <tr style="cursor:pointer" wire:key="return-{{ $return->id }}" class="table-row" data-id="{{ $return->id }}">
                            <td class="ps-4" onclick="event.stopPropagation();">
                                <input type="checkbox" class="form-check-input row-checkbox" onchange="toggleRowHighlight(this)">
                            </td>
                            <td class="ps-4">{{ $index + 1 }}</td>
                            <td wire:click="showReceipt({{ $return->id }})">{{ $return->sale?->invoice_number ?? '-' }}</td>
                            <td wire:click="showReceipt({{ $return->id }})">{{ $return->product?->name ?? '-' }}</td>
                            <td wire:click="showReceipt({{ $return->id }})">{{ $return->return_quantity }}</td>
                            <td wire:click="showReceipt({{ $return->id }})">Rs.{{ number_format($return->selling_price, 2) }}</td>
                            <td wire:click="showReceipt({{ $return->id }})">Rs.{{ number_format($return->total_amount, 2) }}</td>
                            <td wire:click="showReceipt({{ $return->id }})">{{ $return->created_at?->format('d/m/Y') }}</td>
                            <td class="text-end pe-4">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                        type="button"
                                        data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                        <i class="bi bi-gear-fill"></i> Actions
                                    </button>

                                    <ul class="dropdown-menu dropdown-menu-end">

                                        <!-- Delete Return -->
                                        <li>
                                            <button class="dropdown-item"
                                                wire:click="deleteReturn({{ $return->id }})"
                                                wire:loading.attr="disabled"
                                                wire:target="deleteReturn({{ $return->id }})">

                                                <span wire:loading wire:target="deleteReturn({{ $return->id }})">
                                                    <i class="spinner-border spinner-border-sm me-2"></i>
                                                    Loading...
                                                </span>
                                                <span wire:loading.remove wire:target="deleteReturn({{ $return->id }})">
                                                    <i class="bi bi-trash text-danger me-2"></i>
                                                    Delete
                                                </span>
                                            </button>
                                        </li>

                                    </ul>
                                </div>
                            </td>

                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="bi bi-arrow-return-left display-4 d-block mb-2"></i>
                                No returns found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($returns->hasPages())
            <div class="card-footer bg-light">
                <div class="d-flex justify-content-center">
                    {{ $returns->links('livewire.custom-pagination') }}
                </div>
            </div>
            @endif
        </div>
    </div>


    <!-- Receipt Modal (Bill Style) -->
    <div wire:ignore.self class="modal fade" id="receiptModal" tabindex="-1" aria-labelledby="receiptModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" id="printableReturnReceipt">
                <!-- Header – logo + company name -->
                <div class="modal-header text-center border-0" style="background: linear-gradient(90deg, #3b5b0c, #8eb922); color: #fff;">
                    <div class="w-100">
                        <img src="{{ asset('images/USN.png') }}" alt="Logo"
                            class="img-fluid mb-2" style="max-height:60px;">
                        <h4 class="mb-0 fw-bold">USN AUTO PARTS</h4>

                    </div>
                    <button type="button" class="btn-close btn-close-white closebtn"
                        wire:click="closeModal"></button>
                </div>

                @if($selectedReturn)
                <div class="modal-body">
                    <!-- Customer + Return info (two columns) -->
                    <div class="row mb-4">
                        <div class="col-6">
                            <strong>Customer :</strong><br>
                            {{ $selectedReturn->sale?->customer?->name ?? 'Walk-in Customer' }}<br>
                            {{ $selectedReturn->sale?->customer?->address ?? '' }}<br>
                            Tel: {{ $selectedReturn->sale?->customer?->phone ?? '' }}
                        </div>
                        <div class="col-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td><strong>Return No :</strong></td>
                                    <td>{{ $selectedReturn->id }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Invoice No :</strong></td>
                                    <td>{{ $selectedReturn->sale?->invoice_number ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Return Status :</strong></td>
                                    <td>Completed</td>
                                </tr>
                                <tr>
                                    <td><strong>Return Date :</strong></td>
                                    <td>{{ $selectedReturn->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Items table -->
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:5%">#</th>
                                    <th style="width:15%">ITEM CODE</th>
                                    <th>DESCRIPTION</th>
                                    <th class="text-center" style="width:12%">RETURN QTY</th>
                                    <th class="text-end" style="width:12%">UNIT PRICE</th>
                                    <th class="text-end" style="width:12%">SUBTOTAL</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td>{{ $selectedReturn->product?->code ?? 'N/A' }}</td>
                                    <td>{{ $selectedReturn->product?->name ?? 'N/A' }}</td>
                                    <td class="text-center">{{ $selectedReturn->return_quantity }} Pc(s)</td>
                                    <td class="text-end">Rs.{{ number_format($selectedReturn->selling_price, 2) }}</td>
                                    <td class="text-end">Rs.{{ number_format($selectedReturn->total_amount, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Totals – right-aligned block -->
                    <div class="row">
                        <div class="col-7"></div>
                        <div class="col-5">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td class="text-end"><strong>Total Return Amount (LKR)</strong></td>
                                    <td class="text-end">Rs.{{ number_format($selectedReturn->total_amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="text-end"><strong>Refunded Amount (LKR)</strong></td>
                                    <td class="text-end">Rs.{{ number_format($selectedReturn->total_amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="text-end"><strong>Balance (LKR)</strong></td>
                                    <td class="text-end">Rs.0.00</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="row mt-4  note">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-body p-3">
                                    <strong>Notes:</strong><br>
                                    {{ $selectedReturn->notes ?? 'No additional notes.' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer – logos + address + note -->
                    <div class="mt-4 text-center small">

                        <p class="mb-0">
                            <strong>ADDRESS :</strong> {{ config('shop.address', 'NO 92, PASYALA ROAD, KAL-ELIYA, SRI-LANKA.') }}<br>
                            <strong>TEL :</strong> {{ config('shop.phone', '+94 70 58 53 442') }}, <strong>EMAIL :</strong> {{ config('shop.email', 'phoenixbathware@gmail.com') }}
                        </p>
                        <p class="mt-1 text-muted">
                            Goods return will be accepted within 10 days only. Electrical and body parts non-returnable.
                        </p>
                    </div>
                </div>
                @endif

                <!-- Modal footer buttons -->
                <div class="modal-footer bg-light justify-content-between">
                    <button type="button" class="btn btn-secondary" wire:click="closeModal">
                        <i class="bi bi-x-circle me-1"></i> Close
                    </button>
                    <div>
                        @if($currentReturnId)

                        <button type="button" class="btn btn-primary"
                            onclick="printReturnReceipt()">
                            <i class="bi bi-printer me-1"></i> Print
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div wire:ignore.self class="modal fade" id="deleteReturnModal" tabindex="-1"
        aria-labelledby="deleteReturnModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fw-bold"><i class="bi bi-exclamation-triangle me-2"></i> Confirm Deletion</h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeModal"></button>
                </div>
                <div class="modal-body">
                    @if($selectedReturn)
                    <div class="alert alert-danger">
                        <h6 class="alert-heading">Warning!</h6>
                        <p class="mb-0">You are about to delete this return record. This action cannot be undone and will adjust product stock accordingly.</p>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <p><strong>Return ID:</strong> #{{ $selectedReturn->id }}</p>
                            <p><strong>Product:</strong> {{ $selectedReturn->product?->name ?? '-' }}</p>
                            <p><strong>Quantity:</strong> {{ $selectedReturn->return_quantity }}</p>
                            <p><strong>Amount:</strong> Rs.{{ number_format($selectedReturn->total_amount, 2) }}</p>
                            <p><strong>Date:</strong> {{ $selectedReturn->created_at?->format('d/m/Y') }}</p>
                        </div>
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeModal">Cancel</button>
                    <button type="button" class="btn btn-danger" wire:click="confirmDeleteReturn">
                        <i class="bi bi-trash me-1"></i> Delete Return
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div class="toast-container position-fixed top-0 end-0 p-3">
        <div id="livewire-toast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <strong class="me-auto">Notification</strong>
                <small>Just now</small>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body"></div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .note {
        display: block;
    }

    .table th {
        font-weight: 600;
        background-color: #f8f9fa;
    }

    .btn-group-sm>.btn {
        padding: 0.25rem 0.5rem;
    }

    .modal-header {
        border-bottom: 1px solid #dee2e6;
    }

    .badge {
        font-size: 0.75em;
    }

    .table th {
        border-top: none;
        font-weight: 600;
        color: #ffffff;

        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .table-hover tbody tr:hover {
        background-color: rgba(0, 0, 0, 0.025);
    }

    /* Row selection highlight - Multiple specificity levels */
    tr.selected-row,
    table tbody tr.selected-row,
    table.table tbody tr.selected-row,
    .table tbody tr.selected-row {
        background-color: #d4e6f1 !important;
    }

    tr.selected-row td,
    table tbody tr.selected-row td,
    table.table tbody tr.selected-row td {
        background-color: #d4e6f1 !important;
    }

    table tbody tr.selected-row:hover,
    table.table tbody tr.selected-row:hover {
        background-color: #b8d7e8 !important;
    }

    table tbody tr.selected-row:hover td,
    table.table tbody tr.selected-row:hover td {
        background-color: #b8d7e8 !important;
    }

    .closebtn {
        top: 3%;
        right: 3%;
        position: absolute;
    }

    /* Print styles for receipt */
    @media print {

        .note {
            display: none;
        }

        body * {
            visibility: hidden;
        }

        #printableReturnReceipt,
        #printableReturnReceipt * {
            visibility: visible;
        }

        #printableReturnReceipt {
            position: absolute;
            left: 0;
            top: 0;
            width: 210mm;
            min-height: 297mm;
            padding: 15mm;
            background: #fff;
            font-size: 11pt;
            color: #000;
        }

        .modal,
        .modal-dialog,
        .modal-content {
            all: unset;
        }

        .modal-footer,
        .btn,
        .btn-close {
            display: none !important;
        }

        .modal-header {
            border: none;
            padding: 0;
            text-align: center;
            margin-bottom: 1rem;
            background: #000 !important;
            color: #000 !important;
        }

        .modal-header img {
            max-height: 55px;
            filter: brightness(0) !important;
        }

        .modal-header h4 {
            margin: 4px 0;
            font-size: 1.4rem;
            color: #000;
        }

        .modal-header p {
            margin: 0;
            font-size: 0.85rem;
            color: #000;
        }

        .table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: .8rem;
        }

        .table th,
        .table td {
            border: 1px solid #999;
            padding: 4px 6px;
        }

        .table th {
            background: #e9ecef;
            -webkit-print-color-adjust: exact;
        }

        .table-sm {
            font-size: 0.9rem;
        }

        .table-sm td {
            border: none;
            padding: 2px 4px;
        }

        .table-sm strong {
            min-width: 110px;
            display: inline-block;
        }

        .d-flex img {
            height: 30px;
            margin: 0 8px;
        }

        // Row selection functions
        function toggleRowHighlight(checkbox) {
            const row=checkbox.closest('tr');

            if (checkbox.checked) {
                row.classList.add('selected-row');
                row.style.backgroundColor='#d4e6f1';
            }

            else {
                row.classList.remove('selected-row');
                row.style.backgroundColor='';
                document.getElementById('selectAll').checked=false;
            }
        }

        function toggleAllRows(selectAllCheckbox) {
            const checkboxes=document.querySelectorAll('.row-checkbox');

            checkboxes.forEach(checkbox=> {
                    checkbox.checked=selectAllCheckbox.checked;
                    toggleRowHighlight(checkbox);
                });
        }

        .text-muted {
            font-size: 0.8rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('showModal', (modalId) => {
            const el = document.getElementById(modalId);
            if (el) new bootstrap.Modal(el).show();
        });

        Livewire.on('hideModal', (modalId) => {
            const modal = bootstrap.Modal.getInstance(document.getElementById(modalId));
            if (modal) modal.hide();
        });

        Livewire.on('showToast', (e) => {
            const toast = document.getElementById('livewire-toast');
            toast.querySelector('.toast-body').textContent = e.message;
            toast.querySelector('.toast-header').className = 'toast-header text-white bg-' + e.type;
            new bootstrap.Toast(toast).show();
        });

        Livewire.on('printReceipt', () => {
            printReturnReceipt();
        });

        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') Livewire.dispatch('closeModals');
        });
    });

    function printReturnReceipt() {
        window.print();
    }
</script>
@endpush
