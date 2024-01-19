<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;
use App\Models\User;
use App\Models\Shipment;
use App\Models\ShipmentTransport;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Inventory;
use App\Models\ShipmentOrderItem;
use App\Models\StockHistory;
use App\Models\OrderHistory;
use Auth;
use Lang;
use Carbon\Carbon;

class ShipmentController extends Controller
{
    public $permisssion;

    public function __construct()
    {
        $headers = getallheaders();
        
        $this->permisssion = isset($headers['permission']) ? $headers['permission'] : "";
    }

    public function shipmentListing(Request $request)
    {
        if($this->permisssion !== "shipment-view")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }
        
        $user = auth()->user();
        $search = $request->input("search");
        $filter_status = $request->input("filter_status");
        $shipmentQuery = Shipment::query();
        if(!empty($search)) {
            $shipmentQuery->where(function($query)use($search){
                $query->where("id","LIKE","%".$search."%");
                $query = $query->orWhereHas('routeDetail',function($query1) use($search){
                    $query1->where("name",'LIKE',"%".$search."%");              
                });
            });
        }
        if(!empty($filter_status)) {
            $shipmentQuery->where(function($query)use($filter_status){
                $query->where("status",$filter_status);
            });
        }
        $data = $shipmentQuery->where('user_id', $user->id)->withCount('orderShipments')->orderBy('id', 'DESC')->get();

        $success  = $data;
        $message  = Lang::get("messages.shipment_list");
        return sendResponse($success, $message);
    }

    public function addShipment(Request $request)
    {
        if($this->permisssion !== "shipment-edit")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }

        $validator = Validator::make($request->all(), [
            'route_id' => 'required',
        ]);
        
        if($validator->fails()) {
            return sendError(Lang::get('validation_error'), $validator->errors(), 422);
        }

        $validated = $request->all();

        $user = auth()->user();
        $shipment = Shipment::create([
            'route_id' => $validated['route_id'],
            'user_id' => $user->id,
            'status' => "4",
        ]);
        
        $shipmentTransportData = ShipmentTransport::create(["shipment_id"=>$shipment->id,"position"=>1,"added_by"=>$user->id]);

        $success = $shipment;
        $message = Lang::get("messages.shipment_created_successfully");
        return sendResponse($success, $message);
    }

    public function getShipment($id)
    {
        if($this->permisssion !== "shipment-view")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }

        $user = auth()->user();
        $shipment = Shipment::with(['routeDetail','orderShipments','shipmentTransports'])->where('user_id',$user->id)->find($id);

        if(!$shipment) {
            return sendError(Lang::get('messages.shipment_not_found'), Lang::get('messages.shipment_not_found'), 404);
        }

        $success = $shipment;
        $message = Lang::get("messages.shipment_detail");
        return sendResponse($success, $message);
    }
