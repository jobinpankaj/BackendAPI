<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\UserBillingAddress;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Lang;
use Auth;
use DB;
use App\Models\Product;
use Carbon\Carbon;
class RolesAndPermissionController extends Controller
{
    public $permission;
    public $guard_name;

    public function __construct()
    {
        $headers = getallheaders();

        $this->permission = isset($headers['permission']) ? $headers['permission'] : "";
    }
    public function getPermission(request $request)
    {
        // dd(auth()->user()->id);
        if($this->permission !== "role-view")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }
        // $guardName = 'api';
        // $user_id = auth()->user()->id;
        // $role_name = "user_role_".$user_id;
        // $roles = Role::where("name",$role_name)->first();
        // $permissions = Permission::where('module_name','!=','role-management')->pluck('id','id')->all();
        // // dd($permissions);
        // $roles->syncPermissions($permissions);
        // // $user->assignRole([$roles>id]);
        // $success  = $roles;
        // $message  = Lang::get("messages.permission_list");
        $permissions = array(
            // 'role-management' => [
            //     "1" => "role-view",
            //     "2" => "role-edit"
            // ],
            // 'user-management' => [
            //     "3" => "user-view",
            //     "4" => "user-edit"
            // ],
            'retailers-management' => [
                "5" => "retailer-view"
            ],
            'dashboard-management' => [
                "11" => "dashboard-view"
            ],
            'order-management' => [
                "13"=>"order-view",
                "14" => "order-edit"
            ],
            'inventory-management' => [
                "15" => "inventory-view",
                "16" => "inventory-edit"
            ],
            'product-management' => [
                "17" => "product-view",
                "18" => "product-edit"
            ],
            'shipment-management' => [
                "21" => "shipment-view",
                "22" => "shipment-edit"
            ],
            'reports-management' => [
                "23" => "reports-view",
            ],
            'groups-management' => [
                "25" => "groups-view",
                "26" => "groups-edit"
            ],
            'pricing-management' => [
                "27" => "pricing-view",
                "28" => "pricing-edit"
            ]
        );
        $success['permissions']  = $permissions;
        $message   = Lang::get("messages.permission_list");
        return sendResponse($success, $message);

    }
     // Add Role
     public function addRole(Request $request)
     {

         if($this->permission !== "role-edit")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }
         $validator = Validator::make($request->all(), [
             'name' => 'required|unique:roles',
             'permissions' => 'required'
         ]);

         if ($validator->fails()) return sendError(Lang::get('messages.validation_error'), $validator->errors(), 422);
         {

            $permissionsArr = explode(",",$request->input("permissions"));
            $insertData = array(
                            "name" => $request->input("name"),
                            'role_name' => $request->input("name"),
                            "guard_name" => 'api',
                            "parent_id"  => auth()->user()->id,
                            );
            $role = Role::create($insertData);
            $permissions = Permission::whereIn("id",$permissionsArr)->where("guard_name","=",'api')->pluck('id','id');

            $role->syncPermissions($permissions);
            $success['data']  = $role;
            $message          = Lang::get("messages.role_created");
            return sendResponse($success, $message);
         }
     }
     public function SupplierRoleList(request $request)
     {
         if($this->permission !== "role-view")
         {
             return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
         }
         $data = Role::where('parent_id',auth()->user()->id)->get();
         $success  = $data;
         $message  = Lang::get("messages.roles_list");
         return sendResponse($success, $message);

     }
     public function viewSupplier(request $request,$id)
     {

        $validator = Validator::make($request->all(), [
            'id' => 'required',
            // 'permissions' => 'required',
        ]);
        if($this->permission !== "role-view")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }
        $data = Role::where('id',$request->id)->first();
        $success  = $data;
        $message  = Lang::get("messages.roles_details");
        return sendResponse($success, $message);
     }
     public function storeSupplierPermissions(request $request,$id)
     {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'name' => 'required',
        ]);
        if($this->permission !== "role-edit")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }
        $data = Role::where('id',$request->id)->first();
        if(!empty($data))
        {
           Role::where('id',$data->id)->update([
            'name' => $request->name,
           ]);
        }
        $success  = $data;
        $message  = Lang::get("messages.roles_updated_successfully");
        return sendResponse($success, $message);
     }
     public function addSupplierUser(Request $request)
     {
         if($this->permission !== "user-edit")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }
         $validator = Validator::make($request->all(), [
             'first_name' => 'required',
             'last_name' => 'required',
             'address' => 'required',
             'email' => 'required|email|unique:users',
             'mobile' => 'required',
             'country' => 'required',
             'state' => 'required',
            //  'country' => 'required',
             'city' => 'required',
             'role' => 'required',
             'password' => 'required',
             'confirm_password' => 'required',
             'is_enable'    => 'required'

         ]);
         if($validator->fails()) {
            return sendError(Lang::get('validation_error'), $validator->errors(), 422);
        }
        // dd($request->all());s
         $user = auth()->user();
        //  dd($user->user_type_id);
        $data = new User();
        $data->first_name = $request->first_name;
        $data->last_name = $request->last_name;
        $data->address = $request->address;
        $data->email = $request->email;
        $data->phone_number = $request->mobile;
        $data->country = $request->country;
        $data->state = $request->state;
        $data->city = $request->city;
        $data->role_id = $request->role;
        $data->is_enable = $request->is_enable;
        if($request->input("password")){
            $data->password = Hash::make($request->input("password"));
        }
        if($request->file("user_image")){
            $userImage = $request->file("user_image");
            $res = $userImage->store('profile_images',['disk'=>'public']);
            $data->user_image = $res;
            // $user->save();
        }
        $current_date_time = Carbon::now()->toDateTimeString();
        // dd($current_date_time);
        $data->email_verified_at = $current_date_time;
        $data->user_type_id = $user->user_type_id;
        $data->added_by = $user->id;
        $data->save();
        if($data->save())
        {
            $user_id = $data->id;
            $userData = User::find($user_id);
            $role_name = "user_role_".$user_id;
            $role = Role::where("id",$request->role)->update(['name' => $role_name]);
            // $role = Role::update(['name' => $role_name]);

            $userData->assignRole($role);

        }


        // $data = User::create($requestData);
        $success['data']  = $data;
        $message          = Lang::get("messages.role_created");
        return sendResponse($success, $message);

     }
      // Delete Role
    public function deleteRole(Request $request)
    {
        if($this->permission !== "role-edit")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }
        $validator = Validator::make($request->all(), [
            'id' => 'required'
        ]);

        if ($validator->fails()) return sendError(Lang::get('messages.validation_error'), $validator->errors(), 422);

        $role = Role::find($request->input("id"));
        if($role)
        {
            $role->delete();
            $success['data']  = [];
            $message          = Lang::get("messages.role_deleted");
            return sendResponse($success, $message);
        }
        else {
            return sendError(Lang::get('messages.not_found'), Lang::get('messages.not_found'), 404);
        }
    }

    //GetUserData
    public function getUserList(request $request)
    {
        if($this->permission !== "user-view")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }
        $user = User::where("added_by",auth()->user()->id)->get();
        foreach($user as $key => $value)
        {
            $role= Role::where('id',$value->role_id)->first();
            // dd($role);
            $value->role_id = $role->id ?? null;
            $value->role_name = $role->role_name ?? null;
        }
        $success  = $user;
        $message  = Lang::get("messages.user_list");
        return sendResponse($success, $message);

    }
    public function getretailerPermission(request $request)
    {
        if($this->permission !== "supplier-view")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }
        // $guardName = 'api';
        // $user_id = auth()->user()->id;
        // $role_name = "user_role_".$user_id;
        // $roles = Role::where("name",$role_name)->first();
        // $permissions = Permission::where('module_name','!=','role-management')->pluck('id','id')->all();
        // // dd($permissions);
        // $roles->syncPermissions($permissions);
        // // $user->assignRole([$roles>id]);
        // $success  = $roles;
        // $message  = Lang::get("messages.permission_list");
        $permissions = array(
            // 'role-management' => [
            //     "1" => "role-view",
            //     "2" => "role-edit"
            // ],
            // 'user-management' => [
            //     "3" => "user-view",
            //     "4" => "user-edit"
            // ],
            'retailers-management' => [
                "5" => "retailer-view"
            ],
            'dashboard-management' => [
                "11" => "dashboard-view"
            ],
            'order-management' => [
                "13"=>"order-view",
                "14" => "order-edit"
            ],
            'inventory-management' => [
                "15" => "inventory-view",
                "16" => "inventory-edit"
            ],
            'product-management' => [
                "17" => "product-view",
                "18" => "product-edit"
            ],
            'shipment-management' => [
                "21" => "shipment-view",
                "22" => "shipment-edit"
            ],
            'reports-management' => [
                "23" => "reports-view",
            ],
            'groups-management' => [
                "25" => "groups-view",
                "26" => "groups-edit"
            ],
            'pricing-management' => [
                "27" => "pricing-view",
                "28" => "pricing-edit"
            ]
        );
        $success['permissions']  = $permissions;
        $message   = Lang::get("messages.permission_list");
        return sendResponse($success, $message);

    }
     // Add Role
     public function addretailerRole(Request $request)
     {

         if($this->permission !== "supplier-view")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }
         $validator = Validator::make($request->all(), [
             'name' => 'required|unique:roles',
             'permissions' => 'required'
         ]);

         if ($validator->fails()) return sendError(Lang::get('messages.validation_error'), $validator->errors(), 422);
         {

            $permissionsArr = explode(",",$request->input("permissions"));
            $insertData = array(
                            "name" => $request->input("name"),
                            "guard_name" => 'api',
                            "parent_id"  => auth()->user()->id,
                            );
            $role = Role::create($insertData);
            $permissions = Permission::whereIn("id",$permissionsArr)->where("guard_name","=",'api')->pluck('id','id');

            $role->syncPermissions($permissions);
            $success['data']  = $role;
            $message          = Lang::get("messages.role_created");
            return sendResponse($success, $message);
         }
     }
     public function retailerRoleList(request $request)
     {
         if($this->permission !== "supplier-view")
         {
             return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
         }
         $data = Role::where('parent_id',auth()->user()->id)->get();
         $success  = $data;
         $message  = Lang::get("messages.roles_list");
         return sendResponse($success, $message);

     }
     public function viewretailer(request $request,$id)
     {

        $validator = Validator::make($request->all(), [
            'id' => 'required',
            // 'permissions' => 'required',
        ]);
        if($this->permission !== "supplier-view")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }
        $data = Role::where('id',$request->id)->first();
        $success  = $data;
        $message  = Lang::get("messages.roles_details");
        return sendResponse($success, $message);
     }
     public function storeretailerPermissions(request $request,$id)
     {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'name' => 'required',
        ]);
        if($this->permission !== "supplier-view")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }
        $data = Role::where('id',$request->id)->first();
        if(!empty($data))
        {
           Role::where('id',$data->id)->update([
            'name' => $request->name,
           ]);
        }
        $success  = $data;
        $message  = Lang::get("messages.roles_updated_successfully");
        return sendResponse($success, $message);
     }
     public function addretailerUser(Request $request)
     {
         if($this->permission !== "supplier-view")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }
         $validator = Validator::make($request->all(), [
             'first_name' => 'required',
             'last_name' => 'required',
             'address' => 'required',
             'email' => 'required|email|unique:users',
             'mobile' => 'required',
             'country' => 'required',
             'state' => 'required',
            //  'country' => 'required',
             'city' => 'required',
             'role' => 'required',
             'password' => 'required',
             'confirm_password' => 'required',
             'is_enable'    => 'required'

         ]);
         if($validator->fails()) {
            return sendError(Lang::get('validation_error'), $validator->errors(), 422);
        }
         $user = auth()->user();
        //  dd($user->user_type_id);
        $data = new User();
        $data->first_name = $request->first_name;
        $data->last_name = $request->last_name;
        $data->address = $request->address;
        $data->email = $request->email;
        $data->phone_number = $request->mobile;
        $data->country = $request->country;
        $data->state = $request->state;
        $data->city = $request->city;
        $data->role_id = $request->role;
        $data->is_enable = $request->is_enable;
        if($request->input("password")){
            $data->password = Hash::make($request->input("password"));
        }
        if($request->file("user_image")){
            $userImage = $request->file("user_image");
            $res = $userImage->store('profile_images',['disk'=>'public']);
            $data->user_image = $res;
            // $user->save();
        }
        $current_date_time = Carbon::now()->toDateTimeString();
        // dd($current_date_time);
        $data->email_verified_at = $current_date_time;
        $data->user_type_id = $user->user_type_id;
        $data->added_by = $user->id;
        $data->save();


        // $data = User::create($requestData);
        $success['data']  = $data;
        $message          = Lang::get("messages.role_created");
        return sendResponse($success, $message);

     }
      // Delete Role
    public function deleteretailerRole(Request $request)
    {
        if($this->permission !== "supplier-view")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }
        $validator = Validator::make($request->all(), [
            'id' => 'required'
        ]);

        if ($validator->fails()) return sendError(Lang::get('messages.validation_error'), $validator->errors(), 422);

        $role = Role::find($request->input("id"));
        if($role)
        {
            $role->delete();
            $success['data']  = [];
            $message          = Lang::get("messages.role_deleted");
            return sendResponse($success, $message);
        }
        else {
            return sendError(Lang::get('messages.not_found'), Lang::get('messages.not_found'), 404);
        }
    }

    //GetUserData
    public function getretailerUserList(request $request)
    {
        if($this->permission !== "supplier-view")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }
        $user = User::where("added_by",auth()->user()->id)->get();
        foreach($user as $key => $value)
        {
            $role= Role::where('id',$value->role_id)->first();
            // dd($role);
            $value->role_id = $role->id ?? null;
            $value->role_name = $role->name ?? null;
        }
        $success  = $user;
        $message  = Lang::get("messages.user_list");
        return sendResponse($success, $message);

    }
    public function topRetailerList(request $request)
     {

        if($this->permission == "groups-view")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }
        //$orders = Product::select('products.id')->get();
      $orders = Product::select('products.id', 'products.product_name','products.user_id AS Added_By','orders.added_by')
       ->join('order_items', 'products.id', '=', 'order_items.product_id')
       ->join('orders', 'order_items.order_id', '=', 'orders.id')
       ->selectRaw('SUM(order_items.quantity) as total_quantity_sold,COUNT(order_items.product_id) as total_sold')
       ->groupBy('products.id', 'order_items.product_id')
       ->orderBy('total_sold', 'desc')
       ->get();
    //    **/
        // dd($orders);
        $users = User::all();
        $mappedData = $orders->map(function ($order) use ($users) {
            $user = $users->where('id', $order->Added_By)->first();
            echo "$user";
            return [
                'order_id' => $order->id,
                'Added By' => $user ? $user->first_name : 'N/A',
            ];
        });
        $success  = $orders;
        $message  = Lang::get("messages.topRetailerList");
        return sendResponse($success, $message);
     }
}
