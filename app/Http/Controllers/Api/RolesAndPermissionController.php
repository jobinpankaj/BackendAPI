<?php
namespace App\Http\Controllers\Api;

use Stichoza\GoogleTranslate\GoogleTranslate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Pdf;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Test;
use App\Models\Order;
use App\Models\ProductStyle;
use App\Models\ProductFormat;
use App\Models\Inventory;
use App\Models\Warehouse;
use App\Models\CustomReports;
use App\Models\SalesReports;
use App\Models\UserBillingAddress;
use App\Models\BusinessCategory;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Lang;
use Auth;
use DB;
use App\Models\Product;
use App\Models\SupplierReports;
use App\Models\InventoryReports;
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
            'suppliers-management' => [
                "9" => "supplier-view"
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
            'suppliers-management' => [
                "9" => "supplier-view"
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
        if($this->permission !== "dashboard-view")
       {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
       }
        //$orders = Product::select('products.id')->get();
      $orders = Product::select('products.product_name', 'order_items.price', DB::raw('CONCAT(users.first_name," ",users.last_name) as fullname'), 'order_items.sub_total',)
      // $orders = Product::select("*")
       ->join('order_items', 'products.id', '=', 'order_items.product_id')
       ->join('orders', 'order_items.order_id', '=', 'orders.id')
       ->join('users', 'users.id', '=', 'orders.retailer_id')
       ->selectRaw('SUM(order_items.quantity) as total_quantity_sold,COUNT(order_items.product_id) as total_sold')
       ->groupBy('products.id', 'order_items.product_id')
       ->orderBy('order_items.sub_total', 'desc')->limit(6)
       ->get();
    //    **/
        //dd($orders);
      //  $users = User::all();
      //  $mappedData = $orders->map(function ($order) use ($users) {
      //      $user = $users->where('id', $order->Added_By)->first();
      //      echo "$user";
      //      return [
    //            'order_id' => $order->id,
    //            'Added By' => $user ? $user->first_name : 'N/A',
    //        ];
    //    });
        $success  = $orders;
        $message  = Lang::get("messages.topRetailerList");
        return sendResponse($success, $message);
     }

     public function topProductList(request $request)
    {
         if($this->permission !== "dashboard-view")
         {
             return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
         }
         //$orders = Product::select('products.id')->get();
     $orders = Product::select('products.product_name', 'order_items.price', 'users.first_name', 'order_items.sub_total',)
   // $orders = Product::select("*")
        ->join('order_items', 'products.id', '=', 'order_items.product_id')
        ->join('orders', 'order_items.order_id', '=', 'orders.id')
        ->join('users', 'users.id', '=', 'orders.retailer_id')
        ->selectRaw('SUM(order_items.quantity) as total_quantity_sold,COUNT(order_items.product_id) as total_sold')
        ->groupBy('products.id', 'order_items.product_id')
        ->orderBy('total_quantity_sold', 'desc')->limit(6)
        ->get();
     //    **/

       //  $users = User::all();
       //  $mappedData = $orders->map(function ($order) use ($users) {
       //      $user = $users->where('id', $order->Added_By)->first();
       //      echo "$user";
       //      return [
     //            'order_id' => $order->id,
     //            'Added By' => $user ? $user->first_name : 'N/A',
     //        ];
     //    });
        $success  = $orders;
        $message  = Lang::get("messages.topRetailerList");
        return sendResponse($success, $message);
        }

    public function GetSuppliersProductsName(request $request)
    {
      if($this->permission !== "reports-view")
    {
      return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
    }
//    $user_id = Auth::user()->id;
        $user_id = 101;
        $orders = Product::select("products.product_name")
        ->where('products.user_id', '=', $user_id)
        ->groupBy("products.product_name")
        ->get();
        $success  = $orders;
        $message  = Lang::get("messages.topRetailerList");
        return sendResponse($success, $message);
    }

    public function GetSuppliersProductsType(request $request)
    {
      if($this->permission !== "reports-view")
    {
        return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
    }
      $user_id = Auth::user()->id;
    //$user_id = 101;
      $orders = Product::select("products.product_type")
      ->groupBy("products.product_type")
      ->get();
      $success  = $orders;
      $message  = Lang::get("messages.topRetailerList");
      return sendResponse($success, $message);
     }

    public function GetSuppliersProductStyle(request $request)
    {
      if($this->permission !== "reports-view")
    {
          return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
    }
      $user_id = Auth::user()->id;
      //$user_id = 101;
      $orders = ProductStyle::select("product_styles.name")
      ->groupBy("product_styles.name")
      ->get();
      $success  = $orders;
      $message  = Lang::get("messages.topRetailerList");
      return sendResponse($success, $message);
      }

    public function GetSuppliersProductFormat(request $request)
    {
        if($this->permission !== "reports-view")
      {
          return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
      }
        $user_id = Auth::user()->id;
        //$user_id = 101;
        $orders = ProductFormat::select("product_formats.name")
        ->groupBy("product_formats.name")
        ->get();
        $success  = $orders;
        $message  = Lang::get("messages.topRetailerList");
        return sendResponse($success, $message);
        }

    public function GetRetailers(request $request)
    {
    if($this->permission !== "reports-view")
    {
      return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
    }
        $user_id = Auth::user()->id;
        $orders = User::select("first_name")
        ->where('users.added_by', '=', $user_id)
        ->where('users.user_type_id', '=', 4)
        ->get();
       $success  = $orders;
       $message  = Lang::get("messages.topRetailerList");
       return sendResponse($success, $message);
   }

   public function GetRetailersCity(request $request)
   {
   if($this->permission !== "reports-view")
   {
       return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
   }
       $user_id = Auth::user()->id;
       $orders = User::select("city")
        ->where('users.added_by', '=', $user_id)
        ->where('users.user_type_id', '=', 4)
        ->whereNotNull('city')
        ->groupBy('city')
        ->get();
        $success  = $orders;
        $message  = Lang::get("messages.topRetailerList");
        return sendResponse($success, $message);
    }

   public function GetSuppliergroup(request $request)
   {
   if($this->permission !== "reports-view")
   {
      return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
    }
       $user_id = Auth::user()->id;
     //$user_id = 101;
       $orders = User::select("groups.name")
       ->join('groups', 'groups.added_by', '=', 'users.id')
       ->groupBy('groups.name')
       ->get();
       $success  = $orders;
       $message  = Lang::get("messages.topRetailerList");
       return sendResponse($success, $message);
    }

    public function GetRetailersList(request $request)
    {
    if($this->permission !== "reports-view")
    {
      return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
    }
    //    $user_id = Auth::user()->id;
        //$user_id =4;
        $orders = User::select("user_profiles.user_id", "user_profiles.business_name")
        ->join('user_profiles', 'users.id', '=', 'user_profiles.user_id')
        //->where('users.added_by', '=', $user_id)
        ->where('users.user_type_id','=', 4)
        ->get();
        $success  = $orders;
        $message  = Lang::get("messages.topRetailerList");
        return sendResponse($success, $message);
    }

    public function GetDistributorsList(request $request)
    {
    if($this->permission !== "reports-view")
    {
      return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
    }
        $user_id = Auth::user()->id;
      //$user_id =4;
        $orders = User::select("user_profiles.user_id", "user_profiles.company_name")
        ->join('user_profiles', 'users.id', '=', 'user_profiles.user_id')
        //->where('users.added_by', '=', $user_id)
        ->where('user_type_id','=', 2)
        ->get();
        $success  = $orders;
        $message  = Lang::get("messages.topRetailerList");
        return sendResponse($success, $message);
        }

    public function GetSuppliersList(request $request)
    {
    if($this->permission !== "reports-view")
    {
      return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
    }
      //  $user_id = Auth::user()->id;
        $orders = User::select("user_profiles.user_id", "user_profiles.company_name")
        ->join('user_profiles', 'users.id', '=', 'user_profiles.user_id')
        ->where('users.user_type_id','=', 3)
        ->get();
        $success  = $orders;
        $message  = Lang::get("messages.topRetailerList");
        return sendResponse($success, $message);
     }

    public function GetCompanyName(request $request)
    {
    if($this->permission !== "reports-view")
    {
      return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
    }
        $user_id = Auth::user()->id;
        $orders = UserProfile::select("user_id","company_name")
        ->where('user_id', '=', $user_id)
        ->get();
        $success  = $orders;
        $message  = Lang::get("messages.topRetailerList");
        return sendResponse($success, $message);
        }

    public function GetCadCsp(request $request)
    {
  if($this->permission !== "reports-view")
    {
      return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
    }
        //$user_id = Auth::user()->id;
        $orders = User::select("business_categories.id","business_categories.name")
        ->join("user_profiles","user_profiles.user_id","=","users.id")
        ->join("business_categories", "business_categories.id","=","user_profiles.business_category_id")
        //->where("users.added_by", "=", $user_id)
        ->groupBy("name")
        ->orderBy("id")
        ->get();
        $success  = $orders;
        $message  = Lang::get("messages.topRetailerList");
        return sendResponse($success, $message);
         }

    public function GetWarehousesList(request $request)
    {
    if($this->permission !== "reports-view")
    {
      return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
    }
        $user_id = Auth::user()->id;
          // $user_id = 101;
        $orders = User::select("warehouses.id","warehouses.name","warehouses.user_id")
        ->join("warehouses", "warehouses.user_id", "=", "users.id")
        ->groupBy("warehouses.name")
        ->get();
        $success  = $orders;
        $message  = Lang::get("messages.topRetailerList");
        return sendResponse($success, $message);
        }

    public function GetUsersList(request $request)
    {
    if($this->permission !== "reports-view")
    {
      return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
    }

        $orders = \DB::select("SELECT id,first_name,last_name,email,status,user_type_id FROM users WHERE status!=1 AND user_type_id != 1");
        $success  = $orders;
        $message  = Lang::get("messages.topRetailerList");
        return sendResponse($success, $message);
        }

    public function PostReportProductList(request $request)
    {
      if($this->permission !== "reports-view")
      {
        return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
    }
    //  else{

    //    return "Successfully";
    //  }
       $user_id = Auth::user()->id;

//dd("success");
/*
       $user_id = 101;
       $cad= "11";
       $type= "xlsx";
       $from_date= "2023-10-10";
       $group= "Tout marchands";
       $invoice_state= "Pending";
       $lang= "CAfr";
       $order_state= "Approved";
       $product_format= "Can 473ml x 24";
       $product_name= "Berliner Weisse";
       $product_style= "Berliner Weisse";
       $product_type= "Beer";
       $retailer= 127;
       $distributor= 101;
       $to_date= "2024-03-13";
*/
      $cad = $request->input('cad');
      $date_type = $request->input('date_type');
      $type = $request->input('file_type');
      $from_date = $request->input('from_date');
      $group = $request->input('group');
      $invoice_state = $request->input('invoice_state');
      $lang = $request->input('language');
      $order_state = $request->input('order_state');
      $product_format = $request->input('product_format');
      $product_name = $request->input('product_name');
      $product_style = $request->input('product_style');
      $product_type = $request->input('product_type');
      $retailer  = $request->input('retailer');
      $distributor = $request->input('supplier');
      $to_date = $request->input('to_date');

      $orders = DB::table('users')
      //->select('*')
          ->select('orders.id as order_id',
          DB::raw('ifnull(orders.total_quantity,0) as orders_total_quantity'),
          'orders.supplier_id',
          'orders.retailer_id',
          DB::raw('ifnull(orders.created_at,0) as order_created_at'),
          DB::raw('ifnull(orders.delivered_on,0) as order_delivered_on'),
          DB::raw('ifnull(products.product_name,0) as products_product_name'),
          DB::raw('ifnull(orders.shipped_on,0) as orders_shipped_on'),
          DB::raw('ifnull(product_styles.name,0) as product_style'),
          DB::raw('ifnull(products.product_type,0) as products_product_type'),
          DB::raw('ifnull(product_formats.name,0) as product_format'),
          DB::raw('ifnull(user_profiles.company_name,0) as company_Profile_name'),
          DB::raw('(CASE
           WHEN orders.invoice_status = "0" THEN "Pending"
           WHEN orders.invoice_status = "1" THEN "Paid"
           WHEN orders.invoice_status = "2" THEN "Overdue"
           WHEN orders.invoice_status = "3" THEN "Closed"
           WHEN orders.invoice_status = "4" THEN "Collect"
           ELSE "Status not Updated"
           END) AS invoice_status'),
                     DB::raw('(CASE
                         WHEN orders.status = "0" THEN "Pending"
                         WHEN orders.status = "1" THEN "Approved"
                         WHEN orders.status = "2" THEN "On Hold"
                         WHEN orders.status = "3" THEN "Shipped"
                         WHEN orders.status = "4" THEN "Delivered"
                         WHEN orders.status = "5" THEN "Cancelled"
                         ELSE "Status not Updated"
                         END) AS order_status'),
                         DB::raw('(CASE
                             WHEN user_profiles.business_category_id = "NULL" THEN "NA"
                             WHEN user_profiles.business_category_id = "1" THEN "CAD"
                             WHEN user_profiles.business_category_id = "2" THEN "CAD"
                             WHEN user_profiles.business_category_id = "3" THEN "CSP"
                             WHEN user_profiles.business_category_id = "4" THEN "CSP"
                             WHEN user_profiles.business_category_id = "5" THEN "CSP"
                             WHEN user_profiles.business_category_id = "6" THEN "CAD"
                             WHEN user_profiles.business_category_id = "7" THEN "CAD"
                             WHEN user_profiles.business_category_id = "8" THEN "CAD"
                             WHEN user_profiles.business_category_id = "9" THEN "CAD"
                             WHEN user_profiles.business_category_id = "10" THEN "CSP"
                             WHEN user_profiles.business_category_id = "11" THEN "CSP"
                             WHEN user_profiles.business_category_id = "12" THEN "CSP"
                             WHEN user_profiles.business_category_id = "13" THEN "CSP"
                             ELSE "NA"
                             END) AS company_type'))
                         ->join('user_profiles','users.id','=','user_profiles.user_id')
                         ->join('products', 'users.added_by', '=', 'products.user_id')
                         ->join('orders', 'user_profiles.user_id', '=', 'orders.retailer_id')
                         ->join('product_format_deposit', 'products.user_id', '=', 'product_format_deposit.user_id')
                         ->join('product_formats','products.product_format', '=', 'product_formats.id')
                         ->join('product_styles','product_styles.id','=','products.style')
                         ->whereDate('orders.created_at', '>=', $from_date)
                         ->whereDate('orders.created_at', '<=', $to_date)
                         ->where('users.added_by', '=', $distributor)
                         //->where('user_profiles.user_id', '=', $retailer)
                      //  ->where('products.user_id', '=', $distributor)
                      //  ->AND('users.added_by', '=', $distributor)
     // ->where('orders.supplier_id', '=', $distributor)
   //   ->where('orders.retailer_id', '=', $retailer)

                        ->where(function($query) use ($retailer, $request) {
                        if ($retailer == 'all') {
                            return $query->where('user_profiles.status', 1);
                        }
                        else {
                            return $query->where('user_profiles.user_id', $retailer);
                        }
                    })

                        ->having('order_status', '=', $order_state)
                        ->having('invoice_status', '=', $invoice_state)
                        ->where('user_profiles.business_category_id','=', $cad)
                        ->where('products.product_type','=', $product_type)
                        ->where('products.product_name', $product_name)
                        ->where('product_styles.name', $product_style)
                        ->where('product_formats.name', $product_format)
                        ->groupBy('orders.id')
                        ->get();
//dd($orders);
                        $today=date('YmdHi');

     if(($lang=='CAfr')){
// XML Starts
      $spreadsheet = new Spreadsheet();
      $sheet = $spreadsheet->getActiveSheet();
      $sheet->setCellValue('A1', 'Date de commande');
      $sheet->setCellValue('B1', 'Date de livraison');
      $sheet->setCellValue('C1', 'Statut de la commande');
      $sheet->setCellValue('D1', 'Statut de la facture');
      $sheet->setCellValue('E1', 'CSP/CAO');
      $sheet->setCellValue('F1', 'Nom du produit');
      $sheet->setCellValue('G1', 'type de produit');
      $sheet->setCellValue('H1', 'Style du produit');
      $sheet->setCellValue('I1', 'Format du produit');
      $sheet->setCellValue('J1', 'DCommande totale');
      $sheet->setCellValue('K1', 'Expédiés sur');
      $sheet->setCellValue('L1', 'Délivré le');
      $rows = 2;

      foreach($orders as $OrderDetails){
      $tr = new GoogleTranslate('fr');

      $sheet->setCellValue('A' . $rows, $tr->translate($OrderDetails->order_created_at));
      $sheet->setCellValue('B' . $rows, $tr->translate($OrderDetails->order_delivered_on));
      $sheet->setCellValue('C' . $rows, $tr->translate($OrderDetails->order_status));
      $sheet->setCellValue('D' . $rows, $tr->translate($OrderDetails->invoice_status));
      $sheet->setCellValue('E' . $rows, $OrderDetails->company_type);
      $sheet->setCellValue('F' . $rows, $tr->translate($OrderDetails->products_product_name));
      $sheet->setCellValue('G' . $rows, $tr->translate($OrderDetails->products_product_type));
      $sheet->setCellValue('H' . $rows, $tr->translate($OrderDetails->product_style));
      $sheet->setCellValue('I' . $rows, $tr->translate($OrderDetails->product_format));
      $sheet->setCellValue('J' . $rows, $tr->translate($OrderDetails->orders_total_quantity));
      $sheet->setCellValue('K' . $rows, $tr->translate($OrderDetails->orders_shipped_on));
      $sheet->setCellValue('L' . $rows, $tr->translate($OrderDetails->order_delivered_on));
      $rows++;
    }
      $rand = rand().$lang;
      $rand = $lang.rand();
      $fileName = "buvon".$rand.".".$today.".".$type;
      if($type == 'xlsx') {
      $writer = new Xlsx($spreadsheet);
      $writer->save("reports/".$fileName);
      header("Content-Type: application/vnd.ms-excel");

      $url = url('reports/');
      $records = new SupplierReports;
      $records->user_id = $user_id;
      $records->filename = $fileName;
      $records->file_path = $url;
      $records->file_type = "XLSX";
      $records->save();
      $success  = $records;
      $message  = Lang::get("messages.retailer_user_list");
      return sendResponse($success, $message);
      } elseif($type == 'csv') {
      $writer = new Csv($spreadsheet);
      $writer->save("reports/".$fileName);
      header("Content-Type: application/vnd.ms-excel");

      $url = url('reports/');
      $records = new SupplierReports;
      $records->user_id = $user_id;
      $records->filename = $fileName;
      $records->file_path = $url;
      $records->file_type = strtoupper($type);
      $records->save();
      $success  = $records;
      $message  = Lang::get("messages.retailer_user_list");
      return sendResponse($success, $message);
    }
      elseif($type == 'pdf') {
      $class = \PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf::class;
      \PhpOffice\PhpSpreadsheet\IOFactory::registerWriter('Pdf', $class);
      $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Pdf');
      $writer->save("reports/".$fileName);
      header("Content-type:application/pdf");

      $url = url('reports/');
      $records = new SupplierReports;
      $records->user_id = $user_id;
      $records->filename = $fileName;
      $records->file_path = $url;
      $records->file_type = strtoupper($type);
      $records->save();
      $success  = $records;
      $message  = Lang::get("messages.retailer_user_list");
      return sendResponse($success, $message);
    }}
      elseif(!empty($orders) && ($lang='CAeng')){
    // XML Starts
      $spreadsheet = new Spreadsheet();
      $sheet = $spreadsheet->getActiveSheet();
      $sheet->setCellValue('A1', 'Order Date');
      $sheet->setCellValue('B1', 'Date Of Delivery');
      $sheet->setCellValue('C1', 'Order Status');
      $sheet->setCellValue('D1', 'Invoice Status');
      $sheet->setCellValue('E1', 'CSP/CAD');
      $sheet->setCellValue('F1', 'Product Name');
      $sheet->setCellValue('G1', 'Product Type');
      $sheet->setCellValue('H1', 'Product Style');
      $sheet->setCellValue('I1', 'Product Format');
      $sheet->setCellValue('J1', 'Total Order');
      $sheet->setCellValue('K1', 'Shipped On');
      $sheet->setCellValue('L1', 'Delivered On');
      $rows = 2;

      foreach($orders as $OrderDetails){
      $sheet->setCellValue('A' . $rows, $OrderDetails->order_created_at);
      $sheet->setCellValue('B' . $rows, $OrderDetails->order_delivered_on);
      $sheet->setCellValue('C' . $rows, $OrderDetails->order_status);
      $sheet->setCellValue('D' . $rows, $OrderDetails->invoice_status);
      $sheet->setCellValue('E' . $rows, $OrderDetails->company_type);
      $sheet->setCellValue('F' . $rows, $OrderDetails->products_product_name);
      $sheet->setCellValue('G' . $rows, $OrderDetails->products_product_type);
      $sheet->setCellValue('H' . $rows, $OrderDetails->product_style);
      $sheet->setCellValue('I' . $rows, $OrderDetails->product_format);
      $sheet->setCellValue('J' . $rows, $OrderDetails->orders_total_quantity);
      $sheet->setCellValue('K' . $rows, $OrderDetails->orders_shipped_on);
      $sheet->setCellValue('L' . $rows, $OrderDetails->order_delivered_on);
      $rows++;
    }
      $rand = $lang.rand();
      $fileName = "buvon_".$rand.".".$today.".".$type;
      if($type == 'xlsx') {
      $writer = new Xlsx($spreadsheet);
      $writer->save("reports/".$fileName);
      header("Content-Type: application/vnd.ms-excel");

      $url = url('reports/');
      $records = new SupplierReports;
      $records->user_id = $user_id;
      $records->filename = $fileName;
      $records->file_path = $url;
      $records->file_type = strtoupper($type);
      $records->save();
      $success  = $records;
      $message  = Lang::get("messages.retailer_user_list");
      return sendResponse($success, $message);
    }
      elseif($type == 'csv')
    {
      $writer = new Csv($spreadsheet);
      $writer->save("reports/".$fileName);
      header("Content-Type: application/vnd.ms-excel");

      $url = url('reports/');
      $records = new SupplierReports;
      $records->user_id = $user_id;
      $records->filename = $fileName;
      $records->file_path = $url;
      $records->file_type = strtoupper($type);
      $records->save();
      $success  = $records;
      $message  = Lang::get("messages.retailer_user_list");
      return sendResponse($success, $message);
    }
      elseif($type == 'pdf')
    {
      $class = \PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf::class;
      \PhpOffice\PhpSpreadsheet\IOFactory::registerWriter('Pdf', $class);
      $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Pdf');
      $writer->save("reports/".$fileName);
      header("Content-type:application/pdf");

      $url = url('reports/');
      $records = new SupplierReports;
      $records->user_id = $user_id;
      $records->filename = $fileName;
      $records->file_path = $url;
      $records->file_type = strtoupper($type);
      $records->save();
      $success  = $records;
      $message  = Lang::get("messages.retailer_user_list");
      return sendResponse($success, $message);
    }}
      else{
     return sendError('Access Denied', ['error' => Lang::get("Unable to insert data")], 403);
    }
    }

    public function PostReportInventoryList(request $request)
    {
     if($this->permission !== "reports-view")
    {
      return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
    }
    $user_id = Auth::user()->id;
/*      $user_id = 101;
      $type = "xlsx";
      $lang = "CAfr";
      $warehouse = 3;
      $product_name = "Berliner Weisse";
      $product_type = "Beer";
      $product_style = "Berliner Weisse";
      $product_format = "Can 473ml x 24";
      $by_user = 101;
      $from_date = "2023-03-01";
      $to_date = "2024-01-11";
*/
      $type= $request->input('file_type');
      $lang= $request->input('language');
      $from_date = $request->input('from_date');
      $to_date = $request->input('to_date');
      $by_user = $request->input('by_user');
      $warehouse = $request->input('warehouse');
      $product_name = $request->input('product_name');
      $product_type = $request->input('product_type');
      $product_style = $request->input('product_style');
      $product_format = $request->input('product_format');

      $orders = DB::table('warehouses')
                ->select('warehouses.id as warehouse_id',
                DB::raw('ifnull(users.first_name,0) as first_name'),
                DB::raw('ifnull(users.last_name,0) as last_name'),
                DB::raw('ifnull(users.email,0) as email'),
                DB::raw('ifnull(user_profiles.company_name,0) as company_name'),
                DB::raw('ifnull(users.user_type_id,0) as user_type_id'),
                DB::raw('ifnull(warehouses.name,0) as warehouse_name'),
                DB::raw('ifnull(stock_histories.new_stock,0) as new_stock'),
                DB::raw('ifnull(stock_histories.quantity,0) as quantity'),
                DB::raw('ifnull(stock_histories.lot_date,0) as lot_date'),
                DB::raw('ifnull(stock_histories.datetime,0) as datetime'),
                DB::raw('ifnull(products.product_name,0) as product_name'),
                DB::raw('ifnull(products.product_type,0) as product_type'),
                'product_styles.name as product_style', 'product_formats.name as product_format')
                ->join('inventories', 'warehouses.id', '=', 'inventories.warehouse_id')
                ->join('products', 'products.id', '=', 'inventories.product_id')
                ->join('product_format_deposit', 'products.user_id', '=', 'product_format_deposit.user_id')
                ->join('product_formats', 'products.product_format', '=', 'product_formats.id')
                ->join('product_styles', 'product_styles.id', '=', 'products.style')
                ->join('users', 'warehouses.user_id', '=','users.id')
                ->join('stock_histories', 'stock_histories.created_by','=', 'users.id')
                ->join('user_profiles', 'users.id', '=', 'user_profiles.user_id')
                ->whereDate('warehouses.created_at', '>=', $from_date)
                ->whereDate('warehouses.created_at', '<=', $to_date)
                ->where('warehouses.id', '=', $warehouse)
                ->where('products.product_name', '=', $product_name)
                ->where('products.product_type', '=', $product_type)
                ->where('product_styles.name', '=', $product_style)
                ->where('product_formats.name', '=', $product_format)
                ->where('users.id', '=', $by_user)
                ->get();

      $today=date('YmdHi');
      if(!empty($orders) && ($lang=='CAfr')){
      // XML Starts
      $spreadsheet = new Spreadsheet();
      $sheet = $spreadsheet->getActiveSheet();
      $sheet->setCellValue('A1', 'Date spécifique');
      $sheet->setCellValue('B1', 'Entrepôt');
      $sheet->setCellValue('C1', 'Fournisseur');
      $sheet->setCellValue('D1', 'type de produit');
      $sheet->setCellValue('E1', 'Format');
      $sheet->setCellValue('F1', 'Type');
      $sheet->setCellValue('G1', 'Style');
      $sheet->setCellValue('H1', 'Stock actuel');
      $sheet->setCellValue('I1', 'Dernière opération');
      $sheet->setCellValue('J1', 'Dernier réglage');
      $sheet->setCellValue('K1', 'Dernière expédition');
      $sheet->setCellValue('L1', 'Courrier utilisateur');
      $rows = 2;

      foreach($orders as $OrderDetails){
      $tr = new GoogleTranslate('fr');
      $OrderDetails->last_operation = "0";
      $OrderDetails->last_adjustment = "0";
      $OrderDetails->last_shipment = "0";

      $sheet->setCellValue('A' . $rows, $tr->translate($OrderDetails->datetime));
      $sheet->setCellValue('B' . $rows, $tr->translate($OrderDetails->warehouse_name));
      $sheet->setCellValue('C' . $rows, $tr->translate($OrderDetails->company_name));
      $sheet->setCellValue('D' . $rows, $tr->translate($OrderDetails->product_name));
      $sheet->setCellValue('E' . $rows, $tr->translate($OrderDetails->product_format));
      $sheet->setCellValue('F' . $rows, $tr->translate($OrderDetails->product_type));
      $sheet->setCellValue('G' . $rows, $tr->translate($OrderDetails->product_style));
      $sheet->setCellValue('H' . $rows, $tr->translate($OrderDetails->new_stock));
      $sheet->setCellValue('I' . $rows, $tr->translate($OrderDetails->last_operation));
      $sheet->setCellValue('J' . $rows, $tr->translate($OrderDetails->last_adjustment));
      $sheet->setCellValue('K' . $rows, $tr->translate($OrderDetails->last_shipment));
      $sheet->setCellValue('L' . $rows, $tr->translate($OrderDetails->email));
      $rows++;
      }
      $rand = rand().$lang;
      $rand = $lang.rand();
      $fileName = "buvon".$rand.".".$today.".".$type;
      if($type == 'xlsx') {
      $writer = new Xlsx($spreadsheet);
      $writer->save("reports/".$fileName);
      header("Content-Type: application/vnd.ms-excel");

      $url = url('reports/');
      $records = new InventoryReports;
      $records->user_id = $user_id;
      $records->filename = $fileName;
      $records->file_path = $url;
      $records->file_type = "XLSX";
      $records->save();
      $success  = $records;
      $message  = Lang::get("messages.retailer_user_list");
      return sendResponse($success, $message);
      } elseif($type == 'csv') {
      $writer = new Csv($spreadsheet);
      $writer->save("reports/".$fileName);
      header("Content-Type: application/vnd.ms-excel");

      $url = url('reports/');
      $records = new InventoryReports;
      $records->user_id = $user_id;
      $records->filename = $fileName;
      $records->file_path = $url;
      $records->file_type = strtoupper($type);
      $records->save();
      $success  = $records;
      $message  = Lang::get("messages.retailer_user_list");
      return sendResponse($success, $message);
      }
      elseif($type == 'pdf') {
      $class = \PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf::class;
      \PhpOffice\PhpSpreadsheet\IOFactory::registerWriter('Pdf', $class);
      $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Pdf');
      $writer->save("reports/".$fileName);
      header("Content-type:application/pdf");

      $url = url('reports/');
      $records = new InventoryReports;
      $records->user_id = $user_id;
      $records->filename = $fileName;
      $records->file_path = $url;
      $records->file_type = strtoupper($type);
      $records->save();
      $success  = $records;
      $message  = Lang::get("messages.retailer_user_list");
      return sendResponse($success, $message);
      }}
      elseif(!empty($orders) && ($lang='CAeng')){
      // XML Starts
      $spreadsheet = new Spreadsheet();
      $sheet = $spreadsheet->getActiveSheet();
      $sheet->setCellValue('A1', 'Specific date');
      $sheet->setCellValue('B1', 'Warehouse');
      $sheet->setCellValue('C1', 'Supplier');
      $sheet->setCellValue('D1', 'Product Name');
      $sheet->setCellValue('E1', 'Format');
      $sheet->setCellValue('F1', 'Type');
      $sheet->setCellValue('G1', 'Style');
      $sheet->setCellValue('H1', 'Stock');
      $sheet->setCellValue('I1', 'Last operation');
      $sheet->setCellValue('J1', 'Last adjustment');
      $sheet->setCellValue('K1', 'Last shipment');
      $sheet->setCellValue('L1', 'User Mail');
      $rows = 2;

      foreach($orders as $OrderDetails){
      $OrderDetails->last_operation = "0";
      $OrderDetails->last_adjustment = "0";
      $OrderDetails->last_shipment = "0";

      $sheet->setCellValue('A' . $rows, $OrderDetails->datetime);
      $sheet->setCellValue('B' . $rows, $OrderDetails->warehouse_name);
      $sheet->setCellValue('C' . $rows, $OrderDetails->company_name);
      $sheet->setCellValue('D' . $rows, $OrderDetails->product_name);
      $sheet->setCellValue('E' . $rows, $OrderDetails->product_format);
      $sheet->setCellValue('F' . $rows, $OrderDetails->product_type);
      $sheet->setCellValue('G' . $rows, $OrderDetails->product_style);
      $sheet->setCellValue('H' . $rows, $OrderDetails->new_stock);
      $sheet->setCellValue('I' . $rows, $OrderDetails->last_operation);
      $sheet->setCellValue('J' . $rows, $OrderDetails->last_adjustment);
      $sheet->setCellValue('K' . $rows, $OrderDetails->last_shipment);
      $sheet->setCellValue('L' . $rows, $OrderDetails->email);
      $rows++;
      }
      $rand = $lang.rand();
      $fileName = "buvon_".$rand.".".$today.".".$type;
      if($type == 'xlsx') {
      $writer = new Xlsx($spreadsheet);
      $writer->save("reports/".$fileName);
      header("Content-Type: application/vnd.ms-excel");

      $url = url('reports/');
      $records = new InventoryReports;
      $records->user_id = $user_id;
      $records->filename = $fileName;
      $records->file_path = $url;
      $records->file_type = strtoupper($type);
      $records->save();
      $success  = $records;
      $message  = Lang::get("messages.retailer_user_list");
      return sendResponse($success, $message);
      }
      elseif($type == 'csv')
      {
      $writer = new Csv($spreadsheet);
      $writer->save("reports/".$fileName);
      header("Content-Type: application/vnd.ms-excel");

      $url = url('reports/');
      $records = new InventoryReports;
      $records->user_id = $user_id;
      $records->filename = $fileName;
      $records->file_path = $url;
      $records->file_type = strtoupper($type);
      $records->save();
      $success  = $records;
      $message  = Lang::get("messages.retailer_user_list");
      return sendResponse($success, $message);
      }
      elseif($type == 'pdf')
      {
      $class = \PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf::class;
      \PhpOffice\PhpSpreadsheet\IOFactory::registerWriter('Pdf', $class);
      $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Pdf');
      $writer->save("reports/".$fileName);
      header("Content-type:application/pdf");

      $url = url('reports/');
      $records = new InventoryReports;
      $records->user_id = $user_id;
      $records->filename = $fileName;
      $records->file_path = $url;
      $records->file_type = strtoupper($type);
      $records->save();
      $success  = $records;
      $message  = Lang::get("messages.retailer_user_list");
      return sendResponse($success, $message);
      }}
      else{
      return sendError('Access Denied', ['error' => Lang::get("Unable to insert data")], 403);
      }

      }

      public function PostCustomReports(request $request){

        if($this->permission !== "reports-view")
       {
        return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }

        $user_id = Auth::user()->id;
/*
        $user_id = 101;
        $cad= "11";
        $type= "xlsx";
        $from_date= "2023-10-10";
        $lang= "CAfr";
        $order_state= "Approved";
        $product_type= "Beer";
        $distributor= 101;
        $to_date= "2024-03-13";
*/
        $type= $request->input('file_type');
        $lang= $request->input('language');
        $from_date = $request->input('from_date');
        $to_date = $request->input('to_date');
        $product_name = $request->input('distributer');
        $product_type = $request->input('product_type');

        $orders = DB::table('users')
        //->select('*')
            ->select('orders.id as order_id',
            DB::raw('ifnull(orders.total_quantity,0) as orders_total_quantity'),
            'orders.supplier_id',
            'orders.retailer_id',
            DB::raw('ifnull(orders.created_at,0) as order_created_at'),
            DB::raw('ifnull(orders.delivered_on,0) as order_delivered_on'),
            DB::raw('ifnull(user_profiles.business_name,0) as business_name'),
            DB::raw('ifnull(products.product_name,0) as products_product_name'),
            DB::raw('ifnull(orders.shipped_on,0) as orders_shipped_on'),
            DB::raw('ifnull(product_styles.name,0) as product_style'),
            DB::raw('ifnull(products.product_type,0) as products_product_type'),
            DB::raw('ifnull(product_formats.name,0) as product_format'),
            DB::raw('ifnull(user_profiles.company_name,0) as company_Profile_name'),
            DB::raw('(CASE
             WHEN orders.invoice_status = "0" THEN "Pending"
             WHEN orders.invoice_status = "1" THEN "Paid"
             WHEN orders.invoice_status = "2" THEN "Overdue"
             WHEN orders.invoice_status = "3" THEN "Closed"
             WHEN orders.invoice_status = "4" THEN "Collect"
             ELSE "Status not Updated"
             END) AS invoice_status'),
                       DB::raw('(CASE
                           WHEN orders.status = "0" THEN "Pending"
                           WHEN orders.status = "1" THEN "Approved"
                           WHEN orders.status = "2" THEN "On Hold"
                           WHEN orders.status = "3" THEN "Shipped"
                           WHEN orders.status = "4" THEN "Delivered"
                           WHEN orders.status = "5" THEN "Cancelled"
                           ELSE "Status not Updated"
                           END) AS order_status'),
                           DB::raw('(CASE
                               WHEN user_profiles.business_category_id = "NULL" THEN "NA"
                               WHEN user_profiles.business_category_id = "1" THEN "CAD"
                               WHEN user_profiles.business_category_id = "2" THEN "CAD"
                               WHEN user_profiles.business_category_id = "3" THEN "CSP"
                               WHEN user_profiles.business_category_id = "4" THEN "CSP"
                               WHEN user_profiles.business_category_id = "5" THEN "CSP"
                               WHEN user_profiles.business_category_id = "6" THEN "CAD"
                               WHEN user_profiles.business_category_id = "7" THEN "CAD"
                               WHEN user_profiles.business_category_id = "8" THEN "CAD"
                               WHEN user_profiles.business_category_id = "9" THEN "CAD"
                               WHEN user_profiles.business_category_id = "10" THEN "CSP"
                               WHEN user_profiles.business_category_id = "11" THEN "CSP"
                               WHEN user_profiles.business_category_id = "12" THEN "CSP"
                               WHEN user_profiles.business_category_id = "13" THEN "CSP"
                               ELSE "NA"
                               END) AS company_type'))
                           ->join('user_profiles','users.id','=','user_profiles.user_id')
                           ->join('products', 'users.added_by', '=', 'products.user_id')
                           ->join('orders', 'user_profiles.user_id', '=', 'orders.retailer_id')
                           ->join('product_format_deposit', 'products.user_id', '=', 'product_format_deposit.user_id')
                           ->join('product_formats','products.product_format', '=', 'product_formats.id')
                           ->join('product_styles','product_styles.id','=','products.style')
                           //->join('warehouses', 'warehouses.id', '=', 'inventories.warehouse_id')
                           //->join('inventories', 'users.id', '=', 'inventories.distributor_id')
                           ->whereDate('orders.created_at', '>=', $from_date)
                           ->whereDate('orders.created_at', '<=', $to_date)
                           ->where('users.added_by', '=', $distributor)
                           //->where('user_profiles.user_id', '=', $retailer)
                          // ->where('products.user_id', '=', $distributor)
       // ->where('orders.supplier_id', '=', $distributor)
     //   ->where('orders.retailer_id', '=', $retailer)
                          ->having('order_status', '=', $order_state)
                          ->where('products.product_type','=', $product_type)
                          ->groupBy('orders.id')
                          ->get();
//dd($orders);
        $today=date('YmdHi');

        if(!empty($orders) && ($lang=='CAfr')){
        // XML Starts
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Date de commande');
        $sheet->setCellValue('B1', 'Date de livraison');
        $sheet->setCellValue('C1', 'Statut de la commande');
        $sheet->setCellValue('D1', 'Statut de la facture');
        $sheet->setCellValue('E1', 'CSP/CAO');
        $sheet->setCellValue('F1', 'Nom du produit');
        $sheet->setCellValue('G1', 'type de produit');
        $sheet->setCellValue('H1', 'Style du produit');
        $sheet->setCellValue('I1', 'Format du produit');
        $sheet->setCellValue('J1', 'DCommande totale');
        $sheet->setCellValue('K1', 'Expédiés sur');
        $sheet->setCellValue('L1', 'Délivré le');
        $rows = 2;

        foreach($orders as $OrderDetails){
        $tr = new GoogleTranslate('fr');

        $sheet->setCellValue('A' . $rows, $tr->translate($OrderDetails->order_created_at));
        $sheet->setCellValue('B' . $rows, $tr->translate($OrderDetails->order_delivered_on));
        $sheet->setCellValue('C' . $rows, $tr->translate($OrderDetails->order_status));
        $sheet->setCellValue('D' . $rows, $tr->translate($OrderDetails->invoice_status));
        $sheet->setCellValue('E' . $rows, $OrderDetails->company_type);
        $sheet->setCellValue('F' . $rows, $tr->translate($OrderDetails->products_product_name));
        $sheet->setCellValue('G' . $rows, $tr->translate($OrderDetails->products_product_type));
        $sheet->setCellValue('H' . $rows, $tr->translate($OrderDetails->product_style));
        $sheet->setCellValue('I' . $rows, $tr->translate($OrderDetails->product_format));
        $sheet->setCellValue('J' . $rows, $tr->translate($OrderDetails->orders_total_quantity));
        $sheet->setCellValue('K' . $rows, $tr->translate($OrderDetails->orders_shipped_on));
        $sheet->setCellValue('L' . $rows, $tr->translate($OrderDetails->order_delivered_on));
        $rows++;
        }
        $rand = rand().$lang;
        $rand = $lang.rand();
        $fileName = "buvon".$rand.".".$today.".".$type;
        if($type == 'xlsx') {
        $writer = new Xlsx($spreadsheet);
        $writer->save("reports/".$fileName);
        header("Content-Type: application/vnd.ms-excel");

        $url = url('reports/');
        $records = new CustomReports;
        $records->user_id = $user_id;
        $records->filename = $fileName;
        $records->file_path = $url;
        $records->file_type = "XLSX";
        $records->save();
        $success  = $records;
        $message  = Lang::get("messages.retailer_user_list");
        return sendResponse($success, $message);
        } elseif($type == 'csv') {
        $writer = new Csv($spreadsheet);
        $writer->save("reports/".$fileName);
        header("Content-Type: application/vnd.ms-excel");

        $url = url('reports/');
        $records = new CustomReports;
        $records->user_id = $user_id;
        $records->filename = $fileName;
        $records->file_path = $url;
        $records->file_type = strtoupper($type);
        $records->save();
        $success  = $records;
        $message  = Lang::get("messages.retailer_user_list");
        return sendResponse($success, $message);
        }
        elseif($type == 'pdf') {
        $class = \PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf::class;
        \PhpOffice\PhpSpreadsheet\IOFactory::registerWriter('Pdf', $class);
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Pdf');
        $writer->save("reports/".$fileName);
        header("Content-type:application/pdf");

        $url = url('reports/');
        $records = new CustomReports;
        $records->user_id = $user_id;
        $records->filename = $fileName;
        $records->file_path = $url;
        $records->file_type = strtoupper($type);
        $records->save();
        $success  = $records;
        $message  = Lang::get("messages.retailer_user_list");
        return sendResponse($success, $message);
        }}
        elseif(!empty($orders) && ($lang='CAeng')){
        // XML Starts
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Order Date');
        $sheet->setCellValue('B1', 'Date Of Delivery');
        $sheet->setCellValue('C1', 'Order Status');
        $sheet->setCellValue('D1', 'Invoice Status');
        $sheet->setCellValue('E1', 'CSP/CAD');
        $sheet->setCellValue('F1', 'Product Name');
        $sheet->setCellValue('G1', 'Product Type');
        $sheet->setCellValue('H1', 'Product Style');
        $sheet->setCellValue('I1', 'Product Format');
        $sheet->setCellValue('J1', 'Total Order');
        $sheet->setCellValue('K1', 'Shipped On');
        $sheet->setCellValue('L1', 'Delivered On');
        $rows = 2;

        foreach($orders as $OrderDetails){
        $sheet->setCellValue('A' . $rows, $OrderDetails->order_created_at);
        $sheet->setCellValue('B' . $rows, $OrderDetails->order_delivered_on);
        $sheet->setCellValue('C' . $rows, $OrderDetails->order_status);
        $sheet->setCellValue('D' . $rows, $OrderDetails->invoice_status);
        $sheet->setCellValue('E' . $rows, $OrderDetails->company_type);
        $sheet->setCellValue('F' . $rows, $OrderDetails->products_product_name);
        $sheet->setCellValue('G' . $rows, $OrderDetails->products_product_type);
        $sheet->setCellValue('H' . $rows, $OrderDetails->product_style);
        $sheet->setCellValue('I' . $rows, $OrderDetails->product_format);
        $sheet->setCellValue('J' . $rows, $OrderDetails->orders_total_quantity);
        $sheet->setCellValue('K' . $rows, $OrderDetails->orders_shipped_on);
        $sheet->setCellValue('L' . $rows, $OrderDetails->order_delivered_on);
        $rows++;
        }
        $rand = $lang.rand();
        $fileName = "buvon_".$rand.".".$today.".".$type;
        if($type == 'xlsx') {
        $writer = new Xlsx($spreadsheet);
        $writer->save("reports/".$fileName);
        header("Content-Type: application/vnd.ms-excel");

        $url = url('reports/');
        $records = new CustomReports;
        $records->user_id = $user_id;
        $records->filename = $fileName;
        $records->file_path = $url;
        $records->file_type = strtoupper($type);
        $records->save();
        $success  = $records;
        $message  = Lang::get("messages.retailer_user_list");
        return sendResponse($success, $message);
        }
        elseif($type == 'csv')
        {
        $writer = new Csv($spreadsheet);
        $writer->save("reports/".$fileName);
        header("Content-Type: application/vnd.ms-excel");

        $url = url('reports/');
        $records = new CustomReports;
        $records->user_id = $user_id;
        $records->filename = $fileName;
        $records->file_path = $url;
        $records->file_type = strtoupper($type);
        $records->save();
        $success  = $records;
        $message  = Lang::get("messages.retailer_user_list");
        return sendResponse($success, $message);
        }
        elseif($type == 'pdf')
        {
        $class = \PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf::class;
        \PhpOffice\PhpSpreadsheet\IOFactory::registerWriter('Pdf', $class);
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Pdf');
        $writer->save("reports/".$fileName);
        header("Content-type:application/pdf");

        $url = url('reports/');
        $records = new CustomReports;
        $records->user_id = $user_id;
        $records->filename = $fileName;
        $records->file_path = $url;
        $records->file_type = strtoupper($type);
        $records->save();
        $success  = $records;
        $message  = Lang::get("messages.retailer_user_list");
        return sendResponse($success, $message);
        }}
        else{
        return sendError('Access Denied', ['error' => Lang::get("Unable to insert data")], 403);
        }

      }

   public function PostSuperSalesReports(request $request){
     if($this->permission !== "reports-view")
    {
     return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
     }

     $user_id = Auth::user()->id;
/*
   $by_user = "101";
   //$file_type = "csv"
   $from_date = "2023-03-01";
   $language = "CAeng";
   $product_format = "Can 473ml x 24";
   $product_type = "Beer";
   $retailer = "127";
   $supplier = "101";
   $to_date = "2024-03-28"; */

   $by_user = $request->input('by_user');
   //$file_type = "csv"
   $from_date = $request->input('from_date');
   $to_date = $request->input('to_date');
   $lang = $request->input('language');
   $type= $request->input('file_type');
   $product_format = $request->input('product_format');
   $product_type = $request->input('product_type');
   $retailer = $request->input('retailer');
   $supplier = $request->input('supplier');

   $orders = DB::table('users')
   //->select('*')
       ->select('orders.id as order_id','products.id as product_id',
       DB::raw('ifnull(order_items.quantity,0) as order_item_quantity'),
       DB::raw('ifnull(order_items.sub_total,0) as item_revenue'),
       DB::raw('ifnull(order_items.price,0) as order_item_price'),
       'orders.supplier_id',
       'orders.retailer_id',
       DB::raw('(SELECT company_name FROM user_profiles
                  WHERE user_id = '.$supplier.')as supplier_name'),
       DB::raw('ifnull((SELECT company_name FROM user_profiles
                             WHERE user_id = '.$retailer.'),0) as retailer_name'),
       DB::raw('ifnull((SELECT city FROM users
                             WHERE id = '.$retailer.'),0) as retailer_city'),
       DB::raw('ifnull(orders.created_at,0) as order_created_at'),
       DB::raw('ifnull(orders.delivered_on,0) as order_delivered_on'),
       DB::raw('ifnull(products.product_name,0) as products_product_name'),
       DB::raw('ifnull(orders.shipped_on,0) as orders_shipped_on'),
       DB::raw('ifnull(product_styles.name,0) as product_style'),
       DB::raw('ifnull(products.product_type,0) as products_product_type'),
       DB::raw('ifnull(product_formats.name,0) as product_format'),
       DB::raw('ifnull(pricings.discount_percent,0) as discount'),
       DB::raw('(CASE
        WHEN orders.invoice_status = "0" THEN "Pending"
        WHEN orders.invoice_status = "1" THEN "Paid"
        WHEN orders.invoice_status = "2" THEN "Overdue"
        WHEN orders.invoice_status = "3" THEN "Closed"
        WHEN orders.invoice_status = "4" THEN "Collect"
        ELSE "Status not Updated"
        END) AS invoice_status'),
                  DB::raw('(CASE
                      WHEN order_items.status = "0" THEN "Pending"
                      WHEN order_items.status = "1" THEN "Approved"
                      WHEN order_items.status = "2" THEN "On Hold"
                      WHEN order_items.status = "3" THEN "Shipped"
                      WHEN order_items.status = "4" THEN "Delivered"
                      WHEN order_items.status = "5" THEN "Cancelled"
                      ELSE "Status not Updated"
                      END) AS order_status'),
                      DB::raw('(CASE
                          WHEN user_profiles.business_category_id = "NULL" THEN "NA"
                          WHEN user_profiles.business_category_id = "1" THEN "CAD"
                          WHEN user_profiles.business_category_id = "2" THEN "CAD"
                          WHEN user_profiles.business_category_id = "3" THEN "CSP"
                          WHEN user_profiles.business_category_id = "4" THEN "CSP"
                          WHEN user_profiles.business_category_id = "5" THEN "CSP"
                          WHEN user_profiles.business_category_id = "6" THEN "CAD"
                          WHEN user_profiles.business_category_id = "7" THEN "CAD"
                          WHEN user_profiles.business_category_id = "8" THEN "CAD"
                          WHEN user_profiles.business_category_id = "9" THEN "CAD"
                          WHEN user_profiles.business_category_id = "10" THEN "CSP"
                          WHEN user_profiles.business_category_id = "11" THEN "CSP"
                          WHEN user_profiles.business_category_id = "12" THEN "CSP"
                          WHEN user_profiles.business_category_id = "13" THEN "CSP"
                          ELSE "NA"
                          END) AS company_type'))
                      ->join('user_profiles','users.id','=','user_profiles.user_id')
                      ->join('products', 'users.added_by', '=', 'products.user_id')
                      ->join('pricings', 'products.id', '=', 'pricings.product_id')
                      ->join('orders', 'user_profiles.user_id', '=', 'orders.retailer_id')
                      ->join('order_items', 'order_items.order_id','=','orders.id')
                      ->join('product_format_deposit', 'products.user_id', '=', 'product_format_deposit.user_id')
                      ->join('product_formats','products.product_format', '=', 'product_formats.id')
                      ->join('product_styles','product_styles.id','=','products.style')
                      ->whereDate('orders.created_at', '>=', $from_date)
                      ->whereDate('orders.created_at', '<=', $to_date)
                      ->where('orders.added_by', '=', $by_user)
                      //->where('user_profiles.user_id', '=', $retailer)
                   //  ->where('products.user_id', '=', $distributor)
                   //  ->AND('users.added_by', '=', $distributor)
  // ->where('orders.supplier_id', '=', $distributor)
//   ->where('orders.retailer_id', '=', $retailer)
                    ->where('orders.supplier_id', $supplier)
                    ->where('orders.retailer_id', $retailer)

                     ->where('products.product_type','=', $product_type)
                     ->where('product_formats.name', $product_format)
                     ->groupBy('orders.id')
                     ->get();
          $today=date('YmdHi');

          if(!empty($orders) && ($lang=='CAfr')){
          // XML Starts
          $spreadsheet = new Spreadsheet();
          $sheet = $spreadsheet->getActiveSheet();
          $sheet->setCellValue('A1', 'Date de commande');
          $sheet->setCellValue('B1', 'Date de livraison');
          $sheet->setCellValue('C1', 'Commande expédiée');
          $sheet->setCellValue('D1', 'Fournisseur');
          $sheet->setCellValue('E1', 'Quantité');
          $sheet->setCellValue('F1', 'Nom du produit');
          $sheet->setCellValue('G1', 'type de produit');
          $sheet->setCellValue('H1', 'Format du produit');
          $sheet->setCellValue('I1', 'Prix ​​par pièce');
          $sheet->setCellValue('J1', 'Revenu');
          $sheet->setCellValue('K1', 'Statut des factures');
          $sheet->setCellValue('L1', 'Rabais');
          $sheet->setCellValue('M1', 'Fréquence');
          $sheet->setCellValue('N1', 'Dernière commande');
          $sheet->setCellValue('O1', 'Par détaillant');
          $sheet->setCellValue('P1', 'Ville');
          $rows = 2;

          foreach($orders as $OrderDetails){
          $tr = new GoogleTranslate('fr');

          $OrderDetails->frequency = 0;
          $OrderDetails->last_order = 0;

          $sheet->setCellValue('A' . $rows, $OrderDetails->order_created_at);
          $sheet->setCellValue('B' . $rows, $OrderDetails->order_delivered_on);
          $sheet->setCellValue('C' . $rows, $OrderDetails->orders_shipped_on);
          $sheet->setCellValue('D' . $rows, $OrderDetails->supplier_name);
          $sheet->setCellValue('E' . $rows, $OrderDetails->order_item_quantity);
          $sheet->setCellValue('F' . $rows, $OrderDetails->products_product_name);
          $sheet->setCellValue('G' . $rows, $OrderDetails->products_product_type);
          $sheet->setCellValue('H' . $rows, $OrderDetails->product_format);
          $sheet->setCellValue('I' . $rows, $OrderDetails->order_item_price);
          $sheet->setCellValue('J' . $rows, $OrderDetails->item_revenue);
          $sheet->setCellValue('K' . $rows, $OrderDetails->invoice_status);
          $sheet->setCellValue('L' . $rows, $OrderDetails->discount);
          $sheet->setCellValue('M' . $rows, $OrderDetails->frequency);
          $sheet->setCellValue('N' . $rows, $OrderDetails->last_order);
          $sheet->setCellValue('O' . $rows, $OrderDetails->retailer_name);
          $sheet->setCellValue('P' . $rows, $OrderDetails->retailer_city);
          $rows++;
          }
          $rand = rand().$lang;
          $rand = $lang.rand();
          $fileName = "buvon".$rand.".".$today.".".$type;
          if($type == 'xlsx') {
          $writer = new Xlsx($spreadsheet);
          $writer->save("reports/".$fileName);
          header("Content-Type: application/vnd.ms-excel");

          $url = url('reports/');
          $records = new SalesReports;
          $records->user_id = $user_id;
          $records->filename = $fileName;
          $records->file_path = $url;
          $records->file_type = "XLSX";
          $records->save();
          $success  = $records;
          $message  = Lang::get("messages.retailer_user_list");
          return sendResponse($success, $message);
          } elseif($type == 'csv') {
          $writer = new Csv($spreadsheet);
          $writer->save("reports/".$fileName);
          header("Content-Type: application/vnd.ms-excel");

          $url = url('reports/');
          $records = new SalesReports;
          $records->user_id = $user_id;
          $records->filename = $fileName;
          $records->file_path = $url;
          $records->file_type = strtoupper($type);
          $records->save();
          $success  = $records;
          $message  = Lang::get("messages.retailer_user_list");
          return sendResponse($success, $message);
          }
          elseif($type == 'pdf') {
          $class = \PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf::class;
          \PhpOffice\PhpSpreadsheet\IOFactory::registerWriter('Pdf', $class);
          $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Pdf');
          $writer->save("reports/".$fileName);
          header("Content-type:application/pdf");

          $url = url('reports/');
          $records = new SalesReports;
          $records->user_id = $user_id;
          $records->filename = $fileName;
          $records->file_path = $url;
          $records->file_type = strtoupper($type);
          $records->save();
          $success  = $records;
          $message  = Lang::get("messages.retailer_user_list");
          return sendResponse($success, $message);
          }}
          elseif(!empty($orders) && ($lang='CAeng')){
          // XML Starts
          $spreadsheet = new Spreadsheet();
          $sheet = $spreadsheet->getActiveSheet();
          $sheet->setCellValue('A1', 'Order Date');
          $sheet->setCellValue('B1', 'Date Of Delivery');
          $sheet->setCellValue('C1', 'Order Shipped');
          $sheet->setCellValue('D1', 'Supplier');
          $sheet->setCellValue('E1', 'Quantity');
          $sheet->setCellValue('F1', 'Product Name');
          $sheet->setCellValue('G1', 'Product Type');
          $sheet->setCellValue('H1', 'Product Format');
          $sheet->setCellValue('I1', 'Price Per Peice');
          $sheet->setCellValue('J1', 'Revanue');
          $sheet->setCellValue('K1', 'Invoices');
          $sheet->setCellValue('L1', 'Discount');
          $sheet->setCellValue('M1', 'Frequency');
          $sheet->setCellValue('N1', 'Last Order');
          $sheet->setCellValue('O1', 'By Retailser');
          $sheet->setCellValue('P1', 'CIty');
          $rows = 2;

          foreach($orders as $OrderDetails){
          $OrderDetails->frequency = 0;
          $OrderDetails->last_order = 0;

          $sheet->setCellValue('A' . $rows, $OrderDetails->order_created_at);
          $sheet->setCellValue('B' . $rows, $OrderDetails->order_delivered_on);
          $sheet->setCellValue('C' . $rows, $OrderDetails->orders_shipped_on);
          $sheet->setCellValue('D' . $rows, $OrderDetails->supplier_name);
          $sheet->setCellValue('E' . $rows, $OrderDetails->order_item_quantity);
          $sheet->setCellValue('F' . $rows, $OrderDetails->products_product_name);
          $sheet->setCellValue('G' . $rows, $OrderDetails->products_product_type);
          $sheet->setCellValue('H' . $rows, $OrderDetails->product_format);
          $sheet->setCellValue('I' . $rows, $OrderDetails->order_item_price);
          $sheet->setCellValue('J' . $rows, $OrderDetails->item_revenue);
          $sheet->setCellValue('K' . $rows, $OrderDetails->invoice_status);
          $sheet->setCellValue('L' . $rows, $OrderDetails->discount);
          $sheet->setCellValue('M' . $rows, $OrderDetails->frequency);
          $sheet->setCellValue('N' . $rows, $OrderDetails->last_order);
          $sheet->setCellValue('O' . $rows, $OrderDetails->retailer_name);
          $sheet->setCellValue('P' . $rows, $OrderDetails->retailer_city);
          $rows++;
          }
          $rand = $lang.rand();
          $fileName = "buvon_".$rand.".".$today.".".$type;
          if($type == 'xlsx') {
          $writer = new Xlsx($spreadsheet);
          $writer->save("reports/".$fileName);
          header("Content-Type: application/vnd.ms-excel");

          $url = url('reports/');
          $records = new SalesReports;
          $records->user_id = $user_id;
          $records->filename = $fileName;
          $records->file_path = $url;
          $records->file_type = strtoupper($type);
          $records->save();
          $success  = $records;
          $message  = Lang::get("messages.retailer_user_list");
          return sendResponse($success, $message);
          }
          elseif($type == 'csv')
          {
          $writer = new Csv($spreadsheet);
          $writer->save("reports/".$fileName);
          header("Content-Type: application/vnd.ms-excel");

          $url = url('reports/');
          $records = new SalesReports;
          $records->user_id = $user_id;
          $records->filename = $fileName;
          $records->file_path = $url;
          $records->file_type = strtoupper($type);
          $records->save();
          $success  = $records;
          $message  = Lang::get("messages.retailer_user_list");
          return sendResponse($success, $message);
          }
          elseif($type == 'pdf')
          {
          $class = \PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf::class;
          \PhpOffice\PhpSpreadsheet\IOFactory::registerWriter('Pdf', $class);
          $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Pdf');
          $writer->save("reports/".$fileName);
          header("Content-type:application/pdf");

          $url = url('reports/');
          $records = new SalesReports;
          $records->user_id = $user_id;
          $records->filename = $fileName;
          $records->file_path = $url;
          $records->file_type = strtoupper($type);
          $records->save();
          $success  = $records;
          $message  = Lang::get("messages.retailer_user_list");
          return sendResponse($success, $message);
          }}
          else{
          return sendError('Access Denied', ['error' => Lang::get("Unable to insert data")], 403);
          }

   }
/* report sections */
   public function getsalesReport()
   {
    if($this->permission !== "reports-view")
   {
     return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
    }
    $user_id = Auth::user()->id;
    $reports = SupplierReports::all("created_at","filename","file_path","file_type","user_id")
    ->where('user_id','=',$user_id);

    $success  = $reports;
    $message  = Lang::get("messages.retailer_user_list");
    return sendResponse($success, $message);
    }

  public function getInventoryReport()
  {
    if($this->permission !== "reports-view")
   {
     return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
    }
    $user_id = Auth::user()->id;
    $reports = InventoryReports::all("created_at","filename","file_path","file_type","user_id")->where('user_id','=',$user_id);
    $success  = $reports;
    $message  = Lang::get("messages.retailer_user_list");
    return sendResponse($success, $message);
    }

  public function getCustomReports()
  {
    if($this->permission !== "reports-view")
   {
     return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
    }
    $user_id = Auth::user()->id;
    $reports = CustomReports::all("created_at","filename","file_path","file_type","user_id")->where('user_id','=',$user_id);
    $success  = $reports;
    $message  = Lang::get("messages.retailer_user_list");
    return sendResponse($success, $message);
}
public function getSuperSalesReports()
{
  if($this->permission !== "reports-view")
 {
   return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
  }
  $user_id = Auth::user()->id;
  $reports = SalesReports::all("created_at","filename","file_path","file_type","user_id")->where('user_id','=',$user_id);
  $success  = $reports;
  $message  = Lang::get("messages.retailer_user_list");
  return sendResponse($success, $message);
}


// dashboard viewSupplier


  public function GetfullWarehouses(request $request)
  {
//  if($this->permission !== "dashboard-view")
//  {
//    return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
//  }
//      $user_id = Auth::user()->id;
        // $user_id = 101;
      $orders = Warehouse::select(DB::raw('COUNT(id) as total_wh')
      , '*')
    ->get();
    dd($orders);
      $success  = $orders;
      $message  = Lang::get("messages.topRetailerList");
      return sendResponse($success, $message);
      }



  public function TotalCustomers(request $request)
      {
      if($this->permission !== "dashboard-view")
      {
        return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
      }
            // $user_id = 101;
$orders =User::select('user_type_id', DB::raw('COUNT(*) as count'))
    ->groupBy('user_type_id')
    ->get();

          $success  = $orders;
          $message  = Lang::get("messages.topRetailerList");
          return sendResponse($success, $message);
          }
}
