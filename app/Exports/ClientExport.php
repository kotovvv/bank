<?php

namespace App\Exports;

use App\Models\Client;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ClientExport implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Client::select('inn','fullName','phoneNumber','organizationName','address','region','registration','initiator','date_added')->get();
    }

    public function headings(): array
    {
       return [
        'inn','fullName','phoneNumber','organizationName','address','region','registration','initiator'
       ];
    }
}
