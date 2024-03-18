<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderShipment extends Model
{
    use HasFactory;
    protected $table = "order_shipments";

    protected $guarded = [];

    public function shipmentInformation()
    {
        return $this->belongsTo(Shipment::class,"shipment_id","id")->with("routeDetail");
    }

    public function shipmentOrderItems()
    {
        return $this->hasMany(ShipmentOrderItem::class)->with("orderItems");
    }

    public function orders()
    {
        return $this->hasOne(Order::class,"id","order_id")->with(['items','supplierInformation','retailerInformation']);
    }

    public function shipmentTransportInformation()
    {
        return $this->hasOne(ShipmentTransport::class,"id","shipment_transport_id");
    }
}
