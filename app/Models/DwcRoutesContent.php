<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DwcRoutesContent extends Model
{
    use HasFactory;

    protected $table = "dwc_routes_contents";

    protected $fillable = ["dwc_route_id","description","message","site_language_id"];
}
