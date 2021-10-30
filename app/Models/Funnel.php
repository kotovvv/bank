<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Funnel extends Model
{
    public $timestamps = false;
    use HasFactory;
    protected $fillable = [
        'id','name','order','color'
    ];
}
