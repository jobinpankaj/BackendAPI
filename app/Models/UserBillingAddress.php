<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserBillingAddress extends Model
{
    use HasFactory;
    protected $fillable = [ 
        'user_id',
        'address_to',
        'contact_email',
        'phone_number',
        'address_1',
        'latitude',
        'longitude',
        'address_2',
        'city',
        'place_id',
        'postal_code',
        'state',
        'country',
        'company_number_neq',
        'company_name',
        'gst_registration_number',
        'qst_registration_number',
        'order_number_prefix'
    ];

    protected function getUploadLogoAttribute($value)
    {
        if($value){
            return url('/storage/'.$value);
        }
        else{
            return null;
        }
    }

}
