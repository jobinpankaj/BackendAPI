<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Exception;
use Lang;

class RolesController extends Controller
{
    public $permission;

    public $guard_name;

    public function __construct(Request $request){
        $headers = getallheaders();
        $userType = $headers['usertype'];
        $this->guard_name=$userType;   
    }

    // Permissions List
    public function permissionList(Request $request)
    {
       $permissionList = Permission::where("guard_name","=",$this->guard_name)->get();
        $success['data']  = $permissionList;
        $message          = Lang::get("messages.permission_list");
        return sendResponse($success, $message);   
    }

    // Role List
    public function roleList(Request $request)
    {
        $roleList = Role::whereNotNull("parent_id")->where("guard_name",$this->guard_name)->get();
        $success['data']  = $roleList;
        $message          = Lang::get("messages.role_list");
        return sendResponse($success, $message);
    }

    // Add Role
    public function addRole(Request $request,$guard_name)
    {
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:roles',
            'permissions' => 'required'
        ]);

        if ($validator->fails()) return sendError(Lang::get('messages.validation_error'), $validator->errors(), 422);
        if(!empty($this->guard_name))
        {
            $permissionsArr = explode(",",$request->input("permissions"));
            $insertData = array(
                            "name" => $request->input("name"),
                            "guard_name" => $this->guard_name,
                            );
            $role = Role::create($insertData);
            $permissions = Permission::whereIn("id",$permissionsArr)->where("guard_name","=",$this->guard_name)->pluck('id','id');
            
            $role->syncPermissions($permissions);
            $success['data']  = $role;
            $message          = Lang::get("messages.role_created");
            return sendResponse($success, $message);
        }
        else {

        }
    }

    // Edit Role
    public function editRole(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'name' => 'required',
            'permissions' => 'required',
        ]);

        if ($validator->fails()) return sendError(Lang::get('messages.validation_error'), $validator->errors(), 422);
        if($request->input("name")){
            $data = Role::where("name",$request->input("name"))->where("id","<>",$request->input("id"))->where("guard_name",$this->guard_name)->get();
            if($data->count() > 0)
            {
                $error = ["name"=>["Role name is already exists."]];
                return sendError(Lang::get('messages.validation_error'), $error, 422);
            }
        }
        $permissionsArr = explode(",",$request->input("permissions"));
        $role = Role::find($request->input("id"));
        $role->update(['name'=>$request->input('name')]);
        $permissions = Permission::whereIn("id",$permissionsArr)->pluck('id','id');
        $role->syncPermissions($permissions);
        $success['data']  = $role;
        $message          = Lang::get("messages.role_updated");
        return sendResponse($success, $message);
    }

    // Get Role Data
    public function getRoleData(Request $request, $id)
    {
        if($id)
        {
            $role = Role::with("permissions")->find($id);
            if($role)
            {
                $success['data']  = $role;
                $message          = Lang::get("messages.role_data");
                return sendResponse($success, $message);
            }
            else {
                return sendError(Lang::get('not_found'), Lang::get('not_found'), 404);
            }
        }
        else {
            return sendError(Lang::get('not_found'), Lang::get('not_found'), 404);
        }
    }

    // Delete Role
    public function deleteRole(Request $request)
    {
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

    // Change Role
    public function updateRoleStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'status' => 'required' // 1/0
        ]);

        if ($validator->fails()) return sendError(Lang::get('messages.validation_error'), $validator->errors(), 422);

        $role = Role::find($request->input("id"));
        if($role)
        {
            $role->update(["status"=>$request->input("status")]);
            $success['data']  = [];
            $message          = Lang::get("messages.role_status_updated");
            return sendResponse($success, $message);
        }
        else {
            return sendError(Lang::get('messages.not_found'), Lang::get('messages.not_found'), 404);
        }
    }
}
