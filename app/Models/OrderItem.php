<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_id', 'order_supplier_id', 'product_style_id', 'product_format_id', 'product_id', 'price', 'quantity', 'tax', 'sub_total', 'status',
    ];

    public function getStatusAttribute($value)
    {
        switch($value) {
            case '0':
                return 'Pending';
            case '1':
                return 'Approved';
            case '2':
                return 'On Hold';
            case '3':
                return 'Shipped';
            case '4':
                return 'Delivered';
            case '5':
                return 'Cancelled';
        }
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id')->with(['description','productFormat','productStyle','productCategory','pricing','inventory']);
    }

    public function order()
    {
        return $this->belongsTo(Order::class,"order_id","id")->with(["retailerInformation","supplierInformation"]);
    }
}
