<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DistributorGroup extends Model
{
    protected $table = 'distributor_group';

    protected $fillable = [
        'distributor_name',
        'distributor_email',
        'supplier_id',
    ];

    // You can define relationships, custom methods, and other functionality here
}
