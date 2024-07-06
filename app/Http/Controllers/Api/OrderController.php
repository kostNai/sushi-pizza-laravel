<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use PhpParser\Node\Expr\Array_;

class OrderController extends Controller
{
    public function newOrder(Request $request){
        $current_user = auth()->user();
        $new_product = Product::find($request->product_id);

        $sauce = Product::find(69);
        $sticks = Product::find(68);
        $bag = Product::find(70);
        $number = Carbon::now()->getTimestamp();

        try {
        $order = Order::create([
            'number' => $number,
            'user_id'=>$current_user->getAuthIdentifier()
        ]);
        $new_order = Order::where('id',$order->id)->with('user')->get();
        $order->products()->attach([$sauce->id,$sticks->id,$bag->id,$new_product->id]);
        return response()->json([
            'status'=>true,
            'order'=>$new_order
        ]);
        }catch(HttpResponseException $exception){
            return response()->json([
                'status'=>false,
                'message'=>$exception->getMessage()
            ],$exception->getCode());
        }

    }
    public function addToOrder(Request $request){
        $order = Order::find($request->order_id);
        $product  = Product::find($request->product_id);

        try {
            $order->products()->attach($product->id);

            $order->products()->updateExistingPivot($product->id,[
                'product_quantity'=> $order->products()->find($product->id)->pivot->product_quantity+1
         ]);
        return response()->json([
            'status'=>true,
            'new order'=>$order
        ]);
        }catch (HttpResponseException $exception){
            return response()->json([
                'status'=>false,
                'message'=>$exception->getMessage()
            ],$exception->getCode());
        }

    }
    public function removeFromOrder(Request $request){
        $order = Order::find($request->order_id);
        $product = Product::find($request->product_id);
        try {
        $order->products()->detach($product->id);
        return response()->json([
            'status'=>true,
            'order'=>$order
        ]);

        }catch (HttpResponseException $exception){
            return response()->json([
                'status'=>false,
                'message'=>$exception->getMessage()
            ],$exception->getCode());
        }
    }
    public function deleteOrder($order_id){
        $order = Order::find($order_id);
        $order_product = OrderProduct::where('order_id',$order->id)->get();
        dd($order_product);
        if(!$order){
            return response()->json([
                'status'=>false,
                'message'=>'Product not found'
            ],404);
        }
        try {
            $order->delete();
            return response()->json([
                'status'=>true,
                'message'=>'Success'
            ]);
        }catch (HttpResponseException $exception){
            return response()->json([
                'status'=>false,
                'message'=>$exception->getMessage()
            ],$exception->getCode());
        }
    }
    public function getOrder(Request $request){
//        find(50)->pivot->product_quantity
        $products = Order::find($request->order_id)->products()->get();
        return response()->json([
           'products'=>$products
        ]);
    }
    public function get2(){
        $users = Order::find(15)->user()->with('address')->get();
        return response()->json([
           'users'=>$users
        ]);
    }



}
