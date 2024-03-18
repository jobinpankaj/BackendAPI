<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Shipment;
use App\Models\OrderShipment;
use App\Models\ShipmentOrderItem;
use App\Models\ShipmentTransport;
use App\Models\Product;
use App\Models\Order;
use App\Models\InvoiceDetail;
use PDF;
use Ramsey\Uuid\Uuid;
use DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class PDFController extends Controller
{
    public $permisssion;

    public function __construct()
    {
        $headers = getallheaders();
        
        $this->permisssion = isset($headers['permission']) ? $headers['permission'] : "";
    }

    public function generatePickupAndDeliveryTicket(Request $request)
    {
        // if($this->permisssion !== "shipment-view")
        // {
        //     return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        // }
            
        $shipment_id = $request->input("shipment_id");
        $document_type = $request->input("document_type");
        $shipment = Shipment::find($shipment_id);
        if($document_type == "pickup_ticket")
        {
            $transportData = ShipmentTransport::with("orderShipmentsDesc")->whereHas("orderShipmentsDesc")->where("shipment_id",$shipment_id)->get();
            $data["transportData"] = $transportData;
            
            $name = "pickup-ticket-".$shipment_id."-".time().".pdf";   
            $pdf = PDF::loadView('pdf/pickup-ticket', $data)->save(public_path('storage/pdf_files/'.$name));

            $myFile = storage_path('app/public/pdf_files/'.$name);
            return response()->download($myFile)->deleteFileAfterSend(true);
            return response()->download($myFile);

        }
        else if($document_type == "delivery_ticket")
        {
            $transportData = ShipmentTransport::with("orderShipmentsDesc")->whereHas("orderShipments")->where("shipment_id",$shipment_id)->get();
            // dd($transportData);
            $data["transportData"] = $transportData;
            
            $name = "delivery-ticket-".$shipment_id."-".time().".pdf";
            $pdf = PDF::loadView('pdf/delivery-ticket', $data)->save(public_path('storage/pdf_files/'.$name));

            $myFile = storage_path('app/public/pdf_files/'.$name);
            return response()->download($myFile)->deleteFileAfterSend(true);
            return response()->download($myFile);

        }
    }

    public function downloadProductBarcode(Request $request,$id)
    {
        $product_id = $id;
        $productInfo = Product::find($product_id);
        if($productInfo)
        {
            $name = "barcode-".$product_id."-".time().".pdf";
            $data["productInfo"] = $productInfo;
            $pdf = PDF::loadView('pdf/barcode', $data)->save(public_path('storage/pdf_files/'.$name));

            $myFile = storage_path('app/public/pdf_files/'.$name);
            return response()->download($myFile)->deleteFileAfterSend(true);
        }
    }
    public function creatOrderInvoice(Request $request,$id)
    {
        $order_id = $id;
        $orderInfo = Order::find($order_id);
        $user_id = auth()->user();
        $userName =substr($user_id->first_name, 0,3);
        // dd($check);
        if($orderInfo)
        {
            $orderData = Order::with(['items','supplierInformation','retailerInformation','orderShipments','orderDistributors'])->where('id', $id)->first();
            // dd($orderData);
            $orderItems = $orderData->items;
            // dd($orderItems);
            $totalPrices = [];
            $totalQuantity = [];
            $totalTax = [];
            foreach($orderItems as $orderItem)
            {
                $price = $orderItem->sub_total;
                $quantity = $orderItem->quantity;
                $tax  = $orderItem->tax;
                // Calculate total price for the current order item
                $totalPrice = $price ;
                $totalPrices[] = $totalPrice;
                $totalQuantity[] = $quantity;
                $totalTax[] = $tax;
            }
            $totalOrderPrice = array_sum($totalPrices);
            $totalOrderQuantity = array_sum($totalQuantity);
            $totalOrderTax = array_sum($totalTax);
            $invoiceDat = InvoiceDetail::where('created_by',$user_id->id)->latest()->first();
            // dd($invoiceDat);
            // if(!empty($invoiceData))
            // {
            //     $invoiceData->update(['invoice_number' => $invoiceData->invoice_number + 1]);
            // }
            
            // $orderRefrenceNumber = substr($orderData->order_reference,12);
            $orderRefrenceNumber = $request->invoice_no;
            $invoiceData = new InvoiceDetail();
            $invoiceData->created_by = $user_id->id;
            if(!empty($invoiceDat))
            {
                $invoiceData->invoice_number = $invoiceDat->invoice_number +1;
            }
            else{
                $invoiceData->invoice_number = $orderRefrenceNumber;

            }
            $invoiceData->order_id = $order_id;
            $invoiceData->save();
            $invoiceId = "$userName-"."$invoiceData->invoice_number".".pdf";
            $invoiceNumber = "$userName-"."$invoiceData->invoice_number";
            $data["orderData"] = $orderData;
            $data['invoiceNumber'] = $invoiceNumber;
            $data['totalOrderTax'] = $totalOrderTax;
            $pdf = PDF::loadView('invoices/order-invoice', $data)->save(public_path('storage/order_invoices/'.$invoiceId));

            $myFile = storage_path('app/public/order_invoices/'.$invoiceId);
            return response()->download($myFile);
        }
    }
    public function getInvoiceList(request $request)
    {
        $user_id = auth()->user()->id;
        $invoiceList = InvoiceDetail::where('created_by',$user_id)->orwhere('created_for',$user_id)->get();
        $success  = $invoiceList;
        $message  = Lang::get("InvoiceList");
        return sendResponse($success, $message);
    }
}
