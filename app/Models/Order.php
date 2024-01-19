<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_reference', 'added_by', 'added_by_user_type', 'note', 'total_quantity', 'total_amount', 'status', 'order_date','supplier_id','retailer_id','parent_id', 'delivered_on',
    ];

    protected $appends = ['order_date'];

    public function getOrderDateAttribute()
    {
        return Carbon::parse($this->created_at)->format('d-m-Y');
    }

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

    public function retailerInformation()
    {
        return $this->belongsTo(User::class,'retailer_id','id')->with(['userMainAddress','userProfile','userRoutes']);

    }

    public function supplierInformation()
    {
        return $this->belongsTo(User::class,'supplier_id','id')->with(['userMainAddress','userProfile']);

    }

    // public function distributor()
    // {
    //     return $this->belongsTo(User::class, 'id', 'distributor_id');
    // }

    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'id')->with('product');
    }

    // order and items middle table relation between supplier/retailer/distributor
    // public function orderSuppliers()
    // {
    //     return $this->hasMany(OrderSupplier::class, 'order_id', 'id')->with(['retailerInformation','supplierInformation','distributorInformation','orderSupplierRoutes','orderSupplierShipments']);
    // }

    // order and items middle table relation between supplier/retailer/distributor
    public function orderDistributors()
    {
        return $this->hasMany(OrderDistributor::class, 'order_id', 'id')->with(['distributorInfo']);
    }

    public function history()
    {
        return $this->hasMany(OrderHistory::class, 'order_id', 'id')->orderBy('id', 'desc');
    }

    public function orderRoutes()
    {
        return $this->belongsToMany(DwcRoute::class, 'shipment_order_suppliers', 'order_id', 'route_id');
    }

    public function orderShipments()
    {
        return $this->hasOne(OrderShipment::class,'order_id','id')->with(['shipmentInformation']);
    }
}
