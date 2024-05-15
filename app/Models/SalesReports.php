<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesReports extends Model
{
    use HasFactory;
    protected $table = 'sales_reports';
    protected $fillable = ["created_at","filename","file_path","user_id"];
}
