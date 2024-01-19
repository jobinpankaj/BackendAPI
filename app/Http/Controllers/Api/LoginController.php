<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\BusinessCategory;
use App\Models\SiteLanguage;
use App\Models\UserType;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Mail;
use App\Mail\ForgotPasswordLink;
use App\Mail\EmailVerificationLink;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    /**
     * User login API method
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required',
            "usertype" => 'required',
        ]);

        if ($validator->fails()) return sendError(Lang::get('messages.validation_error'), $validator->errors(), 422);
        $userTypeData = UserType::where("name","=",$request->input("usertype"))->first();
        if(!$userTypeData)
        {
            return sendError('Unauthorised', ['error' => Lang::get("messages.something_went_wrong")], 500);
        }

        $credentials = array(
                            "email" => $request->input("email"),
                            "password" => $request->input("password"),
                            'user_type_id' => $userTypeData->id,
                        );
                    
        if (Auth::attempt($credentials)) {

            $user             = Auth::user();
            if($user->status == "1"){
                if(!empty($user->email_verified_at))
                {
                    $added_by_user_type_id = ($user->addedByUser()->first()) ? $user->addedByUser()->first()->user_type_id : '';
                    $success["loginCount"] = ($added_by_user_type_id == "1" && in_array($user->user_type_id,['3','2'])) ? \DB::table("oauth_access_tokens")->where("user_id",$user->id)->get()->count() : "2" ;
                    $success['first_name']  = $user->first_name;
                    $success['last_name']  = $user->last_name;
                    $success['user_image']  = $user->user_image;
                    $success['usertype']  = strtolower($user->userType()->first()->name);
                    $success['token'] = $user->createToken('accessToken')->accessToken;
                    $success["permissions"] = $user->getAllPermissions();
                    $success["userProfile"] = $user->userProfile()->first();
                    $user->permission_revised = "0";
                    if($user->user_type_id == "4"){
                        $success['cartListing'] = Cart::where('user_id',$user->id)->with('productInfo')->get();
                    }
                    $user->save();

                    return sendResponse($success, Lang::get('messages.login_successfully'));
                }
                else{
                    return sendError('Bad Request', ['error' => Lang::get('messages.email_not_verified')], 400);       
                }
            }
            else{
                return sendError('Unauthorised', ['error' => Lang::get('messages.not_authorized')], 401);
            }
        } else {
            return sendError('Bad Request', ['error' => Lang::get('messages.email_password_wrong')], 400);
        }
    }

    public function loginWithOtherUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
        ]);

        if ($validator->fails()) return sendError(Lang::get('messages.validation_error'), $validator->errors(), 422);
        $user_id = $request->input("user_id");

        if (Auth::loginUsingId($user_id)) {

            $user             = Auth::user();
            $success["loginCount"] =  "2" ;
            $success['first_name']  = $user->first_name;
            $success['last_name']  = $user->last_name;
            $success['user_image']  = $user->user_image;
            $success['usertype']  = strtolower($user->userType()->first()->name);
            $success['token'] = $user->createToken('accessToken')->accessToken;
            $success["permissions"] = $user->getAllPermissions();
            $success["userProfile"] = $user->userProfile()->first();
            $user->permission_revised = "0";
            // if($user->user_type_id == "4"){
            //     $success['cartListing'] = Cart::where('user_id',$user->id)->with('productInfo')->get();
            // }
            $user->save();

            return sendResponse($success, Lang::get('messages.login_successfully'));
               
        } else {
            return sendError('Bad Request', ['error' => Lang::get('messages.email_password_wrong')], 400);
        }
    }

    
    /**
     * User logout API method
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        if (Auth::check()) {
            Auth::user()->token()->revoke();
            $success['data'] = [];
            return sendResponse($success, Lang::get("messages.logout_successfully"));
        }else{
            return sendError('Unauthorised', ['error' => 'Unauthorised'], 401);
        }
    }

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
        ]);

        if ($validator->fails()) return sendError(Lang::get('messages.validation_error'), $validator->errors(), 422);
        $user = User::where(["email" => $request->input('email'), "status" => "1"])->first();
        if($user)
        {
            $email = $request->input("email");
            $code = uniqid();
            \DB::table('password_resets')->where("email",$email)->delete();
            \DB::table('password_resets')->insert(["email" => $email,"token"=>$code]);
            // $url = url("emailVerified?email=".$email);
            // $front_base_url = ($user->user_type_id == "1") ? config('app.frontend_admin_url') : config('app.frontend_url');
            $front_base_url = config('app.frontend_admin_url');
            $url = $front_base_url."reset-password?email=".$user->email."&code=".$code;
            $content = '';
            $content .= '<p>'.Lang::get("messages.forgot_password_email_content_1").'<a href="'.$url.'" target="_blank" style="text-decoration:none;">'.Lang::get("messages.click_here").'</a>'.Lang::get("messages.forgot_password_email_content_2").'</p>';
            $details = [
                'name' => $user->first_name.' '.$user->last_name,
                'body' => $content,
                'subject' => Lang::get('messages.reset_password')
            ];
            try {
                Mail::to($user->email)->send(new ForgotPasswordLink($details));
                $success = [];
                return sendResponse($success, Lang::get('messages.reset_password_link_sent_successfully'));
            }
            catch(Exception $e){
                // dd($e->message());
                return sendError('Not Found', ['error' => $e], 404);
            }
        }
        else {
            return sendError('Not Found', ['error' => Lang::get('messages.user_not_found')], 200);
        }
    }

    /**
     * User registration API method
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function registerRetailer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email|unique:users',
            'password' => 'required|min:8'
        ]);

        if ($validator->fails()) return sendError(Lang::get('messages.validation_error'), $validator->errors(), 422);

        $code = uniqid();
        try {
            $url = config('app.frontend_admin_url')."email-verification?email=".$request->email."&token=".$code;
            $content = '';            
            $content .= '<p>'.Lang::get("messages.email_verification_content_1").'<a href="'.$url.'" target="_blank" style="text-decoration:none;">'.Lang::get("messages.click_here").'</a>'.Lang::get("messages.email_verification_content_2").'</p>';
            $details = [
                'name' => "User",
                'body' => $content,
                'subject' => Lang::get('messages.email_verification_subject')
            ];
            try {
                Mail::to($request->email)->send(new EmailVerificationLink($details));
                $user = User::create([
                    'email'    => $request->email,
                    'password' => Hash::make($request->password),
                    "user_type_id" => "4",
                    "email_verify_token" => $code
                ]);
                $success = [];
                return sendResponse($success, Lang::get('messages.user_registered_successfully'));
            }
            catch(Exception $e){
                // dd($e->message);
                return sendError('Not Found', ['error' => Lang::get('messages.email_not_found')], 400);
            }
        } catch (Exception $e) {
            return sendError('Not Found', ['error' => Lang::get('messages.something_went_wrong')], 500);
        }
    }

    public function emailVerification(Request $request)
    {
        $email = $request->get("email");
        $token = $request->get("token");
        if(!empty($email) && !empty($token)){
            $user = User::where(["email"=>$email,"email_verify_token"=>$token])->first();
            if($user) {
                if($user->email_verified_at)
                {
                    $success = [];
                    return sendResponse($success, Lang::get('messages.already_verified')); 
                }
                else{
                    $user->where("email",$email)->update(["email_verified_at"=> date("Y-m-d H:i:s")]);
                    $success = [];
                    return sendResponse($success, Lang::get('messages.email_verified_successfully'));   
                }
            }
            else {
                return sendError('Not Found', ['error' => Lang::get('messages.user_not_found')], 404);       
            }
        }
        else {
            return sendError('Not Found', ['error' => Lang::get('messages.user_not_found')], 404);
        }
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'code'  => 'required',
            'password' => 'required|min:8',
            'confirm_password' => 'required|min:8|same:password'
        ]);

        if ($validator->fails()) return sendError(Lang::get('messages.validation_error'), $validator->errors(), 422);

        $userInfo = \DB::table('password_resets')->where("email","=",$request->input('email'))->where("token","=",$request->input('code'))->first();
        if($userInfo) {
            $password = Hash::make($request->input('password'));
            $user = User::where("email","=",$request->input("email"))->first();
            $user->password = $password;
            $user->save();
            // User::where("email","=",$request->input("email"))->update(["password"=>$password]);
            \DB::table('password_resets')->where("email","=",$request->input("email"))->delete();
            $success = ["userType" => $user->userType()->first()->name];
            return sendResponse($success, Lang::get('messages.reset_password_successfully'));
        }
        else {
            return sendError('Not Found', ['error' => Lang::get('messages.user_not_found')], 404);
        }
    }

    public function getQrCodeImage(Request $request)
    {
        $userData = Auth::user();
        // dd($userData);
        $success = $this->googleAuthenticatorMethod($userData);
        return sendResponse($success, Lang::get('messages.qr_code_image'));
    }

    public function validateOtp(Request $request)
    {
        $email = Auth::user()->email;
        $otp = $request->input("otp");
        $user = User::where("email",$email)->first();
        $secret = $user->google2fa_secret;

        $google2fa = app('pragmarx.google2fa');
        $success = [];
        if($google2fa->verifyKey($secret, $otp)){
            return sendResponse($success, Lang::get('messages.otp_verified'));
        }
        else{
            return sendError('Not Found', ['error' => Lang::get('messages.otp_expired')], 403);
            // return sendResponse($success, Lang::get('messages.otp_expired'));
        }
    }

    public function googleAuthenticatorMethod($userData)
    {
        $registration_data = $userData;
        $user = User::where("email",$userData["email"])->first();
        if(empty($user['google2fa_secret']))
        {
            $google2fa = app('pragmarx.google2fa');
            $google2fa_secret = $google2fa->generateSecretKey();
            $user->google2fa_secret = $google2fa_secret;
            $user->save(); 
            $QR_Image = $google2fa->getQRCodeInline(
              config('app.name'),
              $userData['email'],
              $google2fa_secret
            );
            return array("qrcode_image"=>base64_encode($QR_Image),"secret" => $google2fa_secret);
        }
        else{
            return array("qrcode_image"=>"","secret" => "");   
        }
    }

    public function getBusinessCategories(Request $request)
    {
        $data = BusinessCategory::where("status","=","1")->get();
        $success = $data;
        return sendResponse($success, Lang::get('messages.business_categories_list'));
    }

    public function getSiteLanguages(Request $request)
    {
        $data = SiteLanguage::where("status","=","1")->get();
        $success = $data;
        return sendResponse($success, Lang::get('messages.site_languages_list'));
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_password' => 'required',
            'new_password' => 'required|min:8',
            'confirm_new_password' => 'required|same:new_password'
        ]);

        if ($validator->fails()) return sendError(Lang::get('messages.validation_error'), $validator->errors(), 422);

        $user = auth()->user();

        if(Hash::check($request->input('old_password'), $user->password)) {

            if(Hash::check($request->input('new_password'), $user->password)) {
                return sendError('New password cannot be same as old password', ['error' => Lang::get('messages.same_password')], 400);
            } else {
                $user->password = Hash::make($request->input('new_password'));
                $user->save();

                Auth::user()->token()->revoke();

                return sendResponse([], Lang::get('messages.password_changed_successfully'));
            }
        } else {
            return sendError('Invalid Old Password', ['error' => Lang::get('messages.invalid_old_password')], 200);
        }
    }
}