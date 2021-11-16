<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    public $timestamps = false;
    public $incrementing = false;
    use HasFactory;
    protected $fillable = [
        'client_id', 'user_id','bank_id','funnel_id','other','dateadd','timeadd'
    ];

}
