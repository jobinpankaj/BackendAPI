<?php 

namespace App\Traits;

use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\UserMainAddress;
use App\Models\UserBillingAddress;
use App\Models\UserShippingAddress;
use App\Models\ProfilePermit;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Carbon\Carbon;
use Lang;
use Mail;
use Auth;
use App\Mail\OnBoardingMail;
use Illuminate\Support\Facades\Hash;

trait UserTrait
{
    public function addUserInformation($request,$user_type_id,$id)
    {
        if($id)
        {
            $user = User::find($id);
            $rules = [
                'first_name' => 'required',
                'last_name' => 'required',
                'email' => 'required|unique:users,id,'.$id,
                'phone_number' => 'required|unique:users,id,'.$id
            ];
        }
        else {
            $rules = [
                'first_name' => 'required',
                'last_name' => 'required',
                'email' => 'required|unique:users',
                'password' => 'required',
                'phone_number' => 'required|unique:users'
            ];   
        }
        if($request->file("user_image")){
            $rules['user_image'] = 'required|mimes:png,jpg,jpeg,pdf|max:2048';
        }

        $validator = Validator::make($request->all(), $rules);
        $success = [];
        $message = "";

        if ($validator->fails()) return sendError(Lang::get('validation_error'), $validator->errors(), 422);

        $insertUserData = array(
                        "first_name" => $request->input("first_name"),
                        "last_name" => $request->input("last_name"),
                        "email" => $request->input("email"),
                        "phone_number" => $request->input("phone_number"),
                        "user_type_id" => $user_type_id,
                        "added_by" => $request->user()->id,
                        );
        if($id) {
            if($request->input("password")){
                $insertUserData["password"] = Hash::make($request->input("password"));
            }
            $insertUserData["status"] = $request->input("status");
            User::where("id",$id)->update($insertUserData);
            
            $message = Lang::get("user_updated_successfully");
        }
        else{
            if(in_array($request->input("status"),["0","1"])){
                $insertUserData["status"] = $request->input("status");
            }
            $insertUserData["email_verified_at"] = date("Y-m-d H:i:s");
            $insertUserData["password"] = Hash::make($request->input("password"));
            $user = User::create($insertUserData);
            $message = Lang::get("created_successfully");
        }
        $user_id = $user->id;
        if($request->file("user_image")){
            $userImage = $request->file("user_image");
            $res = $userImage->store('profile_images',['disk'=>'public']);
            $user["user_image"] = $res;
            $user->save();
        }
        if(empty($id))
        {
            // mail code start
            $loginArray = array(
                "2" => "distributor/login",
                "3" => "supplier/login",
                "4" => "retailer/login"
                );
            $login_url = config('app.frontend_admin_url')."".$loginArray[$user_type_id];
            $content = '';            
            $content .= '<p>'.Lang::get("messages.on_board_content_1");
            $content .= Lang::get("messages.on_board_content_login_url").'<a href="'.$login_url.'" target="_blank" style="text-decoration:none;">'.$login_url.'</a><br/>';
            $content .= Lang::get("messages.on_board_content_username").$user->email.'<br/>';
            $content .= Lang::get("messages.on_board_content_password").$request->input("password").'<br/>';
            $content .= Lang::get("messages.on_board_content_2").'</p>';
            $details = [
                'name' => $user->full_name,
                'body' => $content,
                'subject' => Lang::get('messages.on_boarding_subject')
            ];
            $success  = $user;
            try {
                Mail::to($user->email)->send(new OnBoardingMail($details));
            }
            catch(Exception $e){
                // return sendError('Not Found', ['error' => Lang::get('messages.something_went_wrong')], 500);
            }
        }
        else {
            $user = $user->refresh();
            $success  = $user;
        }
        // mail code end
        return sendResponse($success, $message);
    }

