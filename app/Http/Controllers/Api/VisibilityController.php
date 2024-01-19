<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Visibility;
use Illuminate\Support\Facades\Lang;

class VisibilityController extends Controller
{
    public function getVisibilities()
    {
        $data = Visibility::where('status', 1)->get();
        $success = $data;
        return sendResponse($success, Lang::get('messages.visibility_list'));
    }
}
