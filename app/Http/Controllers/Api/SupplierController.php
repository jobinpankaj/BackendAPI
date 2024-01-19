<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RetailerSupplierRequest;
use App\Models\SupplierDistributor;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Validator;

class SupplierController extends Controller
{
    public function getRetailerRequests()
    {
        // if($this->permission !== "retailer-view")
        // {
        //     return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        // }

        $user = auth()->user();
        $data = RetailerSupplierRequest::with('retailerInformation')->where('supplier_id', $user->id)->get();
        $success = $data;
        return sendResponse($success, Lang::get('messages.retailer_supplier_request_list'));
    }

    public function actionOnRetailerRequest(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return sendError(Lang::get('validation_error'), $validator->errors(), 422);
        }

        $validated = $request->all();

        $retailerSupplierRequest = RetailerSupplierRequest::find($id);

        if (!$retailerSupplierRequest) {
            return sendError(Lang::get('messages.retailer_supplier_request_not_found'), Lang::get('messages.retailer_supplier_request_not_found'), 404);
        }

        $retailerSupplierRequest->status = $validated['action'];

        $retailerSupplierRequest->save();

        $success = $retailerSupplierRequest;
        $message = Lang::get("messages.retailer_supplier_request_updated_successfully");
        return sendResponse($success, $message);
    }

    public function getAllDistributors()
    {
        $distributors = User::with(['userProfile', 'userMainAddress'])->where('status', "1")->where('user_type_id', 2)->get();
        $distributors = $distributors->map(function ($item) {
            // Accessing company_name from the user_profile object
            $company_name = $item->userProfile->company_name ?? null;
        
            $item->comapany_name = $company_name;
        
            return $item;
        });
        
        // Now each item in $data has an additional property 'company_name'
        foreach ($distributors as $item) {
            $company_name = $item->company_name; // Accessing the added 'company_name' property
        }
        $success = $distributors;
        $message = Lang::get("messages.distributor_user_list");
        return sendResponse($success, $message);
    }

    public function linkDistributors(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'distributors' => 'required|string'
        ]);

        if ($validator->fails()) {
            return sendError(Lang::get('validation_error'), $validator->errors(), 422);
        }

        $validated = $request->all();

        $user = auth()->user();
        $distributors = explode(",", $validated['distributors']);

        // Delete existing linked distributors
        SupplierDistributor::where('supplier_id', $user->id)->delete();

        foreach ($distributors as $distributor) {
            SupplierDistributor::create([
                'supplier_id' => $user->id,
                'distributor_id' => $distributor
            ]);
        }

        $message = Lang::get("messages.distributors_linked_to_supplier_successfully");
        return sendResponse([], $message);
    }

    public function getLinkedDistributors()
    {
        $user = auth()->user();

        $distributors = SupplierDistributor::where('supplier_id', $user->id)->pluck('distributor_id')->toArray();

        $data = User::whereIn('id', $distributors)->select('id', 'first_name', 'last_name', 'email')->with('userProfile')->get();
        $data = $data->map(function ($item) {
            // Accessing company_name from the user_profile object
            $company_name = $item->userProfile->company_name ?? null;
        
            $item->comapany_name = $company_name;
        
            return $item;
        });
        
        // Now each item in $data has an additional property 'company_name'
        foreach ($data as $item) {
            $id = $item->id;
            $firstName = $item->first_name;
            $lastName = $item->last_name;
            $email = $item->email;
            $company_name = $item->company_name; // Accessing the added 'company_name' property
        }
        $success = $data;
        $message = Lang::get("messages.distributors_linked_to_supplier_fetched_successfully");
        return sendResponse($success, $message);
    }

    public function getLinkedDistributorsCompany()
    {
        $user = auth()->user();

        $distributors = SupplierDistributor::where('supplier_id', $user->id)->pluck('distributor_id')->toArray();

        $data = UserProfile::whereIn('user_id', $distributors)->select('id', 'company_name', 'user_id')->with(['userInfo'])->get();

        $success = $data;
        $message = Lang::get("messages.distributors_companies_fetched_successfully");
        return sendResponse($success, $message);
    }

    public function getLinkedRetailers()
    {
        $user = auth()->user();

        $retailers = RetailerSupplierRequest::where("supplier_id", $user->id)->where('status', '1')->pluck('retailer_id')->toArray();

        $data = User::with(['userMainAddress','userProfile'])->whereIn('id', $retailers)->get();

        $success  = $data;
        $message  = Lang::get("messages.retailer_user_list");
        return sendResponse($success, $message);
    }
}
