<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipmentProgress extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'shipper_id',
        'tracking_number',
        'status',
        'schedule_delivery_date',
        'delivery_date',
        'create_datetime',
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function shipper()
    {
        return $this->hasOne(Shipper::class, 'id', 'shipper_id');
    }
}
