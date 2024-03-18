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
use App\Models\Test;
use App\Models\Order;
use App\Models\UserProfile;
use App\Models\ProductStyle;
use App\Models\ProductFormat;
use App\Models\Inventory;
use App\Models\Warehouse;
use App\Models\UserBillingAddress;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Lang;
use Auth;
use DB;
use App\Models\Product;
use App\Models\SupplierReports;
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

public function GetSuppliersProductsName(request $request){
    if($this->permission !== "reports-view")
    {
      return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
    }
    $user_id = Auth::user()->id;
  //$user_id = 101;
  $orders = Product::select("products.product_name")
    ->where('products.user_id', '=', $user_id)
    ->groupBy("products.product_name")
    ->get();
    $success  = $orders;
    $message  = Lang::get("messages.topRetailerList");
    return sendResponse($success, $message);
  }

  public function GetSuppliersProductsType(request $request){
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

    public function GetSuppliersProductStyle(request $request){
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
      public function GetSuppliersProductFormat(request $request){
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

  public function GetRetailers(request $request){
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
   public function GetRetailersCity(request $request){
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

    public function GetSuppliergroup(request $request){
      if($this->permission !== "reports-view")
      {
        return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
      }
       $user_id = Auth::user()->id;
     //$user_id = 101;
       $orders = User::select("groups.name")
         ->join('groups', 'groups.added_by', '=', 'users.id')
         ->where('users.id', '=', $user_id)
         ->groupBy('groups.name')
         ->get();

         $success  = $orders;
         $message  = Lang::get("messages.topRetailerList");
         return sendResponse($success, $message);
     }

      public function GetRetailersList(request $request){
        if($this->permission !== "reports-view")
        {
          return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }
          $user_id = Auth::user()->id;
         $orders = User::select("id","first_name", "last_name")
           ->where('users.added_by', '=', $user_id)
           ->where('users.user_type_id', '=', 4)
           ->whereNotNull('first_name')
           ->get();

           $success  = $orders;
           $message  = Lang::get("messages.topRetailerList");
           return sendResponse($success, $message);
       }

       public function GetSuppliersList(request $request){
         if($this->permission !== "reports-view")
         {
           return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
         }
           $user_id = Auth::user()->id;
          $orders = User::select("id", "first_name", "last_name")
            ->where('users.id', '=', $user_id)
            ->get();

            $success  = $orders;
            $message  = Lang::get("messages.topRetailerList");
            return sendResponse($success, $message);
        }
        
               public function GetWarehousesList(request $request){
         if($this->permission !== "reports-view")
         {
          return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }
          $user_id = Auth::user()->id;
          // $user_id = 101;
          $orders = Warehouse::select("warehouses.name")
            ->where('warehouses.user_id', '=', $user_id)
            ->get();

            $success  = $orders;
            $message  = Lang::get("messages.topRetailerList");
            return sendResponse($success, $message);
        }

     public function PostReportProductList(request $request){
     
         if($this->permission !== "reports-view")
    {
      return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
    }
      $user_id = Auth::user()->id;
/*
     $user_id = 101;

     $cad= "La Prairie";
     $date_type= "created_at";
     $type= "xlsx";
     $from_date= "2023-10-11";
     $group= "Tout marchands";
     $invoice_state= "Pending";
     $lang= "CAfr";
     $order_state= "Approved";
     $product_format= "Can 473ml x 24";
     $product_name= "Berliner Weisse";
     $product_style= "Berliner Weisse";
     $product_type= "Beer";
     $retailer= 127;
     $supplier= 101;
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
      $retailer = $request->input('retailer');
      $supplier = $request->input('supplier');
      $to_date = $request->input('to_date');

  $orders = DB::table('users')
  ->select('orders.id',
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
          DB::raw('ifnull(users.city,0) as users_city'),
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
                         END) AS order_status'))
  ->join('products', 'users.id', '=', 'products.user_id')
  ->join('orders', 'products.user_id', '=', 'orders.supplier_id')
  ->join('product_format_deposit', 'products.user_id', '=', 'product_format_deposit.user_id')
  ->join('product_formats','products.product_format', '=', 'product_formats.id')
  ->join('product_styles','product_styles.id','=','products.style')
  ->whereDate('orders.created_at', '>=', $from_date)
  ->whereDate('orders.created_at', '<=', $to_date)
      ->where('users.id', '=', $user_id)
      ->where('orders.supplier_id', '=', $supplier)
      ->where('orders.retailer_id', '=', $retailer)
      ->having('order_status', '=', $order_state)
      ->having('invoice_status', '=', $invoice_state)
      ->where('products.product_type','=', $product_type)
      ->where('products.product_name', $product_name)
      ->where('product_styles.name', $product_style)
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
      $sheet->setCellValue('E' . $rows, $tr->translate($OrderDetails->users_city));
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
    $writer->save("export/".$fileName);
    header("Content-Type: application/vnd.ms-excel");

    $url = url('export/');
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
    $writer->save("export/".$fileName);
    header("Content-Type: application/vnd.ms-excel");

    $url = url('export/');
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
    $writer->save("export/".$fileName);
    header("Content-type:application/pdf");

    $url = url('export/');
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
  }
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
    $sheet->setCellValue('E' . $rows, $OrderDetails->users_city);
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
    $writer->save("export/".$fileName);
    header("Content-Type: application/vnd.ms-excel");

    $url = url('export/');
    $records = new SupplierReports;
    $records->user_id = $user_id;
    $records->filename = $fileName;
    $records->file_path = $url;
    $records->file_type = strtoupper($type);
    $records->save();
    $success  = $records;
    $message  = Lang::get("messages.retailer_user_list");
    return sendResponse($success, $message);
    } elseif($type == 'csv') {
    $writer = new Csv($spreadsheet);
    $writer->save("export/".$fileName);
    header("Content-Type: application/vnd.ms-excel");

    $url = url('export/');
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
  $writer->save("export/".$fileName);
  header("Content-type:application/pdf");

  $url = url('export/');
  $records = new SupplierReports;
  $records->user_id = $user_id;
  $records->filename = $fileName;
  $records->file_path = $url;
  $records->file_type = strtoupper($type);
  $records->save();
  $success  = $records;
  $message  = Lang::get("messages.retailer_user_list");
  return sendResponse($success, $message);
} }
  else{
   return sendError('Access Denied', ['error' => Lang::get("Unable to insert data")], 403);
  }
     }

  public function getsalesReport(){
    if($this->permission !== "reports-view")
   {
     return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
    }

  $user_id = Auth::user()->id;
  $reports = SupplierReports::all("created_at","filename","file_path","file_type","user_id")->where('user_id','=',$user_id);
  $success  = $reports;
  $message  = Lang::get("messages.retailer_user_list");
  return sendResponse($success, $message);
}
}
