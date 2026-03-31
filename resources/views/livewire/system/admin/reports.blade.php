<div>
    @push('styles')
    <style>
        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            height: 100%;
        }

        .stat-value {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #6c757d;
            font-size: 14px;
            margin-bottom: 5px;
        }

        .btn-outline-primary,
        .btn-outline-secondary {
            font-size: 0.8rem;
            font-weight: 500;
            border-radius: 6px;
            padding: 0.3rem 0.7rem;
            transition: all 0.15s ease;
        }

        .btn-outline-primary:hover,
        .btn-outline-secondary:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        /* MOBILE RESPONSIVE STYLES */
        @media (max-width: 768px) {
            .stat-card {
                padding: 12px;
                margin-bottom: 15px;
            }

            .stat-value {
                font-size: 20px !important;
            }

            .content-tab {
                padding: 8px 12px !important;
                white-space: nowrap;
            }

            .widget-container {
                padding: 15px;
            }

            .item-row {
                flex-wrap: wrap;
            }

            .item-details {
                width: 100%;
                margin-bottom: 5px;
            }

            .avatar {
                width: 32px;
                height: 32px;
                margin-right: 10px;
            }

            .amount {
                font-size: 13px;
            }
        }

        @media (max-width: 576px) {
            .content-tabs {
                margin-bottom: 15px;
            }

            .stat-card {
                padding: 10px;
            }

            .stat-value {
                font-size: 18px !important;
            }

            .status-badge {
                padding: 0.15rem 0.35rem;
                font-size: 0.7rem;
            }

            .widget-header h6 {
                font-size: 1rem;
            }

            .widget-header p {
                font-size: 0.75rem;
            }

            .item-row {
                align-items: flex-start;
            }

            .item-details h6 {
                font-size: 0.9rem;
            }

            .d-flex-mobile-column {
                flex-direction: column !important;
            }

            .justify-content-mobile-between {
                justify-content: space-between !important;
            }

            .mb-mobile-2 {
                margin-bottom: 0.5rem !important;
            }

            .w-mobile-100 {
                width: 100% !important;
            }

            .text-truncate-mobile {
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                max-width: 100%;
            }
        }
    </style>
    @endpush

    <!-- Reports Content -->
    <div class="container-fluid p-0">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="fw-bold mb-1">
                            <i class="bi bi-file-earmark-bar-graph text-success me-2"></i>Reports Dashboard</h4>
                        <p class="text-muted mb-0">Generate and export various business reports</p>
                    </div>
                    <!-- Download Excel Button - Only show after report is generated -->
                    @if($selectedReport && count($currentReportData) > 0)
                    <div class="d-flex gap-2">
                        <button wire:click="downloadReport" class="btn btn-success">
                            <i class="bi bi-download"></i> Download Excel
                        </button>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Report Filters -->
        <div class="mb-4">
            <div class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
                <div class="card-body p-4" style="background: #f8f9fa;">
                    <div class="row g-4 align-items-end">
                        <div class="col-lg-8">
                            <label class="form-label fw-bold text-primary mb-3 d-flex align-items-center">
                                <i class="bi bi-check-circle-fill me-2" style="color: #0D47A1;"></i>
                                Choose Your Report
                            </label>
                            <select class="form-select form-select-lg shadow-sm" 
                                    wire:model.live="selectedReport" 
                                    style="border: 2px solid #e0e0e0; border-radius: 8px; padding: 12px 16px; font-weight: 500; transition: all 0.3s ease; height: auto;">
                                <option value="" style="color: #6c757d;">
                                    -- Select Report Type --
                                </option>
                                <option value="daily-sales">
                                    Daily Sales Report
                                </option>
                                <option value="monthly-sales">
                                    Monthly Sales Report
                                </option>
                                <option value="daily-purchases">
                                    Daily Purchases Report
                                </option>
                                <option value="inventory-stock">
                                    Product Stock Report
                                </option>
                                <option value="outstanding-accounts">
                                    Outstanding Accounts
                                </option>
                                
                            </select>
                        </div>
                        @if($selectedReport)
                        <div class="col-lg-4">
                            <div class="alert alert-info border-0 shadow-sm mb-0" style="border-radius: 8px; background: #e3f2fd; border-left: 4px solid #2196F3;">
                                <div class="d-flex gap-2">
                                    <i class="bi bi-exclamation-circle-fill" style="color: #FFA500; font-size: 1.2rem; flex-shrink: 0;"></i>
                                    <div>
                                        <h6 class="alert-heading fw-bold mb-1" style="color: #1976D2; font-size: 0.95rem;">
                                            Quick Tip
                                        </h6>
                                        <p class="mb-0 small" style="color: #555;">
                                            @if($selectedReport === 'daily-sales')
                                                View detailed daily sales breakdown in a calendar format. Use the month and year selectors below to navigate through different historical periods efficiently.
                                            @elseif($selectedReport === 'monthly-sales')
                                                Analyze monthly sales trends throughout the year. Compare performance across different months at a glance.
                                            @elseif($selectedReport === 'daily-purchases')
                                                Track all purchase orders for selected dates. View supplier information, order status, and totals.
                                            @elseif($selectedReport === 'inventory-stock')
                                                Monitor current product stock levels. Get alerts for low stock items and view availability status.
                                            @elseif($selectedReport === 'outstanding-accounts')
                                                View outstanding customer and supplier accounts. Track receivables and payables balances.
                                            @elseif($selectedReport === 'payments')
                                                Review all payment records including payment method and status. Track transaction history.
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <style>
            .form-select:focus {
                border-color: #667eea !important;
                box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25) !important;
            }
            
            .form-select {
                cursor: pointer;
            }
            
            .form-select:hover {
                border-color: #667eea !important;
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2) !important;
            }
        </style>

        <!-- Report Content -->
        @if($selectedReport && (count($currentReportData) > 0 || $selectedReport === 'monthly-sales' || $selectedReport === 'daily-purchases'))
        <div>
            @if($selectedReport === 'daily-sales')
                @include('livewire.admin.reports.daily-sales-report', ['data' => $currentReportData, 'dailySalesReportTotal' => $currentReportTotal])
            @elseif($selectedReport === 'monthly-sales')
                @include('livewire.admin.reports.monthly-sales-report', ['data' => $currentReportData, 'monthlySalesReportTotal' => $currentReportTotal])
            @elseif($selectedReport === 'daily-purchases')
                @include('livewire.admin.reports.daily-purchases', ['reportData' => $currentReportData, 'reportTotal' => $currentReportTotal, 'reportStartDate' => $reportStartDate, 'reportEndDate' => $reportEndDate])
            @elseif($selectedReport === 'inventory-stock')
                @include('livewire.admin.reports.inventory-stock', ['reportData' => $currentReportData, 'reportStats' => $reportStats ?? []])
            @elseif($selectedReport === 'outstanding-accounts')
                @include('livewire.admin.reports.outstanding-accounts', ['reportData' => $currentReportData])
            @elseif($selectedReport === 'payments')
                @include('livewire.admin.reports.payments-report', ['data' => $currentReportData])
            @endif
        </div>
        @elseif($selectedReport)
        <!-- Empty State - Report selected but no data -->
        <div class="alert alert-warning text-center py-5">
            <div class="text-warning mb-3">
                <i class="bi bi-search display-4"></i>
            </div>
            <h4 class="text-warning">No Data Found</h4>
            <p class="text-warning">No records found for the selected criteria. Try adjusting your filters.</p>
            <button wire:click="clearFilters" class="btn btn-outline-warning">
                <i class="bi bi-arrow-clockwise"></i> Clear Filters
            </button>
        </div>
        @endif
    </div>

    <!-- Loading Indicator -->
    <div wire:loading class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100">
                    <i class="bi bi-arrow-repeat animate-spin text-blue-600 text-xl"></i>
                </div>
                <h3 class="text-lg leading-6 font-medium text-gray-900 mt-2">Generating Report</h3>
                <p class="text-sm text-gray-500 mt-1">Please wait while we process your request...</p>
            </div>
        </div>
    </div>
</div>
