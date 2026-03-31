<div>
    @push('styles')
        <style>
            .stat-card {
                background: white;
                border-radius: 8px;
                padding: 15px;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
                height: 100%;
                margin-bottom: 15px;
            }

            @media (min-width: 768px) {
                .stat-card {
                    padding: 20px;
                }
            }

            .stat-value {
                font-size: 18px;
                font-weight: 600;
                margin-bottom: 5px;
            }
            
            @media (min-width: 768px) {
                .stat-value {
                    font-size: 24px;
                }
            }

            .stat-label {
                color: #6c757d;
                font-size: 13px;
                margin-bottom: 5px;
            }
            
            @media (min-width: 768px) {
                .stat-label {
                    font-size: 14px;
                }
            }

            .stat-change {
                color: #28a745;
                font-size: 12px;
            }
            
            @media (min-width: 768px) {
                .stat-change {
                    font-size: 13px;
                }
            }

            .stat-change-alert {
                color: #842029;
                font-size: 12px;
            }
            
            @media (min-width: 768px) {
                .stat-change-alert {
                    font-size: 13px;
                }
            }

            .content-tabs {
                display: flex;
                overflow-x: auto;
                border-bottom: 1px solid #dee2e6;
                margin-bottom: 20px;
                padding-bottom: 2px;
                -webkit-overflow-scrolling: touch;
            }

            .content-tab {
                padding: 8px 12px;
                cursor: pointer;
                font-weight: 500;
                color: #495057;
                border-bottom: 3px solid transparent;
                transition: all 0.2s;
                white-space: nowrap;
            }
            
            @media (min-width: 768px) {
                .content-tab {
                    padding: 10px 20px;
                }
            }

            .content-tab.active {
                color: #0d6efd;
                border-bottom-color: #0d6efd;
            }

            .content-tab:hover:not(.active) {
                color: #0d6efd;
                border-bottom-color: #dee2e6;
            }

            .tab-content {
                display: none;
            }

            .tab-content.active {
                display: block;
            }

            .chart-card {
                box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
                border-radius: 0.5rem;
                margin-bottom: 20px;
            }

            .chart-header {
                background-color: #f8f9fa;
                padding: 0.75rem 1rem;
                border-bottom: 1px solid #dee2e6;
                border-top-left-radius: 0.5rem;
                border-top-right-radius: 0.5rem;
            }
            
            @media (min-width: 768px) {
                .chart-header {
                    padding: 1rem 1.5rem;
                }
            }

            .chart-container {
                position: relative;
                height: 250px;
                padding: 1rem;
            }
            
            @media (min-width: 768px) {
                .chart-container {
                    height: 300px;
                    padding: 1.5rem;
                }
            }

            .chart-scroll-container {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .recent-sales-card {
                box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
                border-radius: 0.5rem;
                height: auto;
                min-height: 300px;
                width: 100%;
                margin-bottom: 20px;
            }
            
            @media (min-width: 768px) {
                .recent-sales-card {
                    height: 380px;
                }
            }

            .avatar {
                width: 32px;
                height: 32px;
                background-color: #e9ecef;
                border-radius: 50%;
                display: flex;
                justify-content: center;
                align-items: center;
                margin-right: 10px;
                color: #6c757d;
                font-size: 0.8rem;
                font-weight: bold;
            }
            
            @media (min-width: 768px) {
                .avatar {
                    width: 40px;
                    height: 40px;
                    margin-right: 15px;
                    font-size: 1rem;
                }
            }

            .amount {
                font-weight: bold;
                color: #198754;
                font-size: 0.9rem;
            }
            
            @media (min-width: 768px) {
                .amount {
                    font-size: 1rem;
                }
            }

            .widget-container {
                background-color: #fff;
                border-radius: 8px;
                box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.05);
                padding: 1rem;
                margin-bottom: 20px;
                height: 100%;
                width: 100%;
                margin-left: 0;
            }
            
            @media (min-width: 768px) {
                .widget-container {
                    padding: 1.5rem;
                }
            }

            .widget-header {
                margin-bottom: 15px;
            }

            .widget-header h6 {
                margin-bottom: 5px;
                font-weight: 600;
                font-size: 1rem;
            }
            
            @media (min-width: 768px) {
                .widget-header h6 {
                    font-size: 1.25rem;
                }
            }

            .widget-header p {
                font-size: 0.8rem;
                color: #6c757d;
                margin-bottom: 0;
            }
            
            @media (min-width: 768px) {
                .widget-header p {
                    font-size: 0.875rem;
                }
            }

            .item-row {
                display: flex;
                margin-bottom: 10px;
                flex-wrap: wrap;
            }

            .item-details {
                flex-grow: 1;
                margin-right: 10px;
                min-width: 0; /* Prevent text overflow */
            }

            .status-badge {
                text-align: center;
                min-width: 70px;
                font-size: 0.65rem;
                border-radius: 4px;
                padding: 0.2rem 0.4rem;
                margin-left: auto;
                margin-right: 0.5rem;
            }
            
            @media (min-width: 768px) {
                .status-badge {
                    min-width: 80px;
                    font-size: 0.7rem;
                    padding: 0.2rem 0.5rem;
                }
            }

            .in-stock {
                background-color: #d1e7dd;
                color: #0f5132;
            }

            .low-stock {
                background-color: #fff3cd;
                color: #664d03;
            }

            .out-of-stock {
                background-color: #f8d7da;
                color: #842029;
            }

            .progress {
                height: 6px !important;
                margin-bottom: 0.5rem;
                background-color: #e9ecef;
                border-radius: 0.25rem;
                overflow: hidden;
            }
            
            @media (min-width: 768px) {
                .progress {
                    height: 8px !important;
                }
            }

            .progress-bar {
                height: 100%;
                background-color: #007bff;
            }

            .staff-info {
                display: flex;
                align-items: center;
                margin-bottom: 10px;
                padding-bottom: 10px;
                border-bottom: 1px solid #eee;
                flex-wrap: wrap;
            }
            
            @media (min-width: 768px) {
                .staff-info {
                    margin-bottom: 15px;
                    padding-bottom: 15px;
                    flex-wrap: nowrap;
                }
            }

            .staff-status {
                margin-right: 10px;
                margin-bottom: 5px;
            }
            
            @media (min-width: 768px) {
                .staff-status {
                    margin-right: 15px;
                    margin-bottom: 0;
                }
            }

            .staff-status-badge {
                padding: 4px 8px;
                border-radius: 4px;
                font-size: 0.7rem;
                font-weight: bold;
                white-space: nowrap;
            }
            
            @media (min-width: 768px) {
                .staff-status-badge {
                    padding: 6px 10px;
                    font-size: 0.75rem;
                }
            }

            .present {
                background-color: #d1e7dd;
                color: #0f5132;
            }

            .late {
                background-color: #fff3cd;
                color: #664d03;
            }

            .absent {
                background-color: #f8d7da;
                color: #842029;
            }

            .staff-details {
                flex-grow: 1;
                min-width: 0; /* Prevent text overflow */
            }

            .staff-details h6 {
                margin-bottom: 2px;
                font-size: 0.9rem;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            
            @media (min-width: 768px) {
                .staff-details h6 {
                    margin-bottom: 5px;
                    font-size: 1rem;
                }
            }

            .staff-details p {
                font-size: 0.8rem;
                color: #6c757d;
                margin-bottom: 2px;
            }
            
            @media (min-width: 768px) {
                .staff-details p {
                    font-size: 0.875rem;
                }
            }

            .staff-details .bi {
                margin-right: 5px;
            }

            .attendance-icon {
                margin-left: 5px;
                color: #0f5132;
                font-size: 1rem;
            }
            
            @media (min-width: 768px) {
                .attendance-icon {
                    margin-left: 10px;
                    font-size: 1.2rem;
                }
            }

            .late-icon {
                color: #664d03;
            }

            .absent-icon {
                color: #842029;
            }

            .avatar-circle {
                width: 30px;
                height: 30px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: bold;
                font-size: 13px;
            }
            
            @media (min-width: 768px) {
                .avatar-circle {
                    width: 36px;
                    height: 36px;
                    font-size: 16px;
                }
            }

            .customer-card {
                transition: all 0.2s ease;
            }

            .customer-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            }

            .widget-header {
                margin-bottom: 15px;
            }

            .widget-header h6 {
                font-size: 1.1rem;
                margin-bottom: 3px;
                font-weight: 500;
                color: #212529;
            }
            
            @media (min-width: 768px) {
                .widget-header h6 {
                    font-size: 1.25rem;
                    margin-bottom: 5px;
                }
            }

            .widget-header p {
                font-size: 0.8rem;
                color: #6c757d;
                margin-bottom: 0;
            }
            
            @media (min-width: 768px) {
                .widget-header p {
                    font-size: 0.875rem;
                }
            }

            .staff-avatar {
                display: flex;
                align-items: center;
                justify-content: center;
                width: 28px;
                height: 28px;
            }
            
            @media (min-width: 768px) {
                .staff-avatar {
                    width: 32px;
                    height: 32px;
                }
            }

            .staff-card {
                transition: all 0.2s ease;
            }

            .staff-card:hover {
                transform: translateY(-2px);
            }

            /* Ensure proper progress bar display */
            .inventory-container,
            .staff-sales-container {
                max-height: 300px;
                overflow-y: auto;
                padding-right: 5px;
                -webkit-overflow-scrolling: touch;
            }
            
            @media (min-width: 768px) {
                .inventory-container,
                .staff-sales-container {
                    max-height: 380px;
                }
            }
            
            /* Mobile optimizations */
            .list-group-item {
                padding: 0.5rem 0.75rem;
            }
            
            @media (min-width: 768px) {
                .list-group-item {
                    padding: 0.75rem 1.25rem;
                }
            }
            
            /* Fix truncated numbers in mobile */
            .text-end {
                text-align: right !important;
                min-width: 80px;
            }
            
            /* Adjust tabs for mobile scrolling */
            .content-tabs::-webkit-scrollbar {
                height: 3px;
            }
            
            .content-tabs::-webkit-scrollbar-thumb {
                background-color: rgba(0,0,0,.2);
                border-radius: 3px;
            }
        </style>
    @endpush

    <!-- Navigation Tabs -->
    <div class="content-tabs">
        <div class="content-tab active" data-tab="overview">Overview</div>
        <div class="content-tab" data-tab="analytics">Analytics</div>
        <div class="content-tab" data-tab="reports">Reports</div>
        <div class="content-tab" data-tab="notifications">Notifications</div>
    </div>

    <!-- Overview Content -->
    <div id="overview" class="tab-content active">
        <!-- Stats Cards Row -->
        <div class="row g-2 g-md-3 mb-3 mb-md-4">
            <!-- Total Revenue Card -->
            <div class="col-12 col-sm-6 col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="stat-label">Total Revenue</div>
                    </div>
                    <div class="stat-value">Rs.{{ number_format($totalRevenue, 2) }}</div>
                    <div class="stat-info mt-1">
                        <div class="d-flex justify-content-between mb-1">
                            <small>Revenue</small>
                            <small>{{ $revenuePercentage }}% of total sales</small>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-success" role="progressbar"
                                style="width: {{ $revenuePercentage }}%;" aria-valuenow="{{ $revenuePercentage }}"
                                aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <div class="d-flex justify-content-between mt-1">
                            <small class="text-muted small">Rs.{{ number_format($totalRevenue) }} of
                                Rs.{{ number_format($totalRevenue + $totalDueAmount) }}</small>
                        </div>
                    </div>

                    <!-- Fully Paid Invoices Information -->
                    <div class="stat-info mt-2 mt-md-3 pt-2 border-top">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted"><i class="bi bi-check-circle-fill text-success me-1"></i> Fully
                                Paid</small>
                            <span class="badge bg-success">{{ $fullPaidCount }}</span>
                        </div>
                        <small class="d-block text-end text-success">Rs.{{ number_format($fullPaidAmount, 2) }}</small>
                    </div>
                </div>
            </div>

            <!-- Total Due Amount Card -->
            <div class="col-12 col-sm-6 col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="stat-label">Total Due Amount</div>
                    </div>
                    <div class="stat-value">Rs.{{ number_format($totalDueAmount, 2) }}</div>
                    <div class="stat-info mt-1">
                        <div class="d-flex justify-content-between mb-1">
                            <small>Due Amount</small>
                            <small>{{ $duePercentage }}% of total sales</small>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-danger" role="progressbar"
                                style="width: {{ $duePercentage }}%;" aria-valuenow="{{ $duePercentage }}"
                                aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <div class="d-flex justify-content-between mt-1">
                            <small class="text-muted small">Rs.{{ number_format($totalDueAmount) }} of
                                Rs.{{ number_format($totalDueAmount + $totalRevenue) }}</small>
                        </div>
                    </div>

                    <!-- Partial Payment Info -->
                    <div class="stat-info mt-2 mt-md-3 pt-2 border-top">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted"><i class="bi bi-clock-fill text-danger me-1"></i> Partial
                                Paid</small>
                            <span class="badge bg-danger">{{ $partialPaidCount }}</span>
                        </div>
                        <small class="d-block text-end text-danger">Rs.{{ number_format($totalDueAmount, 2) }}</small>
                    </div>
                </div>
            </div>

            <!-- Inventory Status Card -->
            <div class="col-12 col-sm-6 col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="stat-label">Inventory Status</div>
                    </div>
                    <div class="stat-value">{{ number_format($totalInventory) }} <span
                            class="fs-6 text-muted">units</span></div>

                    <!-- Sales Progress -->
                    <div class="stat-info mt-1">
                        <div class="d-flex justify-content-between mb-1">
                            <small>Sold Stock</small>
                            <small>{{ $soldPercentage }}% of stock</small>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-primary" role="progressbar"
                                style="width: {{ $soldPercentage }}%;" aria-valuenow="{{ $soldPercentage }}"
                                aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <div class="d-flex justify-content-between mt-1">
                            <small class="text-muted small">{{ number_format($soldInventory) }} / 
                                {{ number_format($totalInventory) }}</small>
                        </div>
                    </div>

                    <!-- Available Stock Info -->
                    <div class="stat-info mt-2 mt-md-3 pt-2 border-top">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted"><i class="bi bi-box-seam-fill text-primary me-1"></i> Available</small>
                            <span class="badge bg-primary">{{ $totalInventory - $soldInventory }}</span>
                        </div>
                        <small class="d-block text-end text-primary">
                            Rs.{{ number_format(($availableStockValue), 0) }}
                        </small>
                    </div>
                </div>
            </div>

            <!-- Customer Status Card -->
            <div class="col-12 col-sm-6 col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="stat-label">Customer Status</div>
                    </div>
                    <div class="stat-value">{{ $totalCustomers }} <span
                            class="fs-6 text-muted">{{ Str::plural('customer', $totalCustomers) }}</span></div>

                    <!-- Customer Types -->
                    <div class="stat-info mt-1">
                        @foreach ($customerTypes as $type => $count)
                            <div class="d-flex justify-content-between mb-1">
                                <small>{{ ucfirst($type) }}</small>
                                <small>{{ round(($count / $totalCustomers) * 100) }}%</small>
                            </div>
                            <div class="progress mb-1">
                                <div class="progress-bar {{ $type == 'wholesale' ? 'bg-info' : 'bg-success' }}"
                                    role="progressbar" style="width: {{ round(($count / $totalCustomers) * 100) }}%;"
                                    aria-valuenow="{{ round(($count / $totalCustomers) * 100) }}" aria-valuemin="0"
                                    aria-valuemax="100"></div>
                            </div>
                            <small class="d-block text-muted mb-2 small">{{ $count }} {{ ucfirst($type) }}</small>
                        @endforeach
                    </div>

                    <!-- Customer Info -->
                    <div class="stat-info mt-2 mt-md-3 pt-2 border-top">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted"><i class="bi bi-person-check-fill text-info me-1"></i> Active</small>
                            <span class="badge bg-info">{{ $totalCustomers }}</span>
                        </div>
                        <small class="d-block text-end text-info">
                            Rs.{{ number_format($recentSales->sum('total_amount'), 0) }}
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart Section -->
        <div class="row g-2 g-md-3">
            <div class="col-12 col-lg-8">
                <div class="chart-card mb-3">
                    <div class="chart-header d-flex justify-content-between align-items-center flex-wrap">
                        <div>
                            <h6 class="mb-1">Brand-wise Sales</h6>
                            <p class="text-muted mb-0 small">Your sales performance by Product brands</p>
                        </div>
                        <div class="mt-2 mt-sm-0">
                            {{-- <select class="form-select form-select-sm w-auto" id="chart-time-range">
                                <option selected value="all">All Time</option>
                                <option value="year">This Year</option>
                                <option value="month">This Month</option>
                            </select> --}}
                        </div>
                    </div>
                    <!-- Add scrollable wrapper for the chart -->
                    <div class="chart-scroll-container">
                        <div class="chart-container" style="min-width: {{ max(300, count($brandSales) * 60) }}px;">
                            <canvas id="salesChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Sales Section -->
            <div class="col-12 col-lg-4">
                <div class="recent-sales-card">
                    <div class="card-body p-2 p-md-3">
                        <div>
                            <h6 class="card-title">Recent Sales</h6>
                            <p class="card-subtitle text-muted small mb-2 mb-md-3">Your latest customer transactions</p>
                        </div>
                        <ul class="list-group list-group-flush">
                            @forelse($recentSales as $sale)
                                <li class="list-group-item d-flex align-items-center">
                                    <div class="avatar">
                                        {{ strtoupper(substr($sale->name, 0, 1)) }}{{ strtoupper(substr(strpos($sale->name, ' ') !== false ? substr($sale->name, strpos($sale->name, ' ') + 1, 1) : '', 0, 1)) }}
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0 fs-6">{{ $sale->name }}</h6>
                                        <p class="text-muted small mb-0">{{ $sale->email }}</p>
                                    </div>
                                    <div class="text-end">
                                        <div class="amount">+Rs.{{ number_format($sale->total_amount, 0) }}</div>
                                        @if ($sale->due_amount > 0)
                                            <div class="text-danger small fw-bold">
                                                Rs.{{ number_format($sale->due_amount, 0) }}</div>
                                        @else
                                            <span class="badge bg-success">Paid</span>
                                        @endif
                                    </div>
                                </li>
                            @empty
                                <li class="list-group-item text-center py-4">
                                    <i class="bi bi-receipt text-muted d-block mb-2" style="font-size: 1.5rem;"></i>
                                    <p class="text-muted mb-0">No sales recorded yet</p>
                                </li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Inventory Status Widget Row -->
        <div class="container-fluid mt-3 mt-md-4 p-0">
            <div class="row g-2 g-md-3">
                <!-- Product Inventory Status -->
                <div class="col-12 col-lg-5">
                    <div class="widget-container">
                        <div class="widget-header">
                            <h6>Product Inventory Status</h6>
                            <p class="text-muted small mb-0">Your current Product stock levels</p>
                        </div>

                        <div class="inventory-container">
                            @forelse($productInventory as $item)
                                @php
                                    $stockPercentage =
                                        $item->total_quantity > 0
                                            ? round(($item->available_quantity / $item->total_quantity) * 100, 1)
                                            : 0;

                                    // Determine stock status and colors
                                    if ($stockPercentage == 0) {
                                        $statusBadge = 'out-of-stock';
                                        $statusText = 'Out of Stock';
                                        $progressBarColor = 'bg-danger';
                                    } elseif ($stockPercentage <= 20) {
                                        $statusBadge = 'low-stock';
                                        $statusText = 'Low Stock';
                                        $progressBarColor = 'bg-warning';
                                    } else {
                                        $statusBadge = 'in-stock';
                                        $statusText = 'In Stock';
                                        $progressBarColor = '';
                                    }
                                @endphp

                                <div class="d-flex align-items-center flex-wrap flex-md-nowrap mt-3">
                                    <div class="me-2" style="width: 35px; height: 35px;">
                                        <img src="{{ $item->image ? asset('storage/' . $item->image) : asset('images/product.jpg') }}"
                                            alt="{{ $item->name }}" class="img-fluid rounded"
                                            style="width: 100%; height: 100%; object-fit: cover;">
                                    </div>
                                    <div class="flex-grow-1 min-width-0 me-2">
                                        <h6 class="mb-0 text-truncate fs-6">{{ $item->brand }} {{ $item->name }}
                                            {{ $item->model }}</h6>
                                        <p class="text-muted small mb-0">SKU: {{ $item->code }}</p>
                                    </div>
                                    <span class="status-badge {{ $statusBadge }} my-1 my-md-0">{{ $statusText }}</span>
                                    <div class="ms-md-2 text-muted small">
                                        {{ $item->available_quantity }}/{{ $item->total_quantity }}</div>
                                </div>

                                <div class="progress mt-2">
                                    <div class="progress-bar" style="width: {{ $stockPercentage }}%;"></div>
                                </div>

                                <div class="d-flex justify-content-between mb-3 flex-wrap">
                                    <div class="text-muted small">Value:
                                        Rs.{{ number_format($item->available_value, 0) }}</div>
                                    <div class="text-muted small">{{ $stockPercentage }}% remaining</div>
                                </div>

                                @if (!$loop->last)
                                    <hr class="my-2">
                                @endif
                            @empty
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-2"></i> No Product inventory has been assigned to you
                                    yet.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Customer Payment Dashboard -->
                <div class="col-12 col-lg-7">
                    <div class="widget-container p-2 p-md-3">
                        <div class="widget-header mb-3 d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="fw-bold">Customer Payment Dashboard</h6>
                                <p class="text-muted small mb-0">Sales performance by customer</p>
                            </div>
                        </div>

                        <!-- Make content scrollable if many customers exist -->
                        <div class="staff-sales-container">
                            @forelse($customerPaymentStats ?? [] as $customer)
                                <div
                                    class="staff-card p-2 p-md-3 mb-3 bg-light rounded shadow-sm border-start border-3 {{ $loop->even ? 'border-primary' : 'border-info' }}">
                                    <div class="d-flex align-items-start mb-2">
                                        <div class="staff-avatar me-2">
                                            <span
                                                class="badge {{ $loop->even ? 'bg-primary' : 'bg-info' }} bg-opacity-25 text-white fw-medium py-2 px-2">
                                                {{ strtoupper(substr($customer->name, 0, 1)) }}
                                            </span>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="fw-bold mb-0 fs-6 text-truncate">{{ $customer->name }}</h6>
                                        </div>
                                    </div>

                                    <!-- Sales Progress Section -->
                                    <div class="sales-progress mb-2">
                                        <div class="d-flex justify-content-between align-items-center mb-1 flex-wrap">
                                            <small class="text-muted">Sales Progress</small>
                                            <div class="d-flex align-items-center flex-wrap">
                                                <small
                                                    class="me-1 text-success fw-bold">Rs.{{ number_format($customer->collected_amount, 0) }}</small>
                                                <small class="text-muted">/
                                                    Rs.{{ number_format($customer->total_sales, 0) }}</small>
                                                <span class="badge bg-success ms-1">{{ round(($customer->collected_amount / $customer->total_sales) * 100) }}%</span>
                                            </div>
                                        </div>
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar bg-success" 
                                                 role="progressbar"
                                                 style="width: {{ round(($customer->collected_amount / $customer->total_sales) * 100) }}%"
                                                 aria-valuenow="{{ round(($customer->collected_amount / $customer->total_sales) * 100) }}"
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Payment Collection Section -->
                                    <div class="payment-progress">
                                        <div class="d-flex justify-content-between align-items-center mb-1 flex-wrap">
                                            <small class="text-muted">Payment Collection</small>
                                            <div class="d-flex align-items-center flex-wrap">
                                                <small
                                                    class="me-1 text-success fw-bold">Rs.{{ number_format($customer->collected_amount, 0) }}</small>
                                                @if ($customer->due_amount > 0)
                                                    <small class="text-danger fw-bold">-
                                                        Rs.{{ number_format($customer->due_amount, 0) }}</small>
                                                    <span
                                                        class="badge bg-danger ms-1">{{ 100 - round(($customer->collected_amount / $customer->total_sales) * 100) }}%</span>
                                                @else
                                                    <span class="badge bg-success ms-1">Paid</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar {{ $customer->due_amount > 0 ? 'bg-danger' : 'bg-success' }}"
                                                role="progressbar"
                                                style="width: {{ round(($customer->collected_amount / $customer->total_sales) * 100) }}%">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-2"></i> No customer payment data available yet.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Analytics Content -->
    <div id="analytics" class="tab-content">
        <div class="alert alert-info">
            Analytics content will appear here when this tab is selected.
        </div>
    </div>

    <!-- Reports Content -->
    <div id="reports" class="tab-content">
        <div class="alert alert-info">
            Reports content will appear here when this tab is selected.
        </div>
    </div>

    <!-- Notifications Content -->
    <div id="notifications" class="tab-content">
        <div class="alert alert-info">
            Notifications content will appear here when this tab is selected.
        </div>
    </div>
