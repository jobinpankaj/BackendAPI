<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDistributor extends Model
{
    use HasFactory;
    protected $table = "order_distributors";
    protected $fillable = [
                            'order_id',
                            'order_item_id',
                            'distributor_id',
                            'status'
                        ];

    public function orders()
    {
        return $this->belongsTo(Order::class,'order_id','id');
    }

    public function distributorInfo()
    {
        return $this->belongsTo(User::class,'distributor_id','id')->with(['userMainAddress','userProfile']);
    }
}
