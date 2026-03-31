<?php

namespace App\Exports;

use App\Models\Salary;
use App\Models\Sale;
use Maatwebsite\Excel\Concerns\FromCollection;

class SalaryReportExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Salary::all();
    }
}
