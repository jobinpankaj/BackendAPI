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
use App\Traits\UserTrait;
use Lang;
use Auth;

class UsersController extends Controller
{
    use UserTrait;
    public $guard_name;
    public $permission;

    public function __construct(Request $request){
        $headers = getallheaders();
        $this->permission = $headers['permission'] ? $headers['permission'] : "";
        // $userType = $headers['usertype'];
        // $this->guard_name= $userType;   
    }

    // supplier user api's

    public function suppliersList(Request $request)
    {
        if($this->permission !== "supplier-view")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }
        $data = $this->getUserList($request,"3");
        $success  = $data;
        $message  = Lang::get("messages.supplier_user_list");
        return sendResponse($success, $message);   
    }

    public function supplierFilterList()
    {
        if($this->permission !== "supplier-view")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }
        $data = User::select('id','first_name','last_name')->where("user_type_id","3")->whereNotNull('first_name')->get();
        $success  = $data;
        $message  = Lang::get("messages.supplier_user_list");
        return sendResponse($success, $message);   
    }

    public function getSupplierUserData(Request $request,$id="")
    {
        if($this->permission !== "supplier-view")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }
        $userData = $this->getUserData($id,"3");
        return $userData;
    }
    
    public function addSupplierUser(Request $request, $id="")
    {
        if($this->permission !== "supplier-edit")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }
        
        $returnData = $this->addUserInformation($request,$user_type_id=3,$id);
        return $returnData;
    }
    
    public function addSupplierProfile(Request $request)
    {
        if($this->permission !== "supplier-edit")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }

        $user_id = $request->input("user_id");
        $rules['user_id'] = 'required';
        if($user_id) {
            $userProfileData = UserProfile::where("user_id",$user_id)->first();
            $userBillingInfoData = UserBillingAddress::where("user_id",$user_id)->first();

            if($userProfileData){
                $rules['company_name'] = 'required|unique:user_profiles,company_name,'.$user_id.',user_id';
                $rules['alcohol_production_permit'] = 'nullable|unique:user_profiles,alcohol_production_permit,'.$user_id.',user_id';
                $rules['contact_email'] = 'nullable|email|unique:user_profiles,contact_email,'.$user_id.',user_id';
                $rules['phone_number'] = 'nullable|unique:user_profiles,phone_number,'.$user_id.',user_id';
                $rules['website_url'] = 'nullable|unique:user_profiles,website_url,'.$user_id.',user_id';
            }
            else{
                $rules['company_name'] = 'nullable|unique:user_profiles';
                $rules['contact_email'] = 'nullable|email|unique:user_profiles';
                $rules['phone_number'] = 'nullable|unique:user_profiles';
                $rules['website_url'] = 'nullable|unique:user_profiles';
                $rules['alcohol_production_permit'] = 'nullable|unique:user_profiles';
            }
            if($userBillingInfoData){
                $rules['gst_registration_number'] = 'nullable|unique:user_billing_addresses,gst_registration_number,'.$user_id.',user_id';
                $rules['qst_registration_number'] = 'nullable|unique:user_billing_addresses,qst_registration_number,'.$user_id.',user_id';
            }
            else{
                $rules['order_number_prefix'] = 'nullable';
                $rules['gst_registration_number'] = 'nullable|unique:user_billing_addresses';
                $rules['qst_registration_number'] = 'nullable|unique:user_billing_addresses';
            }
        }

        if($request->file("alcohol_production_permit_image")){
            $rules['alcohol_production_permit_image'] = 'required|mimes:png,jpg,jpeg|max:2048'; 
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) return sendError(Lang::get('validation_error'), $validator->errors(), 422);

        $user = User::find($user_id);
        if($user)
        {
            $this->addUserProfile($request,$user_id);
            $this->addUserBillingAddress($request,$user_id);
            $success  = [];
            $message  = Lang::get("messages.created_successfully");
            return sendResponse($success, $message);
        }
        else{
            return sendError('Not Found', ['error' => Lang::get("messages.not_found")], 404);
        }
    }

    public function addSupplierAddress(Request $request)
    {
        if($this->permission !== "supplier-edit")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }
        // dd($user_id);
        $validator = Validator::make($request->all(), [
            'main_address' => 'nullable',
            'user_id' => 'required',
        ]);
        $user_id = $request->input("user_id");

        if ($validator->fails()) return sendError(Lang::get('validation_error'), $validator->errors(), 422);

        $user = User::find($user_id);
        if($user)
        {
            $this->addUserMainAddress($request,$user_id);
            $this->addUserBillingAddress($request,$user_id);
            $success  = [];
            $message  = Lang::get("messages.created_successfully");
            return sendResponse($success, $message);
        }
        else{
            return sendError('Not Found', ['error' => Lang::get("messages.not_found")], 404);
        }
    }

    public function getSupplierDefaultPermissions(Request $request)
    {
        if($this->permission !== "supplier-edit")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }
        $permissions = array(
                            'role-management' => [
                                "1" => "role-view",
                                "2" => "role-edit"
                            ],
                            'user-management' => [
                                "3" => "user-view",
                                "4" => "user-edit"
                            ],
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
        $message          = Lang::get("messages.permission_list");
        return sendResponse($success, $message);
    }

    public function storeSupplierPermissions(Request $request)
    {
        if($this->permission !== "supplier-edit")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }
        
        $returnData = $this->storeUserPermissions($request);
        return $returnData;
    }

    public function updateUserStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'status' => 'required' // 1/0
        ]);

        if ($validator->fails()) return sendError(Lang::get('messages.validation_error'), $validator->errors(), 422);

        $user = User::find($request->input("user_id"));
        if($user)
        {
            $user->update(["status"=>$request->input("status")]);
            $success  = [];
            $message  = Lang::get("messages.user_status_updated");
            return sendResponse($success, $message);
        }
        else {
            return sendError(Lang::get('messages.not_found'), Lang::get('messages.not_found'), 404);
        }
    }

