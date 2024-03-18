<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\UserBillingAddress;
use App\Models\RetailerSupplierRequest;
use App\Models\Order;
use App\Traits\UserTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;

class RetailerController extends Controller
{
    use UserTrait;
    public $permission;

    public function __construct(Request $request){
        $headers = getallheaders();
        $this->permission = $headers['permission'] ? $headers['permission'] : "";
    }

    public function getSupplierListOnDashboard(Request $request)
    {
        if($this->permission != 'dashboard-view'){
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }
        $usersQuery = User::query();
        $usersQuery->where(function($query){
            $query = $query->whereHas('userMainAddress',function($query1){
                $query1->whereNotNull("latitude");
            });
        });
        $usersQuery->where("user_type_id","=","3");
        $usersQuery->select('id','first_name','last_name')->with(['userMainAddress']);
        $data = $usersQuery->get();
        $success  = $data;
        $message  = Lang::get("messages.supplier_user_list");
        return sendResponse($success, $message);
    }

    public function getLocalSuppliers(Request $request)
    {
        if($this->permission != 'dashboard-view'){
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }
        $data = $this->localSupplierUsers($request);
        $success  = $data;
        $message  = Lang::get("messages.supplier_user_list");
        return sendResponse($success, $message);
    }

    public function sendRequestToSupplier(Request $request)
    {
        if($this->permission != 'dashboard-view'){
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }
        $user_id = Auth::user()->id;
        $validator = Validator::make($request->all(), [
            'supplier_id' => 'required',
            'request_note' => 'required',
        ]);
        if ($validator->fails()) return sendError(Lang::get('validation_error'), $validator->errors(), 422);

        $supplier_id = $request->input("supplier_id");
        $requestData = RetailerSupplierRequest::where("retailer_id","=",$user_id)->where("supplier_id","=",$supplier_id)->get();
        // dd($requestData->count());
        if($requestData->count() < 1)
        {
            RetailerSupplierRequest::create([
                "retailer_id" => $user_id,
                "supplier_id" => $supplier_id,
                "request_note" => $request->input("request_note"),
            ]);
            $success = [];
            $message  = Lang::get("messages.request_sent_successfully");
            return sendResponse($success, $message);
        }
        else{
            $success = [];
            $message  = Lang::get("messages.already_request_sent_successfully");
            return sendResponse($success, $message);
        }
    }

    public function suppliersList(Request $request)
    {
        if($this->permission !== "supplier-view")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }
        $user_id = auth()->user()->id;

        $data = RetailerSupplierRequest::with(['supplierInformation'])->orWhereHas('supplierInformation',function($query){
                $query->whereHas("userMainAddress",function($query1){
                    $query1->whereNotNull("latitude");
                    $query1->whereNotNull("longitude");
                });
            })->where("retailer_id","=",$user_id)
            ->get();
        $success  = $data;
        $message  = Lang::get("messages.supplier_user_list");
        return sendResponse($success, $message);
    }


    public function suppliersAllList(Request $request)
    {
        if($this->permission !== "supplier-view")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }
        $user = auth()->user();

        $data = User::with('userMainAddress')->where('user_type_id',3)->whereHas('userMainAddress', function ($query) {
            $query->whereNotNull('latitude')->whereNotNull('longitude');
        })
        ->get();
            ;
        // $supplier_data = RetailerSupplierRequest::with(['supplierInformation'])->orWhereHas('supplierInformation',function($query){
        //     $query->whereHas("userMainAddress",function($query1){
        //         $query1->whereNotNull("latitude");
        //         $query1->whereNotNull("longitude");
        //     });
        // })->where("retailer_id","=",$user_id)
        // ->get();
        // Create an array with supplier IDs
        //$supplierIds = $supplier_data->pluck('supplier_id')->all();

        // Map each item in the $data collection
        $data = $data->map(function ($item) use($user){
            // Check if the user's ID exists in the supplier IDs
            // if (in_array($item->id, $supplierIds)) {
            //     // If it exists, set the status from the $supplier_data
            //     $supplierEntry = $supplier_data->where('supplier_id', $item->id)->first();
            //     $item->status = $supplierEntry->status; // Replace 'status' with the actual column name
            // } else {
            //     // If it doesn't exist, set a static status (e.g., 3)
            //     $item->status = 3;
            // }

            $item->supplier_status = 0;
            $item->supplier_data = [];
            $supplier_data = RetailerSupplierRequest::where(['retailer_id'=>$user->id,'supplier_id'=>$item->id])->first();
            if($supplier_data)
            {
                $item->supplier_status = $supplier_data->status;
                $item->supplier_data = $supplier_data;
            }
            return $item;
        });
        $success  = $data;
        $message  = Lang::get("messages.supplier_user_list");
        return sendResponse($success, $message);
    }

    public function getOrderListOnDashboard(Request $request)
    {
        if($this->permission !== "dashboard-view")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }
        $user = auth()->user();

        $data["orders"] = Order::with(['supplierInformation'])->where("added_by","=",$user->id)->latest()->take(3)->get();
        $success  = $data;
        $message  = Lang::get("messages.order_list");
        return sendResponse($success, $message);
    }

    public function retailerList(Request $request)
    {
        // dd(888);
        if($this->permission !== "retailer-view")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }

        $search = $request->input("search");
        $user_id = auth()->user()->id;
        $user_type_id = 4;
        // $data = RetailerSupplierRequest::where('supplier_id',$user_id)->first();
        // $check = User::where('id',$data->supplier_id)->first();
        // dd($check);
        $usersQuery = User::query();
        if(!empty($search)) {
            $usersQuery->where(function($query)use($search){
                $query->where(function($query1)use($search){
                    $query1->where("first_name",'LIKE',"%".$search."%")->orWhere("last_name",'LIKE',"%".$search."%")->orWhere("email",'LIKE',"%".$search."%")->orWhere("phone_number",'LIKE',"%".$search."%")->orWhereRaw("concat(first_name,' ',last_name) like '%".$search."%'");
                });
                $query = $query->orWhereHas('userRoutes',function($query1) use($search){
                    $query1->where("name",'LIKE',"%".$search."%");
                });
            });
        }
        $filter_retailer_id = $request->input("filter_retailer_id");
        if(!empty($filter_retailer_id)){
            $usersQuery->where(function($query)use($filter_retailer_id){
                $query->where("id","=",$filter_retailer_id);
            });
        }
        $filter_route_id = $request->input("filter_route_id");
        if(!empty($filter_route_id)){
            $usersQuery->whereHas('userRoutes', function($query)use($filter_route_id){
                $query->where("id","=",$filter_route_id);
            });
        }
        // $usersQuery->whereHas('userRoutes', function($query)use($user_id){
        //     $query->whereHas('userInformation',function($query1)use($user_id){
        //         $query1->where("id",$user_id);
        //     });
        // });
        $usersQuery->where("user_type_id","=",$user_type_id);
        $usersQuery->where("status","=","1");
        $usersQuery->with(['userProfile','userMainAddress']);
        $usersQuery->with('userRoutes',function($query)use($user_id){
            $query->whereHas('userInformation',function($query1)use($user_id){
                $query1->where("id",$user_id);
            });
        });
        $data = $usersQuery->get();
        $success  = $data;
        $message  = Lang::get("messages.retailer_user_list");
        return sendResponse($success, $message);
    }
    public function retailerListDetail(Request $request,$id="")
    {
        if($this->permission !== "retailer-view")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }
        $userData = $this->getUserData($id,"4");
        return $userData;
    }
}
