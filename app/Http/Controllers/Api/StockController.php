<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Stock;
use App\Models\StockHistory;
use App\Models\Inventory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Validator;

class StockController extends Controller
{
    public $permisssion;

    public function __construct()
    {
        $headers = getallheaders();
        
        $this->permisssion = isset($headers['permission']) ? $headers['permission'] : "";
    }

    public function stockList()
    {
        return response()->json(['message' => 'Please connect with developer.']);
    }

    public function getStock($id)
    {
        return response()->json(['message' => 'Please connect with developer.']);
    }

    public function addStock(Request $request)
    {
        if($this->permisssion !== "inventory-edit")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }

        $validator = Validator::make($request->all(), [
            'inventory_id' => 'required|numeric|exists:inventories,id',
            'upc' => 'nullable|string',
            'sku' => 'nullable|string',
            'note' => 'nullable|string',
            'in_transit' => 'nullable|numeric',
            'at_warehouse' => 'nullable|numeric',
            'delivery' => 'nullable|numeric'
        ]);
        
        if($validator->fails()) {
            return sendError(Lang::get('validation_error'), $validator->errors(), 422);
        }

        $validated = $request->all();

        $stock = Stock::create([
            'inventory_id' => $validated['inventory_id'],
            'upc' => $validated['upc'] ?? null,
            'sku' => $validated['sku'] ?? null,
            'note' => $validated['note'] ?? null,
            'in_transit' => $validated['in_transit'] ?? 0,
            'at_warehouse' => $validated['at_warehouse'] ?? 0,
            'delivery' => $validated['delivery'] ?? 0,
        ]);

        StockHistory::create([
            'stock_id' => $stock->id,
            'reason' => 'Stock Added',
            'datetime' => Carbon::now(),
            'created_by' => auth()->user()->id,
            'new_stock' => $validated['at_warehouse'] ?? 0
        ]);

        $success = $stock;
        $message = Lang::get("messages.stock_created_successfully");
        return sendResponse($success, $message);
    }

    public function updateStock(Request $request, $id)
    {
        if($this->permisssion !== "inventory-edit")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }

        $validator = Validator::make($request->all(), [
            'inventory_id' => 'required|numeric|exists:inventories,id',
            'upc' => 'nullable|string',
            'sku' => 'nullable|string',
            'note' => 'nullable|string',
            'in_transit' => 'nullable|numeric',
            'at_warehouse' => 'nullable|numeric',
            'delivery' => 'nullable|numeric'
        ]);

        if($validator->fails()) {
            return sendError(Lang::get('validation_error'), $validator->errors(), 422);
        }
        
        $validated = $request->all();

        $stock = Stock::where('inventory_id', $validated['inventory_id'])->find($id);

        if(!$stock) {
            return sendError(Lang::get('messages.stock_not_found'), Lang::get('messages.stock_not_found'), 404);
        }
        
        // Update Stock
        $stock->upc = $validated['upc'];
        $stock->sku = $validated['sku'];
        $stock->note = $validated['note'];
        $stock->in_transit = $validated['in_transit'];
        $stock->at_warehouse = $validated['at_warehouse'];
        $stock->delivery = $validated['delivery'];
        
        $stock->save();

        $success = $stock;
        $message = Lang::get("messages.stock_updated_successfully");
        return sendResponse($success, $message);
    }

    public function updateInventoryStock(Request $request)
    {
        if($this->permisssion !== "inventory-edit")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }

        $validator = Validator::make($request->all(), [
            'product_id' => 'required|numeric|exists:inventories,product_id',
            'batch' => 'required|exists:inventories,batch',
            'reason' => 'required',
            'quantity'=> 'required|numeric',
        ]);

        if($validator->fails()) {
            return sendError(Lang::get('validation_error'), $validator->errors(), 422);
        }
        
        $user = auth()->user();
        $validated = $request->all();

        $inventory = Inventory::where('id', $validated['inventory_id'])->where(["product_id"=>$validated["product_id"],"batch" => $validated["batch"],"added_by" => $user->id])->first();

        if(!$inventory) {
            return sendError(Lang::get('messages.stock_not_found'), Lang::get('messages.stock_not_found'), 404);
        }
        
        $inventory->quantity += $validated["quantity"];
        $inventory->save();

        StockHistory::create([
            'stock_id' => $inventory->id,
            'reason' => $validated["reason"],
            'datetime' => Carbon::now(),
            'created_by' => auth()->user()->id,
            'new_stock' => $inventory->quantity ?? 0
        ]);

        $success = $inventory;
        $message = Lang::get("messages.stock_updated_successfully");
        return sendResponse($success, $message);
    }
}
