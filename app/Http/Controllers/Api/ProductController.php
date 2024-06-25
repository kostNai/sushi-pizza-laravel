<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use App\Models\Product_Category;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;


class ProductController extends Controller
{
    public function addProduct(Request $request){
        if(!$request->product_name||!$request->product_desc || !$request->product_price || !$request->product_weight){
            return response()->json([
                'status'=>false,
                'message'=>'Усі поля мають бути заповнені'
            ],500);
        }
        $category = Product_Category::where('category_name',$request->category_name)->first();
        if(!$category){
            return response()->json([
                'status'=>false,
                'message'=>'Category not found'
            ],404);
        }
        $category_id = $category->id;
        if($request->file('product_img')) {
            $url = Storage::disk('s3')->put('sushi/products_images', $request->file('product_img'));
            $full_url = Storage::disk('s3')->url($url);
            if (!$request->hasFile('product_img')) {
                return response()->json([
                    'message' => 'file error',
                    'request' => $request
                ], 404);
            }
        }
        $product = Product::create([
            'product_name'=>$request->product_name,
            'product_desc'=>$request->product_desc,
            'product_weight'=>$request->product_weight,
            'product_price'=>$request->product_price,
            'product_image'=>$request->file('product_img')?$full_url:'',
            'sale_count'=>0,
            'category_id'=>$category_id
        ]);
        $newCategory = Product_Category::create([
            'category_name'=>$category->category_name,
            'product_id'=>$product->id
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

    public function getAllWithPaginate(){
        $products = Product::with('category')->paginate(12);


        return response()->json([
            'status'=>true,
            'products'=>$products
        ]);
    }
    public function getAll(){
        $products = Product::with('category')->get();


        return response()->json([
            'status'=>true,
            'products'=>$products
        ]);
    }

    public function getByCategory(Request $request){
        $category = Product_Category::where('category_name',$request->category_name)->first();
        if(!$category){
            return  response()->json([
                'status'=>false,
                'message'=>'Category not found'
                ],404);
        }
        $products = Product::where('category_id',$category->id)->paginate(12);

        return response()->json([
            'status'=>true,
            'message'=>'Success',
            'products'=>$products
        ]);
    }

    public function getProductById($id){
        $product = Product::where('id',$id)->first();

        if(!$product){
            return response()->json([
                'status'=>false,
                'message'=>'Product not found'
            ],404);
        }

        return response()->json([
            'status'=>true,
            'product'=>$product
        ]);
    }
    public function getOrderedProduct (Request $request){
        $param = $request->param;
        $order_option = $request->order_option?$request->order_option:'asc';
        $products = Product::with('category')->orderBy($param,$order_option)->get();
        return response()->json([
            'products'=>$products
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
            'status'=>true,
            'message'=>'Product deleted success'
        ]);
    }

    public function addImage(Request $request,$id){
        $product = Product::where('id',$id)->first();

        $url = Storage::disk('s3')->put('sushi/products_images',$request->file('product_img'));
        if (!$request->hasFile('product_img')) {
            return response()->json([
                'message' => 'file error',
                'request' => $request
            ], 404);
        }
        $full_url = Storage::disk('s3')->url($url);
        if (!$request->hasFile('product_img')) {
            return response()->json([
                'message' => 'file error',
                'request' => $request
            ], 404);
        }

        $product->update([
            'product_image'=>$full_url
        ]);
        return response()->json([
            'status'=>true,
            'product'=>$product
        ]);
    }

    public function editProduct(Request $request, $id){
        $product = Product::where('id',$id)->first();
        $new_category = null;
        $category = null;

        if(!$product){
            return response()->json([
                'status'=>false,
                'message'=>'Product not found'
            ],404);
        }
        if($request->category_name){
            $category = Product_Category::where('product_id',$product->id)->first();
            if(!$category){
               $new_category = Product_Category::create([
                   'category_name'=>$request->category_name,
                   'product_id'=>$product->id,
               ]);
            }

            $category->update([
                'category_name'=>$request->category_name,
                'product_id'=>$product->id
            ]);


        }
        $product->update([
            'product_name'=>$request->product_name?$request->product_name:$product->product_name,
            'product_desc'=>$request->product_desc?$request->product_desc:$product->product_desc,
            'product_weight'=>$request->product_weight?$request->product_weight:$product->product_weight,
            'product_price'=>$request->product_price?$request->product_price:$product->product_price,
        ]);


        return response()->json([
            'status'=>false,
            'message'=>'Product was update successful',
            'product'=>$product
        ]);
    }

}
