<?php

namespace App\Exports;

use App\BhadsStatus;
use Maatwebsite\Excel\Concerns\FromCollection;

class StatusExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return BhadsStatus::where("imageStatus",false)->get();
    }
}