    public function addUserProfile($insertData, $user_id) 
    {
        $userProfileData = UserProfile::where("user_id",$user_id)->first();
        $insertUserProfileData['user_id'] = $user_id;
        if(isset($insertData['business_name']))
        {
            $insertUserProfileData['business_name'] = $insertData["business_name"];
        }
        if(isset($insertData['group_name']))
        {
            $insertUserProfileData['group_name'] = $insertData["group_name"];
        }
        if(isset($insertData['business_category_id']))
        {
            $insertUserProfileData['business_category_id'] = $insertData["business_category_id"];
        }
        if(isset($insertData['contact_email']))
        {
            $insertUserProfileData['contact_email'] = $insertData["contact_email"];
        }
        if(isset($insertData['public_phone_number']))
        {
            $insertUserProfileData['public_phone_number'] = $insertData["public_phone_number"];
        }
        if(isset($insertData['phone_number']))
        {
            $insertUserProfileData['phone_number'] = $insertData["phone_number"];
        }
        if(isset($insertData['contact_name']))
        {
            $insertUserProfileData['contact_name'] = $insertData["contact_name"];
        }
        if(isset($insertData['website_url']))
        {
            $insertUserProfileData['website_url'] = $insertData["website_url"];
        }
        if(isset($insertData['office_number']))
        {
            $insertUserProfileData['office_number'] = $insertData["office_number"];
        }
        if(isset($insertData['opc_status']))
        {
            $insertUserProfileData['opc_status'] = $insertData["opc_status"];
        }
        if(isset($insertData['home_consumption']))
        {
            $insertUserProfileData['home_consumption'] = $insertData["home_consumption"];
        }
        if(isset($insertData['alcohol_permit']))
        {
            $insertUserProfileData['alcohol_permit'] = $insertData["alcohol_permit"];
        }
        if(isset($insertData['company_name']))
        {
            $insertUserProfileData['company_name'] = $insertData["company_name"];
        }
        if(isset($insertData['alcohol_production_permit']))
        {
            $insertUserProfileData['alcohol_production_permit'] = $insertData["alcohol_production_permit"];
        }
        if(isset($insertData['business_name_status']))
        {
            $insertUserProfileData['business_name_status'] = $insertData["business_name_status"];
        }
        if(isset($insertData['distribution_bucket_status']))
        {
            $insertUserProfileData['distribution_bucket_status'] = $insertData["distribution_bucket_status"];
        }
        if(isset($insertData['have_product_status']))
        {
            $insertUserProfileData['have_product_status'] = $insertData["have_product_status"];
        }
        if(isset($insertData['agency_sell_and_collect_status']))
        {
            $insertUserProfileData['agency_sell_and_collect_status'] = $insertData["agency_sell_and_collect_status"];
        }
        if(isset($insertData['produce_product_status']))
        {
            $insertUserProfileData['produce_product_status'] = $insertData["produce_product_status"];
        }
        if(isset($insertData['order_type']))
        {
            $insertUserProfileData['order_type'] = $insertData["order_type"];
        }
        if(isset($insertData['alcohol_production_limit']))
        {
            $insertUserProfileData['alcohol_production_limit'] = $insertData["alcohol_production_limit"];
        }

        if($insertData->file("alcohol_production_permit_image")){
            $userImage = $insertData->file("alcohol_production_permit_image");
            $res = $userImage->store('alcohol_production_permits',['disk'=>'public']);
            $insertUserProfileData["alcohol_production_permit_image"] = $res;
        }

        $userData = UserProfile::updateOrCreate(["user_id" => $user_id],$insertUserProfileData);

        if($insertData["alcohol_permit"])
        {
            $this->addUserAlcoholPermits($insertData,$userData->id);
        }
        return $userData->id;
    }

    public function addUserAlcoholPermits($insertData, $user_profile_id)
    {
        $data = [];
        if($insertData["permit_numbers"])
        {
            ProfilePermit::where("user_profile_id",$user_profile_id)->delete();
            foreach($insertData['permit_numbers'] as $in)
            {
                $data = ['user_profile_id' => $user_profile_id, 'permit_number' => $in];
                $userData = ProfilePermit::insert($data);
            }
        }
        return $data;
    }

