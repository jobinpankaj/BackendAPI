<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Validator;

class WarehouseController extends Controller
{
    public $permisssion;

    public function __construct()
    {
        $headers = getallheaders();
        
        $this->permisssion = isset($headers['permission']) ? $headers['permission'] : "";
    }

    public function warehousesList()
    {
        if($this->permisssion !== "inventory-view")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }
        
        $user = auth()->user();
        $warehouses = Warehouse::where('user_id', $user->id)->get();

        $success  = $warehouses;
        $message  = Lang::get("messages.warehouses_list");
        return sendResponse($success, $message);
    }

    public function getWarehouse($id)
    {
        if($this->permisssion !== "inventory-view")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }

        $user = auth()->user();
        $warehouse = Warehouse::where('user_id', $user->id)->find($id);

        if(!$warehouse) {
            return sendError(Lang::get('messages.warehouse_not_found'), Lang::get('messages.warehouse_not_found'), 404);
        }

        $success = $warehouse;
        $message = Lang::get("messages.warehouse_detail");
        return sendResponse($success, $message);
    }

    public function addWarehouse(Request $request)
    {
        if($this->permisssion !== "inventory-edit")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:200',
            'address' => 'required|string|max:1000',
            'aisles' => 'required|numeric',
            'shelves' => 'required|numeric'
        ]);
        
        if($validator->fails()) {
            return sendError(Lang::get('validation_error'), $validator->errors(), 422);
        }

        $validated = $request->all();

        $user = auth()->user();
        $warehouse = Warehouse::create([
            'name' => $validated['name'],
            'address' => $validated['address'],
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'aisles' => $validated['aisles'],
            'shelves' => $validated['shelves'],
            'user_id' => $user->id,
        ]);

        $success = $warehouse;
        $message = Lang::get("messages.warehouse_created_successfully");
        return sendResponse($success, $message);
    }

    public function updateWarehouse(Request $request,$id)
    {
        if($this->permisssion !== "inventory-edit")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }

        $validator = Validator::make($request->all(), [
            // 'id' => 'required|numeric',
            
        ]);
        
        if($validator->fails()) {
            return sendError(Lang::get('validation_error'), $validator->errors(), 422);
        }

        $validated = $request->all();

        $user = auth()->user();
        $warehouse = Warehouse::find($id);
        if(!$warehouse) {
            return sendError(Lang::get('messages.warehous_not_found'), Lang::get('messages.warehouse_not_found'), 404);
        }
        //update warehouse
        $warehouse->name = $validated['name'];
        $warehouse->address = $validated['address'];
        $warehouse->latitude = $validated['latitude'];
        $warehouse->longitude = $validated['longitude'];
        $warehouse->aisles = $validated['aisles'];
        $warehouse->shelves = $validated['shelves'];
        $warehouse->user_id = $user->id;
        $warehouse->save();
        $success = $warehouse;
        $message = Lang::get("messages.warehouse_updated_successfully");
        return sendResponse($success, $message);
    }
}
