<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RetailerSupplierRequest extends Model
{
    use HasFactory;
    protected $table = 'retailer_supplier_requests';

    protected $fillable = [ 
        'retailer_id',
        'supplier_id',
        'request_note',
    ];

    public function supplierInformation()
    {
        return $this->belongsTo(User::class,'supplier_id','id')->with('userMainAddress');
    }

    public function retailerInformation()
    {
        return $this->belongsTo(User::class,'retailer_id','id');
    }

}
