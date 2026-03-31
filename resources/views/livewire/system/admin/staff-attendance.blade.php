<div class="container-fluid p-3">
    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <h2 class="card-title mb-0 h5">
                    {{ ucfirst(str_replace('_', ' ', $form['date_filter'])) }} Attendance
                </h2>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <form wire:submit.prevent="import" enctype="multipart/form-data"
                        class="d-flex flex-column flex-sm-row gap-2">
                        <div class="flex-grow-1">
                            <input type="file" wire:model="file" accept=".xls,.xlsx"
                                class="form-control form-control-sm">
                            @error('file')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm whitespace-nowrap">Import</button>
                    </form>

                    <div class="d-flex gap-2">
                        <select wire:model="form.date_filter" wire:change="$refresh" class="form-select form-select-sm"
                            style="width: auto;">
                            <option value="today">Today</option>
                            <option value="yesterday">Yesterday</option>
                            <option value="this_week">This Week</option>
                            <option value="last_week">Last Week</option>
                            <option value="this_month">This Month</option>
                            <option value="last_month">Last Month</option>
                        </select>
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by name or ID..."
                            class="form-control form-control-sm" style="min-width: 200px;">
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            @if (session('message'))
            <div class="alert alert-success rounded-0 border-start-0 border-end-0 mb-0">
                {{ session('message') }}
            </div>
            @endif
            @if (session('error'))
            <div class="alert alert-danger rounded-0 border-start-0 border-end-0 mb-0">
                {{ session('error') }}
            </div>
            @endif

            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="px-3" style="width: 120px;">Photo</th>
                            <th class="px-3">Name</th>
                            <th class="px-3">Check In</th>
                            <th class="px-3">Break</th>
                            <th class="px-3">Check Out</th>
                            <th class="px-3">Time Worked</th>
                            <th class="px-3">Late Hours</th>
                            <th class="px-3">Overtime</th>
                            <th class="px-3">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($employees as $employee)
                        @if (in_array($form['date_filter'], ['this_week', 'last_week', 'this_month', 'last_month']))
                        {{-- Multi-day view - show all attendance records for the period --}}
                        @forelse ($employee->attendances as $attendance)
                        <tr class="bg-white">
                            <td class="px-3">
                                <img class="rounded-circle object-cover" src="{{ $employee->profile_photo_path ? $employee->profile_photo_path : 'https://media.istockphoto.com/id/1300845620/vector/user-icon-flat-isolated-on-white-background-user-symbol-vector-illustration.jpg?s=612x612&w=0&k=20&c=yBeyba0hUkh14_jgv1OKqIH0CCSWU_4ckRkAoy2p73o=' }}"
                                    alt="{{ $employee->name ?? 'Unknown' }}" style="width: 50px; height: 50px;">
                            </td>
                            <td class="px-3  text-dark">{{ $employee->name ?? ($employee->fname . ' ' .
                                $employee->lname) }}</td>
                            <td class="px-3">{{ $attendance && $attendance->check_in ? $attendance->check_in : '--' }}
                            </td>
                            <td class="px-3">
                                @if($attendance->break_start)
                                {{ $attendance->break_start ? $attendance->break_start : '--' }} - {{
                                $attendance->break_end ? $attendance->break_end : '--' }}
                                @else
                                --
                                @endif
                            </td>
                            <td class="px-3">{{ $attendance->check_out ? $attendance->check_out : '--' }}</td>
                            <td class="px-3">{{ $attendance->time_worked ? number_format($attendance->time_worked, 1) .
                                ' hrs' : '--' }}</td>
                            <td class="px-3">{{ $attendance->late_hours ? number_format($attendance->late_hours, 1) . '
                                hrs' : '--' }}</td>
                            <td class="px-3">{{ $attendance->over_time ? number_format($attendance->over_time, 1) . '
                                hrs' : '--' }}</td>
                            <td class="px-3">
                                @php
                                $status = $attendance->status ?? 'absent';
                                $statusClass = match ($status) {
                                'present' => 'bg-success-subtle text-success-emphasis',
                                'late' => 'bg-warning-subtle text-warning-emphasis',
                                'early' => 'bg-info-subtle text-info-emphasis',
                                'leave', 'absent', null => 'bg-danger-subtle text-danger-emphasis',
                                default => 'bg-secondary-subtle text-secondary-emphasis',
                                };
                                @endphp
                                <select wire:model="statusUpdates.{{ $attendance->attendance_id }}"
                                    wire:change="updateStatus({{ $attendance->attendance_id }}, {{ $employee->emp_id }})"
                                    class="form-select form-select-sm fw-semibold border-0 rounded-pill ps-3 {{ $statusClass }}"
                                    style="width: 110px;">
                                    <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                                    <option value="absent">Absent</option>
                                    <option value="present">Present</option>
                                    <option value="late">Late</option>
                                    <option value="early">Early</option>
                                    <option value="leave">Leave</option>
                                </select>
                            </td>
                        </tr>
                        @empty
                        <tr class="bg-white">
                            <td class="px-3">
                                <img class="rounded-circle object-cover" src="{{ $employee->profile_photo_path ? $employee->profile_photo_path : 'https://media.istockphoto.com/id/1300845620/vector/user-icon-flat-isolated-on-white-background-user-symbol-vector-illustration.jpg?s=612x612&w=0&k=20&c=yBeyba0hUkh14_jgv1OKqIH0CCSWU_4ckRkAoy2p73o=' }}"
                                    alt="{{ $employee->fname ?? 'Unknown' }}" style="width: 50px; height: 50px;">
                            </td>
                            <td class="px-3 fw-bold text-dark">{{ $employee->fname . ' ' . $employee->lname }}</td>
                            <td colspan="7" class="text-center text-muted">No attendance records for {{ str_replace('_',
                                ' ', $form['date_filter']) }}</td>
                        </tr>
                        @endforelse
                        @else
                        {{-- Single-day view --}}
                        @php $attendance = $employee->attendances->first(); @endphp
                        <tr class="bg-white">
                            <td class="px-3">
                                <img class="rounded-circle object-cover" src="{{ $employee->profile_photo_path ? $employee->profile_photo_path : 'https://media.istockphoto.com/id/1300845620/vector/user-icon-flat-isolated-on-white-background-user-symbol-vector-illustration.jpg?s=612x612&w=0&k=20&c=yBeyba0hUkh14_jgv1OKqIH0CCSWU_4ckRkAoy2p73o=' }}"
                                    alt="{{ $employee->name ?? 'Unknown' }}" style="width: 50px; height: 50px;">
                            </td>
                            <td class="px-3  text-dark">{{ $employee->name ?? ($employee->fname . ' ' .
                                $employee->lname) }}</td>
                            <td class="px-3">{{ $attendance && $attendance->check_in ? $attendance->check_in : '--' }}
                            </td>
                            <td class="px-3">
                                @if($attendance && $attendance->break_start)
                                {{ $attendance->break_start ? $attendance->break_start : '--' }} - {{
                                $attendance->break_end ? $attendance->break_end : '--' }}
                                @else
                                --
                                @endif
                            </td>
                            <td class="px-3">{{ $attendance && $attendance->check_out ? $attendance->check_out : '--' }}
                            </td>
                            <td class="px-3">{{ $attendance && $attendance->time_worked ?
                                number_format($attendance->time_worked, 1) . ' hrs' : '--' }}</td>
                            <td class="px-3">{{ $attendance && $attendance->late_hours ?
                                number_format($attendance->late_hours, 1) . ' hrs' : '--' }}</td>
                            <td class="px-3">{{ $attendance && $attendance->over_time ?
                                number_format($attendance->over_time, 1) . ' hrs' : '--' }}</td>
                            <td class="px-3">
                                @php
                                $status = $attendance->status ?? 'absent';
                                $statusClass = match ($status) {
                                'present' => 'bg-success-subtle text-success-emphasis',
                                'late' => 'bg-warning-subtle text-warning-emphasis',
                                'early' => 'bg-info-subtle text-info-emphasis',
                                'leave', 'absent', null => 'bg-danger-subtle text-danger-emphasis',
                                default => 'bg-secondary-subtle text-secondary-emphasis',
                                };
                                @endphp
                                <select
                                    wire:model="statusUpdates.{{ $attendance ? $attendance->attendance_id : 'new_' . $employee->emp_id }}"
                                    wire:change="updateStatus({{ $attendance ? $attendance->attendance_id : 'null' }}, {{ $employee->emp_id }})"
                                    class="form-select form-select-sm fw-semibold border-0 rounded-pill ps-3 {{ $statusClass }}"
                                    style="width: 110px;">
                                    <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                                    <option value="absent">Absent</option>
                                    <option value="present">Present</option>
                                    <option value="late">Late</option>
                                    <option value="early">Early</option>
                                    <option value="leave">Leave</option>
                                </select>
                            </td>
                        </tr>
                        @endif
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted p-4">No employees found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer bg-light">
            <div class="d-flex flex-column flex-md-row align-items-center justify-content-between">
                <span class="text-muted small">
                    Showing {{ $employees->firstItem() }} to {{ $employees->lastItem() }} of {{ $employees->total() }}
                    entries
                </span>
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item" @if($employees->onFirstPage()) aria-disabled="true" @endif>
                            <button wire:click="previousPage" class="page-link" @if($employees->onFirstPage()) disabled
                                @endif>Prev</button>
                        </li>
                        <li class="page-item active" aria-current="page">
                            <span class="page-link">{{ $employees->currentPage() }}</span>
                        </li>
                        <li class="page-item" @if(!$employees->hasMorePages()) aria-disabled="true" @endif>
                            <button wire:click="nextPage" class="page-link" @if(!$employees->hasMorePages()) disabled
                                @endif>Next</button>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>
