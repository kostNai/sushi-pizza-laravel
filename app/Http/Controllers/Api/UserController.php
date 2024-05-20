<?php

namespace App\Http\Controllers\Api;

use App\Models\Role;
use App\Models\Token;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function register(Request $request){
       $login = $request->login;
       $password = Hash::make($request->password);
       $email = $request->email;
       $name = $request->name;
       $phone_number = $request->phone_number;

       if(!$login || !$password || !$email){
           return response()->json([
               'status'=>false,
               'message'=>'Необхідно заповнити всі поля'
           ]);
       }
       if(User::where('login',$login)->first()){
           return response()->json([
               'status'=>false,
               'message'=>'Користувач з таким логіном вже існує'
           ]);
       }
       if(strlen($request->password)<4){
           return response()->json([
               'status'=>false,
               'message'=>'Пароль має буди не менше 4 символів'
           ]);
       }
       if(User::where('email',$email)->first()){
           return response()->json([
               'status'=>false,
               'message'=>'Ця електронна адреса вже зайнята'
           ]);
       }

       $user = User::create([
           'login'=>$login,
           'email'=>$email,
           'password'=>$password,
           'name'=>$name,
           'phone_number'=>$phone_number
       ]);
       if(!$user){
           return response()->json([
               'status'=>false
           ],500);
       }
       return response()->json([
           'status'=>true,
           'user'=>$user
       ]);

    }

    public function login(Request $request){
        if(!User::where('login',$request->login)->first()){
            return response()->json([
                'status'=>false,
                'message'=>'Невірний логін'
            ]);
        }

        $user = User::where('login',$request->login)->first();
        if(!Hash::check($request->password,$user->password)){
            return response()->json([
                'status'=>false,
                'message'=>'Невірний пароль'
            ]);
        }
        $refresh_credentials = request(['login', 'password','id']);
        $access_credentials = request(['login', 'password']);

        $new_refresh_token = auth()->claims(['login' => $user->login,'email'=>$user->email,'id'=>$user->id])->attempt($refresh_credentials);
        $access_token = auth()->claims(['login' => $user->login,'email'=>$user->email])->setTTL(60)->attempt($access_credentials);
//        $exp = auth()->payload('exp');

//        dd($user->id);
        $refresh_token = Token::create([
            'refresh_token'=>$new_refresh_token,
            'user_id'=>$user->id
        ]);
        $user->token_id=$refresh_token->id;
        $refresh_token->save();
        $user->save();

        return response()->json([
                'status'=>true,
                'access_token'=>$access_token,
                'user'=>$user
            ]
        );
    }

    public function changeRole(Request $request){
        $user = User::where('login',$request->login)->first();
        $new_role = Role::where('role_name',$request->role_name)->first();
        $role = Role::where('user_id',$user->id)->first();


        if(!$new_role){
            return response()->json([
                'status'=>false,
                'message'=>'This role name not found'
            ],404);
        }

        if(!$role){
            $role = Role::create([
                'role_name'=>$new_role->role_name,
                'user_id'=>$user->id
            ]);
        }
        $user->role_id = $new_role->id;
        $role->role_name=$new_role->role_name;
        $role->save();
        $user->save();

        return response()->json([
            'status'=>true,
            'user'=>$user
        ]);
    }

    public function refresh(){
        $current_user = auth()->user();
        $user = User::where('login',$current_user->login)->first();
        $token = Token::where('user_id',$user->id)->first();

        if(!$token){
            return response()->json([
                'status'=>false,
                'message'=>'Refresh token not found'
            ],404);
        }
        $new_token = auth()->refresh();
        $token->refresh_token = $new_token;
        $user->token_id = $token->id;
        $access_token = auth()->setTtl(60)->refresh();
        $token->save();
        $user->save();
        return response()->json([
            'status'=>true,
            'message'=>'Success',
            'access_token'=>$access_token
        ]);
    }

    public function deleteUser($id){
        $user = User::where('id',$id)->first();
        if(!$user){
            return response()->json([
                'status'=>false,
                'message'=>'User not found'
            ],404);
        }
        $user->delete();
        return response()->json([
            'status'=>true,
            'message'=>'Success'
        ]);
    }

    public function addUser(Request $request){
        $user = User::create([
            'login'=>$request->login,
            'email'=>$request->email,
            'password'=>Hash::make($request->password),
            'name'=>$request->name,
            'phone_number'=>$request->phone_number,
        ]);

        if(!$user){
            return response()->json([
                'status'=>false,
                'message'=>'Error'
            ]);
        }
        return response()->json([
            'status'=>true,
            'message'=>'Success',
            'user'=>$user
        ]);
    }

}
