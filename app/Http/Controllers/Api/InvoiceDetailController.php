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
use App\Models\ProductFormatDeposit;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Lang;
class InvoiceDetailController extends Controller
{
    public function createOrderInvoice(Request $request,$id)
    {
        $order_id = $id;
        $orderInfo = Order::find($order_id);
        $user_id = auth()->user();
        $userName =substr($user_id->first_name, 0,3);
       
        if($orderInfo)
        {
            $data = Order::with(['items','supplierInformation','retailerInformation','orderShipments','orderDistributors'])->where('id', $id)->first();
            // dd($orderData);
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
                $product_format_deposit = ProductFormatDeposit::where('product_format_id',$orderItem->product_format_id)->where('user_id',$user_id->id)->first();
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
                if($tax_id == 2)
                {
                    $GST = $pricing['tax_amount'];
                    $totalGST []= $GST;
                }
                elseif($tax_id == 3)
                {
                    $QST = $pricing['tax_amount'];
                    $totalQST []= $QST;

                }
                elseif($tax_id == 4)
                {
                    $GSTQST = $pricing['tax_amount'];
                    $totalGSTQST []= $GSTQST;
                    
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
            $totalOrderPrice = array_sum($totalPrices);
            $totalOrderQuantity = array_sum($totalQuantity);
            $totalOrderTax = array_sum($totalTax);
            $totalOrderProductDeposit = array_sum($totalProductDeposit);
            $totalOrderGST = array_sum($totalGST);
            $totalOrderQST = array_sum($totalQST);
            $totalOrderGSTQST = array_sum($totalGSTQST);
            $subtotal = $totalOrderPrice + $totalOrderProductDeposit;
            $finalPrice = $subtotal + $totalOrderGST + $totalOrderQST + $totalOrderGSTQST;
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
            // $invoiceDat = InvoiceDetail::where('created_by',$user_id->id)->latest()->first();
            // $orderRefrenceNumber = $request->invoice_no;
            // $invoiceData = new InvoiceDetail();
            // $invoiceData->created_by = $user_id->id;
            // if(!empty($invoiceDat))
            // {
            //     $invoiceData->invoice_number = $invoiceDat->invoice_number +1;
            // }
            // else{
            //     $invoiceData->invoice_number = $orderRefrenceNumber;

            // }
            // $invoiceData->order_id = $order_id;
            // $invoiceData->save();
            // $data= $orderData;
            $success = $data;
            $message = Lang::get("messages.order_detail");
            return sendResponse($success, $message);
        }
    }
}
