<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'user_id', 
        'service_name', 
        'barber_name', 
        'booking_date', 
        'booking_time', 
        'total_price', 
        'status'
    ];
}