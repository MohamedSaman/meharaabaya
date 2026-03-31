<?php

namespace App\Exports;

use App\Models\Payment;
use App\Models\Salary;
use App\Models\Sale;
use Maatwebsite\Excel\Concerns\FromCollection;

class PaymentsReportExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Payment::all();
    }
}
