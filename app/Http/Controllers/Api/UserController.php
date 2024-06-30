<?php

namespace App\Http\Controllers\Api;

use App\Models\Role;
use App\Models\Token;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

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
           ],500);
       }
       if(User::where('login',$login)->first()){
           return response()->json([
               'status'=>false,
               'message'=>'Користувач з таким логіном вже існує'
           ],500);
       }
       if(strlen($request->password)<4){
           return response()->json([
               'status'=>false,
               'message'=>'Пароль має буди не менше 4 символів'
           ],500);
       }
       if(User::where('email',$email)->first()){
           return response()->json([
               'status'=>false,
               'message'=>'Ця електронна адреса вже зайнята'
           ],500);
       }
        $newRole = Role::create([
            'role_name'=>'user'
        ]);

       $user = User::create([
           'login'=>$login,
           'email'=>$email,
           'password'=>$password,
           'name'=>$name,
           'phone_number'=>$phone_number,
           'role_id'=>$newRole->id
       ]);
       $newRole->update([
           'user_id'=>$user->id
       ]);
       if(!$user){
           return response()->json([
               'status'=>false
           ],500);
       }

       return response()->json([
           'status'=>true,
           'user'=>$user,
           'role'=>$newRole->role_name
       ]);

    }

    public function login(Request $request){

        if(!$request->login || !$request->password){
            return response()->json([
                'status'=>false,
                'message'=>'Усі поля мають бути заповнені'
            ],500);
        }

        if(!User::where('login',$request->login)->first()){
            return response()->json([
                'status'=>false,
                'message'=>'Невірний логін'
            ],500);
        }

        $user = User::where('login',$request->login)->first();
        if(!Hash::check($request->password,$user->password)){
            return response()->json([
                'status'=>false,
                'message'=>'Невірний пароль'
            ],500);
        }
        $new_access_token = Token::where('user_id',$user->id)->first();
        $role = Role::where('user_id',$user->id)->first();
        $role_name = $role?$role->role_name:'';
        $refresh_credentials = request(['login', 'password','id']);
        $access_credentials = request(['login', 'password']);

        $new_refresh_token = auth()->claims(['login' => $user->login,'email'=>$user->email,'id'=>$user->id])->attempt($refresh_credentials);
        $access_token = auth()->claims(['login' => $user->login,'email'=>$user->email,'role'=>$role_name,'phone_number'=>$user->phone_number,'name'=>$user->name,'user_image'=>$user->user_image])->setTTL(60)->attempt($access_credentials);

        if(!$new_access_token){
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
        $new_access_token->refresh_token = $new_refresh_token;
        $new_access_token->save();
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
        $access_token = auth()->claims(['login' => $user->login,'email'=>$user->email,'phone_number'=>$user->phone_number,'name'=>$user->name,'user_image'=>$user->user_image])->setTtl(60)->refresh();

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
        $newRole = Role::create([
            'role_name'=>'user'
        ]);
        $user = User::create([
            'login'=>$request->login,
            'email'=>$request->email,
            'password'=>Hash::make($request->password),
            'name'=>$request->name,
            'phone_number'=>$request->phone_number,
            'role_id'=>$newRole->id
        ]);
        $newRole->update([
            'user_id'=>$user->id
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

    public function editUser(Request $request, $id){
        $user = User::where('id',$id)->first();
        $role = Role::where('role_name',$request->role_name)->first();

        if(!$user){
            return response()->json([
                'status'=>false,
                'message'=>'User not found'
            ],404);
        }
        $full_url = '';
        if($request->hasFile('user_img')) {
            $url = Storage::disk('s3')->put('sushi/users_images', $request->file('user_img'));
            $full_url = Storage::disk('s3')->url($url);
            if (!$request->hasFile('user_img')) {
                return response()->json([
                    'message' => 'file error',
                    'request' => $request
                ], 404);
            }
        }
        $user->update([
            'email'=>$request->email?$request->email:$user->email,
            'name'=>$request->name?$request->name:$user->name,
            'phone_number'=>$request->phone_number?$request->phone_number:$user->phone_number,
            'role_id'=>$request->role_name?$role->id:$user->role_id,
            'user_image'=>$request->hasFile('user_img')?$full_url:$user->user_image
        ]);

        $user->save();

        return response()->json([
            'status'=>true,
            'message'=>'Success',
            'user'=>$user
        ]);
    }

    public function editCurrentUser(Request $request){
        $currentUser = auth()->user();
        $user = User::with('role')->where('id',$currentUser->id)->first();

        if($request->hasFile('user_img')) {
            $url = Storage::disk('s3')->put('sushi/users_images', $request->file('user_img'));
            $full_url = Storage::disk('s3')->url($url);
            if (!$request->hasFile('user_img')) {
                return response()->json([
                    'message' => 'file error',
                    'request' => $request
                ], 404);
            }
        }
        $user->update([
            'email'=>!$request->email||$request->email==''?$user->email:$request->email,
            'name'=>!$request->name||$request->name==''?$user->name:$request->name,
            'phone_number'=>!$request->phone_number||$request->phone_number==''?$user->phone_number:$request->phone_number,
            'user_image'=>$request->hasFile('user_img')?$full_url:$user->user_image
        ]);


        $user->save();

        return response()->json([
            'status'=>true,
            'message'=>'Success',
            'user'=>$user
        ]);
    }
    public function getAll(){
        $users = User::with('role')->get();

        if(!$users){
            return response()->json([
                'status'=>false,
                'message'=>'error'
            ]);
        }

        return response()->json([
            'status'=>true,
            'Message'=>'Success',
            'users'=>$users
        ]);
    }

    public function logOut(){
       $user = auth()->user();

       $token = Token::where('user_id',$user->id)->first();

       $user->token_id = null;
       $token->delete();

       $user->save();

       return response()->json([
           'status'=>true,
           'Message'=>'Success'
       ]);

    }
    public function getOrderedUsers (Request $request){
        $param = $request->param;
        $order_option = $request->order_option?$request->order_option:'asc';
        $users = null;
        try{
            if($param == 'role_name'){
                $users = User::with('role')
                    ->orderBy(Role::select('role_name')->whereColumn('roles.id','users.role_id'),$order_option)->get();
                return response()->json([
                    'users'=>$users
                ]);
            }
            $users = User::with('role')->orderBy($param,$order_option)->get();
            return response()->json([
                'users'=>$users
            ]);}
        catch(HttpResponseException $exception){
            return response()->json([
                'status'=>false,
                'Message'=>$exception->getMessage()
            ]);
        }
    }

    public function getCurrentUser(){
        try{

        $user = auth()->user();
        return response()->json([
            'status'=>true,
            'user'=>$user
        ]);
        }catch (HttpResponseException $exception){
            return response()->json([
                'status'=>false,
                'message'=>$exception->getMessage()
            ]);
        }

    }

}
