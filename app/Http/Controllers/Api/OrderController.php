<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderHistory;
use App\Models\OrderItem;
use App\Models\Shipment;
use App\Models\OrderShipment;
use App\Models\Product;
use App\Models\SupplierDistributor;
use App\Models\OrderDistributor;
use App\Models\ShipmentTransport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Lang;
use Ramsey\Uuid\Uuid;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Tax;
use App\Models\ProductFormatDeposit;
class OrderController extends Controller
{
    public $permisssion;

    public function __construct()
    {
        $headers = getallheaders();

        $this->permisssion = isset($headers['permission']) ? $headers['permission'] : "";
        dd($this->permisssion);
    }

    public function supplierOrderList()
    {
        if($this->permisssion !== "order-view")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }

        $user = auth()->user();
        $data = Order::with(['items', 'retailerInformation', 'orderDistributors', 'orderDistributors.distributorInfo'])->has("items")->where('supplier_id', $user->id)->orderBy('created_at','DESC')->get();

        $success = $data;
        $message = Lang::get("messages.order_list");
        return sendResponse($success, $message);
    }

    public function addSupplierOrder(Request $request)
    {
        if($this->permisssion !== "order-edit")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }

        $validator = Validator::make($request->all(), [
            'retailer_id' => 'required|numeric|exists:users,id',
            'distributor_id' => 'required|numeric|exists:users,id',
            // 'deposit' => 'nullable|boolean',
            // 'taxes' => 'nullable|boolean',
            'note' => 'nullable|string',
            'items.*.product_id' => 'required|numeric|exists:products,id',
            'items.*.product_style_id' => 'required|numeric|exists:product_styles,id',
            'items.*.product_format_id' => 'required|numeric|exists:product_formats,id',
            'items.*.price' => 'required|numeric',
            'items.*.quantity' => 'required|numeric',
            'items.*.tax' => 'required|numeric',
            'items.*.sub_total' => 'required|numeric'
        ]);

        if($validator->fails()) {
            return sendError(Lang::get('validation_error'), $validator->errors(), 422);
        }

        $validated = $request->all();

        $user = auth()->user();
        $order_date = date("Y-m-d");
        $createdOn = date("Y-m-d H:i:s");
        $parent_id = uniqid();
        // hexdec is using to convert hexadecimal to numeric
        // dechex is using to convert numeric to hexadecimal
        foreach($validated['items'] as $key=> $cartInfo){

            $orderInfo = Order::where(["retailer_id" => $validated['retailer_id'], "supplier_id" => $user->id, "created_at" => $createdOn])->first();
            if($orderInfo == null)
            {
                $orderInsertData = [
                                    'supplier_id' => $user->id,
                                    'retailer_id' => $validated['retailer_id'],
                                    'order_reference' => hexdec(uniqid()),
                                    'added_by' => $user->id,
                                    'order_date' => $order_date,
                                    'created_at' => $createdOn,
                                    'added_by_user_type' => 'supplier',
                                    'note' => $request->input("note"),
                                    'parent_id' => $parent_id,
                                    'status' => '1',
                                    ];
                $orderInfo = Order::create($orderInsertData);
                // Add Order History
                OrderHistory::create([
                    'order_id' => $orderInfo->id,
                    'user_id' => $user->id,
                    'content' => 'order_placed',
                    'datetime' => Carbon::now()
                ]);
            }
            $order_id = $orderInfo->id;
            $productInfo = Product::with(['pricing'])->where('id',$cartInfo['product_id'])->first();
            $orderItemInsertData = [
                                    'order_id' => $order_id,
                                    'product_id' => $cartInfo['product_id'],
                                    'product_style_id' => $cartInfo['product_style_id'],
                                    'product_format_id'  => $cartInfo['product_format_id'],
                                    'quantity' => $cartInfo['quantity'],
                                    'price' => $cartInfo['price'],
                                    'tax' => $cartInfo['tax'],
                                    'sub_total' => ($cartInfo['price'] * $cartInfo['quantity']) + $cartInfo['tax'],
                                    'created_at' => $createdOn,
                                    ];
            $orderItemInfo = OrderItem::create($orderItemInsertData);

            $orderDistributorInsertData = [
                                            "order_id" => $order_id,
                                            "order_item_id" => $orderItemInfo->id,
                                            "distributor_id" => $validated['distributor_id'],
                                            'created_at' => $createdOn,
                                            ];
            OrderDistributor::create($orderDistributorInsertData);
        }

        $success = [];
        $message = Lang::get("messages.supplier_order_created_successfully");
        return sendResponse($success, $message);
    }

    public function supplierOrderListCount()
{
    if ($this->permisssion !== "order-view") {
        return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
    }

    $user = auth()->user();
    
    $total_order = Order::where('supplier_id', $user->id)->count();
    $pending_order = Order::where('status', '0')->where('supplier_id', $user->id)->count();
    $approved_order = Order::where('status', '1')->where('supplier_id', $user->id)->count();
    $hold_order = Order::where('status', '2')->where('supplier_id', $user->id)->count();
    $shipped_order = Order::where('status', '3')->where('supplier_id', $user->id)->count();
    $delivered_order = Order::where('status', '4')->where('supplier_id', $user->id)->count();
    $cancelled_order = Order::where('status', '5')->where('supplier_id', $user->id)->count();
    
    $success['total_order'] = $total_order;
    $success['pending_order'] = $pending_order;
    $success['approved_order'] = $approved_order;
    $success['hold_order'] = $hold_order;
    $success['shipped_order'] = $shipped_order;
    $success['delivered_order'] = $delivered_order;
    $success['cancelled_order'] = $cancelled_order;

    $message = Lang::get("messages.order_list");
    return sendResponse($success, $message);
}

    public function updateSupplierOrder(Request $request, $id)
    {
        if($this->permisssion !== "order-edit")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }

        $user = auth()->user();
        $supplierOrder = Order::with('items')->where('supplier_id', $user->id)->find($id);

        if(!$supplierOrder) {
            return sendError(Lang::get('messages.supplier_order_not_found'), Lang::get('messages.supplier_order_not_found'), 404);
        }

        $validator = Validator::make($request->all(), [
            'retailer_id' => 'required|numeric|exists:users,id',
            'distributor_id' => 'required|numeric|exists:users,id',
            'deposit' => 'nullable|boolean',
            'taxes' => 'nullable|boolean',
            'note' => 'nullable|string',
            'total' => 'required|numeric',
            'items.*.product_id' => 'required|numeric|exists:products,id',
            'items.*.price' => 'required|numeric',
            'items.*.quantity' => 'required|numeric',
            'items.*.sub_total' => 'required|numeric'
        ]);

        if($validator->fails()) {
            return sendError(Lang::get('validation_error'), $validator->errors(), 422);
        }

        $validated = $request->all();

        // Update Order
        $supplierOrder->retailer_id = $validated['retailer_id'];
        $supplierOrder->distributor_id = $validated['distributor_id'];
        $supplierOrder->deposit = $validated['deposit'] ?? $supplierOrder->deposit;
        $supplierOrder->taxes = $validated['taxes'] ?? $supplierOrder->taxes;
        $supplierOrder->note = $validated['note'] ?? $supplierOrder->note;
        $supplierOrder->total = $validated['total'];

        foreach($validated['items'] as $item)
        {
            // Update Order Item
            $supplierOrder->items()->updateOrCreate([
                'product_id' => $item['product_id']
            ], [
                'price' => $item['price'],
                'quantity' => $item['quantity'],
                'sub_total' => $item['sub_total']
            ]);
        }

        $supplierOrder->save();

        $data = $supplierOrder->refresh();

        // Add Order History
        OrderHistory::create([
            'order_id' => $supplierOrder->id,
            'user_id' => $user->id,
            'content' => 'order_updated',
            'datetime' => Carbon::now()
        ]);

        $success = $data;
        $message = Lang::get("messages.supplier_order_updated_successfully");
        return sendResponse($success, $message);
    }

    public function distributorOrderList()
    {
        if($this->permisssion !== "order-view")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }

        $user = auth()->user();
        $suppliers = SupplierDistributor::where('distributor_id', $user->id);
        $supplierIds = [];
        if($suppliers->count() > 0 )
        {
            $supplierIds = $suppliers->pluck('supplier_id')->toArray();
        }
        $data = Order::with(['items','supplierInformation','retailerInformation','orderShipments'])->whereIn("supplier_id",$supplierIds)->where("status","1")->orderBy('created_at','DESC')->get();

        $success = $data;
        $message = Lang::get("messages.supplier_order_list");
        return sendResponse($success, $message);
    }

    public function assignShipmentToOrder(Request $request)
    {
        if($this->permisssion !== "shipment-edit")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }

        $user = auth()->user();
        $shipmentType = $request->input("shipment_type");
        $shipment = [];
        if($shipmentType == "new") {
            $validator = Validator::make($request->all(), [
                'route_id' => 'required',
                'delivery_date' => 'required',
            ]);

            if($validator->fails()) {
                return sendError(Lang::get('validation_error'), $validator->errors(), 422);
            }

            $validated = $request->all();

            $user = auth()->user();
            $shipment = Shipment::create([
                'route_id' => $validated['route_id'],
                'delivery_date' => $validated['delivery_date'],
                'user_id' => $user->id,
            ]);
            ShipmentTransport::create(["shipment_id"=>$shipment->id,"position"=>1,"added_by"=>$user->id]);
        }
        else if($shipmentType == "existing") {
            $validator = Validator::make($request->all(), [
                'shipment_id' => 'required',
                'delivery_date' => 'required',
            ]);
            $shipment_id = $request->input("shipment_id");
            $shipment = Shipment::find($shipment_id);
            if($shipment == null){
                return sendError(Lang::get('messages.not_found'), Lang::get('messages.shipment_not_found'), 400);
            }
            $shipment->delivery_date = $request->input("delivery_date");
            $shipment->save();
        }
        $order_id_array = $request->input("order_ids");
        $route_id = $shipment->route_id;
        $expected_delivery_date = date("Y-m-d H:i:s",strtotime("+7 days"));
        $added_by = $user->id;
        $insertData = array();
        $orderHistoryInsertData = array();
        foreach($order_id_array as $key => $order_id)
        {
            // $orderItems = OrderItem::where("order_id",$order_id)->get();

            $shipmentOrderData = OrderShipment::where("order_id",$order_id)->get();
            if($shipmentOrderData->count() < 1)
            {
                $insertData[] = [
                    'order_id' => $order_id,
                    'shipment_id' => $shipment->id,
                    'shipment_transport_id' => $shipment->shipmentTransports->first()->id,
                    'expected_delivery_date' => $expected_delivery_date,
                    'delivery_date' => ($shipment && $shipment->delivery_date) ? $shipment->delivery_date : $expected_delivery_date,
                    'added_by' => $added_by,
                    'order_position' => 1,
                ];
                $orderHistoryInsertData[] = [
                    'order_id' => $order_id,
                    'user_id' => $user->id,
                    'shipment_id' => $shipment->id,
                    'content' => 'assigned_to_shipment',
                    'datetime' => Carbon::now()
                ];
            }
        }
        if(count($insertData) < 1)
        {
            return sendError(Lang::get('messages.already_assigned'), Lang::get('messages.already_assigned'), 400);
        }
        else {
            OrderShipment::insert($insertData);
            // Add Order History
            OrderHistory::insert($orderHistoryInsertData);
            $success = [];
            return sendResponse($success,Lang::get('messages.added_successfully'));
        }
    }

    public function orderDetail(Request $request,$id)
    {
        if($this->permisssion !== "order-view")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }

        $user = auth()->user();
        // \DB::enableQueryLog();

        $data = Order::with(['items','supplierInformation','retailerInformation','orderShipments','orderDistributors'])->where('id', $id)->first();
        // dd(\DB::getQueryLog());
        $orderItems = $data->items;

            // dd($orderItems);
            $totalPrices = [];
            $totalQuantity = [];
            $totalTax = [];
            $totalProductDeposit = [];
            $totalGST = [];
            $totalQST = [];
            $totalGSTQST = [];
            foreach($orderItems as $orderItem)
            {
                $product_format_deposit = ProductFormatDeposit::where('product_format_id',$orderItem->product_format_id)->where('user_id',$user->id)->first();
                if(!empty($product_format_deposit))
                {
                    $prod_deposit = $product_format_deposit->product_format_deposit;

                }
                else{
                    $prod_deposit = 0.0;
                }
                // Extracting data from the "product" object within the order item
                $product = $orderItem['product'];
                // Extracting data from the "pricing" object within the product
                $pricing = $product['pricing'];
                $tax_id = $pricing['tax_id'];
                // $GST = $pricing['tax_amount'] * $orderItem->quantity;
                // $pricegst = $pricing['price'] * $orderItem->quantity;
                if($tax_id == 2)
                {
                    $Tax = Tax::where('id',$tax_id)->first();
                    $GST = ($Tax->tax);
                    // $GST = ($Tax->tax) * $orderItem->quantity;
                    $pricegst = ($orderItem->sub_total) - ($orderItem->tax ?? null);
                    // echo "$pricegst";
                    // $pricegst = $pricing['price'] * $orderItem->quantity;
                    $totalorderGst = ($pricegst * $GST) / 100 ;
                    $totalGST []= $totalorderGst;
                }
                elseif($tax_id == 3)
                {
                    $Tax = Tax::where('id',$tax_id)->first();
                    $QST = ($Tax->tax) ;

                    $priceqst = ($orderItem->sub_total) - ($orderItem->tax ?? null);

                    $totalorderQst = ($priceqst * $QST) / 100 ;
                    $totalQST []= $totalorderQst;
                    // dd($totalQST);

                }
                elseif($tax_id == 4)
                {
                    $Tax = Tax::where('id',$tax_id)->first();
                    $GSTQST = ($Tax->tax) ;
                    $pricegstqst = ($orderItem->sub_total) - ($orderItem->tax ?? null);
                    $totalorderGstQst = ($pricegstqst * $GSTQST) / 100 ;
                    $totalGSTQST []= $totalorderGstQst;

                }
                $price = $orderItem->sub_total;
                $quantity = $orderItem->quantity;
                $tax  = $orderItem->tax;
                // Calculate total price for the current order item
                $totalPrice = $price ;
                $totalPrices[] = $totalPrice;
                $totalQuantity[] = $quantity;
                $totalTax[] = $tax;
                $totalProductDeposit [] = $prod_deposit;



            }
            // dd($totalGST);
            $totalOrderPrice = array_sum($totalPrices);
            $totalOrderQuantity = array_sum($totalQuantity);
            $totalOrderTax = array_sum($totalTax);
            $totalOrderProductDeposit = array_sum($totalProductDeposit);
            $totalOrderGST = array_sum($totalGST);

            $totalOrderQST = array_sum($totalQST);
            $totalOrderGSTQST = array_sum($totalGSTQST);
            $subtotal = $totalOrderPrice + $totalOrderProductDeposit;
            // if(!empty($GST))
            // {
            //     dd($pricegst);
            //     $totalorderGstVal = (($pricegst * $GST) * $orderItem->quantity) / 100 ;
            //     dd($totalorderGstVal);
            //     $totalorderGstVal = ($subtotal * $totalOrderGST) / 100 ;
            // }else{
            //     $totalorderGstVal = 0;
            // }
            // if(!empty($QST))
            // {
            //     $totalorderQstVal = (($pricegst * $GST) * $orderItem->quantity) / 100 ;

            //     $totalorderQstVal = ($subtotal * $totalOrderQST) / 100 ;
            // }
            // else{
            //     $totalorderQstVal = 0;
            // }
            // if(!empty($GSTQST))
            // {
            //     $totalorderGstQstVal = (($pricegst * $GST) * $orderItem->quantity) / 100 ;

            //     $totalorderGstQstVal = ($subtotal * $totalOrderGSTQST) / 100 ;
            // }else{
            //     $totalorderGstQstVal = 0;
            // }
            // dd($totalorderGstVal);
            $finalPrice = $subtotal + $totalOrderGST   + $totalOrderQST  + $totalOrderGSTQST  ;
            // dd($subtotal);
            // dd($totalProductDeposit);
            $data['totalOrderPrice'] = $totalOrderPrice;
            $data['totalOrderQuantity'] = $totalOrderQuantity;
            // $data['totalOrderTax'] = $totalOrderTax;
            $data['totalOrderProductDeposit'] = $totalOrderProductDeposit;
            $data['totalOrderGST'] = $totalOrderGST;
            $data['totalOrderQST'] = $totalOrderQST;
            $data['totalOrderGSTQST'] = $totalOrderGSTQST;
            $data['subtotal'] = $subtotal;
            $data['finalPrice'] = $finalPrice;



        $success = $data;
        $message = Lang::get("messages.order_detail");
        return sendResponse($success, $message);
    }

    public function retailerOrderList()
    {
       // if($this->permisssion !== "order-view")
       // {
       //     return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
       // }

        $user = auth()->user();
        $data = Order::with(['items', 'supplierInformation'])->where('retailer_id', $user->id)->orderBy('created_at','DESC')->get();

        $success = $data;
        $message = Lang::get("messages.order_list");
        return sendResponse($success, $message);
    }
    public function retailerOrderListCount()
    {
        if ($this->permisssion !== "order-view") {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }
    
        $user = auth()->user();
        
        $total_order = Order::where('retailer_id', $user->id)->count();
        $pending_order = Order::where('status', '0')->where('retailer_id', $user->id)->count();
        $approved_order = Order::where('status', '1')->where('retailer_id', $user->id)->count();
        $hold_order = Order::where('status', '2')->where('retailer_id', $user->id)->count();
        $shipped_order = Order::where('status', '3')->where('retailer_id', $user->id)->count();
        $delivered_order = Order::where('status', '4')->where('retailer_id', $user->id)->count();
        $cancelled_order = Order::where('status', '5')->where('retailer_id', $user->id)->count();
        
        $success['total_order'] = $total_order;
        $success['pending_order'] = $pending_order;
        $success['approved_order'] = $approved_order;
        $success['hold_order'] = $hold_order;
        $success['shipped_order'] = $shipped_order;
        $success['delivered_order'] = $delivered_order;
        $success['cancelled_order'] = $cancelled_order;
    
        $message = Lang::get("messages.order_list_count");
        return sendResponse($success, $message);
    }
    public function supplierOrderStatusUpdate(Request $request)
    {
        if($this->permisssion !== "order-edit")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }

        $validator = Validator::make($request->all(), [
            'order_id' => 'required',
            'action' => 'required|numeric',
            'expected_delivery_date' => 'nullable|date',
        ]);

        if($validator->fails()) {
            return sendError(Lang::get('validation_error'), $validator->errors(), 422);
        }

        $validated = $request->all();

        $user = auth()->user();
        $orderIds = explode(",", $validated['order_id']);
        Order::where('supplier_id', $user->id)->whereIn('id', $orderIds)->update([
            'status' => $validated['action'],
            'delivered_on' => $validated['expected_delivery_date'] ?? null
        ]);

        $supplierOrders = Order::where('supplier_id', $user->id)->whereIn('id', $orderIds)->get();

        $data = $supplierOrders;

        foreach($supplierOrders as $supplierOrder)
        {
            // Add Order History
            OrderHistory::create([
                'order_id' => $supplierOrder->id,
                'user_id' => $user->id,
                'content' => 'order_status_updated',
                'datetime' => Carbon::now()
            ]);
        }

        $success = $data;
        $message = Lang::get("messages.supplier_order_updated_successfully");
        return sendResponse($success, $message);
    }

    public function publishInvoices(Request $request)
    {
        if($this->permisssion !== "shipment-edit")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }

        $user = auth()->user();
        $order_id_array = $request->input("order_ids");
        $invoiceInsertData = array();
        $orderHistoryInsertData = array();
        foreach($order_id_array as $key => $order_id)
        {
            $orderData = Order::where("id",$order_id)->first();
            // if($orderData !== null)
            // {
            //     $insertData[] = [
            //         'order_id' => $order_id,
            //         'shipment_id' => $shipment->id,
            //         'shipment_transport_id' => $shipment->shipmentTransports->first()->id,
            //         'expected_delivery_date' => $expected_delivery_date,
            //         'delivery_date' => ($shipment && $shipment->delivery_date) ? $shipment->delivery_date : $expected_delivery_date,
            //         'added_by' => $added_by,
            //         'order_position' => 1,
            //     ];
            //     $orderHistoryInsertData[] = [
            //         'order_id' => $order_id,
            //         'user_id' => $user->id,
            //         'shipment_id' => $shipment->id,
            //         'content' => 'assigned_to_shipment',
            //         'datetime' => Carbon::now()
            //     ];
            // }
        }
        if(count($insertData) < 1)
        {
            return sendError(Lang::get('messages.already_assigned'), Lang::get('messages.already_assigned'), 400);
        }
        else {
            OrderShipment::insert($insertData);
            // Add Order History
            OrderHistory::insert($orderHistoryInsertData);
            $success = [];
            return sendResponse($success,Lang::get('messages.added_successfully'));
        }
    }

}