// distributor's api
    public function distributorsList(Request $request)
    {
        if($this->permission !== "distributor-view")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }
        $data = $this->getUserList($request,"2");
        $success  = $data;
        $message  = Lang::get("messages.distributor_user_list");
        return sendResponse($success, $message);
    }

    public function distributorFilterList()
    {
        if($this->permission !== "distributor-view")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }
        $data = User::select('id','first_name','last_name')->where("user_type_id","2")->whereNotNull('first_name')->get();
        $success  = $data;
        $message  = Lang::get("messages.distributor_user_list");
        return sendResponse($success, $message);   
    }
    
    public function addDistributorUser(Request $request,$id="")
    {
        if($this->permission !== "distributor-edit")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }
        
        $returnData = $this->addUserInformation($request,$user_type_id=2,$id);
        return $returnData;
    }
    
    public function addDistributorProfile(Request $request)
    {
        if($this->permission !== "distributor-edit")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }
        
        $user_id = $request->input("user_id");
        $rules['user_id'] = 'required';
        if($user_id) {
            $userProfileData = UserProfile::where("user_id",$user_id)->first();
            $userBillingInfoData = UserBillingAddress::where("user_id",$user_id)->first();

            if($userProfileData){
                $rules['company_name'] = 'required|unique:user_profiles,company_name,'.$user_id.',user_id';
                $rules['alcohol_production_permit'] = 'nullable|unique:user_profiles,alcohol_production_permit,'.$user_id.',user_id';
                $rules['contact_email'] = 'nullable|email|unique:user_profiles,contact_email,'.$user_id.',user_id';
                $rules['phone_number'] = 'nullable|unique:user_profiles,phone_number,'.$user_id.',user_id';
                $rules['website_url'] = 'nullable|url|unique:user_profiles,website_url,'.$user_id.',user_id';
            }
            else{
                $rules['company_name'] = 'nullable|unique:user_profiles';
                $rules['contact_email'] = 'nullable|email|unique:user_profiles';
                $rules['phone_number'] = 'nullable|unique:user_profiles';
                $rules['website_url'] = 'nullable|url|unique:user_profiles';
                $rules['alcohol_production_permit'] = 'nullable|unique:user_profiles';
            }
            if($userBillingInfoData){
                $rules['gst_registration_number'] = 'nullable|unique:user_billing_addresses,gst_registration_number,'.$user_id.',user_id';
                $rules['qst_registration_number'] = 'nullable|unique:user_billing_addresses,qst_registration_number,'.$user_id.',user_id';
            }
            else{
                $rules['gst_registration_number'] = 'nullable|unique:user_billing_addresses';
                $rules['qst_registration_number'] = 'nullable|unique:user_billing_addresses';
            }
        }

        if($request->file("alcohol_production_permit_image")){
            $rules['alcohol_production_permit_image'] = 'required|mimes:png,jpg,jpeg|max:2048'; 
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) return sendError(Lang::get('validation_error'), $validator->errors(), 422);

        $user = User::find($user_id);
        if($user)
        {
            $this->addUserProfile($request,$user_id);
            $this->addUserBillingAddress($request,$user_id);
            $success  = [];
            $message  = Lang::get("messages.created_successfully");
            return sendResponse($success, $message);
        }
        else{
            return sendError('Not Found', ['error' => Lang::get("messages.not_found")], 404);
        }
    }    

    public function addDistributorAddress(Request $request)
    {
        if($this->permission !== "distributor-edit")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }
        $rules['main_address'] = 'nullable';
        $rules['user_id'] = 'required';

        if($request->file("upload_logo")){
            $rules['upload_logo'] = 'required|mimes:png,jpg,jpeg|max:2048'; 
        }
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) return sendError(Lang::get('validation_error'), $validator->errors(), 422);

        $user_id = $request->input("user_id");
        $user = User::find($user_id);
        if($user)
        {
            $this->addUserMainAddress($request,$user_id);
            $this->addUserBillingAddress($request,$user_id);
            $success  = [];
            $message  = Lang::get("messages.created_successfully");
            return sendResponse($success, $message);
        }
        else{
            return sendError('Not Found', ['error' => Lang::get("messages.not_found")], 404);
        }
    }

    public function getDistributorDefaultPermissions(Request $request)
    {
        if($this->permission !== "distributor-edit")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }
        $permissions = array(
                            'role-management' => [
                                "1" => "role-view",
                                "2" => "role-edit"
                            ],
                            'user-management' => [
                                "3" => "user-view",
                                "4" => "user-edit"
                            ],
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
                                "14"=>"order-edit",
                            ],
                            'inventory-management' => [
                                "15" => "inventory-view",
                                "16" => "inventory-edit"
                            ],
                            'product-management' => [
                                "17" => "product-view"
                            ],
                            'routes-management' => [
                                "19" => "routes-view",
                                "20" => "routes-edit"
                            ],
                            'shipment-management' => [
                                "21" => "shipment-view",
                                "22" => "shipment-edit"
                            ],
                            'reports-management' => [
                                "23" => "reports-view",
                            ],
                            'delivery-user-management' => [
                                "33" => "delivery-user-view",
                                "34" => "delivery-user-edit"
                            ],
                        );
        $success['permissions']  = $permissions;
        $message  = Lang::get("messages.permission_list");
        return sendResponse($success, $message);
    }

    public function storeDistributorPermissions(Request $request)
    {
        if($this->permission !== "distributor-edit")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }
        
        $returnData = $this->storeUserPermissions($request);
        return $returnData;
    }

    public function getDistributorUserData(Request $request,$id="")
    {
        if($this->permission !== "distributor-view")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }
        $userData = $this->getUserData($id,"2");
        return $userData;
    }

