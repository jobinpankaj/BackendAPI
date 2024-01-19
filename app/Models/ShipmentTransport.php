<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipmentTransport extends Model
{
    use HasFactory;
    protected $table = "shipment_transports";

    protected $guarded = [];

    public function orderShipments()
    {
        return $this->hasMany(OrderShipment::class)->with(["orders"])->orderBy("order_position","asc");
    }

    public function orderShipmentsDesc()
    {
        return $this->hasMany(OrderShipment::class)->with(["shipmentOrderItems"])->orderBy("order_position","desc");
    }

    public function orderShipmentWithOrderItems()
    {
        return $this->hasMany(OrderShipment::class)->with("shipmentOrderItems");
    }
}
