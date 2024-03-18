<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserShippingAddress extends Model
{
    use HasFactory;
    protected $fillable = [ 
        'user_id',
        'delivery_time',
        'delivery_notes',
        'contact_name',
        'phone_number',
        'address_1',
        'latitude',
        'longitude',
        'address_2',
        'city',
        'postal_code',
        'state',
        'country',
    ];
}
