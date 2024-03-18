<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use App\Models\UserType;
use App\Models\UserProfile;
use App\Models\UserMainAddress;
use App\Models\UserShippingAddress;
use App\Models\UserBillingAddress;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles,SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone_number',
        'password',
        'user_type_id',
        'added_by',
        'email_verified_at',
        'email_verify_token',
        'city',
        'country',
        'state',
        'address',
        'google2fa_secret',
        'status',
        'permission_revised'
    ];
    protected $dates = ['deleted_at'];
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'google2fa_secret'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $appends = ['full_name'];

    protected function getUserImageAttribute($value)
    {
        if ($value) {
            return url('/storage/' . $value);
        } else {
            return null;
        }
    }

    public function getFullNameAttribute()
    {
        $name = ($this->first_name) ? $this->first_name : '';
        $name = ($this->last_name) ? $name . ' ' . $this->last_name : $name;
        return $name;
    }

    public function userType()
    {
        return $this->belongsTo(UserType::class);
    }

    public function userProfile()
    {
        return $this->hasOne(UserProfile::class, 'user_id', 'id');
    }

    public function userMainAddress()
    {
        return $this->hasOne(UserMainAddress::class, 'user_id', 'id');
    }

    public function userBillingAddress()
    {
        return $this->hasOne(UserBillingAddress::class, 'user_id', 'id');
    }

    public function userShippingAddress()
    {
        return $this->hasOne(UserShippingAddress::class, 'user_id', 'id');
    }

    public function setGoogle2faSecretAttribute($value)
    {
        $this->attributes['google2fa_secret'] = encrypt($value);
    }

    public function getGoogle2faSecretAttribute($value)
    {
        return ($value) ? decrypt($value) : "";
    }

    public function inventory()
    {
        return $this->hasOne(Inventory::class, 'added_by', 'id');
    }

    public function userRoutes()
    {
        return $this->belongsToMany(DwcRoute::class, 'user_routes', 'user_id', 'dwc_route_id');
    }

    public function addedByUser()
    {
        return $this->hasOne(User::class,"id","added_by");
    }
}
