<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'added_by', 'name', 'color', 'order_confirm_msg', 'order_confirm_msg_lang', 'order_default_note', 'order_default_note_lang', 'is_min_order_count', 'min_items', 'min_kegs', 'is_min_order_value', 'min_price', 'tax_applicability', 'bill_deposits', 'order_approval', 'online_payment','offline_payment','is_accepted_payment',
    ];

    public function retailers()
    {
        return $this->hasManyThrough(User::class, GroupRetailer::class, 'group_id', 'id', 'id', 'retailer_id');
    }
}
