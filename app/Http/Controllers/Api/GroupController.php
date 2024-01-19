<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Validator;
use App\Models\GroupRetailer;
class GroupController extends Controller
{
    public $permisssion;

    public function __construct()
    {
        $headers = getallheaders();
        
        $this->permisssion = isset($headers['permission']) ? $headers['permission'] : "";
    }

    public function groupList()
    {
        if($this->permisssion !== "groups-view")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }

        $user = auth()->user();
        $data = Group::where('added_by', $user->id)->get();
        foreach($data as $key => $value)
        {
            $data1 = GroupRetailer::where('group_id',$value->id)->count();
            $value->retailerCount = $data1;
        }
        $success  = $data;
        $message  = Lang::get("messages.group_list");
        return sendResponse($success, $message);
    }

    public function getGroup($id)
    {
        if($this->permisssion !== "groups-view")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }

        $user = auth()->user();
        $group = Group::with(['retailers', 'retailers.userProfile'])->where('added_by', $user->id)->find($id);

        if(!$group) {
            return sendError(Lang::get('messages.group_not_found'), Lang::get('messages.group_not_found'), 404);
        }

        // $retailersArray = [];
        // foreach($group->retailers as $retailer)
        // {
        //     array_push($retailersArray, [
        //         'retailer_id' => $retailer->id,
        //         'retailer_company' => $retailer->userProfile->company_name ?? null
        //     ]);
        // }

        // $group['retailers_list'] = $retailersArray;

        $success = $group;
        $message = Lang::get("messages.group_detail");
        return sendResponse($success, $message);
    }

    public function addGroup(Request $request)
    {
        if($this->permisssion !== "groups-edit")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'color' => 'required|string',
            'order_confirm_msg' => 'required|string',
            'order_confirm_msg_lang' => 'required|numeric',
            'order_default_note' => 'required|string',
            'order_default_note_lang' => 'required|numeric',
            'is_min_order_count' => 'nullable|boolean',
            'min_items' => 'required_if:is_min_order_count,1',
            'min_kegs' => 'required_if:is_min_order_count,1',
            'is_min_order_value' => 'nullable|boolean',
            'min_price' => 'required_if:is_min_order_value,1',
            'tax_applicability' => 'required|in:Applicable,Not Applicable',
            'bill_deposits' => 'required|in:Required,Not Required',
            'order_approval' => 'required|in:Automatic,Manual',
            // 'online_payment'   => 'required',
            // 'offline_payment'   => 'required',
            'is_accepted_payment'   => 'required'
            // 'payment_method' => 'nullable|string'
        ]);
        
        if($validator->fails()) {
            return sendError(Lang::get('validation_error'), $validator->errors(), 422);
        }

        $validated = $request->all();
        
        $user = auth()->user();

        $group = Group::create([
            'added_by' => $user->id,
            'name' => $validated['name'],
            'color' => $validated['color'],
            'order_confirm_msg' => $validated['order_confirm_msg'],
            'order_confirm_msg_lang' => $validated['order_confirm_msg_lang'],
            'order_default_note' => $validated['order_default_note'],
            'order_default_note_lang' => $validated['order_default_note_lang'],
            'is_min_order_count' => $validated['is_min_order_count'],
            'min_items' => $validated['min_items'],
            'min_kegs' => $validated['min_kegs'],
            'is_min_order_value' => $validated['is_min_order_value'],
            'min_price' => $validated['min_price'],
            'tax_applicability' => $validated['tax_applicability'],
            'bill_deposits' => $validated['bill_deposits'],
            'order_approval' => $validated['order_approval'],
            'online_payment'    => $validated['online_payment'] ?? null ,
            'offline_payment'   => $validated['offline_payment'] ?? null,
            'is_accepted_payment'   => $validated['is_accepted_payment'],
            // 'payment_method' => $validated['payment_method'],
        ]);

        $success = $group;
        $message = Lang::get("messages.group_created_successfully");
        return sendResponse($success, $message);
    }

    public function updateGroup(Request $request, $id)
    {
        if($this->permisssion !== "groups-edit")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'color' => 'required|string',
            'order_confirm_msg' => 'required|string',
            'order_confirm_msg_lang' => 'required|numeric',
            'order_default_note' => 'required|string',
            'order_default_note_lang' => 'required|numeric',
            'is_min_order_count' => 'nullable|boolean',
            'min_items' => 'required_if:is_min_order_count,1',
            'min_kegs' => 'required_if:is_min_order_count,1',
            'is_min_order_value' => 'nullable|boolean',
            'min_price' => 'required_if:is_min_order_value,1',
            'tax_applicability' => 'required|in:Applicable,Not Applicable',
            'bill_deposits' => 'required|in:Required,Not Required',
            'order_approval' => 'required|in:Automatic,Manual',
            'online_payment'   => 'required',
            'offline_payment'   => 'required',
            'is_accepted_payment'   => 'required',
            // 'payment_method' => 'nullable|string'
        ]);

        if($validator->fails()) {
            return sendError(Lang::get('validation_error'), $validator->errors(), 422);
        }
        
        $validated = $request->all();

        $group = Group::find($id);

        if(!$group) {
            return sendError(Lang::get('messages.group_not_found'), Lang::get('messages.group_not_found'), 404);
        }
        
        // Update Group
        $group->name = $validated['name'];
        $group->color = $validated['color'];
        $group->order_confirm_msg = $validated['order_confirm_msg'] ?? $group->order_confirm_msg;
        $group->order_confirm_msg_lang = $validated['order_confirm_msg_lang'] ?? $group->order_confirm_msg_lang;
        $group->order_default_note = $validated['order_default_note'] ?? $group->order_default_note;
        $group->order_default_note_lang = $validated['order_default_note_lang'] ?? $group->order_default_note_lang;
        $group->is_min_order_count = $validated['is_min_order_count'] ?? $group->is_min_order_count;
        $group->min_items = $validated['min_items'] ?? $group->min_items;
        $group->min_kegs = $validated['min_kegs'] ?? $group->min_kegs;
        $group->is_min_order_value = $validated['is_min_order_value'] ?? $group->is_min_order_value;
        $group->min_price = $validated['min_price'] ?? $group->min_price;
        $group->tax_applicability = $validated['tax_applicability'] ?? $group->tax_applicability;
        $group->bill_deposits = $validated['bill_deposits'] ?? $group->bill_deposits;
        $group->order_approval = $validated['order_approval'] ?? $group->order_approval;
        $group->online_payment = $validated['online_payment'] ?? $group->online_payment;
        $group->offline_payment = $validated['offline_payment'] ?? $group->offline_payment;
        $group->is_accepted_payment = $validated['is_accepted_payment'] ?? $group->is_accepted_payment;

        // $group->payment_method = $validated['payment_method'] ?? $group->payment_method;
        
        $group->save();

        $success = $group;
        $message = Lang::get("messages.group_updated_successfully");
        return sendResponse($success, $message);
    }
}
