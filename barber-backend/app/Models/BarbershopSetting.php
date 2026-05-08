<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BarbershopSetting extends Model
{
    protected $fillable = [
        'is_open',
        'shop_name',
        'address',
    ];

    protected $casts = [
        'is_open' => 'boolean',
    ];

}
