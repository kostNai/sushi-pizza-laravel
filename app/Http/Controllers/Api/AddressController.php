<?php

namespace App\Http\Controllers\Api;


use App\Models\Address;

use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpKernel\Exception\HttpException;


class AddressController extends Controller
{
    public function addAddress(Request $request){
        $user = auth()->user();

        if(!$user){
            return response()->json([
                'status'=>false,
                'message'=>'User not found'
            ],404);
        }
        try {
        $address = Address::create([
            'city'=>$request->city,
            'street_name'=>$request->street_name,
            'house_number'=>$request->house_number,
            'flat_number'=>$request->flat_number,
            'user_id'=>$user->id
        ]);
        $user->address_id = $address->id;
        $user->save();
        return response()->json([
            'status'=>true,
            'address'=>$address
        ]);
        }catch(HttpResponseException $exception){
            return response()->json([
                'status'=>false,
                'message'=>$exception->getMessage()
            ],$exception->getCode());
        }
    }

    public function editAddress(Request $request, $address_id){
        $current_address = Address::where('id',$address_id)->first();

        if(!$current_address){
            return response()->json([
                'status'=>false,
                'message'=>'Address not found'
            ],404);
        }

        try {
            $current_address->update([
                'city'=>$request->city?$request->city:$current_address->city,
                'street_name'=>$request->street_name?$request->street_name:$current_address->street_name,
                'house_number'=>$request->house_number?$request->house_number:$current_address->house_number,
                'flat_number'=>$request->flat_number?$request->flat_number:$current_address->flat_number,
            ]);
            return response()->json([
                'status'=>true,
                'address'=>$current_address
            ]);
        }catch(HttpException $exception){
            return response()->json([
                'status'=>false,
                'message'=>$exception->getMessage()
            ],$exception->getCode());
        }
    }
}
