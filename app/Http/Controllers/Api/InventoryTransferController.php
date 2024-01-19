<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InventoryTransfer;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Inventory;
use App\Models\InventoryTransferProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Validator;
use DB;
use Mail;
use App\Mail\DistributorTransferInfoMail;
class InventoryTransferController extends Controller
{
    public $permisssion;

    public function __construct()
    {
        $headers = getallheaders();
        
        $this->permisssion = isset($headers['permission']) ? $headers['permission'] : "";
    }

    public function getInventoryTransferList()
    {
        if($this->permisssion !== "inventory-view")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }

        $user = auth()->user();
        // $inventoryTransfers = InventoryTransfer::with('transferProducts')->where('sender', $user->id)->get();
        $inventoryTransfers = InventoryTransfer::where('sender', $user->id)->get();
        // dd($inventoryTransfers);
        foreach($inventoryTransfers as $key => $value)
        {
            $inventory_sender = UserProfile::where('user_id',$user->id)->first();
            $inventory_reciever = UserProfile::where('user_id',$value->recipient)->first();
            
            $value->senderName = $inventory_sender['company_name'] ?? null;
            if(!empty($inventory_reciever))
            {
            $value->recipentName = $inventory_reciever['company_name'] ?? null;

            }  

            // $inventory_sender = User::where('id',$user->id)->first();
            // $inventory_reciever = User::where('id',$value->recipient)->first();
            
            // $value->senderName = $inventory_sender['first_name'] .' '. $inventory_sender['last_name'] ?? null;
            // if(!empty($inventory_reciever))
            // {
            // $value->recipentName = $inventory_reciever['first_name'] .' '.$inventory_reciever['last_name'] ?? null;

            // }    

            $inventoryTransferProd = InventoryTransferProduct::select(DB::raw('SUM(send) AS total_send'),DB::raw('SUM(received) AS total_received'),DB::raw('SUM(broken) AS total_broken'))->where('inventory_transfer_id',$value->id)->first();
            
            $value->send = $inventoryTransferProd->total_send ?? null;
            $value->recieved = $inventoryTransferProd->total_received ?? null;
            $value->broken = $inventoryTransferProd->total_broken ?? null;

            // dd($inventoryTransferProd);
        }

