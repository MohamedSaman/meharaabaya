<div class="dashboard-content">
    @push('styles')
    <style>
        .dashboard-content {
            color: #111827;
            font-size: 14px;
            line-height: 1.45;
        }

        .dashboard-content .text-muted {
            color: #4b5563 !important;
        }

        .text-phoenix {
            color: #8a6114 !important;
        }

        /* Base styles */
        .stat-card {
            background: #ffffff;
            border: 1px solid #eadfca;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 8px 24px rgba(17, 24, 39, 0.06);
            height: 100%;
        }

        .stat-value {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 5px;
            color: #111827;
        }

        .stat-label {
            color: #374151;
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .stat-change {
            color: #0f5132;
            font-size: 13px;
        }

        .stat-change-alert {
            color: #842029;
            font-size: 13px;
        }

        .chart-card {
            background: #ffffff;
            border: 1px solid #eadfca;
            box-shadow: 0 8px 24px rgba(17, 24, 39, 0.06);
            border-radius: 10px;
            margin-bottom: 20px;
            height: 100%;
        }

        .chart-header {
            background-color: #fff8ea;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #eadfca;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }

        .chart-container {
            position: relative;
            height: 300px;
            padding: 1.5rem;
        }

        .chart-scroll-container {
            overflow-x: auto;
        }

        .widget-container {
            background-color: #fff;
            border: 1px solid #eadfca;
            border-radius: 10px;
            box-shadow: 0 8px 24px rgba(17, 24, 39, 0.06);
            padding: 20px;
            margin-bottom: 20px;
            height: 100%;
        }

        .widget-header {
            margin-bottom: 15px;
        }

        .widget-header h6 {
            font-size: 1.25rem;
            margin-bottom: 5px;
            font-weight: 700;
            color: #111827;
        }

        .widget-header p {
            font-size: 0.875rem;
            color: #4b5563;
            margin-bottom: 0;
        }

        .item-row {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .item-details {
            flex-grow: 1;
            margin-right: 10px;
        }

        .item-details h6 {
            font-size: 1rem;
            margin-bottom: 3px;
            color: #111827;
            font-weight: 600;
        }

        .item-details p {
            font-size: 0.875rem;
            color: #4b5563;
            margin-bottom: 0;
        }

        .status-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 5px;
            font-size: 0.8rem;
            font-weight: bold;
            white-space: nowrap;
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
            height: 0.5rem;
            margin-top: 5px;
            background-color: #e5e7eb;
            border-radius: 0.25rem;
            overflow: hidden;
        }

        .progress-bar {
            background-color: #8a6114;
            height: 4px;
        }

        /* Stats progress bars */
        .stat-card .progress {
            height: 4px;
            margin-bottom: 5px;
        }

        .stat-card .progress-bar {
            height: 4px;
        }

        .stat-info small,
        .stat-change-alert small {
            font-size: 12px;
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
                font-size: 22px !important;
            }

            .stat-info small,
            .stat-change-alert small {
                font-size: 11px !important;
                white-space: normal !important;
            }

            .chart-header {
                padding: 0.75rem !important;
                flex-direction: column !important;
                align-items: flex-start !important;
            }

            .chart-header .btn {
                margin-top: 0.5rem;
                font-size: 0.8rem;
            }

            .chart-header h6 {
                font-size: 1rem;
            }

            .chart-header p {
                font-size: 0.75rem;
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
        }

        @media (max-width: 576px) {
            .stat-card {
                padding: 10px;
            }

            .stat-value {
                font-size: 20px !important;
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

            .text-truncate-mobile {
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                max-width: 100%;
            }
        }
    </style>
    @endpush

    <!-- Overview Content -->
    <div class="container-fluid p-0">
        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h3 class="fw-bold text-dark mb-2">
                    <i class="bi bi-speedometer2 text-phoenix me-2"></i> Overview
                </h3>
                <p class="text-muted mb-0">Get a complete view of your product performance and stock activity.</p>
            </div>
        </div>
        <!-- Stats Cards Row - Updated to 3 cards -->
        <div class="row mb-4">
            <!-- Total Sold Stocks Card -->
            <div class="col-sm-6 col-lg-4 mb-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="stat-label">Total Sold Stocks</div>
                    </div>
                    <div class="stat-value">{{ number_format($soldStock) }} <span class="fs-6 text-muted">units</span></div>
                    <div class="stat-info">
                        <div class="d-flex justify-content-between mb-1">
                            <small>Sold Percentage</small>
                            <small>{{ $soldPercentage }}% of total</small>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-success" role="progressbar"
                                style="width: {{ $soldPercentage }}%;" aria-valuenow="{{ $soldPercentage }}"
                                aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <small class="text-muted text-truncate-mobile">{{ number_format($soldStock) }} of {{ number_format($totalStock) }} units</small>
                        </div>
                    </div>

                    <!-- Additional Info -->
                    <div class="stat-info mt-3 pt-2 border-top">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted"><i class="bi bi-cart-check-fill text-phoenix me-1"></i> Sales Value</small>
                            <span class="badge bg-success">Rs.{{ number_format($totalSales, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Total Available Stocks Card -->
            <div class="col-sm-6 col-lg-4 mb-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="stat-label">Total Available Stocks</div>
                    </div>
                    <div class="stat-value">{{ number_format($availableStock) }} <span class="fs-6 text-muted">units</span></div>
                    <div class="stat-info">
                        <div class="d-flex justify-content-between mb-1">
                            <small>Available Percentage</small>
                            <small>{{ $availablePercentage }}% of total</small>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-primary" role="progressbar"
                                style="width: {{ $availablePercentage }}%;" aria-valuenow="{{ $availablePercentage }}"
                                aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <small class="text-muted text-truncate-mobile">{{ number_format($availableStock) }} of {{ number_format($totalStock) }} units</small>
                        </div>
                    </div>

                    <!-- Additional Info -->
                    <div class="stat-info mt-3 pt-2 border-top">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted"><i class="bi bi-box-seam text-phoenix me-1"></i> Inventory Value</small>
                            <span class="badge bg-primary">Rs.{{ number_format($totalAvailableInventory, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Damage Stocks Card -->
            <div class="col-sm-6 col-lg-4 mb-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="stat-label">Damage Stocks</div>
                    </div>
                    <div class="stat-value">{{ number_format($damagedStock) }} <span class="fs-6 text-muted">units</span></div>
                    <div class="stat-change-alert">
                        <div class="d-flex justify-content-between mb-1">
                            <small>Damage Percentage</small>
                            <small>{{ $damagedPercentage }}% of total</small>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $damagedPercentage }}%;"
                                aria-valuenow="{{ $damagedPercentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <small class="text-muted text-truncate-mobile">{{ number_format($damagedStock) }} of {{ number_format($totalStock) }} units</small>
                        </div>
                    </div>

                    <!-- Additional Info -->
                    <div class="stat-info mt-3 pt-2 border-top">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted"><i class="bi bi-exclamation-triangle-fill text-danger me-1"></i> Damage Value</small>
                            <span class="badge bg-danger">Rs.{{ number_format($damagedValue, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Equal Size Cards Section -->
        <div class="row">
            <!-- Sales Overview By Categories Card -->
            <div class="col-lg-6 col-md-12 mb-4">
                <div class="chart-card">
                    <div class="chart-header d-flex justify-content-between align-items-center flex-wrap">
                        <div class="mb-mobile-2">
                            <h6 class="mb-1">Sales Overview By Categories</h6>
                            <p class="text-muted mb-0 small">Compare sales performance by Product Categories</p>
                        </div>
                       
                    </div>
                    <!-- Add scrollable wrapper for the chart -->
                    <div class="chart-scroll-container">
                        <div class="chart-container" style="min-width: {{ count($categorySales) * 60 }}px;">
                            <canvas id="salesChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Inventory Status Card -->
            <div class="col-lg-6 col-md-12 mb-4">
                <div class="widget-container">
                    <div class="widget-header d-flex justify-content-between align-items-start flex-wrap">
                        <div class="mb-2 mb-md-0">
                            <h6>Inventory Status</h6>
                            <p class="text-muted small mb-0">Current stock levels and alerts</p>
                        </div>
                        <a href="{{ route('admin.Product-stock-details') }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-box-seam"></i>
                        </a>
                    </div>

                    <!-- Scrollable container -->
                    <div class="inventory-container" style="max-height: 300px; overflow-y: auto;">
                        @forelse($ProductInventory as $Product)
                        @php
                        // Calculate stock percentage and status
                        $stockPercentage = $Product->total_stock > 0 ?
                        round(($Product->available_stock / $Product->total_stock) * 100, 2) : 0;

                        // Determine stock status badge
                        if ($Product->available_stock == 0) {
                        $statusClass = 'out-of-stock';
                        $statusText = 'Out of Stock';
                        $progressClass = 'bg-danger';
                        } elseif ($stockPercentage <= 25) { $statusClass='low-stock' ; $statusText='Low Stock' ;
                            $progressClass='bg-warning' ; } else { $statusClass='in-stock' ; $statusText='In Stock'
                            ; $progressClass='' ; } @endphp <div class="item-row @if(!$loop->first) mt-3 @endif">
                            <div class="item-details">
                                <h6 class="text-truncate-mobile">{{ $Product->name }} {{ $Product->model }}</h6>
                                <p class="text-muted small text-truncate-mobile">SKU: {{ $Product->code }}</p>
                            </div>
                            <div class="d-flex align-items-center flex-wrap mt-1 mt-md-0">
                                <span class="status-badge {{ $statusClass }}">{{ $statusText }}</span>
                                <div class="ms-2 text-muted small">{{ $Product->available_stock }}/{{
                                    $Product->total_stock }}</div>
                            </div>
                    </div>
                    <div class="progress">
                        <div class="progress-bar {{ $progressClass }}" style="width: {{ $stockPercentage }}%;">
                        </div>
                    </div>
                    @empty
                    <div class="alert alert-info">No Product inventory data available.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Prepare data from PHP
        const categoryLabels = @json(collect($categorySales)->pluck('category'));
        const categoryTotals = @json(collect($categorySales)->pluck('total_sales'));

        // Chart instance
        let salesChartInstance = null;

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize category sales chart
            initializeCategorySalesChart();
        });

        function initializeCategorySalesChart() {
            const ctx = document.getElementById('salesChart');
            if (!ctx) return;
            
            salesChartInstance = new Chart(ctx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: categoryLabels,
                    datasets: [{
                        label: 'Sales by Category',
                        backgroundColor: 'rgba(138, 97, 20, 0.82)',
                        borderColor: '#8a6114',
                        borderWidth: 1,
                        data: categoryTotals
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { 
                            enabled: true,
                            displayColors: false,
                            bodyFont: {
                                size: window.innerWidth < 768 ? 12 : 14
                            },
                            titleFont: {
                                size: window.innerWidth < 768 ? 12 : 14
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: '#dee2e6' },
                            ticks: {
                                font: {
                                    size: window.innerWidth < 768 ? 10 : 12
                                }
                            }
                        },
                        x: {
                            grid: { display: false },
                            ticks: {
                                font: {
                                    size: window.innerWidth < 768 ? 10 : 12
                                }
                            }
                        }
                    }
                }
            });
        }

        // Handle window resize for chart
        window.addEventListener('resize', function() {
            if (salesChartInstance) {
                salesChartInstance.update();
            }
        });
    </script>
    @endpush
</div>
