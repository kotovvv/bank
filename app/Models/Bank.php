<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    public $timestamps = false;
    use HasFactory;
    protected $fillable = [
        'id','name','abr'
    ];
}
