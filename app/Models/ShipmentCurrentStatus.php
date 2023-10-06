<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipmentCurrentStatus extends Model
{
    use HasFactory;

    protected $fillable = [ 
        'user_id',          
        'tracking_number',
        'status',
        'schedule_delivery_date',  
        'delivery_date',
        'create_datetime',     
    ];
}
