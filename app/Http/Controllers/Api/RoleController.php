<?php

namespace App\Http\Controllers\Api;


use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class RoleController extends Controller
{
    public function getUsers(Request $request){
       $role = Role::where('role_name',$request->role_name)->first();
       $users = $role->users;
//        $users = $role::with('users')->get();
       if(!$role){
           return response()->json([
               'status'=>false,
               'message'=>'Role not found'
           ]);
       }
       return response()->json([
           'users'=>$users
       ]);
    }


    public function addRole(Request $request){
        $newRole = Role::create([
            'role_name'=>$request->role_name
        ]);

        if(!$newRole){
            return response()->json([
                'status'=>false,
                'message'=>'Error'
            ]);
        }

        return response()->json([
            'status'=>true,
            'message'=>'Success',
            'role'=>$newRole
        ]);
    }

    public function getAll(){
        $roles = Role::all();

        return response()->json([
            'status'=>true,
            'roles'=>$roles
        ]);
    }
}
