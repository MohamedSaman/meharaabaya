@push('styles')
<style>
    /* Add your styles here */
</style>
@endpush

<div>
    <div class="container-fluid">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title">Staff Payment Collection Details</h4>
                <div class="d-flex gap-2">
                    <button wire:click="printData" class="btn btn-secondary">
                        <i class="bi bi-printer"></i> Print
                    </button>
                    <button wire:click="exportToCSV" class="btn btn-success">
                        <i class="bi bi-file-earmark-excel"></i> Export CSV
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped" id="staffDuesTable">
                        <thead>
                            <tr class="text-center">
                                <th>#</th>
                                <th>Staff Name</th>
                                <th>Email</th>
                                <th>Contact</th>
                                <th>Total Sales</th>
                                <th>Collected Amount</th>
                                <th>Due Amount</th>
                                <th>Collection Progress</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($staffDues as $index => $staff)
                                <tr class="text-center">
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $staff->name }}</td>
                                    <td>{{ $staff->email }}</td>
                                    <td>{{ $staff->contact }}</td>
                                    <td>
                                        <span class="badge bg-primary">
                                            Rs.{{ number_format($staff->total_amount, 2) }}</td>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">
                                            Rs.{{ number_format($staff->collected_amount, 2) }}</td>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $staff->due_amount > 0 ? 'danger' : 'success' }}">
                                            Rs.{{ number_format($staff->due_amount, 2) }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            $percentage =
                                                $staff->total_amount > 0
                                                    ? round(($staff->collected_amount / $staff->total_amount) * 100)
                                                    : 100;
                                            $progressClass = match (true) {
                                                $percentage >= 90 => 'success',
                                                $percentage >= 70 => 'info',
                                                $percentage >= 50 => 'primary',
                                                $percentage >= 30 => 'warning',
                                                default => 'danger',
                                            };
                                        @endphp
                                        <div class="d-flex align-items-center">
                                            <div class="progress flex-grow-1" style="height: 10px;">
                                                <div class="progress-bar bg-{{ $progressClass }}" role="progressbar"
                                                    style="width: {{ $percentage }}%;"
                                                    aria-valuenow="{{ $percentage }}" aria-valuemin="0"
                                                    aria-valuemax="100">
                                                </div>
                                            </div>
                                            <span class="ms-2">{{ $percentage }}%</span>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">No staff due records found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $staffDues->links('livewire.custom-pagination') }}
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:initialized', function() {
        Livewire.on('print-table', function() {
            // Create a new window for printing with isolation
            const printWindow = window.open('', '_blank', 'width=1000,height=700');
            
            // Get the table content with proper formatting
            const tableElement = document.querySelector('#staffDuesTable').cloneNode(true);
            
            // Create print-friendly HTML with complete document structure
            const htmlContent = `
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Staff Due Details</title>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <style>
                        @page {
                            size: landscape;
                            margin: 1cm;
                        }
                        body {
                            font-family: Arial, sans-serif;
                            padding: 20px;
                            margin: 0;
                        }
                        h2, h3, p {
                            margin: 0 0 10px 0;
                        }
                        .container {
                            max-width: 100%;
                        }
                        table {
                            width: 100%;
                            border-collapse: collapse;
                            margin-bottom: 20px;
                        }
                        thead {
                            background-color: #f2f2f2;
                        }
                        th, td {
                            border: 1px solid #ddd;
                            padding: 10px 8px;
                            text-align: center;
                        }
                        th {
                            font-weight: bold;
                        }
                        tr:nth-child(even) {
                            background-color: #f9f9f9;
                        }
                        .badge {
                            padding: 5px 10px;
                            border-radius: 4px;
                            font-size: 12px;
                            font-weight: bold;
                            display: inline-block;
                        }
                        .bg-primary, .badge-primary {
                            background-color: #0d6efd;
                            color: white;
                        }
                        .bg-success, .badge-success {
                            background-color: #198754;
                            color: white;
                        }
                        .bg-danger, .badge-danger {
                            background-color: #dc3545;
                            color: white;
                        }
                        .bg-info, .badge-info {
                            background-color: #0dcaf0;
                            color: black;
                        }
                        .bg-warning, .badge-warning {
                            background-color: #ffc107;
                            color: black;
                        }
                        .progress {
                            background-color: #e9ecef;
                            border-radius: 0.25rem;
                            height: 10px;
                            width: 80%;
                            margin-right: 10px;
                            display: inline-block;
                        }
                        .progress-bar {
                            height: 100%;
                            border-radius: 0.25rem;
                        }
                        .header {
                            text-align: center;
                            margin-bottom: 25px;
                            padding-bottom: 15px;
                            border-bottom: 2px solid #ddd;
                        }
                        .footer {
                            text-align: center;
                            margin-top: 30px;
                            border-top: 1px solid #ddd;
                            padding-top: 10px;
                            font-size: 12px;
                            color: #666;
                        }
                        .buttons {
                            text-align: center;
                            margin-top: 25px;
                        }
                        .btn {
                            padding: 8px 16px;
                            border: none;
                            border-radius: 4px;
                            cursor: pointer;
                            margin-right: 10px;
                        }
                        .btn-primary {
                            background-color: #0d6efd;
                            color: white;
                        }
                        .btn-secondary {
                            background-color: #6c757d;
                            color: white;
                        }
                        @media print {
                            .buttons {
                                display: none;
                            }
                        }
                    </style>
                </head>
                <body>
                    <div class="container">
                        <div class="header">
                            <h2>Staff Payment Collection Details</h2>
                            <p>Generated on: ${new Date().toLocaleString()}</p>
                        </div>
                        
                        <div class="table-responsive">
                            ${tableElement.outerHTML}
                        </div>
                        
                        <div class="footer">
                            <p>Mr. WebxKey - Staff Due Report</p>
                        </div>
                        
                        <div class="buttons">
                            <button id="printButton" class="btn btn-primary">
                                <span>Print Report</span>
                            </button>
                            <button id="closeButton" class="btn btn-secondary">
                                <span>Close</span>
                            </button>
                        </div>
                    </div>
                </body>
                </html>
            `;
            
            // Write to the new window
            printWindow.document.open();
            printWindow.document.write(htmlContent);
            printWindow.document.close();
            
            // Wait for content to load completely before adding event listeners
            printWindow.onload = function() {
                // Fix progress bars in the new window
                const progressBars = printWindow.document.querySelectorAll('.progress-bar');
                progressBars.forEach(bar => {
                    const width = bar.style.width;
                    if(width) {
                        bar.style.width = width;
                    }
                });
                
                // Add event listeners to buttons
                const printButton = printWindow.document.getElementById('printButton');
                if(printButton) {
                    printButton.addEventListener('click', function() {
                        printWindow.print();
                    });
                }
                
                const closeButton = printWindow.document.getElementById('closeButton');
                if(closeButton) {
                    closeButton.addEventListener('click', function() {
                        printWindow.close();
                    });
                }
                
                printWindow.focus();
            };
        });
    });
</script>
@endpush
