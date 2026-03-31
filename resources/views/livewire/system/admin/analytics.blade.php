<div>
    @push('styles')
    <style>
        /* ANALYTICS STYLES */
        .analytics-metric-card {
            background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            height: 100%;
        }

        .analytics-metric-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #007bff, #28a745, #ffc107, #dc3545);
        }

        .analytics-metric-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
        }

        .metric-icon {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
            margin-bottom: 16px;
        }

        .revenue-icon { background: linear-gradient(135deg, #007bff, #0056b3); }
        .sales-icon { background: linear-gradient(135deg, #28a745, #1e7e34); }
        .due-icon { background: linear-gradient(135deg, #dc3545, #bd2130); }
        .profit-icon { background: linear-gradient(135deg, #ffc107, #e0a800); }

        .metric-content h6 {
            color: #6c757d;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .metric-value {
            font-size: 28px;
            font-weight: 700;
            color: #212529;
            margin-bottom: 8px;
            line-height: 1.2;
        }

        .metric-change {
            font-size: 13px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .metric-change.positive { color: #28a745; }
        .metric-change.negative { color: #dc3545; }

        .analytics-chart-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(0, 0, 0, 0.05);
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .analytics-chart-card:hover {
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.12);
        }

        .chart-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 20px 24px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .chart-title {
            font-size: 18px;
            font-weight: 600;
            color: #212529;
            margin-bottom: 4px;
        }

        .chart-subtitle {
            font-size: 14px;
            color: #6c757d;
            margin-bottom: 0;
        }

        .chart-body {
            padding: 24px;
            position: relative;
        }

        /* Make Monthly Sales Chart larger */
        .chart-body canvas#monthlySalesChart {
            min-height: 400px !important;
            max-height: 500px !important;
        }

        #monthlySalesChart {
            height: 400px !important;
        }

        .chart-footer {
            padding: 16px 24px;
            background: #f8f9fa;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
        }

        .chart-controls {
            display: flex;
            gap: 8px;
        }

        .chart-controls .btn {
            padding: 6px 16px;
            font-size: 13px;
            border-radius: 20px;
            transition: all 0.2s ease;
        }

        .chart-controls .btn.active {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }

        .status-legend {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 500;
        }

        .legend-color {
            width: 12px;
            height: 12px;
            border-radius: 50%;
        }

        .legend-color.paid { background: #28a745; }
        .legend-color.partial { background: #ffc107; }
        .legend-color.pending { background: #dc3545; }

        .performance-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .performance-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #ffc107, #fd7e14);
        }

        .performance-card.rank-1::before { background: linear-gradient(90deg, #ffc107, #fd7e14); }
        .performance-card.rank-2::before { background: linear-gradient(90deg, #6c757d, #495057); }
        .performance-card.rank-3::before { background: linear-gradient(90deg, #cd7f32, #8B4513); }

        .performance-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
        }

        .rank-badge {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 18px;
            font-weight: 700;
            color: #495057;
            margin-bottom: 12px;
        }

        .month-name {
            font-size: 20px;
            font-weight: 600;
            color: #212529;
            margin-bottom: 16px;
        }

        .performance-stats {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .stat-item {
            display: flex;
            justify-content: between;
            align-items: center;
        }

        .stat-label {
            font-size: 13px;
            color: #6c757d;
            font-weight: 500;
        }

        .stat-value {
            font-size: 15px;
            font-weight: 600;
            color: #212529;
        }

        /* Responsive Analytics Styles */
        @media (max-width: 768px) {
            .analytics-metric-card {
                padding: 16px;
                margin-bottom: 16px;
            }

            .metric-icon {
                width: 48px;
                height: 48px;
                font-size: 20px;
                margin-bottom: 12px;
            }

            .metric-value {
                font-size: 24px;
            }

            .chart-header {
                padding: 16px;
            }

            .chart-body {
                padding: 16px;
            }

            .chart-title {
                font-size: 16px;
            }

            .performance-card {
                padding: 16px;
                margin-bottom: 16px;
            }

            .status-legend {
                gap: 12px;
            }

            .legend-item {
                font-size: 12px;
            }
        }

        @media (max-width: 576px) {
            .analytics-metric-card {
                padding: 12px;
            }

            .metric-value {
                font-size: 20px;
            }

            .chart-controls {
                flex-direction: column;
                gap: 4px;
            }

            .chart-controls .btn {
                padding: 8px 12px;
                font-size: 12px;
            }

            .status-legend {
                flex-direction: column;
                gap: 8px;
            }
        }
    </style>
    @endpush

    <!-- Analytics Content -->
    <div class="container-fluid p-0">
        <!-- Analytics Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="fw-bold mb-1"><i class="bi bi-bar-chart text-success me-2"></i>Analytics Dashboard</h4>
                        <p class="text-muted mb-0">Comprehensive business insights and trends</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Key Metrics Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="analytics-metric-card">
                    <div class="metric-icon revenue-icon">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                    <div class="metric-content">
                        <h6 class="metric-title">Total Revenue</h6>
                        <h3 class="metric-value">Rs.{{ number_format($totalRevenue) }}</h3>
                        <span class="metric-change positive">
                            <i class="bi bi-arrow-up"></i> {{ $revenueChangePercentage }}%
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="analytics-metric-card">
                    <div class="metric-icon sales-icon">
                        <i class="bi bi-receipt"></i>
                    </div>
                    <div class="metric-content">
                        <h6 class="metric-title">Total Invoices</h6>
                        <h3 class="metric-value">{{ array_sum(array_column($monthlySalesData, 'total_invoices')) }}</h3>
                        <span class="metric-change">
                            <i class="bi bi-calendar-month"></i> This Year
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="analytics-metric-card">
                    <div class="metric-icon due-icon">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                    <div class="metric-content">
                        <h6 class="metric-title">Outstanding Due</h6>
                        <h3 class="metric-value">Rs.{{ number_format($totalDueAmount) }}</h3>
                        <span class="metric-change negative">
                            <i class="bi bi-arrow-down"></i> {{ $duePercentage }}%
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="analytics-metric-card">
                    <div class="metric-icon profit-icon">
                        <i class="bi bi-currency-rupee"></i>
                    </div>
                    <div class="metric-content">
                        <h6 class="metric-title">Collection Rate</h6>
                        <h3 class="metric-value">{{ $revenuePercentage }}%</h3>
                        <span class="metric-change positive">
                            <i class="bi bi-check-circle"></i> Collected
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row 1 -->
        <div class="row mb-4">
            <!-- Monthly Sales Trend -->
            <div class="col-xl-8 col-lg-7 mb-4">
                <div class="analytics-chart-card">
                    <div class="chart-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="chart-title">Monthly Sales & Revenue Trends</h6>
                                <p class="chart-subtitle">Sales performance over the last 12 months</p>
                            </div>
                            <div class="chart-controls">
                                <button class="btn btn-sm btn-outline-secondary active" data-chart="sales">Sales</button>
                                <button class="btn btn-sm btn-outline-secondary" data-chart="revenue">Revenue</button>
                            </div>
                        </div>
                    </div>
                    <div class="chart-body">
                        <canvas id="monthlySalesChart" height="400"></canvas>
                    </div>
                </div>
            </div>

            <!-- Invoice Status Distribution -->
            <div class="col-xl-4 col-lg-5 mb-4">
                <div class="analytics-chart-card">
                    <div class="chart-header">
                        <h6 class="chart-title">Invoice Status</h6>
                        <p class="chart-subtitle">Payment status distribution</p>
                    </div>
                    <div class="chart-body">
                        <canvas id="invoiceStatusChart" height="300"></canvas>
                    </div>
                    <div class="chart-footer">
                        <div class="status-legend">
                            <div class="legend-item">
                                <span class="legend-color paid"></span>
                                <span>Fully Paid</span>
                            </div>
                            <div class="legend-item">
                                <span class="legend-color partial"></span>
                                <span>Partial</span>
                            </div>
                            <div class="legend-item">
                                <span class="legend-color pending"></span>
                                <span>Pending</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row 2 -->
        <div class="row mb-4">
            <!-- Payment Trends -->
            <div class="col-xl-6 col-lg-6 mb-4">
                <div class="analytics-chart-card">
                    <div class="chart-header">
                        <h6 class="chart-title">Payment Collection Trends</h6>
                        <p class="chart-subtitle">Monthly payment collections</p>
                    </div>
                    <div class="chart-body">
                        <canvas id="paymentTrendsChart" height="250"></canvas>
                    </div>
                </div>
            </div>

            <!-- Monthly Comparison -->
            <div class="col-xl-6 col-lg-6 mb-4">
                <div class="analytics-chart-card">
                    <div class="chart-header">
                        <h6 class="chart-title">Monthly Revenue vs Due Comparison</h6>
                        <p class="chart-subtitle">Revenue and outstanding comparison</p>
                    </div>
                    <div class="chart-body">
                        <canvas id="revenueComparisonChart" height="250"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Performing Months -->
        @if(count($topPerformingMonths) > 0)
        <div class="row mb-4">
            <div class="col-12">
                <div class="analytics-chart-card">
                    <div class="chart-header">
                        <h6 class="chart-title">Top Performing Months</h6>
                        <p class="chart-subtitle">Best revenue generating months</p>
                    </div>
                    <div class="chart-body">
                        <div class="row">
                            @foreach($topPerformingMonths as $index => $month)
                            <div class="col-xl-4 col-md-6 mb-3">
                                <div class="performance-card rank-{{ $index + 1 }}">
                                    <div class="rank-badge">
                                        @if($index == 0)
                                            <i class="bi bi-trophy-fill text-warning"></i>
                                        @elseif($index == 1)
                                            <i class="bi bi-award-fill text-muted"></i>
                                        @else
                                            <i class="bi bi-award text-dark"></i>
                                        @endif
                                        #{{ $index + 1 }}
                                    </div>
                                    <div class="performance-content">
                                        <h5 class="month-name">{{ $month['month_name'] }}</h5>
                                        <div class="performance-stats">
                                            <div class="stat-item">
                                                <span class="stat-label px-2">Revenue</span>
                                                <span class="stat-value">Rs.{{ number_format($month['revenue']) }}</span>
                                            </div>
                                            <div class="stat-item">
                                                <span class="stat-label px-2">Invoices</span>
                                                <span class="stat-value">{{ $month['total_invoices'] }}</span>
                                            </div>
                                            <div class="stat-item">
                                                <span class="stat-label px-2">Collection Rate</span>
                                                <span class="stat-value">{{ number_format(($month['revenue'] / $month['total_sales']) * 100, 1) }}%</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Detailed Analytics Table -->
        <div class="row">
            <div class="col-12">
                <div class="analytics-chart-card">
                    <div class="chart-header">
                        <h6 class="chart-title">Monthly Breakdown</h6>
                        <p class="chart-subtitle">Detailed monthly performance data</p>
                    </div>
                    <div class="chart-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Month</th>
                                        <th>Invoices</th>
                                        <th>Total Sales</th>
                                        <th>Revenue</th>
                                        <th>Due Amount</th>
                                        <th>Collection %</th>
                                        <th>Growth</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($monthlySalesData as $index => $month)
                                    <tr>
                                        <td>
                                            <strong>{{ $month['month_name'] }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">{{ $month['total_invoices'] }}</span>
                                        </td>
                                        <td>Rs.{{ number_format($month['total_sales']) }}</td>
                                        <td>
                                            <span class="text-success fw-bold">Rs.{{ number_format($month['revenue']) }}</span>
                                        </td>
                                        <td>
                                            <span class="text-danger">Rs.{{ number_format($month['due_amount']) }}</span>
                                        </td>
                                        <td>
                                            @php
                                                $collectionRate = $month['total_sales'] > 0 ? ($month['revenue'] / $month['total_sales']) * 100 : 0;
                                            @endphp
                                            <div class="d-flex align-items-center">
                                                <div class="progress me-2" style="width: 60px; height: 8px;">
                                                    <div class="progress-bar 
                                                        @if($collectionRate >= 80) bg-success 
                                                        @elseif($collectionRate >= 60) bg-warning 
                                                        @else bg-danger @endif" 
                                                        style="width: {{ $collectionRate }}%"></div>
                                                </div>
                                                <span class="small">{{ number_format($collectionRate, 1) }}%</span>
                                            </div>
                                        </td>
                                        <td>
                                            @if($index > 0)
                                                @php
                                                    $prevMonth = $monthlySalesData[$index - 1];
                                                    $growth = $prevMonth['revenue'] > 0 ? (($month['revenue'] - $prevMonth['revenue']) / $prevMonth['revenue']) * 100 : 0;
                                                @endphp
                                                <span class="badge {{ $growth >= 0 ? 'bg-success' : 'bg-danger' }}">
                                                    <i class="bi bi-arrow-{{ $growth >= 0 ? 'up' : 'down' }}"></i>
                                                    {{ number_format(abs($growth), 1) }}%
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Analytics data
        const monthlySalesData = @json($monthlySalesData);
        const invoiceStatusData = @json($invoiceStatusData);
        const paymentTrendsData = @json($paymentTrendsData);

        // Chart instances
        let monthlySalesChartInstance = null;
        let invoiceStatusChartInstance = null;
        let paymentTrendsChartInstance = null;
        let revenueComparisonChartInstance = null;

        // Global helper function to format large numbers
        function formatCurrency(value) {
            if (value >= 10000000) {
                return 'Rs.' + (value / 10000000).toFixed(1) + 'Cr';
            } else if (value >= 100000) {
                return 'Rs.' + (value / 100000).toFixed(1) + 'L';
            } else if (value >= 1000) {
                return 'Rs.' + (value / 1000).toFixed(0) + 'K';
            }
            return 'Rs.' + new Intl.NumberFormat().format(value);
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize analytics charts
            initializeAnalyticsCharts();

            // Listen for Livewire updates
            document.addEventListener('livewire:load', function() {
                initializeAnalyticsCharts();
            });

            // Listen for refresh event
            document.addEventListener('analytics-refreshed', function() {
                initializeAnalyticsCharts();
            });
        });

        function initializeAnalyticsCharts() {
            // Destroy existing charts before creating new ones
            destroyExistingCharts();
            
            // Monthly Sales Trend Chart
            initializeMonthlySalesChart();
            
            // Invoice Status Pie Chart
            initializeInvoiceStatusChart();
            
            // Payment Trends Chart
            initializePaymentTrendsChart();
            
            // Revenue Comparison Chart
            initializeRevenueComparisonChart();
        }

        function destroyExistingCharts() {
            if (monthlySalesChartInstance) {
                monthlySalesChartInstance.destroy();
                monthlySalesChartInstance = null;
            }
            if (invoiceStatusChartInstance) {
                invoiceStatusChartInstance.destroy();
                invoiceStatusChartInstance = null;
            }
            if (paymentTrendsChartInstance) {
                paymentTrendsChartInstance.destroy();
                paymentTrendsChartInstance = null;
            }
            if (revenueComparisonChartInstance) {
                revenueComparisonChartInstance.destroy();
                revenueComparisonChartInstance = null;
            }

            // Reset all canvas elements to prevent dimension accumulation
            const canvasIds = ['monthlySalesChart', 'invoiceStatusChart', 'paymentTrendsChart', 'revenueComparisonChart'];
            canvasIds.forEach(canvasId => {
                const canvas = document.getElementById(canvasId);
                if (canvas) {
                    // Clear all canvas attributes and styles
                    canvas.style.height = '';
                    canvas.style.width = '';
                    canvas.removeAttribute('height');
                    canvas.removeAttribute('width');
                    // Reset canvas parent container if needed
                    const parent = canvas.parentElement;
                    if (parent) {
                        parent.style.height = '';
                        parent.style.width = '';
                    }
                }
            });
        }

        function resetCanvas(canvasId) {
            const canvas = document.getElementById(canvasId);
            if (!canvas) return null;

            // Store parent for context
            const parent = canvas.parentElement;
            
            // Remove all inline styles and attributes
            canvas.removeAttribute('style');
            canvas.removeAttribute('height');
            canvas.removeAttribute('width');
            canvas.style.cssText = '';
            
            // Reset parent container styles if needed
            if (parent) {
                parent.style.height = '';
                parent.style.width = '';
            }

            return canvas;
        }

        function initializeMonthlySalesChart() {
            const canvas = resetCanvas('monthlySalesChart');
            if (!canvas) return;

            const ctx = canvas.getContext('2d');

            const months = monthlySalesData.map(item => item.month_name);
            const salesData = monthlySalesData.map(item => item.total_sales);
            const revenueData = monthlySalesData.map(item => item.revenue);

            monthlySalesChartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: months,
                    datasets: [
                        {
                            label: 'Total Sales',
                            data: salesData,
                            borderColor: '#007bff',
                            backgroundColor: 'rgba(0, 123, 255, 0.15)',
                            borderWidth: 4,
                            pointRadius: 6,
                            pointHoverRadius: 8,
                            pointBackgroundColor: '#007bff',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            fill: true,
                            tension: 0.4
                        },
                        {
                            label: 'Revenue',
                            data: revenueData,
                            borderColor: '#28a745',
                            backgroundColor: 'rgba(40, 167, 69, 0.15)',
                            borderWidth: 4,
                            pointRadius: 6,
                            pointHoverRadius: 8,
                            pointBackgroundColor: '#28a745',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            fill: true,
                            tension: 0.4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    aspectRatio: 1.5,
                    layout: {
                        padding: {
                            top: 15,
                            bottom: 15,
                            left: 10,
                            right: 10
                        }
                    },
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        title: {
                            display: false
                        },
                        legend: {
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                padding: 20
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            borderColor: '#007bff',
                            borderWidth: 1,
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + formatCurrency(context.raw);
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    size: window.innerWidth < 768 ? 10 : 12
                                }
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)'
                            },
                            ticks: {
                                stepSize: 1000000,
                                maxTicksLimit: 12,
                                callback: function(value) {
                                    return formatCurrency(value);
                                },
                                font: {
                                    size: window.innerWidth < 768 ? 11 : 14
                                }
                            }
                        }
                    }
                }
            });
        }

        function initializeInvoiceStatusChart() {
            const ctx = resetCanvas('invoiceStatusChart');
            if (!ctx) return;

            const statusLabels = invoiceStatusData.map(item => {
                switch(item.payment_status) {
                    case 'paid': return 'Fully Paid';
                    case 'partial': return 'Partial';
                    case 'pending': return 'Pending';
                    default: return item.payment_status;
                }
            });
            const statusCounts = invoiceStatusData.map(item => item.count);
            const statusColors = ['#28a745', '#ffc107', '#dc3545'];

            invoiceStatusChartInstance = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: statusLabels,
                    datasets: [{
                        data: statusCounts,
                        backgroundColor: statusColors,
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    aspectRatio: 1,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            borderColor: '#007bff',
                            borderWidth: 1,
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((context.raw / total) * 100).toFixed(1);
                                    return context.label + ': ' + context.raw + ' (' + percentage + '%)';
                                }
                            }
                        }
                    },
                    cutout: '60%'
                }
            });
        }

        function initializePaymentTrendsChart() {
            const ctx = resetCanvas('paymentTrendsChart');
            if (!ctx) return;

            const months = paymentTrendsData.map(item => item.month_name);
            const paymentAmounts = paymentTrendsData.map(item => item.total_payments);

            paymentTrendsChartInstance = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: months,
                    datasets: [{
                        label: 'Collections',
                        data: paymentAmounts,
                        backgroundColor: 'rgba(40, 167, 69, 0.8)',
                        borderColor: '#28a745',
                        borderWidth: 2,
                        borderRadius: 8,
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    aspectRatio: 2,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            borderColor: '#28a745',
                            borderWidth: 1,
                            callbacks: {
                                label: function(context) {
                                    const value = context.raw;
                                    let formattedValue;
                                    if (value >= 10000000) {
                                        formattedValue = 'Rs.' + (value / 10000000).toFixed(1) + ' Cr';
                                    } else if (value >= 100000) {
                                        formattedValue = 'Rs.' + (value / 100000).toFixed(1) + ' L';
                                    } else if (value >= 1000) {
                                        formattedValue = 'Rs.' + (value / 1000).toFixed(0) + ' K';
                                    } else {
                                        formattedValue = 'Rs.' + new Intl.NumberFormat().format(value);
                                    }
                                    return 'Collections: ' + formattedValue;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    size: window.innerWidth < 768 ? 10 : 12
                                }
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)'
                            },
                            ticks: {
                                stepSize: 50000,
                                maxTicksLimit: 8,
                                callback: function(value) {
                                    return formatCurrency(value);
                                },
                                font: {
                                    size: window.innerWidth < 768 ? 10 : 12
                                }
                            }
                        }
                    }
                }
            });
        }

        function initializeRevenueComparisonChart() {
            const ctx = resetCanvas('revenueComparisonChart');
            if (!ctx) return;

            const months = monthlySalesData.map(item => item.month_name);
            const revenueData = monthlySalesData.map(item => item.revenue);
            const dueData = monthlySalesData.map(item => item.due_amount);

            revenueComparisonChartInstance = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: months,
                    datasets: [
                        {
                            label: 'Revenue',
                            data: revenueData,
                            backgroundColor: 'rgba(40, 167, 69, 0.8)',
                            borderColor: '#28a745',
                            borderWidth: 2
                        },
                        {
                            label: 'Outstanding Due',
                            data: dueData,
                            backgroundColor: 'rgba(220, 53, 69, 0.8)',
                            borderColor: '#dc3545',
                            borderWidth: 2
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    aspectRatio: 2,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                padding: 20
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            borderColor: '#007bff',
                            borderWidth: 1,
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + formatCurrency(context.raw);
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    size: window.innerWidth < 768 ? 10 : 12
                                }
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)'
                            },
                            ticks: {
                                stepSize: 50000,
                                maxTicksLimit: 8,
                                callback: function(value) {
                                    return formatCurrency(value);
                                },
                                font: {
                                    size: window.innerWidth < 768 ? 10 : 12
                                }
                            }
                        }
                    }
                }
            });
        }

        // Chart control buttons
        document.addEventListener('click', function(e) {
            if (e.target.matches('[data-chart]')) {
                const chartType = e.target.getAttribute('data-chart');
                const buttons = document.querySelectorAll('[data-chart]');
                
                buttons.forEach(btn => btn.classList.remove('active'));
                e.target.classList.add('active');
                
                // Update chart based on selection
                if (monthlySalesChartInstance && chartType === 'revenue') {
                    // Show only revenue data
                    monthlySalesChartInstance.data.datasets[0].hidden = true;
                    monthlySalesChartInstance.data.datasets[1].hidden = false;
                } else if (monthlySalesChartInstance && chartType === 'sales') {
                    // Show only sales data
                    monthlySalesChartInstance.data.datasets[0].hidden = false;
                    monthlySalesChartInstance.data.datasets[1].hidden = true;
                }
                
                if (monthlySalesChartInstance) {
                    monthlySalesChartInstance.update();
                }
            }
        });

        // Window resize handler for responsive chart sizing
        let resizeTimer;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                [monthlySalesChartInstance, invoiceStatusChartInstance, paymentTrendsChartInstance, revenueComparisonChartInstance].forEach(chart => {
                    if (chart) {
                        chart.resize();
                        chart.update('none');
                    }
                });
            }, 300);
        });
    </script>
    @endpush
</div>