// retailers Api's
    public function retailersList(Request $request)
    {
        if($this->permission !== "retailer-view")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }
        $data = $this->getUserList($request,"4");
        $success  = $data;
        $message  = Lang::get("messages.retailer_user_list");
        return sendResponse($success, $message);
    }

    public function retailerFilterList()
    {
        if($this->permission !== "retailer-view")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }
        $data = User::select('id','first_name','last_name')->where("user_type_id","4")->whereNotNull('first_name')->get();
        $success  = $data;
        $message  = Lang::get("messages.retailer_user_list");
        return sendResponse($success, $message);   
    }
    
    public function addRetailerUser(Request $request,$id="")
    {
        if($this->permission !== "retailer-edit")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }
        
        $returnData = $this->addUserInformation($request,$user_type_id=4,$id);
        return $returnData;
    }

    public function addRetailerProfile(Request $request)
    {
        if($this->permission !== "retailer-edit")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }
        
        $user_id = $request->input("user_id");
        $rules['user_id'] = 'required';
        if($user_id) {
            $userProfileData = UserProfile::where("user_id",$user_id)->first();

            if($userProfileData){
                $rules['business_name'] = 'required|unique:user_profiles,business_name,'.$user_id.',user_id';
                $rules['contact_email'] = 'nullable|email|unique:user_profiles,contact_email,'.$user_id.',user_id';
                $rules['phone_number'] = 'nullable|unique:user_profiles,phone_number,'.$user_id.',user_id';
                $rules['public_phone_number'] = 'nullable|unique:user_profiles,public_phone_number,'.$user_id.',user_id';
                $rules['website_url'] = 'nullable|url|unique:user_profiles,website_url,'.$user_id.',user_id';
            }
            else{
                $rules['business_name'] = 'nullable|unique:user_profiles';
                $rules['contact_email'] = 'nullable|email|unique:user_profiles';
                $rules['phone_number'] = 'nullable|unique:user_profiles';
                $rules['website_url'] = 'nullable|url|unique:user_profiles';
                $rules['public_phone_number'] = 'nullable|unique:user_profiles';
            }
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) return sendError(Lang::get('validation_error'), $validator->errors(), 422);

        $user = User::find($user_id);
        if($user)
        {
            $this->addUserProfile($request,$user_id);
            $this->addUserMainAddress($request,$user_id);

            $permissionsArr = [1,2,3,4,9,11,13,14,17,23,24,29,30,31,32];
            $role_name = "user_role_".$user_id;
            $role = Role::where("name",$role_name)->first();
            if($role == null)
            {
                $insertData = array(
                                "name" => $role_name,
                                "guard_name" => "api",
                                );
                $role = Role::create($insertData);
                $user->assignRole($role);
                $permissions = Permission::whereIn("id",$permissionsArr)->where("guard_name","=","api")->pluck('id','id');
                
                $role->syncPermissions($permissions);
            }
            $success  = [];
            $message  = Lang::get("messages.created_successfully");
            return sendResponse($success, $message);
        }
        else{
            return sendError('Not Found', ['error' => Lang::get("messages.not_found")], 404);
        }
    }

    public function getRetailerUserData(Request $request,$id="")
    {
        if($this->permission !== "retailer-view")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }
        $userData = $this->getUserData($id,"4");
        return $userData;
    }
    
