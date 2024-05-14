<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;
use App\Models\User;
use App\Models\DwcRoute;
use App\Models\SupplierDistributor;
use App\Models\DistributorGroup;

use Lang;
use Auth;

class DistributorController extends Controller
{
    public $permission;

    public function __construct()
    {
        $headers = getallheaders();
        
        $this->permission = isset($headers['permission']) ? $headers['permission'] : "";
    }

    public function retailersList(Request $request)
    {
        if($this->permission !== "retailer-view")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }
        
        $search = $request->input("search");
        $user_id = auth()->user()->id;
        $user_type_id = 4;
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

    public function addRouteToRetailer(Request $request)
    {
        if($this->permission !== "routes-edit")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }

        $rules = [
            'route_id' => 'required',
            'user_ids' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        
        if($validator->fails()) {
            return sendError(Lang::get('validation_error'), $validator->errors(), 422);
        }

        $user_ids = $request->input("user_ids");
        $route_id = $request->input("route_id");
        $routesData = DwcRoute::find($route_id);
        $removeUserIds = [];
        foreach($user_ids as $k=>$u_id){
            $userCountData = User::where("id",$u_id)->withCount('userRoutes')->first();
            // dd($userCountData);
            if($userCountData->user_routes_count >= 5){
                // unset($user_ids[$k]);
                $removeUserIds[] = $u_id;
            }
        }
        $user_ids = array_filter($user_ids, function($e) use ($removeUserIds) {
            return (!in_array($e,$removeUserIds));
        });
        $routesData->routeUsers()->syncWithoutDetaching($user_ids);
        $success  = $user_ids;
        $message  = Lang::get("messages.assigned_successfully");
        return sendResponse($success, $message);
    }

    public function createGroupDistributor(Request $request)
    { 
        if ($this->permission !== "supplier-edit") {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }
        
        $user_id = auth()->user()->id;
        
        $rules = [
            'distributor_name' => 'required',
            'distributor_email' => 'required|email',
        ];
        
        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
            return sendError(Lang::get('validation_error'), $validator->errors(), 422);
        }
        
        $distributor_name = $request->input("distributor_name");
        $distributor_email = $request->input("distributor_email");
        
        try {
            $groupDistributor = new DistributorGroup();
            $groupDistributor->distributor_name = $distributor_name;
            $groupDistributor->distributor_email = $distributor_email;
            $groupDistributor->supplier_id = $user_id;
            $groupDistributor->save();
        
            $success = $groupDistributor->toArray();
            $message = Lang::get("messages.assigned_successfully");
        
            return sendResponse($success, $message);
        } catch (\Exception $e) {
            return sendError('Error', ['error' => $e->getMessage()], 500);
        }
    }

    public function removeRetailerFromRoutes(Request $request)
    {
        if($this->permission !== "routes-edit")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }

        $rules = [
            'user_ids' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        
        if($validator->fails()) {
            return sendError(Lang::get('validation_error'), $validator->errors(), 422);
        }

        $user = auth()->user();

        $user_ids = $request->input("user_ids");
        $routeList = DwcRoute::where("user_id","=",$user->id)->pluck("id");
        if($routeList->count() > 0 )
        {
            $routeIds = $routeList->toArray();
            foreach($user_ids as $user_id){
                \DB::table("user_routes")->where("user_id",$user_id)->whereIn('dwc_route_id',$routeIds)->delete();
            }
            $message  = Lang::get("messages.removed_successfully");
            $success  = [];
            return sendResponse($success, $message);
        }
        else{
            // $message = Lang::get("messages.no_route_found");
            return sendError('Not Found', ['error' => Lang::get("messages.no_route_found")], 404);
        }
    }

    public function getLinkedSuppliers(Request $request)
    {
        if($this->permission !== "supplier-view")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }
        $user = auth()->user();

        $suppliers = SupplierDistributor::where('distributor_id', $user->id);
        if($suppliers->count() > 0 )
        {
            $supplierIds = $suppliers->pluck('supplier_id')->toArray();
            $data = User::whereIn('id', $supplierIds)->with(['userProfile','userMainAddress'])->get();

            $success = $data;
            $message = Lang::get("messages.suppliers_linked_to_distributor_fetched_successfully");
            return sendResponse($success, $message);
        }
        else{
            return sendError('Not Found', ['error' => Lang::get("messages.no_linked_user_found")], 404);
        }
    }

    public function supplierList(Request $request)
    {
        if($this->permission !== "supplier-view")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }
        $user = auth()->user();

        $suppliers = SupplierDistributor::where('distributor_id', $user->id);
        if($suppliers->count() > 0 )
        {
            $supplierIds = $suppliers->pluck('supplier_id')->toArray();
            $search = $request->input("search");
            $usersQuery = User::query();
            if(!empty($search)) {
                $usersQuery->where(function($query)use($search){
                    $query->where(function($query1)use($search){
                        $query1->where("first_name",'LIKE',"%".$search."%")->orWhere("last_name",'LIKE',"%".$search."%")->orWhere("email",'LIKE',"%".$search."%")->orWhere("phone_number",'LIKE',"%".$search."%")->orWhereRaw("concat(first_name,' ',last_name) like '%".$search."%'");
                    });
                });
            }
            $data = $usersQuery->whereIn('id', $supplierIds)->with(['userProfile','userMainAddress'])->get();

            $success = $data;
            $message = Lang::get("messages.supplier_user_list");
            return sendResponse($success, $message);
        }
        else{
            $success=[];
            $message = Lang::get("messages.no_linked_user_found");
            return sendResponse($success, $message);
        }
    }
    
}
