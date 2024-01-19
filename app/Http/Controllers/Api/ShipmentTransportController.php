<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;
use App\Models\ShipmentTransport;
use App\Models\Shipment;
use App\Models\OrderShipment;
use Auth;
use Lang;

class ShipmentTransportController extends Controller
{
    public $permisssion;

    public function __construct()
    {
        $headers = getallheaders();
        
        $this->permisssion = isset($headers['permission']) ? $headers['permission'] : "";
    }

    public function list(Request $request,$id)
    {
        if($this->permisssion !== "shipment-edit")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }
        $shipment = Shipment::find($id); 
        $user = auth()->user();
        if(!$shipment) {
            return sendError(Lang::get('messages.shipment_not_found'), Lang::get('messages.shipment_not_found'), 404);
        }

        $shipmentTransportListing = ShipmentTransport::with(['orderShipments'])->where(["shipment_id"=>$shipment->id])->get();

        $success = $shipmentTransportListing;
        $message = Lang::get("messages.shipment_transport_listing");
        return sendResponse($success, $message);
    }

    public function create(Request $request)
    {
        if($this->permisssion !== "shipment-edit")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }

        $validator = Validator::make($request->all(), [
            'shipment_id' => 'required',
        ]);
        
        if($validator->fails()) {
            return sendError(Lang::get('validation_error'), $validator->errors(), 422);
        }
        $shipment_id = $request->input("shipment_id");
        $shipment = Shipment::find($shipment_id); 
        $user = auth()->user();
        if(!$shipment) {
            return sendError(Lang::get('messages.shipment_not_found'), Lang::get('messages.shipment_not_found'), 404);
        }

        $shipmentTransportData = ShipmentTransport::create(["shipment_id"=>$shipment->id,"position"=>1,"added_by"=>$user->id]);
        $shipmentTransportListing = ShipmentTransport::with(['orderShipments'])->where(["shipment_id"=>$shipment->id])->get();

        $success = $shipmentTransportListing;
        $message = Lang::get("messages.shipment_transport_created_successfully");
        return sendResponse($success, $message);
    }

    public function update(Request $request)
    {
        if($this->permisssion !== "shipment-edit")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }

        $validator = Validator::make($request->all(), [
            'shipment_transport_id' => 'required',
            'name' => "required|string|max:200"
        ]);
        
        if($validator->fails()) {
            return sendError(Lang::get('validation_error'), $validator->errors(), 422);
        }
        $shipment_transport_id = $request->input("shipment_transport_id");
        $shipmentTransportData = ShipmentTransport::find($shipment_transport_id);
        if(!$shipmentTransportData) {
            return sendError(Lang::get('messages.shipment_transport_not_found'), Lang::get('messages.shipment_transport_not_found'), 404);
        }

        $shipmentTransportData->name = $request->input("name");
        $shipmentTransportData->save();
        $shipmentTransportListing = ShipmentTransport::with(['orderShipments'])->where(["shipment_id"=>$shipmentTransportData->shipment_id])->get();

        $success = $shipmentTransportListing;
        $message = Lang::get("messages.shipment_transport_updated_successfully");
        return sendResponse($success, $message);
    }

    public function remove(Request $request)
    {
        if($this->permisssion !== "shipment-edit")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }

        $validator = Validator::make($request->all(), [
            'shipment_transport_id' => 'required',
            'shipment_id' => 'required'
        ]);
        
        if($validator->fails()) {
            return sendError(Lang::get('validation_error'), $validator->errors(), 422);
        }
        $shipment_id = $request->input("shipment_id");
        $shipment_transport_id = $request->input("shipment_transport_id");
        $shipmentTransportData = ShipmentTransport::find($shipment_transport_id);
        if(!$shipmentTransportData) {
            return sendError(Lang::get('messages.shipment_transport_not_found'), Lang::get('messages.shipment_transport_not_found'), 404);
        }
        $shipmentTransportData->delete();
        $shipmentTransportOldestData = ShipmentTransport::where("shipment_id",$shipment_id)->oldest()->first();
        OrderShipment::where("shipment_transport_id",$shipment_transport_id)->update(["shipment_transport_id"=>$shipmentTransportOldestData->id]);
        $shipmentTransportListing = ShipmentTransport::with(['orderShipments'])->where(["shipment_id"=>$shipment_id])->get();

        $success = $shipmentTransportListing;
        $message = Lang::get("messages.transport_deleted_successfully");
        return sendResponse($success, $message);
    }


    public function updateShipmentOrderPosition(Request $request)
    {
        if($this->permisssion !== "shipment-edit")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }
        $shipment_id = $request->input("shipment_id");
        $shipment_transports = $request->input("shipment_transports");
        // $orders = $request->input("orders");

        foreach($shipment_transports as $k=>$v){
            $transport_id = $v["transport_id"];
            $orders = $v["orders"];
            foreach($orders as $order)
            {
                $shipmentTransportData = OrderShipment::where(["order_id"=>$order["order_id"],"shipment_id" => $shipment_id])->whereHas("shipmentInformation",function($query){
                        $query->whereIn("status",["4","3"]);
                    })->first();
                if($shipmentTransportData !== null)
                {
                    $shipmentTransportData->shipment_transport_id = $transport_id;
                    $shipmentTransportData->order_position = $order["position_id"];
                    $shipmentTransportData->save();
                }
            }
        }
        $shipmentTransportListing = ShipmentTransport::with("orderShipments")->where(["shipment_id"=>$shipment_id])->get();

        $success = $shipmentTransportListing;
        $message = Lang::get("messages.transport_position_updated_successfully");
        return sendResponse($success, $message);
    }
    public function routeUpdate(Request $request)
    {
        if($this->permisssion !== "shipment-edit")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }

        $validator = Validator::make($request->all(), [
            'shipment_id' => 'required',
            'shipment_transport_id' => "required"
        ]);
        
        if($validator->fails()) {
            return sendError(Lang::get('validation_error'), $validator->errors(), 422);
        }
        $shipment_id = $request->input("shipment_id");
        $shipmentTransportData = OrderShipment::where('shipment_id',$request->shipment_id)->update([
            'shipment_transport_id' => $request->shipment_transport_id,
        ]);
        // dd($shipmentTransportData);
        if(!$shipmentTransportData) {
            return sendError(Lang::get('messages.shipment_transport_not_found'), Lang::get('messages.shipment_transport_not_found'), 404);
        }

        // $shipmentTransportData->name = $request->input("name");
        // $shipmentTransportData->save();
        $shipmentTransportListing = Shipment::with(['routeDetail','orderShipments','shipmentTransports'])->where('user_id',auth()->user()->id)->where('id',$request->shipment_id)->get();


        $success = $shipmentTransportListing;
        $message = Lang::get("messages.shipment_transport_updated_successfully");
        return sendResponse($success, $message);
    }

}
