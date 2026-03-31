<?php

namespace App\Exports;

use App\Models\Salary;
use App\Models\Sale;
use App\Models\productDetail;
use Maatwebsite\Excel\Concerns\FromCollection;

class InventoryReportExport implements FromCollection
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return productDetail::all();
    }
}
