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
use App\Models\ProfilePermit;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Traits\UserTrait;
use Lang;
use Auth;

class ProfileController extends Controller
{
    use UserTrait;

    public function getRetailerData(Request $request)
    {
        $user_id = Auth::user()->id;
        $userData = $this->getUserData($user_id,"4");
        return $userData;
    }

    public function editRetailerInfo(Request $request)
    {
        $user_id = Auth::user()->id;
        $returnData = $this->addUserInformation($request,$user_type_id=4,$user_id);
        return $returnData;
    }

    public function createRetailerProfile(Request $request)
    {
        $user_id = Auth::user()->id;
        if($user_id) {
            $userProfileData = UserProfile::where("user_id",$user_id)->first();

            if($userProfileData){
                $rules['business_name'] = 'required|unique:user_profiles,business_name,'.$user_id.',user_id';
                $rules['contact_email'] = 'nullable|email|unique:user_profiles,contact_email,'.$user_id.',user_id';
                $rules['phone_number'] = 'nullable|unique:user_profiles,phone_number,'.$user_id.',user_id';
                $rules['public_phone_number'] = 'nullable|unique:user_profiles,public_phone_number,'.$user_id.',user_id';
                $rules['website_url'] = 'nullable|url|unique:user_profiles,website_url,'.$user_id.',user_id';
                if($request->input("alcohol_permit"))
                {
                    if($request->input("permit_numbers"))
                    {
                        $permitData = ProfilePermit::where("user_profile_id","<>",$userProfileData->id)->whereNotIn("permit_number",$request->input("permit_numbers"))->get();
                        if($permitData->count() > 0)
                        {
                            $errors = ["permit_numbers"=>"Already exists."];               
                            if ($validator->fails()){
                                return sendError(Lang::get('validation_error'), $errors, 422);
                            }
                        }
                    }
                    else{
                        $rules['permit_numbers'] = 'required';
                    }
                }
            }
            else{
                $rules['business_name'] = 'required|unique:user_profiles';
                $rules['contact_email'] = 'nullable|email|unique:user_profiles';
                $rules['phone_number'] = 'nullable|unique:user_profiles';
                $rules['website_url'] = 'nullable|url|unique:user_profiles';
                $rules['public_phone_number'] = 'nullable|unique:user_profiles';
                if($request->input("alcohol_permit"))
                {
                    $rules['permit_numbers'] = 'required';
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
        else{
            return sendError('Not Found', ['error' => Lang::get("messages.not_found")], 404);
        }
    }

    public function saveRetailerMainAddress(Request $request)
    {
        $user_id = Auth::user()->id;
        if($user_id)
        {
            $validator = Validator::make($request->all(), [
                'main_address' => 'required',
            ]);
            if ($validator->fails()) return sendError(Lang::get('validation_error'), $validator->errors(), 422);

            $user = User::find($user_id);
            if($user)
            {
                $this->addUserMainAddress($request,$user_id);
                $this->addUserShippingAddress($request,$user_id);
                $success  = [];
                $message  = Lang::get("messages.created_successfully");
                return sendResponse($success, $message);
            }
            else{
                return sendError('Not Found', ['error' => Lang::get("messages.not_found")], 404);
            }
        }
        else{
            return sendError('Not Found', ['error' => Lang::get("messages.not_found")], 404);
        }
    }

    public function saveRetailerBillingAddress(Request $request)
    {
        $user_id = Auth::user()->id;
        if($user_id) {
            $userBillingInfoData = UserBillingAddress::where("user_id",$user_id)->first();

            if($userBillingInfoData) {
                $rules['gst_registration_number'] = 'nullable|unique:user_billing_addresses,gst_registration_number,'.$user_id.',user_id';
                $rules['qst_registration_number'] = 'nullable|unique:user_billing_addresses,qst_registration_number,'.$user_id.',user_id';
            }
            else {
                $rules['gst_registration_number'] = 'nullable|unique:user_billing_addresses';
                $rules['qst_registration_number'] = 'nullable|unique:user_billing_addresses';
            }
        }

        if($request->file("upload_business_certificate")){
            $rules['upload_business_certificate'] = 'required|mimes:png,jpg,jpeg|max:2048'; 
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) return sendError(Lang::get('validation_error'), $validator->errors(), 422);

        $user = User::find($user_id);
        if($user)
        {
            // $this->addUserProfile($request,$user_id);
            $this->addUserBillingAddress($request,$user_id);
            $success  = [];
            $message  = Lang::get("messages.created_successfully");
            return sendResponse($success, $message);
        }
        else{
            return sendError('Not Found', ['error' => Lang::get("messages.not_found")], 404);
        }
    }

    public function getDistributorInfo(Request $request)
    {
        $user_id = Auth::user()->id;
        $userData = $this->getUserData($user_id,"2");
        return $userData;
    }

    public function editDistributorInfo(Request $request)
    {
        $user_id = Auth::user()->id;
        $returnData = $this->addUserInformation($request,$user_type_id=2,$user_id);
        return $returnData;
    }

    public function getSupplierData(Request $request)
    {
        $user_id = Auth::user()->id;
        $userData = $this->getUserData($user_id,"3");
        return $userData;
    }

    public function editSupplierInfo(Request $request)
    {
        $user_id = Auth::user()->id;
        $returnData = $this->addUserInformation($request,$user_type_id=3,$user_id);
        return $returnData;
    }

    public function saveSupplierProfile(Request $request)
    {
        $user_id = Auth::user()->id;
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
                $rules['company_name'] = 'required|unique:user_profiles';
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
            $message  = Lang::get("messages.profile_updated_successfully");
            return sendResponse($success, $message);
        }
        else{
            return sendError('Not Found', ['error' => Lang::get("messages.not_found")], 404);
        }
    }

    public function saveSupplierAddress(Request $request)
    {
        $user_id = Auth::user()->id;
        // dd($user_id);
        $validator = Validator::make($request->all(), [
            'main_address' => 'required'
        ]);
        if ($validator->fails()) return sendError(Lang::get('validation_error'), $validator->errors(), 422);

        $user = User::find($user_id);
        if($user)
        {
            $this->addUserMainAddress($request,$user_id);
            $this->addUserBillingAddress($request,$user_id);
            $success  = [];
            $message  = Lang::get("messages.profile_updated_successfully");
            return sendResponse($success, $message);
        }
        else{
            return sendError('Not Found', ['error' => Lang::get("messages.not_found")], 404);
        }
    }

    public function saveDistributorProfile(Request $request)
    {
        $user_id = Auth::user()->id;
        if($user_id) {
            $userProfileData = UserProfile::where("user_id",$user_id)->first();
            $userBillingInfoData = UserBillingAddress::where("user_id",$user_id)->first();

            if($userProfileData){
                $rules['company_name'] = 'required|unique:user_profiles,company_name,'.$user_id.',user_id';
                $rules['contact_email'] = 'nullable|email|unique:user_profiles,contact_email,'.$user_id.',user_id';
                $rules['phone_number'] = 'nullable|unique:user_profiles,phone_number,'.$user_id.',user_id';
                $rules['website_url'] = 'nullable|unique:user_profiles,website_url,'.$user_id.',user_id';
            }
            else{
                $rules['company_name'] = 'required|unique:user_profiles';
                $rules['contact_email'] = 'nullable|email|unique:user_profiles';
                $rules['phone_number'] = 'nullable|unique:user_profiles';
                $rules['website_url'] = 'nullable|unique:user_profiles';
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

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) return sendError(Lang::get('validation_error'), $validator->errors(), 422);

        $user = User::find($user_id);
        if($user)
        {
            $this->addUserProfile($request,$user_id);
            $this->addUserBillingAddress($request,$user_id);
            $success  = [];
            $message  = Lang::get("messages.profile_updated_successfully");
            return sendResponse($success, $message);
        }
        else{
            return sendError('Not Found', ['error' => Lang::get("messages.not_found")], 404);
        }
    }

    public function saveDistributorAddress(Request $request)
    {
        $user_id = Auth::user()->id;
        // dd($user_id);
        $validator = Validator::make($request->all(), [
            'main_address' => 'required'
        ]);
        if ($validator->fails()) return sendError(Lang::get('validation_error'), $validator->errors(), 422);

        $user = User::find($user_id);
        if($user)
        {
            $this->addUserMainAddress($request,$user_id);
            $this->addUserBillingAddress($request,$user_id);
            $success  = [];
            $message  = Lang::get("messages.profile_updated_successfully");
            return sendResponse($success, $message);
        }
        else{
            return sendError('Not Found', ['error' => Lang::get("messages.not_found")], 404);
        }
    }
}
