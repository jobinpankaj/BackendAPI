<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'added_by', 'supplier_id', 'distributor_id', 'batch', 'product_id', 'quantity', 'warehouse_id', 'aisle', 'aisle_name', 'shelf', 'shelf_name', 'is_visible',
    ];

    protected $appends = [
        'stock_info'
    ];

    public function distributor()
    {
        return $this->belongsTo(User::class, 'distributor_id', 'id');
    }
    public function UserProfile()
    {
        return $this->belongsTO(UserProfile::class,'distributor_id','id');
    }
    public function supplierInfo()
    {
        return $this->belongsTo(User::class, 'supplier_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id')->with(['availability']);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id', 'id');
    }

    public function stocks()
    {
        return $this->hasMany(Stock::class, 'inventory_id', 'id');
    }

    public function getStockInfoAttribute()
    {
        $stockAtWarehouse = $stockDistributorWarehouse = $stockInTransit = $stockDelivery = 0;
        
        foreach ($this->stocks as $itemStock) {

            $stockAtWarehouse += $itemStock->at_warehouse;
            $stockDistributorWarehouse += $this->distributor->inventory->stocks->sum('at_warehouse');
            $stockInTransit += $itemStock->in_transit;
            $stockDelivery += $itemStock->delivery;
        }
        return [
            'at_warehouse' => $stockAtWarehouse,
            'distributor_warehouse' => $stockDistributorWarehouse,
            'in_transit' => $stockInTransit,
            'delivery' => $stockDelivery
        ];
    }
}
