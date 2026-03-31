<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4 gap-2" style="flex-wrap: wrap;">
            <button type="button" class="btn btn-dark btn-sm" style="border-radius: 6px; padding: 8px 16px;" wire:click="previousMonth">
                <i class="bi bi-chevron-left"></i> Previous
            </button>
            
            <div class="d-flex align-items-center gap-3 flex-grow-1 justify-content-center" style="flex-wrap: wrap;">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-calendar-event text-primary" style="font-size: 1.2rem;"></i>
                    <h6 class="mb-0 fw-bold" style="font-size: 1rem;">Daily Sales Report</h6>
                </div>
                <select class="form-select form-select-sm" style="width: 110px; border-radius: 6px; border: 1px solid #ddd;" wire:model.live="dailyMonth">
                    <option value="1">January</option>
                    <option value="2">February</option>
                    <option value="3">March</option>
                    <option value="4">April</option>
                    <option value="5">May</option>
                    <option value="6">June</option>
                    <option value="7">July</option>
                    <option value="8">August</option>
                    <option value="9">September</option>
                    <option value="10">October</option>
                    <option value="11">November</option>
                    <option value="12">December</option>
                </select>
                <select class="form-select form-select-sm" style="width: 100px; border-radius: 6px; border: 1px solid #ddd;" wire:model.live="dailyYear">
                    @for($year = now()->year; $year >= now()->year - 5; $year--)
                    <option value="{{ $year }}">{{ $year }}</option>
                    @endfor
                </select>
            </div>
            
            <button type="button" class="btn btn-dark btn-sm" style="border-radius: 6px; padding: 8px 16px;" wire:click="nextMonth" {{ !$this->canNavigateNext() ? 'disabled' : '' }}>
                Next <i class="bi bi-chevron-right"></i>
            </button>
        </div>

        @if(count($data) > 0)
        <!-- Calendar View -->
        <div class="card border-0 bg-light p-4">
            <!-- Week Day Headers -->
            <div class="row g-2 mb-4">
                <div class="col text-center fw-bold" style="font-size: 0.8rem; color: #00bcd4; text-transform: uppercase; letter-spacing: 0.5px;">Monday</div>
                <div class="col text-center fw-bold" style="font-size: 0.8rem; color: #00bcd4; text-transform: uppercase; letter-spacing: 0.5px;">Tuesday</div>
                <div class="col text-center fw-bold" style="font-size: 0.8rem; color: #00bcd4; text-transform: uppercase; letter-spacing: 0.5px;">Wednesday</div>
                <div class="col text-center fw-bold" style="font-size: 0.8rem; color: #00bcd4; text-transform: uppercase; letter-spacing: 0.5px;">Thursday</div>
                <div class="col text-center fw-bold" style="font-size: 0.8rem; color: #00bcd4; text-transform: uppercase; letter-spacing: 0.5px;">Friday</div>
                <div class="col text-center fw-bold" style="font-size: 0.8rem; color: #00bcd4; text-transform: uppercase; letter-spacing: 0.5px;">Saturday</div>
                <div class="col text-center fw-bold" style="font-size: 0.8rem; color: #00bcd4; text-transform: uppercase; letter-spacing: 0.5px;">Sunday</div>
            </div>

            @php
                $minDate = collect($data)->min('sale_date');
                $maxDate = collect($data)->max('sale_date');
                $firstDate = \Carbon\Carbon::parse($minDate);
                $firstDayOfWeek = $firstDate->copy()->startOfMonth();
                
                // Adjust to Monday
                if ($firstDayOfWeek->dayOfWeek != 1) {
                    $firstDayOfWeek = $firstDayOfWeek->startOfWeek(\Carbon\Carbon::MONDAY);
                }
                
                $dataByDate = collect($data)->keyBy('sale_date');
                $weeks = [];
                $currentWeek = [];
                $currentDate = $firstDayOfWeek->copy();
                $monthEnd = $firstDate->copy()->endOfMonth();
                
                while ($currentDate->lte($monthEnd->copy()->endOfWeek(\Carbon\Carbon::SUNDAY))) {
                    if (count($currentWeek) == 7) {
                        $weeks[] = $currentWeek;
                        $currentWeek = [];
                    }
                    
                    $dateStr = $currentDate->format('Y-m-d');
                    $item = $dataByDate->get($dateStr);
                    $currentWeek[] = [
                        'date' => $currentDate->copy(),
                        'data' => $item,
                        'inMonth' => $currentDate->month == $firstDate->month
                    ];
                    
                    $currentDate->addDay();
                }
                
                if (count($currentWeek) > 0) {
                    $weeks[] = $currentWeek;
                }
            @endphp

            <!-- Calendar Grid -->
            @foreach($weeks as $week)
            <div class="row g-2 mb-3">
                @foreach($week as $day)
                <div class="col" style="min-height: 160px;">
                    @if($day['inMonth'])
                        @if($day['data'] && ($day['data']->grand_total > 0 || $day['data']->return_total > 0))
                            <!-- Sales Day Card -->
                            <div class="card h-100 border-2 border-success" style="background: linear-gradient(135deg, #f0fdf4 0%, #ffffff 100%); cursor: pointer;">
                                <div class="card-body p-3">
                                    <!-- Date Header -->
                                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                        <span class="badge bg-success" style="font-size: 0.75rem;">{{ $day['date']->format('D') }}</span>
                                        <span class="fw-bold text-success" style="font-size: 0.85rem;">{{ $day['date']->format('d M') }}</span>
                                    </div>
                                    
                                    <!-- Sales Row -->
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <small class="text-muted" style="font-size: 0.75rem;">💚 SALES</small>
                                        <span class="fw-bold text-success" style="font-size: 0.95rem;">{{ number_format($day['data']->grand_total, 0) }}</span>
                                    </div>
                                    
                                    <!-- Return Row -->
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <small class="text-muted" style="font-size: 0.75rem;">🔴 RETURN</small>
                                        <span class="fw-bold text-danger" style="font-size: 0.95rem;">{{ number_format($day['data']->return_total, 0) }}</span>
                                    </div>
                                    
                                    <!-- Net Sales -->
                                    <div class="border-top pt-2 mt-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted" style="font-size: 0.75rem;">NET SALES</small>
                                            <span class="fw-bold text-info" style="font-size: 1rem;">{{ number_format($day['data']->grand_total - $day['data']->return_total, 0) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <!-- Closed/No Sales -->
                            <div class="card h-100 border-1 border-secondary bg-light" style="opacity: 0.6;">
                                <div class="card-body p-3 d-flex flex-column align-items-center justify-content-center h-100">
                                    <span class="fw-bold text-success" style="font-size: 0.85rem;">{{ $day['date']->format('d') }}</span>
                                    <small class="text-secondary">{{ $day['date']->format('M') }}</small>
                                    <i class="bi bi-shop text-muted mt-2" style="font-size: 1.2rem; opacity: 0.3;"></i>
                                </div>
                            </div>
                        @endif
                    @else
                        <!-- Outside Month -->
                        <div class="card h-100 border-0 bg-light" style="opacity: 0.2;"></div>
                    @endif
                </div>
                @endforeach
            </div>
            @endforeach
        </div>

        <!-- Summary Cards -->
        <div class="row g-3 mt-4">
            <div class="col-lg-3 col-md-6">
                <div class="card border-0 bg-success text-white h-100" style="border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    <div class="card-body py-4 px-3">
                        <p class="card-text mb-2" style="font-size: 0.85rem; font-weight: 500; opacity: 0.9;">Total Sales</p>
                        <h3 class="card-title mb-0" style="font-size: 1.8rem; font-weight: 700;">Rs.{{ number_format(collect($data)->sum('grand_total'), 2) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card border-0 bg-danger text-white h-100" style="border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    <div class="card-body py-4 px-3">
                        <p class="card-text mb-2" style="font-size: 0.85rem; font-weight: 500; opacity: 0.9;">Total Returns</p>
                        <h3 class="card-title mb-0" style="font-size: 1.8rem; font-weight: 700;">Rs.{{ number_format(collect($data)->sum('return_total'), 2) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card border-0 text-white h-100" style="background: linear-gradient(135deg, #00bcd4 0%, #0097a7 100%); border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    <div class="card-body py-4 px-3">
                        <p class="card-text mb-2" style="font-size: 0.85rem; font-weight: 500; opacity: 0.9;">Net Total</p>
                        <h3 class="card-title mb-0" style="font-size: 1.8rem; font-weight: 700;">Rs.{{ number_format(collect($data)->sum('grand_total') - collect($data)->sum('return_total'), 2) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card border-0 text-white h-100" style="background: #6c757d; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    <div class="card-body py-4 px-3">
                        <p class="card-text mb-2" style="font-size: 0.85rem; font-weight: 500; opacity: 0.9;">Total Transactions</p>
                        <h3 class="card-title mb-0" style="font-size: 1.8rem; font-weight: 700;">{{ collect($data)->sum('total_sales') }}</h3>
                    </div>
                </div>
            </div>
        </div>
        @else
        <div class="text-center py-5 text-muted">
            <i class="bi bi-inbox display-4 mb-3"></i>
            <p>No daily sales data available for the selected period.</p>
        </div>
        @endif
    </div>
</div>