/*
    public function updateShipment(Request $request,$id)
    {
        if($this->permisssion !== "shipment-edit")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }

        $user = auth()->user();
        $shipment = Shipment::where('user_id', $user->id)->find($id);

        if(!$shipment) {
            return sendError(Lang::get('messages.shipment_not_found'), Lang::get('messages.shipment_not_found'), 404);
        }

        $validator = Validator::make($request->all(), [
            'shipment_number' => 'required|digits:10|unique:shipments,id,'.$id,
            'route_id' => 'required',
            'delivery_date' => 'required',
            'description' => 'nullable|max:500',
        ]);

        if($validator->fails()) {
            return sendError(Lang::get('validation_error'), $validator->errors(), 422);
        }

        $validated = $request->all();
        
        // Update Product
        $shipment->shipment_number = $validated['shipment_number'];
        $shipment->description = $validated['description'] ?? null;
        $shipment->route_id = $validated['route_id'];
        $shipment->delivery_date = $validated['delivery_date'];
        $shipment->save();

        $data = $shipment;
        $success = $data;
        $message = Lang::get("messages.shipment_updated_successfully");
        return sendResponse($success, $message);
    }
*/
    public function updateShipmentStatus(Request $request)
    {
        if($this->permisssion !== "shipment-edit")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }
        $validator = Validator::make($request->all(), [
            'status' => 'required',
        ]);
        
        if($validator->fails()) {
            return sendError(Lang::get('validation_error'), $validator->errors(), 422);
        }
        $user = auth()->user();
        $ids = $request->input("shipment_id");
        $status = $request->input("status"); // "3"/"2"/"1"
        foreach($ids as $id)
        {
            $shipment = Shipment::where('user_id', $user->id)->find($id);
            if($shipment == null){
                return sendError(Lang::get('messages.not_found'), Lang::get('messages.shipment_not_found'), 400);   
            }
            
            if($status == "3" && $shipment->status == "4")
            {
                $shipment->status = "3";
                $shipment->save();
               // delete order_shipment_item for particular shipment_id            
                // ShipmentOrderItem::whereHas("orderShipments",function($query)use($shipment){
                //     $query->whereHas("shipmentInformation",function($query2)use($shipment){
                //         $query2->where("id",$shipment->id);
                //     });
                // })->delete();
            }
            if($status == "2" && $shipment->status == "3")
            {
                // need to generate order_shipment_item for pickup ticket
                // check ordered quantity with product inventory (batch number)
                $orderItems = OrderItem::with('order.orderShipments')->whereHas('order',function($query1)use($shipment){
                    $query1->whereHas("orderShipments",function($query)use($shipment){
                        $query->whereHas("shipmentInformation",function($query2)use($shipment){
                            $query2->where("id",$shipment->id);
                        });
                    });
                });
                if($orderItems->count() > 0)
                {
                    $orderItemList = $orderItems->get();
                    $shippedOrderItem = [];
                    foreach($orderItemList as $orderItem)
                    {
                        $productInventories = Inventory::where("product_id",$orderItem->product_id)->where("quantity",">","0")->get();
                        $orderedQuantity = $orderItem->quantity;

                        foreach($productInventories as $productInventory){
                            if($orderedQuantity > 0){
                                $shipped_quantity = $orderedQuantity;
                                if($productInventory->quantity >= $orderedQuantity)
                                {
                                    $productInventory->quantity = $productInventory->quantity - $orderedQuantity;
                                    $shipped_quantity = $orderedQuantity;
                                    $orderedQuantity = 0;
                                }
                                else if($productInventory->quantity < $orderedQuantity)
                                {
                                    $orderedQuantity = $orderedQuantity - $productInventory->quantity;
                                    $shipped_quantity = $productInventory->quantity;
                                    $productInventory->quantity = 0;
                                }
                                $productInventory->save();

                                StockHistory::create([
                                    'stock_id' => $productInventory->id,
                                    'reason' => "Sale",
                                    'datetime' => Carbon::now(),
                                    'created_by' => auth()->user()->id,
                                    'new_stock' => $productInventory->quantity ?? 0
                                ]);

                                $shippedOrderItem[] = array(
                                    "order_item_id" => $orderItem->id,
                                    "order_shipment_id" => $orderItem->order->orderShipments->id,
                                    "shipped_quantity" => $shipped_quantity,
                                    "ordered_quantity" => 0,
                                    "aisle_name" => $productInventory->aisle_name,
                                    "aisle" => $productInventory->aisle,
                                    "shelf_name" => $productInventory->shelf_name,
                                    "shelf" => $productInventory->shelf,
                                    "batch_number" => $productInventory->batch,
                                );
                            }
                            else{
                                break;
                            }
                        }
                    }
                    if(count($shippedOrderItem) > 0)
                    {
                        ShipmentOrderItem::insert($shippedOrderItem);
                    }
                }
                $shipment->status = "2";
                $shipment->save();
            }
            if($status == "1" && $shipment->status == "2")
            {
                $orders = Order::whereHas("orderShipments",function($query)use($shipment){
                    $query->whereHas("shipmentInformation",function($query2)use($shipment){
                        $query2->where("id",$shipment->id);
                    });
                });
                if($orders->count() > 0)
                {
                    $orderIds = $orders->pluck("id")->toArray();
                    foreach($orderIds as $order_id)
                    {
                        $orderHistoryInsertData[] = [
                            'order_id' => $order_id,
                            'user_id' => $user->id,
                            'shipment_id' => $shipment->id,
                            'content' => 'assigned_to_shipment',
                            'datetime' => Carbon::now()
                        ];
                    }

                    // insert into order history for shipped order
                    OrderHistory::insert($orderHistoryInsertData);

                    // update order status to shipped 
                    Order::whereIn("id",$orderIds)->update(["status"=>"3"]);
                }

                $shipment->status = "1";
                $shipment->save();
            }
            // dd("sushant");
        }
        $shipmentQuery = Shipment::query();
        if(!empty($search)) {
            $shipmentQuery->where(function($query)use($search){
                $query = $query->orWhereHas('routeDetail',function($query1) use($search){
                    $query1->where("name",'LIKE',"%".$search."%");              
                });
            });
        }
        $data = $shipmentQuery->where('user_id', $user->id)->withCount('orderShipments')->get();

        $success  = $data;
        $message = Lang::get("messages.shipment_updated_successfully");
        return sendResponse($success, $message);
    }

    public function getExistingShipments(Request $request)
    {
        if($this->permisssion !== "shipment-view")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }
        
        $user = auth()->user();
        $today_date = date("Y-m-d");
        $data = Shipment::whereIn('status',["4","3"])->where('user_id', $user->id)->get();

        $success  = $data;
        $message  = Lang::get("messages.shipment_list");
        return sendResponse($success, $message);
    }

    public function updateShipmentDeliveryDate(Request $request)
    {
        if($this->permisssion !== "shipment-edit")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }
        $validator = Validator::make($request->all(), [
            'shipment_id' => 'required',
            'delivery_date' => 'required',
        ]);
        
        if($validator->fails()) {
            return sendError(Lang::get('validation_error'), $validator->errors(), 422);
        }
        $user = auth()->user();
        $ids = $request->input("shipment_id");
        $delivery_date = $request->input("delivery_date");
        foreach($ids as $id)
        {
            $shipment = Shipment::where('user_id', $user->id)->find($id);
            if($shipment == null){
                return sendError(Lang::get('messages.not_found'), Lang::get('messages.shipment_not_found'), 400);   
            }

            $shipment->delivery_date = $delivery_date; 
            $shipment->save();

            $orders = Order::whereHas("orderShipments",function($query)use($shipment){
                $query->whereHas("shipmentInformation",function($query2)use($shipment){
                    $query2->where("id",$shipment->id);
                });
            });
            // dd($orderIds);
            if($orders->count() > 0)
            {
                $orderIds = $orders->pluck("id")->toArray();
                foreach($orderIds as $order_id)
                {
                    $orderHistoryInsertData[] = [
                        'order_id' => $order_id,
                        'user_id' => $user->id,
                        'shipment_id' => $shipment->id,
                        'content' => 'update_delivery_date',
                        'datetime' => Carbon::now()
                    ];
                }

                // insert into order history for shipped order
                OrderHistory::insert($orderHistoryInsertData);

            }
        }
        $shipmentQuery = Shipment::query();
        if(!empty($search)) {
            $shipmentQuery->where(function($query)use($search){
                $query = $query->orWhereHas('routeDetail',function($query1) use($search){
                    $query1->where("name",'LIKE',"%".$search."%");              
                });
            });
        }
        $data = $shipmentQuery->where('user_id', $user->id)->withCount('orderShipments')->get();

        $success  = $data;
        $message = Lang::get("messages.shipment_updated_successfully");
        return sendResponse($success, $message);
    }

    public function shipmentOrderItemList(Request $request)
    {
        $shipment_id = $request->input("shipment_id");
        $user = auth()->user();
        $shipment = Shipment::where('user_id', $user->id)->find($shipment_id);
        if($shipment == null){
            return sendError(Lang::get('messages.not_found'), Lang::get('messages.shipment_not_found'), 400);   
        }
        $orders = Order::with('items')->whereHas("orderShipments",function($query)use($shipment){
            $query->whereHas("shipmentInformation",function($query2)use($shipment){
                $query2->where("id",$shipment->id);
            });
        });
        // dd($orderIds);
        if($orders->count() > 0)
        {
            $orderList = $orders->get();
            $success = $orderList;
            $message = Lang::get("messages.shipment_detail");
            return sendResponse($success, $message);
        }
    }

    public function getPickupAndDeliveryTicket(Request $request)
    {
        if($this->permisssion !== "shipment-view")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }

        $shipment_id = $request->input("shipment_id");
        $document_type = $request->input("document_type");
        $shipment = Shipment::find($shipment_id);
        $data = [];
        if($document_type == "pickup_ticket")
        {
            $transportData = ShipmentTransport::with("orderShipmentsDesc")->whereHas("orderShipmentsDesc")->where("shipment_id",$shipment_id)->get();
            $data = $transportData;
        }
        else if($document_type == "delivery_ticket")
        {
            $transportData = ShipmentTransport::with("orderShipmentsDesc")->whereHas("orderShipments")->where("shipment_id",$shipment_id)->get();
            
            $data = $transportData;
        }
        $success = $data;
        $message = Lang::get("messages.shipment_detail");
        return sendResponse($success, $message);
    }
}