// 
    public function deliveryUserList()
    {
        $data = User::where("user_type_id","5")->get();
        $success  = $data;
        $message  = Lang::get("messages.delivery_user_list");
        return sendResponse($success, $message);
    }

    public function addDeliveryUser(Request $request)
    {
        $rules = [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|unique:users',
            'password' => 'required',
            'phone_number' => 'required|unique:users',
            'address' => 'required',
            'city' => 'required',
            'state' => 'required',
            'country' => 'required'
        ];
        // dd($request->file("user_image"));
        if($request->file("user_image")){
            $rules['user_image'] = 'required|mimes:png,jpg,jpeg|max:204';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) return sendError(Lang::get('validation_error'), $validator->errors(), 422);
        
        $insertUserData = array(
                        "name" => $request->input("name"),
                        "email" => $request->input("email"),
                        "password" => Hash::make($request->input("password")),
                        "phone_number" => $request->input("phone_number"),
                        "address" => $request->input("address"),
                        "city" => $request->input("city"),
                        "state" => $request->input("state"),
                        "country" => $request->input("country"),
                        "user_type_id" => "5",
                        "status" => ($request->input("status")) ? $request->input("status") : "1",
                        "added_by" => $request->user()->id,
                        );
        $user = User::create($insertUserData);
        $user_id = $user->id;
        if($request->file("user_image")){

        }
        $success  = [];
        $message  = Lang::get("messages.created_successfully");
        return sendResponse($success, $message);
    }

    //DeleteUser

    public function deleteUser(request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if ($validator->fails()) return sendError(Lang::get('messages.validation_error'), $validator->errors(), 422);

        $user = User::find($request->input("id"));
        if($user)
        {
            $user->delete();
            $success  = [];
            $message  = Lang::get("user_deleted_successfully");
            return sendResponse($success, $message);
        }
        else {
            return sendError(Lang::get('user_not_found'), Lang::get('user_not_found'), 404);
        }
    }


}
