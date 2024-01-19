<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;
    protected $table = "carts";
    protected $primaryKey = 'row_id';

    protected $guarded = [];

    public function productInfo()
    {
        return $this->belongsTo(Product::class,'product_id','id');
    }
    public function userProfile()
    {
        return $this->belongsTo(UserProfile::class,'user_id','user_id');
    }
    public function setAttributesAttribute($value)
    {
        $this->attributes['attributes'] = json_encode($value);
    }

    public function getAttributesAttribute($value)
    {
        return ($value) ? json_decode($value) : array();
    }

}