    public function addUserMainAddress($insertData, $user_id)
    {
        $mainAddressData = UserMainAddress::where("user_id",$user_id)->first();
        $insertUserMainAddressData['user_id'] = $user_id;
        if(isset($insertData['main_address']))
        {
            $insertUserMainAddressData['address_1'] = $insertData["main_address"];
        }
        if(isset($insertData['place_id']))
        {
            $insertUserMainAddressData['place_id'] = $insertData["place_id"];
        }
        if(isset($insertData['main_latitude']))
        {
            $insertUserMainAddressData['latitude'] = $insertData["main_latitude"];
        }
        if(isset($insertData['main_longitude']))
        {
            $insertUserMainAddressData['longitude'] = $insertData["main_longitude"];
        }
        if(isset($insertData['main_address_2']))
        {
            $insertUserMainAddressData['address_2'] = $insertData["main_address_2"];
        }
        if(isset($insertData['main_city']))
        {
            $insertUserMainAddressData['city'] = $insertData["main_city"];
        }
        if(isset($insertData['main_postal_code']))
        {
            $insertUserMainAddressData['postal_code'] = $insertData["main_postal_code"];
        }
        if(isset($insertData['main_state']))
        {
            $insertUserMainAddressData['state'] = $insertData["main_state"];
        }
        if(isset($insertData['main_country']))
        {
            $insertUserMainAddressData['country'] = $insertData["main_country"];
        }
        $userData = UserMainAddress::updateOrCreate(["user_id" => $user_id], $insertUserMainAddressData);
        return $userData->id;
    }

    public function addUserShippingAddress($insertData, $user_id)
    {
        $shippingAddressData = UserShippingAddress::where("user_id",$user_id)->first();
        $insertUserShippingAddressData['user_id'] = $user_id;
        if(isset($insertData['delivery_time']))
        {
            $insertUserShippingAddressData['delivery_time'] = $insertData["delivery_time"];
        }
        if(isset($insertData['delivery_notes']))
        {
            $insertUserShippingAddressData['delivery_notes'] = $insertData["delivery_notes"];
        }
        if(isset($insertData['contact_name']))
        {
            $insertUserShippingAddressData['contact_name'] = $insertData["contact_name"];
        }
        if(isset($insertData['phone_number']))
        {
            $insertUserShippingAddressData['phone_number'] = $insertData["phone_number"];
        }
        if(isset($insertData['shipping_address']))
        {
            $insertUserShippingAddressData['address_1'] = $insertData["shipping_address"];
        }
        if(isset($insertData['place_id']))
        {
            $insertUserShippingAddressData['place_id'] = $insertData["place_id"];
        }
        if(isset($insertData['shipping_latitude']))
        {
            $insertUserShippingAddressData['latitude'] = $insertData["shipping_latitude"];
        }
        if(isset($insertData['shipping_longitude']))
        {
            $insertUserShippingAddressData['longitude'] = $insertData["shipping_longitude"];
        }
        if(isset($insertData['shipping_address_2']))
        {
            $insertUserShippingAddressData['address_2'] = $insertData["shipping_address_2"];
        }
        if(isset($insertData['shipping_city']))
        {
            $insertUserShippingAddressData['city'] = $insertData["shipping_city"];
        }
        if(isset($insertData['shipping_postal_code']))
        {
            $insertUserShippingAddressData['postal_code'] = $insertData["shipping_postal_code"];
        }
        if(isset($insertData['shipping_state']))
        {
            $insertUserShippingAddressData['state'] = $insertData["shipping_state"];
        }
        if(isset($insertData['shipping_country']))
        {
            $insertUserShippingAddressData['country'] = $insertData["shipping_country"];
        }
        $userData = UserShippingAddress::updateOrCreate(["user_id" => $user_id], $insertUserShippingAddressData);
        return $userData->id;
    }

