<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pricing extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'product_id', 'price', 'unit_price', 'tax_id', 'tax_amount', 'suggest_retail_price', 'retail_unit_price', 'total_price', 'total_unit_price', 'total_retail_price', 'discount_percent', 'discount_name', 'discount_type', 'purchase_qty', 'is_minimum', 'discount_as_of', 'specific_audience', 'group_id', 'company_id',
    ];

    public function product()
    {
        return $this->hasOne(Product::class, 'id', 'id');
    }
    public function productformat()
    {
        return $this->hasOne(ProductFormat::class, 'id', 'id');
    }
    

}
