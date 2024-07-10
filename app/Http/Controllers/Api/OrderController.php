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

        function attach_product($order,$sauce,$sticks,$bag,$new_product){
            $order->products()->attach([$sauce->id,$sticks->id,$bag->id,$new_product->id]);

            /* NEED TO REFACTORING!!!!! */
            $order->products()->updateExistingPivot($new_product->id,[
                'product_quantity'=> $order->products()->find($new_product->id)->pivot->product_quantity+1]);
            $order->products()->updateExistingPivot($sticks->id,[
                'product_quantity'=> $order->products()->find($sticks->id)->pivot->product_quantity+2]);
            $order->products()->updateExistingPivot($sauce->id,[
                'product_quantity'=> $order->products()->find($sauce->id)->pivot->product_quantity+2]);
            $order->products()->updateExistingPivot($bag->id,[
                'product_quantity'=> $order->products()->find($bag->id)->pivot->product_quantity+1]);
        }

        try {
            if(!$current_user){
                $order = Order::create([
                    'number' => $number,
                ]);
                $new_order = Order::where('id',$order->id)->first();
                attach_product($order,$sauce,$sticks,$bag,$new_product);

                return response()->json([
                    'status'=>true,
                    'order'=>$new_order
                ]);
            }

            $order = Order::create([
                'number' => $number,
                'user_id'=>$current_user->getAuthIdentifier()
            ]);
            $new_order = Order::where('id',$order->id)->with('user')->first();
            attach_product($order,$sauce,$sticks,$bag,$new_product);
//            $order->products()->attach([$sauce->id,$sticks->id,$bag->id,$new_product->id]);
//
//                                    /* NEED TO REFACTORING!!!!! */
//            $order->products()->updateExistingPivot($new_product->id,[
//                    'product_quantity'=> $order->products()->find($new_product->id)->pivot->product_quantity+1]);
//            $order->products()->updateExistingPivot($sticks->id,[
//                    'product_quantity'=> $order->products()->find($sticks->id)->pivot->product_quantity+2]);
//            $order->products()->updateExistingPivot($sauce->id,[
//                    'product_quantity'=> $order->products()->find($sauce->id)->pivot->product_quantity+2]);
//            $order->products()->updateExistingPivot($bag->id,[
//                    'product_quantity'=> $order->products()->find($bag->id)->pivot->product_quantity+1]);

            return response()->json([
                'status'=>true,
                'order'=>$new_order
            ]);
        }catch (HttpResponseException $exception){
            return response()->json([
            'status'=>false,
            'message'=>$exception->getMessage()
            ],$exception->getCode());
        }


    }
    public function update_order($order_id){
        $order = Order::where('id',$order_id)->first();
        $user = auth()->user();

        if(!$order){
            return response()->json([
                'status'=>false,
                'message'=>'Order not found'
            ],404);
        }
        if(!$user){
            return response()->json([
                'status'=>false,
                'message'=>'User not found'
            ],404);
        }

        try {
            $order->update([
                'user_id'=>$user->getAuthIdentifier()
            ]);
            return response()->json([
                'status'=>true,
                'message'=>'success',
                'order'=>$order
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
        $adding_product  = Product::find($request->product_id);

        try {
            $product = Order::find($request->order_id)->products()->find($adding_product->id);
            if($product){
                $order->products()->updateExistingPivot($adding_product->id,[
                    'product_quantity'=> $order->products()->find($product->id)->pivot->product_quantity+1
                ]);
            }
            else{
                $order->products()->attach($adding_product->id);
                $order->products()->updateExistingPivot($adding_product->id,[
                    'product_quantity'=> $order->products()->find($adding_product->id)->pivot->product_quantity+1
             ]);
            }
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
        if(!$order){
            return response()->json([
                'status'=>false,
                'message'=>'Order not found'
            ]);
        }
        $product = Product::find($request->product_id);
        try {
            if($order->products()->find($product->id)->pivot->product_quantity>1){
                $order->products()->updateExistingPivot($product->id,[
                    'product_quantity'=> $order->products()->find($product->id)->pivot->product_quantity-1
                ]);
            }
            else{
                $order->products()->detach($product->id);
            }
            return response()->json([
                'status'=>true,
                'message'=>'success'
            ]);
        }catch (HttpResponseException $exception){
            return response()->json([
                'status'=>false,
                'message'=>$exception->getMessage()
            ],$exception->getCode());
        }
    }

    public function deleteFromOrder(Request $request){
        $order = Order::find($request->order_id);
        if(!$order){
            return response()->json([
                'status'=>false,
                'message'=>'Order not found'
            ]);
        }
        $product = Product::find($request->product_id);
        try {
            $order->products()->detach($product->id);

            return response()->json([
                'status'=>true,
                'message'=>'success'
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
        if(!$order){
            return response()->json([
                'status'=>false,
                'message'=>'Order not found'
            ],404);
        }
        $order_products = OrderProduct::where('order_id',$order->id)->get();
        $current_ids = array();
        foreach ($order_products as $order_product){
            $current_ids[] = $order_product->product_id;
        }
        try {
            $order->products()->detach($current_ids);
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