    public function addUserBillingAddress($insertData, $user_id)
    {
        $billingAddressData = UserBillingAddress::where("user_id",$user_id)->first();
        $insertUserBillingAddressData['user_id'] = $user_id;
        if(isset($insertData['billing_address_to']))
        {
            $insertUserBillingAddressData['address_to'] = $insertData["billing_address_to"];
        }
        if(isset($insertData['billing_contact_email']))
        {
            $insertUserBillingAddressData['contact_email'] = $insertData["billing_contact_email"];
        }
        if(isset($insertData['billing_phone_number']))
        {
            $insertUserBillingAddressData['phone_number'] = $insertData["billing_phone_number"];
        }
        if(isset($insertData['billing_address']))
        {
            $insertUserBillingAddressData['address_1'] = $insertData["billing_address"];
        }
        if(isset($insertData['billing_place_id']))
        {
            $insertUserBillingAddressData['place_id'] = $insertData["billing_place_id"];
        }
        if(isset($insertData['billing_latitude']))
        {
            $insertUserBillingAddressData['latitude'] = $insertData["billing_latitude"];
        }
        if(isset($insertData['billing_longitude']))
        {
            $insertUserBillingAddressData['longitude'] = $insertData["billing_longitude"];
        }
        if(isset($insertData['billing_address_2']))
        {
            $insertUserBillingAddressData['address_2'] = $insertData["billing_address_2"];
        }
        if(isset($insertData['billing_city']))
        {
            $insertUserBillingAddressData['city'] = $insertData["billing_city"];
        }
        if(isset($insertData['billing_postal_code']))
        {
            $insertUserBillingAddressData['postal_code'] = $insertData["billing_postal_code"];
        }
        if(isset($insertData['billing_state']))
        {
            $insertUserBillingAddressData['state'] = $insertData["billing_state"];
        }
        if(isset($insertData['billing_country']))
        {
            $insertUserBillingAddressData['country'] = $insertData["billing_country"];
        }
        if(isset($insertData['company_number_neq']))
        {
            $insertUserBillingAddressData['company_number_neq'] = $insertData["company_number_neq"];
        }
        if(isset($insertData['billing_company_name']))
        {
            $insertUserBillingAddressData['company_name'] = $insertData["billing_company_name"];
        }
        if(isset($insertData['gst_registration_number']))
        {
            $insertUserBillingAddressData['gst_registration_number'] = $insertData["gst_registration_number"];
        }
        if(isset($insertData['qst_registration_number']))
        {
            $insertUserBillingAddressData['qst_registration_number'] = $insertData["qst_registration_number"];
        }
        if(isset($insertData['order_number_prefix']))
        {
            $insertUserBillingAddressData['order_number_prefix'] = $insertData["order_number_prefix"];
        }
        
        if($insertData->file("upload_logo")){
            $userImage = $insertData->file("upload_logo");
            $res = $userImage->store('distributor_upload_logo',['disk'=>'public']);
            $insertUserBillingAddressData["upload_logo"] = $res;
        }
        $userData = UserBillingAddress::updateOrCreate(["user_id" => $user_id], $insertUserBillingAddressData);
        return $userData->id;
    }

