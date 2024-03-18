<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderHistory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_id', 'user_id', 'shipment_id', 'content', 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id', 'user_id');
    }
}
