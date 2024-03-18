<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    use HasFactory;

    protected $table = "shipments";

    protected $fillable = [
                            "shipment_number",
                            "description",
                            "user_id",
                            "route_id",
                            "delivery_date",
                            "delivery_document",
                            "pick_up_document",
                            "status"
                            ];
    protected $appends = ["statusTitle"];


    public function getStatusTitleAttribute()
    {
        $statusArray = ["4"=>"Draft","3"=>"Preparation","2"=>"Shipping","1"=>"Done","0"=>"Return's Management"];
        return $statusArray[$this->status];
    }

    public function routeDetail()
    {
        return $this->belongsTo(DwcRoute::class,'route_id','id');
    }

    // public function orderShipments()
    // {
    //     return $this->belongsToMany(Order::class, 'order_shipments' ,'shipment_id','order_id')->with(['items','supplierInformation','retailerInformation'])->orderBy("order_position","asc");
    // }

    public function orderShipments()
    {
        return $this->hasMany(OrderShipment::class)->with('orders')->orderBy("order_position","asc");
    }

    public function shipmentTransports()
    {
        return $this->hasMany(ShipmentTransport::class);
    }

}
