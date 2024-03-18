<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\DwcRoutesContent;

class DwcRoute extends Model
{
    use HasFactory;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dwc_routes';

    protected $fillable = [
                'name',
                'colour',
                'start_address',
                'start_latitude',
                'start_longitude',
                'end_address',
                'end_latitude',
                'end_longitude',
                'driver_name',
                'truck_details',
                'minimun_number_of_items',
                'user_id',
                'status',
                ];

    public function dwcRouteContentData()
    {
        return $this->hasMany(DwcRoutesContent::class,'dwc_route_id','id');
    }

    public function routeUsers()
    {
        return $this->belongsToMany(User::class, 'user_routes', 'dwc_route_id', 'user_id')->with('userMainAddress')->whereHas('userMainAddress',function($query){
            $query->whereNotNull('latitude');
            $query->whereNotNull('longitude');
        });
    }

    public function userInformation()
    {
        return $this->hasOne(User::class,'id','user_id');
    }

}