        $success = $inventoryTransfers;
        $message = Lang::get("messages.inventory_transfer_list");
        return sendResponse($success, $message);
    }

    public function getInventoryTransfer($id)
    {
        if($this->permisssion !== "inventory-view")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }

        $user = auth()->user();
        $inventoryTransfer = InventoryTransfer::with('transferProducts')->where('sender', $user->id)->find($id);
        
        if(!$inventoryTransfer) {
            return sendError(Lang::get('messages.inventory_transfer_not_found'), Lang::get('messages.inventory_transfer_not_found'), 404);
        }

        $success = $inventoryTransfer;
        $message = Lang::get("messages.inventory_transfer_detail");
        return sendResponse($success, $message);
    }

    public function addInventoryTransfer(Request $request)
    {
        if($this->permisssion !== "inventory-edit")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }

        $validator = Validator::make($request->all(), [
            // 'sender' => 'required|numeric|exists:users,id',
            'warehouse_id' => 'required|numeric',
            'others'    =>  'required|numeric',
            // 'recepient' => 'required|numeric|exists:users,id',
            // 'recepient_type' => 'required|string',
            'recepient_name' => 'nullable|string',
            'products.*.inventory_id' => 'required|numeric|exists:inventories,id',
            'products.*.product_id' => 'required|numeric|exists:products,id',
            'products.*.batch' => 'required|string',
            'products.*.send' => 'required|string',

            // 'products.*.received' => 'required|numeric',
            // 'products.*.broken' => 'required|numeric'
        ]);
        $user_id = auth()->user()->id;
        if($validator->fails()) {
            return sendError(Lang::get('validation_error'), $validator->errors(), 422);
        }

        $validated = $request->all();
        // dd($validated);
        if($validated['others'] == 1)
        {
            $inventoryTransfer = InventoryTransfer::create([
                'sender' => $user_id,
                'warehouse_id' => $validated['warehouse_id'],
                'recipient' => 0,
                'others'    =>  1,
                'recipient_name' => $validated['recepient_name'] ?? null,
                'recipient_type' => $validated['recepient_name'] ?? null,

            ]);
            $inventory = Inventory::where('id',$request->inventory_id)->first();
            // Inventory::where('id',$request->inventory_id)->update([''])

        }else{
            $inventoryTransfer = InventoryTransfer::create([
                'sender' => $user_id,
                'warehouse_id' => $validated['warehouse_id'],
                'recipient' => $validated['recepient'],
                'others'    =>  0,
                'recipient_type' => 'distributor',
                'recipient_name' => $validated['recepient_name'] ?? null
            ]);
            // $recepient_name = User::where('id',$validated['recepient'])->first();
            // $sender_name = User::where('id',$validated['sender'])->first();
            // $content = '';            

            // $content .= '<p>'.Lang::get("messages.tranfer_info_email_1");
            // $content .= Lang::get("messages.tranfer_info_email_Supplierame").$sender_name->full_name.'<br/>';
            // $content .= Lang::get("messages.tranfer_info_email_content_2").'</p>'; 
            // $details = [
            //     'name' => $recepient_name->full_name,
            //     'body' => $content,
            //     'subject' => Lang::get('messages.tranfer_info_email_subject')
            // ];
            // try {
            //     Mail::to($recepient_name->email)->send(new DistributorTransferInfoMail($details));
            // }
            // catch(Exception $e){
            // }
            
        }
       

        $inventoryTransferProducts = [];

        foreach($validated['products'] as $product)
        {
            $inventoryTransferProduct = InventoryTransferProduct::create([
                'inventory_transfer_id' => $inventoryTransfer->id,
                'inventory_id' => $product['inventory_id'],
                'product_id' => $product['product_id'],
                'batch' => $product['batch'] ?? null,
                'send'      => $product['send'],

                
                // 'received' => $product['received'],
                // 'broken' => $product['broken'],
            ]);
            $quantity =Inventory::where('id',$product['inventory_id'])->first();
            $update_quantity = ($quantity->quantity) - $product['send'];
            $check = Inventory::where('id',$product['inventory_id'])->update(['quantity' => $update_quantity]);
            array_push($inventoryTransferProducts, $inventoryTransferProduct);
        }

        $data = collect($inventoryTransfer);
        
        $data['products'] = collect($inventoryTransferProducts);
        
        $success = $data;
        $message = Lang::get("messages.inventory_transfer_created_successfully");
        return sendResponse($success, $message);
    }

    public function updateInventoryTransfer(Request $request, $id)
    {
        if($this->permisssion !== "inventory-edit")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }

        $validator = Validator::make($request->all(), [
            // 'sender' => 'required|numeric|exists:users,id',
            'others'    => 'required|numeric',
            // 'recepient' => 'required|numeric|exists:users,id',
            'products.*.inventory_id' => 'required|numeric|exists:inventories,id',
            'products.*.product_id' => 'required|numeric|exists:products,id',
            'products.*.batch' => 'required|string',
            // 'products.*.received' => 'required|numeric',
            // 'products.*.broken' => 'required|numeric'
        ]);

        if($validator->fails()) {
            return sendError(Lang::get('validation_error'), $validator->errors(), 422);
        }
        $user_id = auth()->user()->id;
        $validated = $request->all();
        
        $inventoryTransfer = InventoryTransfer::with('transferProducts')->find($id);

        if(!$inventoryTransfer) {
            return sendError(Lang::get('messages.inventory_transfer_not_found'), Lang::get('messages.inventory_transfer_not_found'), 404);
        }
        $check = InventoryTransfer::where('id',$request->id)->first();
        
        // Update Inventory Transfer
        
        $inventoryTransfer->sender = $user_id;
        if($check->others == 0 && $validated['others'] ==0)
        {
        $inventoryTransfer->recipient = $validated['recepient'];
        $inventoryTransfer->recipient_type = $validated['recepient_name'];


        }
       
        $inventoryTransfer->others = $validated['others'];
        $inventoryTransfer->recipient = 0;
        $inventoryTransfer->recipient_type = "distributor";
        $inventoryTransfer->recipient_name = $validated["recepient_name"] ?? null;
        $inventoryTransfer->warehouse_id = $validated['warehouse_id'] ?? null;

        foreach($validated['products'] as $product)
        {
            // Update Inventory Transfer
            $quantity =Inventory::where('id',$product['inventory_id'])->first();
            $inventiory_transfer = InventoryTransferProduct::where('inventory_transfer_id',$request->id)->where('inventory_id',$product['inventory_id'])->first();
            // dd($inventiory_transfer->send);
          
            if ($inventiory_transfer && $inventiory_transfer->send < intval($product['send']))
            {
                // $recieived_check = $inventiory_transfer->send;
                // echo " $recieived_check";
                $update_quantity1 = $product['send'] - ($inventiory_transfer->send);
            //    dd($update_quantity1);
                $update_quantity = ($quantity->quantity) - $update_quantity1;
                $check = Inventory::where('id',$product['inventory_id'])->update(['quantity' => $update_quantity]);
            }
            elseif($inventiory_transfer && $inventiory_transfer->send > intval($product['send'])){
                $update_quantity1 =  ($inventiory_transfer->send) - $product['send'];
                //    dd($update_quantity1);
                    $update_quantity = ($quantity->quantity) + $update_quantity1;
                    $check = Inventory::where('id',$product['inventory_id'])->update(['quantity' => $update_quantity]);
            } 

            $inventoryTransfer->transferProducts()->updateOrCreate([
                'product_id' => $product['product_id']
            ], [
                'inventory_id' => $product['inventory_id'],
                'batch' => $product['batch'],
                'send'      => $product['send'] ?? null,

                // 'received' => $product['received'],
                // 'broken' => $product['broken']
            ]);
            
            
        }

        $inventoryTransfer->save();

        $data = $inventoryTransfer->refresh();

        $success = $data;
        $message = Lang::get("messages.inventory_transfer_updated_successfully");
        return sendResponse($success, $message);
    }

    public function getInventoryRecieveList()
    {
        if($this->permisssion !== "inventory-view")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }
        
        $user = auth()->user();
        // dd($user);
        $inventoryTransfers = InventoryTransfer::where('recipient', $user->id)->get();
        foreach($inventoryTransfers as $key => $value)
        {
            $inventory_sender = User::where('id',$value->sender)->first();
            $inventory_reciever = User::where('id',$value->recipient)->first();
            
            $value->senderName = $inventory_sender['first_name'] .''. $inventory_sender['last_name'] ?? null;
            $value->recipentName = $inventory_reciever['first_name'] .''.$inventory_reciever['last_name'] ?? null;

             $inventoryTransferProd = InventoryTransferProduct::select(DB::raw('SUM(send) AS total_send'),DB::raw('SUM(received) AS total_received'),DB::raw('SUM(broken) AS total_broken'))->where('inventory_transfer_id',$value->id)->first();
            
            $value->send = $inventoryTransferProd->total_send ?? null;
            $value->recieved = $inventoryTransferProd->total_received ?? null;
            $value->broken = $inventoryTransferProd->total_broken ?? null;
        }
        
        $success = $inventoryTransfers;
        $message = Lang::get("messages.inventory_transfer_list");
        return sendResponse($success, $message);
    }
    public function getInventoryRecieve($id)
    {
        
        if($this->permisssion !== "inventory-view")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }

        $user = auth()->user();
        $inventoryTransfer = InventoryTransfer::with('transferProducts','transferProducts.product','transferProducts.product.productFormat')->where('recipient', $user->id)->find($id);

        if(!$inventoryTransfer) {
            return sendError(Lang::get('messages.inventory_transfer_not_found'), Lang::get('messages.inventory_transfer_not_found'), 404);
        }
        $inventory_sender = User::where('id',$inventoryTransfer->sender)->first();
        $inventory_reciever = User::where('id',$inventoryTransfer->recipient)->first();
        
        $inventoryTransfer->senderName = $inventory_sender['first_name'] .' '. $inventory_sender['last_name'] ?? null;
        $inventoryTransfer->recipentName = $inventory_reciever['first_name'] .' '.$inventory_reciever['last_name'] ?? null;
        $success = $inventoryTransfer;
        $message = Lang::get("messages.inventory_transfer_detail");
        return sendResponse($success, $message);
    }
    public function updateInventoryRecieve(Request $request, $id)
    {
        if($this->permisssion !== "inventory-edit")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }

        $validator = Validator::make($request->all(), [
            // 'sender' => 'required|numeric|exists:users,id',
            // 'others'    => 'required|numeric',
            // // 'recepient' => 'required|numeric|exists:users,id',
            'products.*.inventory_id' => 'required|numeric|exists:inventories,id',
            'products.*.product_id' => 'required|numeric|exists:products,id',
            'products.*.batch' => 'required|string',
            'products.*.received' => 'required|numeric',
            'products.*.broken' => 'required|numeric'
        ]);

        if($validator->fails()) {
            return sendError(Lang::get('validation_error'), $validator->errors(), 422);
        }
        $user_id = auth()->user()->id;
        $validated = $request->all();
     
        $inventoryTransfer = InventoryTransfer::with('transferProducts')->find($id);
        // dd($inventoryTransfer);
        if(!$inventoryTransfer) {
            return sendError(Lang::get('messages.inventory_transfer_not_found'), Lang::get('messages.inventory_transfer_not_found'), 404);
        }
        
        // Update Inventory Transfer
        
        $inventoryTransfer->sender = $validated['sender'];
        // if($check->others == 0 && $validated['others'] ==0)
        // {
        $inventoryTransfer->recipient = $validated['recepient'];
        $inventoryTransfer->recipient_name = $validated["recepient_name"] ?? null;

        foreach($validated['products'] as $product)
        {
            $inventory = Inventory::where('id',$product['inventory_id'])->where('added_by',auth()->user()->id)->where('batch',$product['batch'])->first();
            // echo "$inventory->quantity";
            if(!empty($inventory))
            {

            $inventiory_transfer = InventoryTransferProduct::where('inventory_transfer_id',$request->id)->where('inventory_id',$product['inventory_id'])->first();
            // echo "$inventiory_transfer->inventory_id";
            if ($inventiory_transfer && $inventiory_transfer->received < intval($product['received']))
            {
                // $recieived_check = $inventiory_transfer->send;
                // echo " $recieived_check";
                $update_quantity1 = $product['received'] - ($inventiory_transfer->received);
            //    dd($update_quantity1);
                $update_quantity = ($inventory->quantity) + $update_quantity1;
                // dd($update_quantity);
                $check = Inventory::where('id',$inventiory_transfer->inventory_id)->update(['quantity' => $update_quantity]);
            }
            elseif($inventiory_transfer && $inventiory_transfer->received > intval($product['received'])){
                $update_quantity1 =  ($inventiory_transfer->received) - $product['received'];
                //    dd($update_quantity1);
                    $update_quantity = ($inventory->quantity) - $update_quantity1;
                    // echo "$update_quantity";
                    $check = Inventory::where('id',$inventiory_transfer->inventory_id)->update(['quantity' => $update_quantity]);
            } 
        }
        else{
            $inventory = Inventory::updateOrCreate([
                'added_by' => $user_id,
                'batch' => $product['batch'],
                'product_id' => $product['product_id']
            ],[
                // 'distributor_id' => $user_id,
                'quantity' => $product['received'],
                'warehouse_id' => 1,
                'aisle' => 1,
                'shelf' => 1,
                'aisle_name' =>'a',
                'shelf_name' => 'b'
            ]);
        }
            // Update Inventory Transfer
            $inventoryTransfer->transferProducts()->updateOrCreate([
                'product_id' => $product['product_id']
            ], [
                'inventory_id' => $product['inventory_id'],
                'batch' => $product['batch'],
                'received' => $product['received'],
                'broken' => $product['broken'],
                
            ]);
          

        }
            
        // }

        $inventoryTransfer->save();
        $data = $inventoryTransfer->refresh();

        $success = $data;
        $message = Lang::get("messages.inventory_transfer_updated_successfully");
        return sendResponse($success, $message);
    }
}
