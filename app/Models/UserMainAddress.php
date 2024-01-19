<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserMainAddress extends Model
{
    use HasFactory;
    protected $fillable = [ 
        'user_id',
        'address_1',
        'latitude',
        'longitude',
        'address_2',
        'city',
        'postal_code',
        'state',
        'country',
        'place_id'
    ];
}
