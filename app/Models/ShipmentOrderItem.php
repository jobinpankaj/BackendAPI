<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipmentOrderItem extends Model
{
    use HasFactory;
    protected $table = "shipment_order_items";

    protected $guarded = [];

    public function orderShipments()
    {
        return $this->belongsToMany(OrderShipment::class,"id","order_shipment_id");
    }

    public function orderItems()
    {
        return $this->belongsTo(OrderItem::class,"order_item_id","id")->with(['product',"order"]);
    }

}
