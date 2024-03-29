<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'role_id',
        'fio',
        'group_id',
        'bank_id',
        // 'password'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [

        'remember_token',
        'password'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function roleName()
    {
      return $this->role->name;
    }
    public function role_() {
        return $this->belongsTo('App\Models\Role');
        // return $this->hasOne('App\Models\Role');
    }
    public function role()
{
    return $this->belongsTo('App\Models\Role');
}
public function getRoleName($user)
{
    return $user->role->where('id','=',$user->id)->first()->name;
}

}
