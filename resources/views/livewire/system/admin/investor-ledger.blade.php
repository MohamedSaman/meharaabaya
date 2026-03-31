@php use Illuminate\Support\Facades\Storage; @endphp
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">
                <i class="bi bi-person-vcard-fill text-primary me-2"></i>
                Investor Cash Flow
            </h3>
            <p class="text-muted mb-0">Track inflow and outflow for this investor</p>
        </div>
        <a href="{{ route('admin.profit-share') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Investors
        </a>
    </div>

    @if (session()->has('success'))
    <div x-data="{ show: true }" 
         x-show="show" 
         x-init="setTimeout(() => show = false, 3000)"
         x-transition.opacity.duration.500ms
         class="position-fixed bottom-0 end-0 p-4" 
         style="z-index: 1060;">
        <div class="alert alert-success shadow-lg border-0 d-flex align-items-center mb-0" role="alert" style="min-width: 300px; border-radius: 12px; background-color: #d1fae5; color: #065f46;">
            <i class="bi bi-check-circle-fill fs-5 me-3"></i>
            <div class="fw-bold fs-6">
                {{ session('success') }}
            </div>
            <button type="button" class="btn-close ms-auto" @click="show = false" aria-label="Close"></button>
        </div>
    </div>
    @endif

    {{-- Investor Profile Hero --}}
    <div class="card border-0 shadow-sm mb-4 investor-hero compact-hero">
        <div class="card-body px-4 py-3">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="avatar-wrap compact-avatar">
                    @if($investor->image)
                    <img src="{{ Storage::disk('public')->url($investor->image) }}" alt="{{ $investor->name }}" class="avatar-img">
                    @else
                    <i class="bi bi-person-circle avatar-icon compact-icon"></i>
                    @endif
                </div>
                <div class="flex-grow-1">
                    <h6 class="mb-1 fw-700">{{ $investor->name }}</h6>
                    <small class="text-muted">Investor Profile</small>
                    <div class="profile-chip chip-share">{{ number_format($investor->profit_share_percentage, 2) }}%</div>
                </div>
                <div class="d-flex flex-wrap gap-2 profile-kpi-row">
                    <div class="profile-chip chip-kpi inflow">
                        <small class="d-block text-uppercase opacity-75">Inflow</small>
                        <strong>{{ number_format($totals['inflow'], 2) }}</strong>
                    </div>
                    <div class="profile-chip chip-kpi outflow">
                        <small class="d-block text-uppercase opacity-75">Outflow</small>
                        <strong>{{ number_format($totals['outflow'], 2) }}</strong>
                    </div>
                    <div class="profile-chip chip-kpi balance">
                        <small class="d-block text-uppercase opacity-75">Balance</small>
                        <strong>{{ number_format($totals['balance'], 2) }}</strong>
                    </div>
                </div>
            </div>

            @if($investor->notes)
            <div class="small text-muted mt-3 ps-1 pt-2 border-top"><i class="bi bi-sticky-fill text-warning me-1"></i><strong>Notes:</strong> {{ $investor->notes }}</div>
            @endif
        </div>
    </div>

    {{-- Ledger Table --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold"><i class="bi bi-table me-1 text-primary"></i>Ledger</h5>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-outline-primary" wire:click="addRow">
                    <i class="bi bi-plus-lg me-1"></i>Add Line
                </button>
                <button type="button" class="btn btn-sm btn-primary" wire:click="saveRows" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="saveRows"><i class="bi bi-save me-1"></i>Save All</span>
                    <span wire:loading wire:target="saveRows"><span class="spinner-border spinner-border-sm me-1"></span>Saving...</span>
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0 excel-table excel-grid-table">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4" style="min-width:130px">Date</th>
                            <th style="min-width:240px">Description</th>
                            <th style="min-width:140px">Inflow</th>
                            <th style="min-width:220px">Outflow</th>
                            <th style="min-width:130px" class="text-center">Payment</th>
                            <th style="width: 50px;" class="text-center"> <i class="bi bi-gear"></i> </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $index => $row)
                        @php
                        $isProfitMargin = str_contains(strtolower((string) ($row['description'] ?? '')), 'profit margin');
                        $hasOutflow = is_numeric($row['outflow'] ?? null) && (float)($row['outflow']) > 0;
                        $hasInflow = is_numeric($row['inflow'] ?? null) && (float)($row['inflow']) > 0;
                        $paymentMethod = $row['payment_method'] ?? '';
                        @endphp
                        <tr>
                            {{-- Date --}}
                            <td class="ps-4">
                                <input type="date"
                                    class="form-control form-control-sm date-input"
                                    wire:model.defer="rows.{{ $index }}.transaction_date"
                                    wire:blur="saveRow({{ $index }})">
                            </td>

                            {{-- Description --}}
                            <td>
                                <input type="text"
                                    class="form-control @error('rows.' . $index . '.description') is-invalid @enderror"
                                    wire:model.defer="rows.{{ $index }}.description"
                                    wire:change="onDescriptionChanged({{ $index }})"
                                    wire:blur="saveRow({{ $index }})"
                                    list="ledger-description-suggestions"
                                    placeholder="Type description">
                                @error('rows.' . $index . '.description') <span class="text-danger small">{{ $message }}</span> @enderror
                            </td>

                            {{-- Inflow --}}
                            <td>
                                <input type="number"
                                    step="0.01"
                                    min="0"
                                    class="form-control @error('rows.' . $index . '.inflow') is-invalid @enderror"
                                    wire:model.defer="rows.{{ $index }}.inflow"
                                    wire:blur="requestPaymentSave({{ $index }})"
                                    @if($isProfitMargin) readonly @endif
                                    placeholder="0.00">
                                @error('rows.' . $index . '.inflow') <span class="text-danger small">{{ $message }}</span> @enderror
                            </td>

                            {{-- Outflow --}}
                            <td>
                                <div class="outflow-inline-wrap">
                                    <input type="number"
                                        step="0.01"
                                        min="0"
                                        class="form-control @error('rows.' . $index . '.outflow') is-invalid @enderror"
                                        wire:model.defer="rows.{{ $index }}.outflow"
                                        @if($isProfitMargin) readonly @endif
                                        wire:blur="requestPaymentSave({{ $index }})"
                                        placeholder="0.00">

                                    @if($isProfitMargin)
                                    <select class="form-select form-select-sm month-inline-select @error('rows.' . $index . '.outflow_month') is-invalid @enderror"
                                        wire:model="rows.{{ $index }}.outflow_month"
                                        wire:change="onOutflowMonthChanged({{ $index }})">
                                        <option value="">Select month</option>
                                        @foreach($monthOptions as $month)
                                        <option value="{{ $month['value'] }}">{{ $month['label'] }}</option>
                                        @endforeach
                                    </select>
                                    @endif
                                </div>

                                @error('rows.' . $index . '.outflow') <span class="text-danger small">{{ $message }}</span> @enderror
                                @error('rows.' . $index . '.outflow_month') <span class="text-danger small">{{ $message }}</span> @enderror
                            </td>

                            {{-- Payment Method Badge --}}
                            <td class="text-center">
                                @if(($hasOutflow || $hasInflow) && $paymentMethod)
                                    @if($paymentMethod === 'cash')
                                        <span class="badge payment-badge badge-cash">
                                            <i class="bi bi-cash-stack me-1"></i>Cash
                                        </span>
                                    @elseif($paymentMethod === 'cheque')
                                        <div class="cheque-tooltip-wrap">
                                            <span class="badge payment-badge badge-cheque cheque-clickable"
                                                  wire:click="viewChequeDetails({{ $index }})"
                                                  style="cursor:pointer">
                                                <i class="bi bi-file-earmark-text me-1"></i>Cheque
                                                @if(!empty($row['cheque_number']))
                                                <small class="d-block cheque-badge-num">{{ Str::limit($row['cheque_number'], 12) }}</small>
                                                @endif
                                            </span>
                                            {{-- Hover Tooltip --}}
                                            @if(!empty($row['cheque_number']))
                                            <div class="cheque-hover-card">
                                                <div class="chc-header">
                                                    <i class="bi bi-file-earmark-text"></i>
                                                    <span>Cheque Details</span>
                                                </div>
                                                <div class="chc-body">
                                                    <div class="chc-row">
                                                        <span class="chc-label">Number</span>
                                                        <span class="chc-value">{{ $row['cheque_number'] }}</span>
                                                    </div>
                                                    @if(!empty($row['cheque_bank']))
                                                    <div class="chc-row">
                                                        <span class="chc-label">Bank</span>
                                                        <span class="chc-value">{{ $row['cheque_bank'] }}</span>
                                                    </div>
                                                    @endif
                                                    @if(!empty($row['cheque_date']))
                                                    <div class="chc-row">
                                                        <span class="chc-label">Date</span>
                                                        <span class="chc-value">{{ $row['cheque_date'] }}</span>
                                                    </div>
                                                    @endif
                                                    @if(!empty($row['cheque_amount']))
                                                    <div class="chc-row">
                                                        <span class="chc-label">Amount</span>
                                                        <span class="chc-value fw-bold">Rs. {{ number_format((float)$row['cheque_amount'], 2) }}</span>
                                                    </div>
                                                    @endif
                                                    @if(!empty($row['cheque_customer']))
                                                    <div class="chc-row">
                                                        <span class="chc-label">Customer</span>
                                                        <span class="chc-value">{{ $row['cheque_customer'] }}</span>
                                                    </div>
                                                    @endif
                                                </div>
                                                <div class="chc-footer">
                                                    <small><i class="bi bi-hand-index me-1"></i>Click for full details</small>
                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                    @elseif($paymentMethod === 'online')
                                        <span class="badge payment-badge badge-online">
                                            <i class="bi bi-globe me-1"></i>Online
                                        </span>
                                    @endif
                                @elseif($hasOutflow || $hasInflow)
                                    <button type="button" class="btn btn-xs btn-outline-secondary" wire:click="requestPaymentSave({{ $index }})">
                                        <i class="bi bi-credit-card me-1"></i>Set
                                    </button>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            {{-- Action / Delete --}}
                            <td class="text-center pe-4">
                                <button type="button" class="btn btn-sm text-danger border-0 bg-transparent hover-danger" 
                                    wire:click="deleteRow({{ $index }})" 
                                    title="Delete Row" 
                                    wire:confirm="Are you sure you want to completely delete this line? This cannot be undone.">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light ledger-total-footer">
                        <tr>
                            <th class="ps-4"></th>
                            <th class="text-uppercase">Totals</th>
                            <th>
                                <span class="text-muted small d-block">Total Inflow</span>
                                <span class="fw-bold text-success">{{ number_format($totals['inflow'], 2) }}</span>
                            </th>
                            <th>
                                <span class="text-muted small d-block">Total Outflow</span>
                                <span class="fw-bold text-danger">{{ number_format($totals['outflow'], 2) }}</span>
                            </th>
                            <th class="text-center">
                                <span class="text-muted small d-block">Balance</span>
                                <span class="fw-bold {{ $totals['balance'] >= 0 ? 'text-primary' : 'text-danger' }}">{{ number_format($totals['balance'], 2) }}</span>
                            </th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <datalist id="ledger-description-suggestions">
                @foreach($descriptionSuggestions as $suggestion)
                <option value="{{ $suggestion }}"></option>
                @endforeach
            </datalist>

            @error('rows')
            <div class="px-4 py-2 text-danger small">{{ $message }}</div>
            @enderror
        </div>
    </div>

    {{-- ─── Payment Method Modal ───────────────────────────────────────── --}}
    @if($showPaymentModal)
    <div class="pm-overlay" wire:click.self="closePaymentModal">
        <div class="pm-modal" wire:click.stop>
            {{-- Modal Header --}}
            <div class="pm-header">
                <div class="pm-header-icon">
                    <i class="bi bi-wallet2"></i>
                </div>
                <div>
                    <h5 class="mb-0 fw-bold">Select Payment Method</h5>
                    <small class="text-muted">Choose how this {{ $isPendingInflow ? 'inflow' : 'outflow' }} will be paid</small>
                </div>
                <button type="button" class="btn-close ms-auto" wire:click="closePaymentModal"></button>
            </div>

            {{-- Amount Display --}}
            @if($pendingPaymentIndex !== null && isset($rows[$pendingPaymentIndex]))
            <div class="pm-amount-strip">
                <span class="pm-amount-label">{{ $isPendingInflow ? 'Inflow Amount' : 'Outflow Amount' }}</span>
                <span class="pm-amount-value">Rs. {{ number_format((float)($rows[$pendingPaymentIndex][$isPendingInflow ? 'inflow' : 'outflow'] ?? 0), 2) }}</span>
            </div>
            @endif

            {{-- Modal Body --}}
            <div class="pm-body">

                <div class="pm-options">
                    {{-- Cash --}}
                    <label class="pm-option {{ $selectedPaymentMethod === 'cash' ? 'active' : '' }}">
                        <input type="radio" wire:model.live="selectedPaymentMethod" value="cash" class="d-none">
                        <div class="pm-option-icon cash-icon">
                            <i class="bi bi-cash-stack"></i>
                        </div>
                        <div class="pm-option-details">
                            <strong>Cash</strong>
                            <small>Pay with cash</small>
                        </div>
                        <div class="pm-option-check">
                            @if($selectedPaymentMethod === 'cash')
                            <i class="bi bi-check-circle-fill text-success"></i>
                            @else
                            <i class="bi bi-circle text-muted"></i>
                            @endif
                        </div>
                    </label>

                    {{-- Cheque --}}
                    <label class="pm-option {{ $selectedPaymentMethod === 'cheque' ? 'active' : '' }}">
                        <input type="radio" wire:model.live="selectedPaymentMethod" value="cheque" class="d-none">
                        <div class="pm-option-icon cheque-icon">
                            <i class="bi bi-file-earmark-text"></i>
                        </div>
                        <div class="pm-option-details">
                            <strong>Cheque</strong>
                            <small>{{ $isPendingInflow ? 'Record as cheque payment' : 'Use available cheque' }}</small>
                        </div>
                        <div class="pm-option-check">
                            @if($selectedPaymentMethod === 'cheque')
                            <i class="bi bi-check-circle-fill text-success"></i>
                            @else
                            <i class="bi bi-circle text-muted"></i>
                            @endif
                        </div>
                    </label>

                    {{-- Online --}}
                    <label class="pm-option {{ $selectedPaymentMethod === 'online' ? 'active' : '' }}">
                        <input type="radio" wire:model.live="selectedPaymentMethod" value="online" class="d-none">
                        <div class="pm-option-icon online-icon">
                            <i class="bi bi-globe"></i>
                        </div>
                        <div class="pm-option-details">
                            <strong>Online Transfer</strong>
                            <small>Bank or digital payment</small>
                        </div>
                        <div class="pm-option-check">
                            @if($selectedPaymentMethod === 'online')
                            <i class="bi bi-check-circle-fill text-success"></i>
                            @else
                            <i class="bi bi-circle text-muted"></i>
                            @endif
                        </div>
                    </label>
                </div>

                {{-- Cheque Selection Panel (shown only when cheque is selected and it is an outflow) --}}
                @if($selectedPaymentMethod === 'cheque' && !$isPendingInflow)
                <div class="pm-cheque-panel">
                    <div class="pm-cheque-header">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-search me-1"></i>Find Cheque</h6>
                        <span class="badge bg-secondary">{{ count($availableCheques) }} available</span>
                    </div>

                    {{-- Search Bar --}}
                    <div class="pm-cheque-search">
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                            <input type="text"
                                class="form-control"
                                wire:model.live.debounce.300ms="chequeSearch"
                                placeholder="Search cheque number or bank...">
                            @if($chequeSearch)
                            <button class="btn btn-outline-secondary" type="button" wire:click="$set('chequeSearch', '')">
                                <i class="bi bi-x-lg"></i>
                            </button>
                            @endif
                        </div>
                    </div>

                    @error('chequeSelection')
                    <div class="alert alert-danger alert-sm py-2 px-3 mb-2">
                        <i class="bi bi-exclamation-triangle me-1"></i>{{ $message }}
                    </div>
                    @enderror

                    {{-- Cheque List --}}
                    <div class="pm-cheque-list">
                        @forelse($availableCheques as $cheque)
                        <div class="pm-cheque-item {{ $selectedChequeId === $cheque['id'] ? 'selected' : '' }}"
                             wire:click="selectCheque({{ $cheque['id'] }})">
                            <div class="pm-cheque-item-main">
                                <div class="pm-cheque-num">
                                    <i class="bi bi-file-earmark-text me-1"></i>
                                    {{ $cheque['cheque_number'] }}
                                </div>
                                <div class="pm-cheque-meta">
                                    <span><i class="bi bi-bank me-1"></i>{{ $cheque['bank_name'] }}</span>
                                    <span><i class="bi bi-calendar3 me-1"></i>{{ $cheque['cheque_date'] }}</span>
                                </div>
                                @if($cheque['customer_name'] !== 'N/A')
                                <div class="pm-cheque-meta">
                                    <span><i class="bi bi-person me-1"></i>{{ $cheque['customer_name'] }}</span>
                                </div>
                                @endif
                            </div>
                            <div class="pm-cheque-item-amount">
                                <span class="fw-bold">Rs. {{ number_format($cheque['cheque_amount'], 2) }}</span>
                                @if($selectedChequeId === $cheque['id'])
                                <i class="bi bi-check-circle-fill text-success mt-1"></i>
                                @endif
                            </div>
                        </div>
                        @empty
                        <div class="pm-cheque-empty">
                            <i class="bi bi-inbox text-muted" style="font-size:2rem"></i>
                            <p class="mb-0 text-muted mt-2">No pending cheques found</p>
                            @if($chequeSearch)
                            <small class="text-muted">Try a different search term</small>
                            @endif
                        </div>
                        @endforelse
                    </div>
                </div>
                @endif
            </div>

            {{-- Modal Footer --}}
            <div class="pm-footer">
                <button type="button" class="btn btn-outline-secondary" wire:click="closePaymentModal">
                    <i class="bi bi-x-lg me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-primary" wire:click="confirmPaymentMethod"
                    wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="confirmPaymentMethod">
                        <i class="bi bi-check-lg me-1"></i>Confirm & Save
                    </span>
                    <span wire:loading wire:target="confirmPaymentMethod">
                        <span class="spinner-border spinner-border-sm me-1"></span>Saving...
                    </span>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ─── Cheque Detail View Modal ─────────────────────────────── --}}
    @if($showChequeDetail && !empty($chequeDetail))
    <div class="pm-overlay" wire:click.self="closeChequeDetailModal">
        <div class="cd-modal" wire:click.stop>
            <div class="cd-header">
                <div class="cd-header-icon">
                    <i class="bi bi-file-earmark-text"></i>
                </div>
                <div>
                    <h5 class="mb-0 fw-bold">Cheque Details</h5>
                    <small class="text-muted">{{ $chequeDetail['description'] ?? '' }}</small>
                </div>
                <button type="button" class="btn-close ms-auto" wire:click="closeChequeDetailModal"></button>
            </div>

            <div class="cd-body">
                {{-- Cheque Number Hero --}}
                <div class="cd-cheque-hero">
                    <div class="cd-cheque-icon">
                        <i class="bi bi-file-earmark-text"></i>
                    </div>
                    <div class="cd-cheque-number">{{ $chequeDetail['cheque_number'] }}</div>
                </div>

                {{-- Details Grid --}}
                <div class="cd-details-grid">
                    <div class="cd-detail-item">
                        <div class="cd-detail-icon"><i class="bi bi-bank"></i></div>
                        <div>
                            <span class="cd-detail-label">Bank Name</span>
                            <span class="cd-detail-value">{{ $chequeDetail['bank_name'] }}</span>
                        </div>
                    </div>

                    <div class="cd-detail-item">
                        <div class="cd-detail-icon"><i class="bi bi-calendar3"></i></div>
                        <div>
                            <span class="cd-detail-label">Cheque Date</span>
                            <span class="cd-detail-value">{{ $chequeDetail['cheque_date'] }}</span>
                        </div>
                    </div>

                    <div class="cd-detail-item">
                        <div class="cd-detail-icon"><i class="bi bi-cash-coin"></i></div>
                        <div>
                            <span class="cd-detail-label">Amount</span>
                            <span class="cd-detail-value fw-bold text-primary">Rs. {{ $chequeDetail['cheque_amount'] }}</span>
                        </div>
                    </div>

                    <div class="cd-detail-item">
                        <div class="cd-detail-icon"><i class="bi bi-flag"></i></div>
                        <div>
                            <span class="cd-detail-label">Status</span>
                            <span class="cd-detail-value">
                                @if($chequeDetail['status'] === 'Complete')
                                    <span class="badge bg-success-subtle text-success">{{ $chequeDetail['status'] }}</span>
                                @elseif($chequeDetail['status'] === 'Pending')
                                    <span class="badge bg-warning-subtle text-warning">{{ $chequeDetail['status'] }}</span>
                                @elseif($chequeDetail['status'] === 'Return')
                                    <span class="badge bg-danger-subtle text-danger">{{ $chequeDetail['status'] }}</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary">{{ $chequeDetail['status'] }}</span>
                                @endif
                            </span>
                        </div>
                    </div>

                    <div class="cd-detail-item">
                        <div class="cd-detail-icon"><i class="bi bi-person"></i></div>
                        <div>
                            <span class="cd-detail-label">Customer</span>
                            <span class="cd-detail-value">{{ $chequeDetail['customer_name'] }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="cd-footer">
                <button type="button" class="btn btn-outline-secondary" wire:click="closeChequeDetailModal">
                    <i class="bi bi-x-lg me-1"></i>Close
                </button>
            </div>
        </div>
    </div>
    @endif

    @push('styles')
    <style>
        /* ─── Investor Hero ─────────────────────────────────────────── */
        .investor-hero {
            background: linear-gradient(160deg, #f7faff 0%, #eef5ff 100%);
            border-radius: 16px;
        }

        .compact-hero {
            border-radius: 12px;
        }

        .single-line-profile {
            row-gap: 8px;
        }

        .profile-contact-row {
            column-gap: 8px;
            row-gap: 6px;
        }

        .profile-kpi-row {
            column-gap: 10px;
            row-gap: 6px;
        }

        .profile-chip {
            background: #ffffff;
            border: 1px solid #e6ecf8;
            border-radius: 8px;
            padding: 6px 12px;
            font-size: 0.82rem;
            color: #1f2937;
            white-space: nowrap;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            display: flex;
            align-items: center;
            transition: all 0.2s ease;
        }

        .profile-chip:hover {
            border-color: #d1dff0;
            background: #fafbfc;
        }

        .profile-chip span {
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .chip-share {
            background: #0f172a;
            color: #ffffff;
            border-color: #0f172a;
            padding: 8px 16px;
            font-weight: 600;
            letter-spacing: 0.3px;
            width: max-content;
        }

        .chip-share:hover {
            background: #1a2241;
            border-color: #1a2241;
        }

        .chip-kpi {
            padding: 8px 14px;
            border-color: transparent;
            flex-direction: column;
            align-items: flex-start;
            text-align: left;
            min-width: 110px;
        }

        .chip-kpi small {
            font-size: 0.65rem;
            font-weight: 600;
            letter-spacing: 0.4px;
        }

        .chip-kpi strong {
            font-size: 0.95rem;
            margin-top: 2px;
        }

        .chip-kpi.inflow {
            color: #157347;
            background: #d1e7dd;
        }

        .chip-kpi.outflow {
            color: #b02a37;
            background: #f8d7da;
        }

        .chip-kpi.balance {
            color: #1d4ed8;
            background: #d1e7f5;
        }

        /* ─── Avatar ────────────────────────────────────────────────── */
        .avatar-wrap {
            width: 78px;
            height: 78px;
            border-radius: 50%;
            background: #e9efff;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            flex-shrink: 0;
        }

        .compact-avatar {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #e9efff 0%, #dce8ff 100%);
        }

        .avatar-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .avatar-icon {
            font-size: 3rem;
            color: #4b63d1;
        }

        .compact-icon {
            font-size: 2rem;
        }

        /* ─── Excel Grid Table ──────────────────────────────────────── */
        .excel-table tbody tr:hover {
            background: #f8fbff;
        }

        .excel-grid-table {
            border-collapse: collapse;
        }

        .excel-grid-table thead th,
        .excel-grid-table tbody td {
            border: 1px solid #e8edf5;
        }

        .excel-grid-table thead th {
            font-weight: 600;
            background-color: #f8faff;
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            color: #475569;
            padding: 10px 12px;
        }

        .ledger-total-footer th {
            background: #d2d3d6ff;
            border-top: 2px solid #d5deea;
            vertical-align: middle;
        }

        .excel-table .form-control {
            border: 1px solid transparent;
            border-radius: 6px;
            background: transparent;
            padding: 0.42rem 0.55rem;
            box-shadow: none;
            font-size: 0.88rem;
        }

        .excel-table .form-control:focus {
            border-color: #4f74ff;
            background: #ffffff;
            box-shadow: 0 0 0 2px rgba(79, 116, 255, 0.12);
        }

        .outflow-inline-wrap {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .outflow-inline-wrap .form-control {
            min-width: 120px;
        }

        .month-inline-select {
            width: 170px;
            min-width: 170px;
        }

        /* ─── Payment Badges ────────────────────────────────────────── */
        .payment-badge {
            padding: 5px 10px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.2px;
            display: inline-flex;
            align-items: center;
            flex-direction: column;
            gap: 1px;
        }

        .badge-cash {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            color: #065f46;
        }

        .badge-cheque {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            color: #1e40af;
        }

        .badge-online {
            background: linear-gradient(135deg, #ede9fe 0%, #ddd6fe 100%);
            color: #5b21b6;
        }

        .cheque-badge-num {
            font-size: 0.65rem;
            opacity: 0.8;
            font-weight: 500;
        }

        .btn-xs {
            padding: 3px 8px;
            font-size: 0.72rem;
        }

        /* ─── Payment Method Modal ──────────────────────────────────── */
        .pm-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(4px);
            z-index: 1060;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            animation: pmFadeIn 0.2s ease;
        }

        @keyframes pmFadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .pm-modal {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.25);
            width: 100%;
            max-width: 520px;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            animation: pmSlideUp 0.25s ease;
        }

        @keyframes pmSlideUp {
            from { opacity: 0; transform: translateY(20px) scale(0.97); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        .pm-header {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 20px 24px 16px;
            border-bottom: 1px solid #f1f5f9;
        }

        .pm-header-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .pm-amount-strip {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 24px;
            background: linear-gradient(135deg, #fef2f2 0%, #fce7f3 100%);
            border-bottom: 1px solid #fecdd3;
        }

        .pm-amount-label {
            font-size: 0.8rem;
            color: #9f1239;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .pm-amount-value {
            font-size: 1.15rem;
            font-weight: 700;
            color: #be123c;
        }

        .pm-body {
            padding: 20px 24px;
            overflow-y: auto;
            flex: 1;
        }

        .pm-options {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .pm-option {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
            background: #fff;
        }

        .pm-option:hover {
            border-color: #cbd5e1;
            background: #f8fafc;
        }

        .pm-option.active {
            border-color: #4f46e5;
            background: #eef2ff;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .pm-option-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            flex-shrink: 0;
        }

        .cash-icon {
            background: #d1fae5;
            color: #065f46;
        }

        .cheque-icon {
            background: #dbeafe;
            color: #1e40af;
        }

        .online-icon {
            background: #ede9fe;
            color: #5b21b6;
        }

        .pm-option-details {
            flex: 1;
        }

        .pm-option-details strong {
            display: block;
            font-size: 0.9rem;
            color: #1e293b;
        }

        .pm-option-details small {
            color: #64748b;
            font-size: 0.78rem;
        }

        .pm-option-check {
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        /* ─── Cheque Selection Panel ────────────────────────────────── */
        .pm-cheque-panel {
            margin-top: 16px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            overflow: hidden;
            animation: pmFadeIn 0.2s ease;
        }

        .pm-cheque-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 16px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
        }

        .pm-cheque-search {
            padding: 12px 16px;
            border-bottom: 1px solid #f1f5f9;
        }

        .pm-cheque-search .form-control {
            border-left: 0;
        }

        .pm-cheque-search .form-control:focus {
            box-shadow: none;
            border-color: #dee2e6;
        }

        .pm-cheque-search .input-group-text {
            border-right: 0;
        }

        .pm-cheque-list {
            max-height: 240px;
            overflow-y: auto;
            padding: 8px;
        }

        .pm-cheque-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 14px;
            border: 2px solid transparent;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.15s ease;
            margin-bottom: 4px;
        }

        .pm-cheque-item:hover {
            background: #f8fafc;
            border-color: #e2e8f0;
        }

        .pm-cheque-item.selected {
            background: #eef2ff;
            border-color: #4f46e5;
            box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.08);
        }

        .pm-cheque-item-main {
            flex: 1;
            min-width: 0;
        }

        .pm-cheque-num {
            font-weight: 600;
            font-size: 0.88rem;
            color: #1e293b;
            margin-bottom: 3px;
        }

        .pm-cheque-meta {
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
        }

        .pm-cheque-meta span {
            font-size: 0.75rem;
            color: #64748b;
        }

        .pm-cheque-item-amount {
            text-align: right;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 2px;
        }

        .pm-cheque-item-amount .fw-bold {
            font-size: 0.88rem;
            color: #1e40af;
        }

        .pm-cheque-empty {
            text-align: center;
            padding: 30px 20px;
        }

        .pm-footer {
            padding: 16px 24px;
            border-top: 1px solid #f1f5f9;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            background: #fafbfc;
        }

        .alert-sm {
            font-size: 0.82rem;
        }

        /* ─── Cheque Hover Tooltip ───────────────────────────────── */
        .cheque-tooltip-wrap {
            position: relative;
            display: inline-flex;
        }

        .cheque-hover-card {
            position: absolute;
            bottom: calc(100% + 8px);
            left: 50%;
            transform: translateX(-50%);
            width: 260px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.14);
            z-index: 1050;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: all 0.2s ease;
        }

        .cheque-tooltip-wrap:hover .cheque-hover-card {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }

        .cheque-hover-card::after {
            content: '';
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            border: 6px solid transparent;
            border-top-color: #ffffff;
        }

        .cheque-hover-card::before {
            content: '';
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            border: 7px solid transparent;
            border-top-color: #e2e8f0;
        }

        .chc-header {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);
            border-radius: 12px 12px 0 0;
            color: #3730a3;
            font-weight: 600;
            font-size: 0.8rem;
        }

        .chc-body {
            padding: 10px 14px;
        }

        .chc-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 4px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .chc-row:last-child {
            border-bottom: none;
        }

        .chc-label {
            font-size: 0.72rem;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            font-weight: 600;
        }

        .chc-value {
            font-size: 0.8rem;
            color: #1e293b;
            font-weight: 500;
            text-align: right;
        }

        .chc-footer {
            padding: 6px 14px 8px;
            text-align: center;
            border-top: 1px solid #f1f5f9;
        }

        .chc-footer small {
            font-size: 0.68rem;
            color: #94a3b8;
        }

        .cheque-clickable:hover {
            transform: scale(1.05);
            box-shadow: 0 2px 8px rgba(30, 64, 175, 0.2);
        }

        .cheque-clickable {
            transition: all 0.15s ease;
        }

        /* ─── Cheque Detail Modal ────────────────────────────────── */
        .cd-modal {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.25);
            width: 100%;
            max-width: 440px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            animation: pmSlideUp 0.25s ease;
        }

        .cd-header {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 20px 24px 16px;
            border-bottom: 1px solid #f1f5f9;
        }

        .cd-header-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .cd-body {
            padding: 20px 24px;
        }

        .cd-cheque-hero {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 16px 18px;
            background: linear-gradient(135deg, #eef2ff 0%, #dbeafe 100%);
            border-radius: 12px;
            margin-bottom: 20px;
        }

        .cd-cheque-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            flex-shrink: 0;
        }

        .cd-cheque-number {
            font-size: 1.05rem;
            font-weight: 700;
            color: #1e293b;
            word-break: break-all;
        }

        .cd-details-grid {
            display: flex;
            flex-direction: column;
            gap: 0;
        }

        .cd-detail-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .cd-detail-item:last-child {
            border-bottom: none;
        }

        .cd-detail-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            font-size: 0.95rem;
            flex-shrink: 0;
        }

        .cd-detail-label {
            display: block;
            font-size: 0.7rem;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            font-weight: 600;
            margin-bottom: 1px;
        }

        .cd-detail-value {
            display: block;
            font-size: 0.88rem;
            color: #1e293b;
            font-weight: 500;
        }

        .cd-footer {
            padding: 16px 24px;
            border-top: 1px solid #f1f5f9;
            display: flex;
            justify-content: flex-end;
            background: #fafbfc;
        }

        .bg-warning-subtle {
            background-color: #fff3cd;
        }

        .bg-secondary-subtle {
            background-color: #e2e3e5;
        }

        /* ─── Responsive ────────────────────────────────────────────── */
        @media (max-width: 768px) {
            .outflow-inline-wrap {
                flex-direction: column;
                align-items: stretch;
                gap: 6px;
            }

            .month-inline-select {
                width: 100%;
                min-width: 0;
            }

            .pm-modal,
            .cd-modal {
                max-width: 100%;
                max-height: 100vh;
                border-radius: 12px;
            }

            .cheque-hover-card {
                display: none !important;
            }
        }
    </style>
    @endpush
</div>