<?php

namespace App\Http\Controllers\Api;


use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class RoleController extends Controller
{
    public function getUsers(Request $request){
       $role = Role::where('role_name',$request->role_name)->first();
       $users = $role->user;
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
}
