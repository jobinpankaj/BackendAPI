<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderManagementFileDetailName extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'order_management_file_detail_name';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'order_id', 'file_path'
    ];   

    public function getFilePathAttribute($value)
    {
        if($value) {

           return $fileUrl = url('storage/' . $value);
        }
        else {
            return null;
        }
    }
   
}