</div>

<script>
    // Chart configuration
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('salesChart').getContext('2d');
        
        // Determine if we're on mobile
        const isMobile = window.innerWidth < 768;

        const chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: @json(collect($brandSales)->pluck('brand')),
                datasets: [{
                    label: 'Sales by Brand',
                    backgroundColor: '#0d6efd',
                    data: @json(collect($brandSales)->pluck('total_sales'))
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Rs.' + new Intl.NumberFormat().format(context.raw);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        },
                        ticks: {
                            callback: function(value) {
                                // Shorter labels on mobile
                                if (isMobile && value > 1000) {
                                    return 'Rs.' + (value/1000) + 'k';
                                }
                                return 'Rs.' + new Intl.NumberFormat().format(value);
                            },
                            font: {
                                size: isMobile ? 10 : 12
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: isMobile ? 8 : 12
                            },
                            maxRotation: 45,
                            minRotation: 45
                        }
                    }
                }
            }
        });

        // Tab switching functionality
        const tabs = document.querySelectorAll('.content-tab');
        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                tabs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');

                document.querySelectorAll('.tab-content').forEach(content => {
                    content.classList.remove('active');
                });

                const tabId = this.getAttribute('data-tab');
                document.getElementById(tabId).classList.add('active');
            });
        });
        
        // Handle window resize for the chart
        window.addEventListener('resize', function() {
            const isMobile = window.innerWidth < 768;
            chart.options.scales.y.ticks.font.size = isMobile ? 10 : 12;
            chart.options.scales.x.ticks.font.size = isMobile ? 8 : 12;
            chart.update();
        });
    });
</script>
