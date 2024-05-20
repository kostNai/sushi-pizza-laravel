<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use App\Models\Product_Category;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ProductController extends Controller
{
    public function addProduct(Request $request){
        $category = Product_Category::where('category_name',$request->category_name)->first();
        if(!$category){
            return response()->json([
                'status'=>false,
                'message'=>'Category not found'
            ]);
        }
        $category_id =$request->category_name?$category->id:null;


        $product = Product::create([
            'product_name'=>$request->product_name,
            'product_desc'=>$request->product_desc,
            'product_weight'=>$request->product_weight,
            'product_price'=>$request->product_price,
            'sale_count'=>0,
            'category_id'=>$category_id
        ]);
        if(!$product){
            return response()->json([
                'status'=>false,
                'message'=>'Error'
            ]);
        }
        return response()->json([
            'status'=>true,
            'message'=>'Success',
            'product'=>$product
        ]);
    }

    public function getAll(){
        $products = Product::all();
        return response()->json([
            'status'=>true,
            'products'=>$products
        ]);
    }


    public function getProductById($id){
        $product = Product::where('id',$id)->first();

        if(!$product){
            return response()->json([
                'status'=>false,
                'message'=>'Product not found'
            ]);
        }

        return response()->json([
            'status'=>true,
            'product'=>$product
        ]);
    }

    public function deleteProduct($id){
        $product = Product::where('id',$id)->first();

        if(!$product){
            return response()->json([
                'status'=>false,
                'message'=>'Product not found'
            ]);
        }
        $product->delete();
        return response()->json([
            'status'=>false,
            'message'=>'Product deleted success'
        ]);
    }
}
