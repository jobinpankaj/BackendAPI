<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StockHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;

class StockHistoryController extends Controller
{
    public $permisssion;

    public function __construct()
    {
        $headers = getallheaders();
        
        $this->permisssion = isset($headers['permission']) ? $headers['permission'] : "";
    }

    public function getStockHistory($id)
    {
        if($this->permisssion !== "inventory-view")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }

        $stockHistory = StockHistory::where('stock_id', $id)->orderBy('id', 'desc')->get();

        $success = $stockHistory;
        $message = Lang::get("messages.stock_history_fetched_successfully");
        return sendResponse($success, $message);
    }
}
