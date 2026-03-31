<div class="card shadow-sm border-0" style="border-radius: 20px; overflow: hidden;">
    <div class="card-header border-0 py-4" style="background: linear-gradient(0deg, rgba(59, 91, 12, 1) 0%, rgba(142, 185, 34, 1) 100%);">
        <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap">
            <button type="button" class="btn shadow-sm" style="background-color: #8eb922; color: #fff; border-radius: 12px; padding: 10px 20px; font-weight: 600; border: none;" wire:click="previousYear">
                <i class="bi bi-chevron-left"></i> Previous
            </button>
            
            <div class="d-flex align-items-center gap-3 flex-wrap justify-content-center">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle p-2 shadow-sm" style="width: 45px; height: 45px; background-color: #fff; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-calendar-month" style="font-size: 1.3rem; color: #8eb922;"></i>
                    </div>
                    <h5 class="mb-0 fw-bold" style="font-size: 1.4rem; color: #fff;">Monthly Sales Report</h5>
                </div>
                <select class="form-select form-select-sm shadow-sm" style="width: 120px; border-radius: 12px; font-weight: 600; font-size: 1.0rem; background-color: #fff;" wire:model.live="monthlyYear">
                    @php
                        // Start year is 2025, show from 2025 to current year + 1
                        $startYear = 2025;
                        $currentYear = now()->year;
                        $endYear = $currentYear + 1;
                        
                        // Determine display range: from startYear to endYear
                        $displayStartYear = $startYear;
                        $displayEndYear = $endYear;
                    @endphp
                    @for($year = $displayStartYear; $year <= $displayEndYear; $year++)
                    <option value="{{ $year }}">{{ $year }}</option>
                    @endfor
                </select>
            </div>
            
            <button type="button" class="btn shadow-sm" style="background-color: #8eb922; color: #fff; border-radius: 12px; padding: 10px 20px; font-weight: 600; border: none;" wire:click="nextYear" {{ !$this->canNavigateNextYear() ? 'disabled' : '' }}>
                Next <i class="bi bi-chevron-right"></i>
            </button>
        </div>
    </div>

    <div class="card-body p-2">
        <!-- Card View - All 12 Months -->
        <div class="row g-2">
            @php
                // Helper function to get month name
                $monthNames = [
                    1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                    5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                    9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
                ];
                
                // Create a map of existing data by month - use correct column name
                $dataMap = [];
                foreach($data as $item) {
                    $monthNum = (int)$item->month;  // Database returns 'month' column, not 'month_number'
                    $yearNum = (int)$item->year;
                    // Create composite key to ensure we filter by correct year too
                    $key = $yearNum . '-' . $monthNum;
                    $dataMap[$key] = $item;
                }
                
                $currentYear = (int)($monthlyYear ?? now()->year);
                $nextYear = $currentYear + 1;
            @endphp
            
            @for($month = 1; $month <= 12; $month++)
                @php
                    // Look up data with year-month key
                    $dataKey = $currentYear . '-' . $month;
                    $item = $dataMap[$dataKey] ?? null;
                    $daysInMonth = \Carbon\Carbon::createFromDate($currentYear, $month, 1)->daysInMonth;
                    $monthDate = \Carbon\Carbon::createFromDate($currentYear, $month, 1);
                    $upcomingDays = $monthDate->diffInDays(\Carbon\Carbon::now());
                    if($monthDate > \Carbon\Carbon::now()) {
                        $upcomingDays = ceil($upcomingDays);
                    }
                @endphp
                
                <div class="col-lg-2 col-md-4 col-sm-6 col-12">
                    <div class="card h-100 border-0 shadow-sm hover-lift" style="border-radius: 12px; overflow: hidden; transition: all 0.15s ease;">
                        <div class="card-header py-2 px-2 border-0" style="background-color: @if($item) #f0f8e8; border-bottom: 2px solid #8eb922; @else #ffe0e0; border-bottom: 2px solid #ff6b6b; @endif">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 fw-semibold" style="font-size: 0.85rem; color: @if($item) #3B5B0C; @else #c92a2a; @endif">{{ $monthNames[$month] }}</h6>
                                <span class="badge" style="background-color: @if($item) #8eb922; @else #ff6b6b; @endif; color: #fff; border-radius: 4px; padding: 2px 6px; font-size: 0.65rem;">{{ $currentYear }}</span>
                            </div>
                        </div>
                        
                        @if($item)
                            <!-- Data Available -->
                            <div class="card-body p-2">
                                <div class="mb-2 p-2 rounded-2" style="background-color: #f8f8f8; border-left: 4px solid #8eb922;">
                                    <div class="d-flex align-items-center mb-1">
                                        <i class="bi bi-graph-up-arrow me-2" style="font-size: 0.85rem; color: #8eb922;"></i>
                                        <small class="fw-medium" style="font-size: 0.75rem; color: #666;">Total Sales</small>
                                    </div>
                                    <strong class="d-block" style="font-size: 0.9rem; color: #3B5B0C;">Rs.{{ number_format($item->grand_total ?? 0, 2) }}</strong>
                                </div>
                                <div class="mb-2 p-2 rounded-2" style="background-color: #f8f8f8; border-left: 4px solid #ff6b6b;">
                                    <div class="d-flex align-items-center mb-1">
                                        <i class="bi bi-arrow-return-left me-2" style="font-size: 0.85rem; color: #ff6b6b;"></i>
                                        <small class="fw-medium" style="font-size: 0.75rem; color: #666;">Returns</small>
                                    </div>
                                    <strong class="d-block" style="font-size: 0.9rem; color: #c92a2a;">Rs.{{ number_format($item->return_total ?? 0, 2) }}</strong>
                                </div>
                                <div class="mb-2 p-2 rounded-2" style="background-color: #f8f8f8; border-left: 4px solid #ffa94d;">
                                    <div class="d-flex align-items-center mb-1">
                                        <i class="bi bi-percent me-2" style="font-size: 0.85rem; color: #ffa94d;"></i>
                                        <small class="fw-medium" style="font-size: 0.75rem; color: #666;">Discount</small>
                                    </div>
                                    <strong class="d-block" style="font-size: 0.9rem; color: #d9480f;">Rs.{{ number_format($item->total_discount ?? 0, 2) }}</strong>
                                </div>
                                <div class="mb-2 p-2 rounded-2" style="background-color: #f8f8f8; border-left: 4px solid #4c6ef5;">
                                    <div class="d-flex align-items-center mb-1">
                                        <i class="bi bi-arrow-left-right me-2" style="font-size: 0.85rem; color: #4c6ef5;"></i>
                                        <small class="fw-medium" style="font-size: 0.75rem; color: #666;">Payment Adjust</small>
                                    </div>
                                    <strong class="d-block" style="font-size: 0.9rem; color: #1c7ed6;">Rs.{{ number_format($item->payment_adjustment ?? 0, 2) }}</strong>
                                </div>
                                <div class="p-2 rounded-2" style="background: linear-gradient(0deg, rgba(59, 91, 12, 1) 0%, rgba(142, 185, 34, 1) 100%);">
                                    <div class="d-flex align-items-center mb-1">
                                        <i class="bi bi-wallet2 me-2" style="font-size: 0.85rem; color: #fff;"></i>
                                        <small class="fw-medium" style="font-size: 0.75rem; color: #fff;">Net Total</small>
                                    </div>
                                    <strong class="d-block" style="font-size: 1rem; color: #fff;">Rs.{{ number_format(($item->grand_total ?? 0) - ($item->return_total ?? 0), 2) }}</strong>
                                </div>
                            </div>
                        @else
                            <!-- No Data - Shop Closed -->
                            <div class="card-body p-3 d-flex flex-column align-items-center justify-content-center" style="min-height: 250px;">
                                <div class="mb-3" style="text-align: center;">
                                    <i class="bi bi-shop-window" style="font-size: 2.5rem; color: #ff6b6b;"></i>
                                </div>
                                <h6 class="fw-bold mb-2" style="color: #c92a2a; font-size: 0.9rem;">Shop Closed</h6>
                                <p class="text-muted mb-3" style="font-size: 0.75rem; text-align: center;">No sales recorded</p>
                                
                                @php
                                    $nextMonthDate = \Carbon\Carbon::createFromDate($currentYear, $month, 1);
                                    $isUpcoming = $nextMonthDate > \Carbon\Carbon::now();
                                @endphp
                                
                                @if($isUpcoming)
                                    <div class="p-2 rounded-2" style="background-color: #e3f2fd; border-left: 3px solid #4c6ef5; width: 100%;">
                                        <small style="color: #1c7ed6; font-weight: 500; font-size: 0.7rem;">
                                            <i class="bi bi-calendar-event"></i> Upcoming Month
                                        </small>
                                        <p style="font-size: 0.75rem; color: #1c7ed6; margin: 3px 0 0 0;">
                                            {{ $daysInMonth }} days - {{ $monthDate->format('M d') }} to {{ $monthDate->endOfMonth()->format('d/m/Y') }}
                                        </p>
                                    </div>
                                @else
                                    <div class="p-2 rounded-2" style="background-color: #fff3cd; border-left: 3px solid #ffa94d; width: 100%;">
                                        <small style="color: #d9480f; font-weight: 500; font-size: 0.7rem;">
                                            <i class="bi bi-exclamation-circle"></i> Past Month
                                        </small>
                                        <p style="font-size: 0.75rem; color: #d9480f; margin: 3px 0 0 0;">
                                            No activity recorded
                                        </p>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            @endfor
        </div>
        
        <!-- Next Year Preview -->
        @php
            $startYear = 2025;
            $currentYear = now()->year;
            $nextYearDisplay = $currentYear + 1;
            // Only show preview if current selected year is not already the last year
            $showPreview = (int)$monthlyYear < $nextYearDisplay;
        @endphp
        
        @if($showPreview)
        <div class="mt-4 pt-3" style="border-top: 2px solid #e0e0e0;">
            <h6 class="fw-bold mb-3" style="color: #3B5B0C; font-size: 1rem;">
                <i class="bi bi-arrow-right-circle"></i> {{ (int)$monthlyYear + 1 }} - Next Year
            </h6>
            <div class="row g-2">
                @for($month = 1; $month <= 3; $month++)
                    @php
                        $nextYearDate = \Carbon\Carbon::createFromDate((int)$monthlyYear + 1, $month, 1);
                        $daysInMonth = $nextYearDate->daysInMonth;
                    @endphp
                    <div class="col-lg-2 col-md-4 col-sm-6 col-12">
                        <div class="card border-0 shadow-sm" style="border-radius: 12px; background-color: #f0f8ff; min-height: 180px; display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 20px; text-align: center;">
                            <i class="bi bi-calendar3" style="font-size: 2rem; color: #4c6ef5; margin-bottom: 10px;"></i>
                            <h6 class="fw-bold" style="color: #1c7ed6; margin-bottom: 5px;">{{ $monthNames[$month] }}</h6>
                            <p style="font-size: 0.75rem; color: #666; margin: 0;">
                                <small>{{ $daysInMonth }} days</small><br>
                                <small style="color: #1c7ed6;">{{ $nextYearDate->format('d/m/Y') }}</small>
                            </p>
                        </div>
                    </div>
                @endfor
            </div>
        </div>
        @endif
    </div>
</div>

<style>
    .hover-lift {
        transition: all 0.25s ease;
    }
    
    .hover-lift:hover {
        transform: translateY(-6px);
        box-shadow: 0 12px 24px rgba(142, 185, 34, 0.25) !important;
    }
    
    .form-select:focus {
        border-color: #8eb922 !important;
        box-shadow: 0 0 0 0.25rem rgba(142, 185, 34, 0.25) !important;
    }
    
    .form-select {
        border-color: #ddd;
    }
</style>
