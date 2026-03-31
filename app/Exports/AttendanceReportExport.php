<?php

namespace App\Exports;

use App\Models\Attendance;
use App\Models\Salary;
use App\Models\Sale;
use Maatwebsite\Excel\Concerns\FromCollection;

class AttendanceReportExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Attendance::all();
    }
}
