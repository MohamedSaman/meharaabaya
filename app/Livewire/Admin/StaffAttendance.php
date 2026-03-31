<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Storage;
use App\Livewire\Concerns\WithDynamicLayout;

#[Title('Staff Attendance')]
class StaffAttendance extends Component
{
    use WithDynamicLayout;

    use WithPagination;
    use WithFileUploads;

    public $form = [
        'date_filter' => 'today',
        'search' => '',
    ];
    public $statusUpdates = [];
    public $file;
    protected $paginationTheme = 'bootstrap';

    public function updatingForm()
    {
        $this->resetPage();
    }

    public function getEmployeesProperty()
    {
        $query = User::query()
            ->with(['attendances' => function ($q) {
                $dateFilter = $this->form['date_filter'] ?? 'today';
                $today = now()->toDateString();
                $yesterday = now()->subDay()->toDateString();
                switch ($dateFilter) {
                    case 'today':
                        $q->whereDate('date', $today);
                        break;
                    case 'yesterday':
                        $q->whereDate('date', $yesterday);
                        break;
                    case 'this_week':
                        $q->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()]);
                        break;
                    case 'last_week':
                        $q->whereBetween('date', [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()]);
                        break;
                    case 'this_month':
                        $q->whereMonth('date', now()->month)->whereYear('date', now()->year);
                        break;
                    case 'last_month':
                        $q->whereMonth('date', now()->subMonth()->month)->whereYear('date', now()->subMonth()->year);
                        break;
                }
            }]);

        if (!empty($this->form['search'])) {
            $search = $this->form['search'];
            $query->where(function ($q) use ($search) {
                $q->where('fname', 'like', "%$search%")
                  ->orWhere('lname', 'like', "%$search%")
                  ->orWhere('emp_id', 'like', "%$search%")
                  ->orWhere('fingerprint_id', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%")
                  ->orWhere(DB::raw("CONCAT(fname, ' ', lname)"), 'like', "%$search%") ;
            });
        }

        return $query->paginate(10);
    }

    public function updateStatus($attendanceId, $empId)
    {
        $status = $this->statusUpdates[$attendanceId] ?? null;
        if ($attendanceId && $status) {
            $attendance = \App\Models\Attendance::find($attendanceId);
            if ($attendance) {
                $attendance->status = $status;
                $attendance->save();
                session()->flash('message', 'Status updated successfully.');
            } else {
                session()->flash('error', 'Attendance record not found.');
            }
        } else {
            session()->flash('error', 'Invalid status update.');
        }
    }

    public function previousPage()
    {
        $this->setPage(max($this->page - 1, 1));
    }

    public function nextPage()
    {
        $this->setPage($this->page + 1);
    }

    public function import()
    {
        $this->validate([
            'file' => 'required|file|mimes:xls,xlsx,csv',
        ]);

        $extension = $this->file->getClientOriginalExtension();
        $path = $this->file->store('attendance_imports');
        $filePath = storage_path('app/' . $path);

        $rows = [];
        if (in_array($extension, ['xls', 'xlsx'])) {
            $excelData = Excel::toArray([], $filePath)[0];
            $header = $excelData[0];
            foreach (array_slice($excelData, 1) as $dataRow) {
                $rows[] = array_combine($header, $dataRow);
            }
        } elseif ($extension === 'csv') {
            if (($handle = fopen($filePath, 'r')) !== false) {
                $header = null;
                while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                    if (!$header) {
                        $header = $data;
                    } else {
                        $rows[] = array_combine($header, $data);
                    }
                }
                fclose($handle);
            }
        }

        $importedIds = [];
        $date = null;
        foreach ($rows as $row) {
            $fingerprintId = trim($row['Person ID'], "'");
            $date = is_numeric($row['Date'])
                ? excelSerialToDate($row['Date'])
                : (\DateTime::createFromFormat('n/j/Y', $row['Date']) ? \DateTime::createFromFormat('n/j/Y', $row['Date'])->format('Y-m-d') : null);
            $userDetail = \App\Models\UserDetail::where('fingerprint_id', $fingerprintId)->first();
            $user = $userDetail ? \App\Models\User::find($userDetail->user_id) : null;

            if ($user && $date) {
                \App\Models\Attendance::updateOrCreate(
                    ['user_id' => $user->id, 'date' => $date],
                    [
                        'check_in' => $row['Check-In'] !== '-' ? excelTimeToString($row['Check-In']) : null,
                        'check_out' => $row['Check-out'] !== '-' ? excelTimeToString($row['Check-out']) : null,
                        'time_worked' => parseMinutes($row['Worked']),
                        'late_hours' => parseMinutes($row['Late']),
                        'over_time' => parseMinutes($row['OT1']),
                        'status' => strtolower($row['Attendance Status']),
                        'present_status' => $row['Attended'] !== '0 min' ? 'present' : 'absent',
                    ]
                );
                $importedIds[] = $user->id;
            }
        }

        // Mark users absent who are not in the imported sheet for that date
        if ($date) {
            $allUserIds = \App\Models\User::pluck('id')->toArray();
            $absentIds = array_diff($allUserIds, $importedIds);
            foreach ($absentIds as $userId) {
                \App\Models\Attendance::updateOrCreate(
                    ['user_id' => $userId, 'date' => $date],
                    [
                        'status' => 'absent',
                        'present_status' => 'absent',
                    ]
                );
            }
        }

        session()->flash('message', 'Attendance imported successfully!');
    }

    public function render()
    {
        return view('livewire.admin.staff-attendance', [
            'form' => $this->form,
            'employees' => $this->employees,
        ])->layout($this->layout);
    }
  
}

function parseMinutes($value) {
    // Converts "540 min" or "0 min" to integer 540 or 0
    return is_numeric($value) ? (int)$value : (int)preg_replace('/[^0-9]/', '', $value);
}

function excelSerialToDate($serial) {
    $unix = ($serial - 25569) * 86400;
    return gmdate('Y-m-d', $unix);
}

function excelTimeToString($value) {
    if (is_numeric($value)) {
        $seconds = $value * 86400;
        return gmdate('H:i', $seconds);
    }
    return $value;
}
