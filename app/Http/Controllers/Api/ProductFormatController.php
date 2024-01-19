<?php

namespace App\Http\Controllers\Api;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Http\Controllers\Controller;
use App\Models\ProductFormat;
use Illuminate\Support\Facades\Lang;
use App\Models\ProductFormatDeposit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use App\Models\UserMainAddress;
use App\Models\UserProfile;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
class ProductFormatController extends Controller
{
    public function getProductFormats()
    {
        $data = ProductFormat::where('status', 1)->get();
        $success = $data;
        return sendResponse($success, Lang::get('messages.product_format_list'));
    }

    //depositUpdate
    public function depositUpdate(request $request)
    {
     
        $validator = Validator::make($request->all(), [
            'product_format_id' => 'required',
            'product_format_deposit'    => 'required',

        ]);

        if($validator->fails()) {
            return sendError(Lang::get('validation_error'), $validator->errors(), 422);
        }
        $user = auth()->user();
        $deposit_check = ProductFormatDeposit::where('product_format_id',$request->product_format_id)->where('user_id',$user->id)->first();
        // dd($deposit_check);
        if(!empty($deposit_check))
        {
            $product =$deposit_check->update(['product_format_deposit'=> $request->product_format_deposit]);
        }
        else{
            $product = new ProductFormatDeposit();
            $product->product_format_id = $request->product_format_id;
            $product->user_id = $user->id;
            $product->product_format_deposit = $request->product_format_deposit;
            $product->save();
        }
        

        $success = $product;
        $message = Lang::get("productDepositUpdate successfully");
        return sendResponse($success, $message);
    }

    public function getProductFormatsDeposit()
    {
        $data = ProductFormat::where('status', 1)->get();
        foreach($data as $key => $value)
        {
            $product_format = ProductFormatDeposit::where('product_format_id',$value->id)->where('user_id',auth()->user()->id)->first();
            if(!empty($product_format))
            {
                $value->product_format_deposit = $product_format->product_format_deposit;
            }
            else{
                $value->product_format_deposit = 0;

            }
            
        }
        $success = $data;
        return sendResponse($success, Lang::get('messages.product_format_list'));
    }
    public function uploaddata(Request $request)
    {
        
        set_time_limit(0);
        $file = $request->file;

        $customerArr = $this->csvToArray($file);
        // dd($customerArr);
       
        // Ensure you have at most 100 rows to insert
    $insertLimit = min(count($customerArr), 100);
        // dd($insertLimit);
        for ($i = 0; $i < $insertLimit; $i ++)
        {
            // dd($customerArr[$i]['E-mail Address']);
            //save user
            $user = new User();
            $user->first_name = $customerArr[$i]['First Name'] ?? null;
            $user->last_name = $customerArr[$i]['Last Name'] ?? null;

            $user->email = $customerArr[$i]['E-mail Address'] ?? null;
            $user->user_type_id = 4;
            $user->phone_number = $customerArr[$i]['Contact Number'];
            $user->status = 1;
            $user->added_by =1;
            $current_date_time = Carbon::now()->toDateTimeString();
            // dd($current_date_time);
            $user->email_verified_at = $current_date_time;
            $user->password = Hash::make('Beer32145');

            $user->save();
            if(!empty($user))
            {
                //save buisness name
                $user_profile = new UserProfile();
                $user_profile->user_id = $user->id;
                $user_profile->business_name = $customerArr[$i]['BUSINESS NAME'] ?? null;
                $user_profile->save();
                
                //save address
                $user_data = new UserMainAddress();
                $user_data->user_id = $user->id;
                $user_data->address_1 = $customerArr[$i]['Address'];
                $user_data->address_2 = $customerArr[$i]['Address2'];
                $user_data->city = $customerArr[$i]['City Name'];
                $user_data->postal_code = $customerArr[$i]['Postal Code'];
                $user_data->city = $customerArr[$i]['City Name'];
                $user_data->city = $customerArr[$i]['State'];
                $user_data->city = $customerArr[$i]['Country'];

                $user_data->save();

                if($user_data)
                {
                    $permissionsArr = [1,2,3,4,9,11,13,14,17,23,24,29,30,31,32];
                    $role_name = "user_role_".$user->id;
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
                }


            }


            // User::firstOrCreate($customerArr[$i]);
        }

        return 'Jobi done or what ever';   

        
        
    }
    function csvToArray($filename = '', $delimiter = ',')
        {
            if (!file_exists($filename) || !is_readable($filename))
                return false;

            $header = null;
            $data = array();
            if (($handle = fopen($filename, 'r')) !== false)
            {
                while (($row = fgetcsv($handle, 1000, $delimiter)) !== false)
                {
                    if (!$header)
                        $header = $row;
                    else
                        $data[] = array_combine($header, $row);
                }
                fclose($handle);
            }

            return $data;
        }
}
