<!-- Dashboard Overview -->
<div>
    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label mb-2">Total Sales</div>
                        <div class="stat-value text-success">Rs.{{ number_format($totalSales, 2) }}</div>
                        <small class="text-muted">This Month</small>
                    </div>
                    <div class="stat-icon bg-success bg-opacity-10 text-success">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label mb-2">Total Purchases</div>
                        <div class="stat-value text-primary">Rs.{{ number_format($totalPurchases, 2) }}</div>
                        <small class="text-muted">This Month</small>
                    </div>
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-cart-check"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label mb-2">Net Profit</div>
                        <div class="stat-value {{ $netProfit >= 0 ? 'text-success' : 'text-danger' }}">
                            Rs.{{ number_format(abs($netProfit), 2) }}
                        </div>
                        <small class="text-muted">This Month</small>
                    </div>
                    <div class="stat-icon {{ $netProfit >= 0 ? 'bg-success' : 'bg-danger' }} bg-opacity-10 {{ $netProfit >= 0 ? 'text-success' : 'text-danger' }}">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label mb-2">Outstanding</div>
                        <div class="stat-value text-warning">Rs.{{ number_format($outstandingAmount, 2) }}</div>
                        <small class="text-muted">Pending Payments</small>
                    </div>
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Sales Graph -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="chart-container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h5 class="fw-bold mb-1">Monthly Sales Trend</h5>
                        <p class="text-muted mb-0">Last 12 months performance</p>
                    </div>
                    <div class="badge bg-primary">
                        <i class="bi bi-graph-up me-1"></i>Trending
                    </div>
                </div>
                <canvas id="monthlySalesChart" height="80"></canvas>
            </div>
        </div>
    </div>

    <!-- Quick Stats Row -->
    <div class="row mb-4">
        <div class="col-lg-4 mb-3">
            <div class="stat-card">
                <h6 class="fw-bold mb-3">
                    <i class="bi bi-arrow-return-left text-danger me-2"></i>Returns
                </h6>
                <div class="d-flex justify-content-between align-items-center">
                    <div class="stat-value text-danger">Rs.{{ number_format($totalReturns, 2) }}</div>
                    <small class="text-muted">This Month</small>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-3">
            <div class="stat-card">
                <h6 class="fw-bold mb-3">
                    <i class="bi bi-wallet2 text-info me-2"></i>Expenses
                </h6>
                <div class="d-flex justify-content-between align-items-center">
                    <div class="stat-value text-info">Rs.{{ number_format($totalExpenses, 2) }}</div>
                    <small class="text-muted">This Month</small>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-3">
            <div class="stat-card">
                <h6 class="fw-bold mb-3">
                    <i class="bi bi-percent text-purple me-2"></i>Profit Margin
                </h6>
                <div class="d-flex justify-content-between align-items-center">
                    <div class="stat-value text-purple">
                        {{ $totalSales > 0 ? number_format(($netProfit / $totalSales) * 100, 1) : 0 }}%
                    </div>
                    <small class="text-muted">This Month</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-12">
            <div class="stat-card">
                <h5 class="fw-bold mb-3">
                    <i class="bi bi-lightning-charge text-warning me-2"></i>Quick Report Access
                </h5>
                <div class="row g-3">
                    <div class="col-md-3 col-6">
                        <button wire:click="$set('selectedReport', 'daily_sales')" 
                                class="btn btn-outline-primary w-100 py-3">
                            <i class="bi bi-calendar-day d-block mb-2" style="font-size: 24px;"></i>
                            Daily Sales
                        </button>
                    </div>
                    <div class="col-md-3 col-6">
                        <button wire:click="$set('selectedReport', 'stock')" 
                                class="btn btn-outline-success w-100 py-3">
                            <i class="bi bi-boxes d-block mb-2" style="font-size: 24px;"></i>
                            Stock Report
                        </button>
                    </div>
                    <div class="col-md-3 col-6">
                        <button wire:click="$set('selectedReport', 'outstanding')" 
                                class="btn btn-outline-warning w-100 py-3">
                            <i class="bi bi-exclamation-circle d-block mb-2" style="font-size: 24px;"></i>
                            Outstanding
                        </button>
                    </div>
                    <div class="col-md-3 col-6">
                        <button wire:click="$set('selectedReport', 'profit_loss')" 
                                class="btn btn-outline-info w-100 py-3">
                            <i class="bi bi-graph-up-arrow d-block mb-2" style="font-size: 24px;"></i>
                            Profit & Loss
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('monthlySalesChart');
        if (ctx) {
            const monthlySalesData = @json($monthlySalesData);
            
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: monthlySalesData.map(d => d.month),
                    datasets: [{
                        label: 'Sales Amount',
                        data: monthlySalesData.map(d => d.amount),
                        borderColor: 'rgb(102, 126, 234)',
                        backgroundColor: 'rgba(102, 126, 234, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointBackgroundColor: 'rgb(102, 126, 234)',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Rs.' + context.parsed.y.toLocaleString('en-LK', {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    });
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'Rs.' + value.toLocaleString();
                                }
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush
