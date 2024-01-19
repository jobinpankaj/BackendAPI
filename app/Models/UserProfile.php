<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ProfilePermit;
use App\Models\BusinessCategory;

class UserProfile extends Model
{
    use HasFactory;
    protected $fillable = [ 
        'user_id',
        'business_name',
        'group_name',
        'business_category_id',
        'contact_email',
        'public_phone_number',
        'phone_number',
        'contact_name',
        'website_url',
        'office_number',
        'opc_status',
        'home_consumption',
        'alcohol_permit',
        'company_name',
        'alcohol_production_permit',
        'alcohol_production_permit_image',
        'business_name_status',
        'distribution_bucket_status',
        'have_product_status',
        'agency_sell_and_collect_status',
        'produce_product_status',
        'status',
        'order_type',
        'alcohol_production_limit',
    ];

    protected function getAlcoholProductionPermitImageAttribute($value)
    {
        if($value){
            return url('/storage/'.$value);
        }
        else{
            return null;
        }
    }

    public function userInfo()
    {
        return $this->belongsTo(User::class, 'user_id', 'id')->select('id', 'first_name', 'last_name');
    }

    public function businessCategory()
    {
        return $this->hasOne(BusinessCategory::class);
    }

    public function userProfilePermits()
    {
        return $this->hasMany(ProfilePermit::class,'user_profile_id','id');
    }
}
