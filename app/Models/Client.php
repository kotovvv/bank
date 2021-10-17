<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;
    protected $fillable = [
        'inn','fullName','phoneNumber','organizationName','address','region','registration','initiator','bank_id','operator_id','funnel_id','date_added'
    ];
}
