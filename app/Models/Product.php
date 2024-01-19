<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'products';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'product_name', 'product_type', 'style', 'sub_category', 'sap_lowbla', 'sap_metro', 'sap_showbay', 'product_format', 'is_organic', 'alcohol_percentage', 'product_image', 'label_image', 'combined_image','barcode_image' , 'user_id', 'status',
    ];

    protected $appends = ['barcode_image_url'];

    public function getProductImageAttribute($value)
    {
        if($value) {
            return url('/storage/'.$value);
        }
        else {
            return null;
        }
    }

    public function getLabelImageAttribute($value)
    {
        if($value) {
            return url('/storage/'.$value);
        }
        else {
            return null;
        }
    }

    public function getCombinedImageAttribute($value)
    {
        if($value) {
            return url('/storage/'.$value);
        }
        else {
            return null;
        }
    }

    public function getBarcodeImageUrlAttribute()
    {
        if($this->barcode_image) {
            return url('/storage/'.$this->barcode_image);
        }
        else {
            return null;
        }
    }

    // Get Description associated with the product
    public function description()
    {
        return $this->hasMany(ProductDescription::class, 'product_id', 'id');
    }

    // Get Product Format
    public function productFormat()
    {
        return $this->belongsTo(ProductFormat::class, 'product_format', 'id');
    }

    // Get Product Style
    public function productStyle()
    {
        return $this->belongsTo(ProductStyle::class, 'style', 'id');
    }

    // Get Product Category
    public function productCategory()
    {
        return $this->belongsTo(SubCategory::class, 'sub_category', 'id');
    }

    //GetProductFormatDeposit
    // public function productFormatDeposit()
    // {
    //     return $this->belongsTo(ProductFormatDeposit::class,'product_format_id','product_format_id');
    // }
    // Add User Information
    public function userInformation()
    {
        return $this->belongsTo(User::class,'user_id','id')->select("id","first_name","last_name");
    }
    public function userProfile()
    {
        return $this->belongsTo(UserProfile::class,'user_id','user_id');
    }
    public function pricing()
    {
        return $this->hasOne(Pricing::class, 'product_id', 'id');
    }

    public function inventory()
    {
        // return $this->belongsTo(Inventory::class, 'id', 'product_id')->where('added_by', auth()->user()->id);
        // return $this->belongsTo(Inventory::class, 'id', 'product_id');
        return $this->hasMany(Inventory::class, 'product_id', 'id');
    }

    public function availability()
    {
        return $this->belongsTo(Availability::class,"id",'product_id')->with(['visibityInformation']);
    }
}
