<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Availability;
use App\Models\AvailabilityCompany;
use App\Models\AvailabilityGroup;
use App\Models\AvailabilityGroupAllocation;
use App\Models\AvailabilityGroupMaximum;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Validator;

class AvailabilityController extends Controller
{
    public $permisssion;

    public function __construct()
    {
        $headers = getallheaders();
        
        $this->permisssion = isset($headers['permission']) ? $headers['permission'] : "";
    }

    public function availabilityList()
    {
        if($this->permisssion !== "inventory-view")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }

        $user = auth()->user();
        $availabilityList = Availability::with(['product', 'allocations', 'maximums', 'visibityInformation', 'product.userInformation','product.productFormat'])->where('added_by', $user->id)->get();

        $success = $availabilityList;
        $message = Lang::get("messages.availability_list");
        return sendResponse($success, $message);
    }

    public function getAvailability($id)
    {
        if($this->permisssion !== "inventory-view")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }
        // $availability = Availability::with(['product', 'allocations', 'allocations.group', 'maximums', 'maximums.group', 'visibityInformation', 'product.userInformation'])->where('product_id', $id)->get();
        $availability = Availability::with(['availabilityGroup','availabilitycompany','availabilitycompany.userProfile','availabilityGroup.group','product', 'allocations', 'allocations.group', 'maximums', 'maximums.group', 'visibityInformation', 'product.userInformation','inventory'])->where('id', $id)->get();

        if(!$availability) {
            return sendError(Lang::get('messages.availability_not_found'), Lang::get('messages.availability_not_found'), 404);
        }
        $groups = AvailabilityGroup::select('availability_id','group_id')->where('availability_id',$id)->get();
        // dd($groups);
        // $mappedcollection = $groups->map(function($group, $key) {									
        //     return [
        //             // 'id' => $user->id,
        //             'name' => $group->name
        //         ];
        //     });
        //     dd($mappedcollection);
        // if(!empty($groups))
        // {
        //     foreach($groups as $key => $value)
        //     {
        //         $groups_name = Group::where('id',$value->group_id)->first();
            
        //         $value->groupName = $groups_name->name ?? null;
        //     }
        // }
        // $availability['groups'] = $groups;
        $success = $availability;
        $message = Lang::get("messages.availability_detail");
        return sendResponse($success, $message);
    }

    public function addAvailability(Request $request)
    {
        // dd($request->all()); die;
        if($this->permisssion !== "inventory-edit")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }

        $validator = Validator::make($request->all(), [
            'product_id' => 'required|numeric|exists:products,id',
            'visibility_id' => 'required|numeric|exists:visibilities,id',
            // 'groups' => 'nullable|string',
            // 'companies' => 'nullable|string',
            'is_limited'  => 'required|numeric',
            // 'allocation.*.group_id' => 'required|numeric|exists:groups,id',
            // 'allocation.*.quantity' => 'required|numeric',
            // 'maximum.*.group_id' => 'required|numeric|exists:groups,id',
            // 'maximum.*.quantity' => 'required|numeric',
        ]);
        
        if($validator->fails()) {
            return sendError(Lang::get('validation_error'), $validator->errors(), 422);
        }

        $validated = $request->all();

        $user = auth()->user();
        $availability = Availability::create([
            'added_by' => $user->id,
            'product_id' => $validated['product_id'],
            'visibility_id' => $validated['visibility_id'],
            'is_limited'    => $validated['is_limited'],
            
        ]);

        if(isset($validated['groups']) && $validated['groups'] !== "") {
            $allGroups = explode(",", $validated['groups']);
            foreach($allGroups as $group) {
                AvailabilityGroup::create([
                    'availability_id' => $availability->id,
                    'group_id' => $group,
                ]);
            }
        }

        if(isset($validated['companies']) &&  $validated['companies']!=="") {
            $allCompanies = explode(",", $validated['companies']);
            foreach($allCompanies as $company) {
                AvailabilityCompany::create([
                    'availability_id' => $availability->id,
                    'company_id' => $company,
                ]);
            }
        }

        $allocations = [];
        if(isset($validated['allocation']))
        {

        
        foreach($validated['allocation'] as $allocation)
        {
            $availabilityGroupAllocation = AvailabilityGroupAllocation::create([
                'availability_id' => $availability->id,
                'group_id' => $allocation['group_id'],
                'allocation_quantity' => $allocation['quantity']
            ]);

            array_push($allocations, $availabilityGroupAllocation);
        }
    }
    if(isset($validated['maximum']))
    {
        $maximums = [];
        foreach($validated['maximum'] as $maximum)
        {
            $availabilityGroupMaximum = AvailabilityGroupMaximum::create([
                'availability_id' => $availability->id,
                'group_id' => $maximum['group_id'],
                'maximum_quantity' => $maximum['quantity']
            ]);

            array_push($maximums, $availabilityGroupMaximum);
        }
    }
        $availability['allocation'] = $allocations ?? null;
        $availability['maximum'] = $maximums ??null;

        $success = $availability;
        $message = Lang::get("messages.availability_created_successfully");
        return sendResponse($success, $message);
    }

    public function updateAvailability(Request $request, $id)
    {
        if($this->permisssion !== "inventory-edit")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }

        $validator = Validator::make($request->all(), [
            'product_id' => 'required|numeric|exists:products,id',
            'visibility_id' => 'required|numeric|exists:visibilities,id',
            'is_limited'    =>'required',
            'group_id' => 'nullable|numeric|exists:groups,id',
            // 'company_id' => 'nullable|numeric|exists:companies_id',
            'allocation.*.group_id' => 'required|numeric|exists:groups,id',
            'allocation.*.quantity' => 'required|numeric',
            'maximum.*.group_id' => 'required|numeric|exists:groups,id',
            'maximum.*.quantity' => 'required|numeric',
        ]);

        if($validator->fails()) {
            return sendError(Lang::get('validation_error'), $validator->errors(), 422);
        }
        
        $validated = $request->all();
        // dd($validated);
        $availability = Availability::find($id);
        // dd($availability);
        if(!$availability) {
            return sendError(Lang::get('messages.availability_not_found'), Lang::get('messages.availability_not_found'), 404);
        }
        
        // Update Availability
        $availability->product_id = $validated['product_id'];
        $availability->visibility_id = $validated['visibility_id'];
        $availability->group_id = $validated['group_id'] ?? $availability->group_id;
        $availability->company_id = $validated['company_id'] ?? $availability->company_id;
        $availability->is_limited = $validated['is_limited'];
        $availability->save();

        $allocations = [];
        if(isset($validated['allocation']))
        {
        foreach($validated['allocation'] as $allocation)
        {
            $availabilityGroupAllocation = AvailabilityGroupAllocation::updateOrCreate([
                'availability_id' => $availability->id,
                'group_id' => $allocation['group_id']
            ],[
                'allocation_quantity' => $allocation['quantity']
            ]);

            array_push($allocations, $availabilityGroupAllocation);
        }
        }   
        if(isset($validated['maximum']))
        {
        $maximums = [];
        foreach($validated['maximum'] as $maximum)
        {
            $availabilityGroupMaximum = AvailabilityGroupMaximum::updateOrCreate([
                'availability_id' => $availability->id,
                'group_id' => $maximum['group_id']
            ],[
                'maximum_quantity' => $maximum['quantity']
            ]);

            array_push($maximums, $availabilityGroupMaximum);
        }
    }
        $availability['allocation'] = $allocations ?? null;
        $availability['maximum'] = $maximums ?? null;

        $success = $availability;
        $message = Lang::get("messages.availability_updated_successfully");
        return sendResponse($success, $message);
    }
}
