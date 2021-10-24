<?php

namespace App\Exports;

use App\Models\Client;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Debugbar;
use DB;

class ClientExport implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $fields = "'inn','fullName','phoneNumber','organizationName','address','region','registration','initiator','date_added'";
            return Client::select($fields)->get();

    }

    public function headings(): array
    {
       return [
        'inn','fullName','phoneNumber','organizationName','address','region','registration','initiator'
       ];
    }
}
