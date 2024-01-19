<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryTransfer extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sender', 'recipient', 'inventory_id', 'status','others','warehouse_id','recipient_name','recipient_type'
    ];

    public function transferProducts()
    {
        return $this->hasMany(InventoryTransferProduct::class, 'inventory_transfer_id', 'id');
    }
}
