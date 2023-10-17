<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Client extends Model
{
    use HasFactory, HasEvents;


    protected $fillable = [
        'name', 'email', 'password','client_id','client_secret'
    ];
    protected $hidden = [
        'password', 'remember_token',
    ];






}