    public function storeUserPermissions($request)
    {
       
        // dd($user_id);
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            // 'permissions' => 'required',
        ]);

        if ($validator->fails()) return sendError(Lang::get('validation_error'), $validator->errors(), 422);
        
        try{
            $user_id = $request->input("user_id");
            $userData = User::find($user_id);
            if($userData){
                $permissions = $request->input("permissions");
                $role_name = "user_role_".$user_id;
                $role = Role::where("name",$role_name)->first();
                if(empty($permissions))
                {
                    if(empty($role))
                    {
                        $success  = [];
                        $message  = Lang::get("messages.user_updated_successfully");
                        return sendResponse($success, $message);
                    }
                    else{
                        $role->syncPermissions([]);
                        $success  = [];
                        $message  = Lang::get("messages.user_updated_successfully");
                        return sendResponse($success, $message);
                    }
                }

                $permissionsArr = explode(",",$request->input("permissions"));
                if($role == null)
                {
                    $insertData = array(
                                    "name" => $role_name,
                                    "guard_name" => "api",
                                    );
                    $role = Role::create($insertData);
                    $userData->assignRole($role);
                }
                $permissions = Permission::whereIn("id",$permissionsArr)->where("guard_name","=","api")->pluck('id','id');
                
                $role->syncPermissions($permissions);
                if (in_array($userData->user_type_id,['2','3'])) {
                    $userData->permission_revised = "1";
                    $userData->save();
                }
                $success  = $role;
                $message  = Lang::get("messages.role_created");
                return sendResponse($success, $message);
            }
            else {
                return sendError('Not Found', ['error' => Lang::get("messages.not_found")], 404);
            }
        }
        catch(Exception $e){
            return sendError('Access Denied', ['error' => $e], 403);
        }
    }

    public function getUserData($id,$user_type_id)
    {
        if($id)
        {
            $user_id = $id;
            $userInfo = User::where("id",$user_id)->where("user_type_id",$user_type_id)->with(['userType','userProfile','userMainAddress','userBillingAddress','userShippingAddress','userRoutes'])->first();
            // dd($id);
            if($userInfo == null)
            {
                return sendError('Not Found', ['error' => Lang::get("messages.user_not_found")], 404);
            }
            $userInfo->userPermissions = ($userInfo->roles()->count() > 0) ? $userInfo->roles()->first()->permissions()->get()->pluck("id") : [];
            $success  = $userInfo;
            $message  = Lang::get("messages.user_information");
            return sendResponse($success, $message);  
        }
        else{
            return sendError('Not Found', ['error' => Lang::get("messages.not_found")], 404);
        } 
    }

    public function getUserList($request,$user_type_id)
    {
        $search = $request->input("search");
        $usersQuery = User::query();
        if(!empty($search)) {
            $usersQuery->where(function($query)use($search,$user_type_id){
                $query->where(function($query1)use($search){
                    $query1->where("first_name",'LIKE',"%".$search."%")->orWhere("last_name",'LIKE',"%".$search."%")->orWhere("email",'LIKE',"%".$search."%")->orWhere("phone_number",'LIKE',"%".$search."%")->orWhereRaw("concat(first_name,' ',last_name) like '%".$search."%'");
                });
                if(in_array($user_type_id,[2,3]))
                {
                    $query = $query->orWhereHas('userProfile',function($query1) use($search){
                        $query1->where("company_name",'LIKE',"%".$search."%");              
                    });
                }
                
                if(in_array($user_type_id,[2,3,4]))
                {
                    $query = $query->orWhereHas('userMainAddress',function($query1) use($search){
                        $query1->where("address_1",'LIKE',"%".$search."%");              
                    });
                }
                if(in_array($user_type_id,[2,3,4]))
                {
                    $query = $query->orWhereHas('userProfile',function($query1) use($search){
                        $query1->where("business_name",'LIKE',"%".$search."%");              
                    });
                }
            });
        }
        $filter_user_id = $request->input("filter_user_id");
        if(!empty($filter_user_id)){
            $usersQuery->where(function($query)use($filter_user_id){
                $query->where("id","=",$filter_user_id);
            });
        }
        $usersQuery->where("user_type_id","=",$user_type_id);
        $usersQuery->where("deleted_at",null);
        $usersQuery->with(['userProfile','userMainAddress','userRoutes']);
        $data = $usersQuery->get();
        return $data;
    }

    public function localSupplierUsers($request)
    {
        $search = $request->input("search");
        $usersQuery = User::query();
        if(!empty($search)) {
            $usersQuery->where(function($query)use($search){
                $query->where(function($query1)use($search){
                    $query1->where("first_name",'LIKE',"%".$search."%")->orWhere("last_name",'LIKE',"%".$search."%")->orWhere("email",'LIKE',"%".$search."%")->orWhere("phone_number",'LIKE',"%".$search."%")->orWhereRaw("concat(first_name,' ',last_name) like '%".$search."%'");
                });
            });
        }
        $usersQuery->where("user_type_id", "=", 3)
        ->whereNotNull("first_name")
        ->whereHas("userMainAddress", function ($query1) {
            $query1->whereNotNull("latitude")
                ->whereNotNull("longitude");
        });
        $data = $usersQuery->select("id","first_name","last_name")->get();
        return $data;
    }
    // public function localSupplierUsers($request)
    // {
    //     $search = $request->input("search");
    //     $usersQuery = User::query();
    //     if(!empty($search)) {
    //         $usersQuery->where(function($query)use($search){
    //             $query->where(function($query1)use($search){
    //                 $query1->where("first_name",'LIKE',"%".$search."%")->orWhere("last_name",'LIKE',"%".$search."%")->orWhere("email",'LIKE',"%".$search."%")->orWhere("phone_number",'LIKE',"%".$search."%")->orWhereRaw("concat(first_name,' ',last_name) like '%".$search."%'");
    //             });
    //         });
    //     }
    //     $usersQuery->where("user_type_id","=",3);
    //     $usersQuery->whereNotNull("first_name");
    //     $data = $usersQuery->select("id","first_name","last_name")->get();
    //     return $data;
    // }
}