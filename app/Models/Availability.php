<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Availability extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'added_by', 'product_id', 'visibility_id','is_limited','company_id','group_id',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id', 'id')->select('id','name');
    }

    public function availabilityGroup()
    {
        return $this->hasMany(AvailabilityGroup::class,'availability_id', 'id')->select('availability_id','group_id');
    }
     
    public function allocations()
    {
        return $this->hasMany(AvailabilityGroupAllocation::class, 'availability_id', 'id');
    }

    public function maximums()
    {
        return $this->hasMany(AvailabilityGroupMaximum::class, 'availability_id', 'id');
    }

    public function visibityInformation()
    {
        return $this->hasOne(Visibility::class,"id","visibility_id");
    }
    public function inventory()
    {
        return $this->hasone(Inventory::class,'product_id','product_id');
    }
    public function availabilitycompany()
    {
        // return $this->hasMany(AvailabilityCompany::class, 'availability_id', 'id');
        return $this->hasManyThrough(User::class, AvailabilityCompany::class, 'availability_id', 'id', 'id', 'company_id');

    }
}
