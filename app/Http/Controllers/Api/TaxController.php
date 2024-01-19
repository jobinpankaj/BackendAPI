<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tax;
use Illuminate\Support\Facades\Lang;

class TaxController extends Controller
{
    public function getTaxes()
    {
        $data = Tax::where('status', 1)->get();
        $success = $data;
        return sendResponse($success, Lang::get('messages.tax_list'));
    }
}
