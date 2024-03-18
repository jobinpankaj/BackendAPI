<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfilePermit extends Model
{
    use HasFactory;

    protected $fillable = [ 
        'user_profile_id',
        'permit_number',
    ];
}
