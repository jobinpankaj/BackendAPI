<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;
use App\Models\DwcRoute;
use App\Models\DwcRoutesContent;
use Lang;
use Auth;

class RoutesController extends Controller
{
    public $permission;

    public function __construct()
    {
        $headers = getallheaders();
        
        $this->permission = isset($headers['permission']) ? $headers['permission'] : "";
    }

    public function routesList(Request $request)
    {
        if($this->permission !== "routes-view")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }
        
        $user = auth()->user();
        $search = $request->input("search");
        $routeQuery = DwcRoute::query();
        if(!empty($search)) {
            $routeQuery->where(function($query)use($search){
                $query->where(function($query1)use($search){
                    $query1->where("name",'LIKE',"%".$search."%");
                });
                $query->orWhereHas("dwcRouteContentData",function($query1)use($search){
                    $query1->where("description",'LIKE',"%".$search."%")->orWhere("message",'LIKE',"%".$search."%");
                });
            });
        }
        $data = $routeQuery->with(['dwcRouteContentData','userInformation'])->withCount('routeUsers')->where('user_id', $user->id)->get();

        $success  = $data;
        $message  = Lang::get("messages.routes_list");
        return sendResponse($success, $message);
    }

    public function getRoutes($id)
    {
        if($this->permission !== "routes-view")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }

        $user = auth()->user();
        $routeQuery = DwcRoute::query();
        $routeQuery->with(['dwcRouteContentData','routeUsers']);
        $routesData = $routeQuery->where('user_id',$user->id)->find($id);

        if(!$routesData) {
            return sendError(Lang::get('messages.route_not_found'), Lang::get('messages.route_not_found'), 404);
        }

        $success = $routesData;
        $message = Lang::get("messages.routes_detail");
        return sendResponse($success, $message);
    }

    public function addRoutes(Request $request)
    {
        if($this->permission !== "routes-edit")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }

        $rules = [
            'name' => 'required|string|max:100|unique:dwc_routes',
            'colour' => 'required|string|max:40',
            'start_address' => 'nullable',
            'end_address' => 'nullable',
            'route_description.*.description' => 'required|string|max:500',
            'route_description.*.message' => 'required|string|max:500',
            'driver_name' => 'nullable|string|max:200',
        ];
        // if($request->input("minimum_per_delivery_status") == "1")
        // {
        //     $rules["minimun_number_of_items"] = 'required';
        // }

        $validator = Validator::make($request->all(), $rules);
        
        if($validator->fails()) {
            return sendError(Lang::get('validation_error'), $validator->errors(), 422);
        }

        $user = auth()->user();

        $insertData = array(
            'name' => $request->input("name"),
            'colour' => $request->input("colour"),
            'start_address' => $request->input("start_address"),
            'start_latitude' => $request->input("start_latitude"),
            'start_longitude' => $request->input("start_longitude"),
            'end_address' => $request->input("end_address"),
            'end_latitude' => $request->input("end_latitude"),
            'end_longitude' => $request->input("end_longitude"),
            'driver_name' => $request->input("driver_name"),
            'truck_details' => $request->input("truck_details"),
            // 'minimum_per_delivery_status' => $request->input("minimum_per_delivery_status"),
            // 'minimun_number_of_items' => $request->input("minimun_number_of_items"),
            'user_id' => $user->id,
        );

        $dwcRoute = DwcRoute::create($insertData);
        $route_description = $request->input("route_description");
        $route_description_data = [];
        foreach($route_description as $rd)
        {  
            $route_description_data[] = array("dwc_route_id" => $dwcRoute->id,'description'=>$rd['description'],'message'=>$rd['message'],'site_language_id'=>$rd['language_id']);
        }
        if(count($route_description_data)>0){
            DwcRoutesContent::insert($route_description_data);
        }
        
        $success = $dwcRoute;
        $message = Lang::get("messages.routes_created_successfully");
        return sendResponse($success, $message);
    }

    public function updateRoutes(Request $request,$id)
    {
        if($this->permission !== "routes-edit")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }

        $user = auth()->user();
        $dwcRoute = DwcRoute::where('user_id', $user->id)->find($id);

        if(!$dwcRoute) {
            return sendError(Lang::get('messages.route_not_found'), Lang::get('messages.route_not_found'), 404);
        }

        $rules = [
            'name' => 'required|string|max:100|unique:dwc_routes,name,'.$id.',id',
            'start_address' => 'required',
            'end_address' => 'required',
            'route_description.*.description' => 'required|string|max:500',
            'route_description.*.message' => 'required|string|max:500',
            'driver_name' => 'required|string|max:200',
        ];
        // if($request->input("minimum_per_delivery_status") == "1")
        // {
        //     $rules["minimun_number_of_items"] = 'required';
        // }

        $validator = Validator::make($request->all(), $rules);
        
        if($validator->fails()) {
            return sendError(Lang::get('validation_error'), $validator->errors(), 422);
        }

        $dwcRoute->name = $request->input("name");
        $dwcRoute->start_address = $request->input("start_address");
        $dwcRoute->start_latitude = $request->input("start_latitude");
        $dwcRoute->start_longitude = $request->input("start_longitude");
        $dwcRoute->end_address = $request->input("end_address");
        $dwcRoute->end_latitude = $request->input("end_latitude");
        $dwcRoute->end_longitude = $request->input("end_longitude");
        $dwcRoute->driver_name = $request->input("driver_name");
        $dwcRoute->truck_details = $request->input("truck_details");
        // $dwcRoute->minimum_per_delivery_status = $request->input("minimum_per_delivery_status");
        // $dwcRoute->minimun_number_of_items = $request->input("minimun_number_of_items");
        $dwcRoute->save();

        $route_description = $request->input("route_description");
        $route_description_data = [];
        foreach($route_description as $rd)
        {  
            $route_description_data = array("dwc_route_id" => $dwcRoute->id,'description'=>$rd['description'],'message'=>$rd['message'],'site_language_id'=>$rd['language_id']);
            DwcRoutesContent::updateOrCreate(['dwc_route_id'=>$dwcRoute->id,'site_language_id'=>$rd['language_id']],$route_description_data);
        }

        $success = $dwcRoute;
        $message = Lang::get("messages.routes_updated_successfully");
        return sendResponse($success, $message);
    }
}
