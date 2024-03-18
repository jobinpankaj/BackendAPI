<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockHistory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'stock_id', 'reason', 'datetime', 'created_by', 'quantity', 'new_stock', 'state', 'lot_number', 'lot_date',
    ];
}
