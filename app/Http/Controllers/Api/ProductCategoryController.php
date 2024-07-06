<?php

namespace App\Http\Controllers\Api;


use App\Models\Product_Category;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ProductCategoryController extends Controller
{
    public function getAll(){
        $categories = Product_Category::all();

        if(!$categories){
            return response()->json([
                'status'=>false,
                'message'=>'Error'
            ]);
        }

        return response()->json([
            'status'=>true,
            'message'=>'Success',
            'categories'=>$categories
        ]);
    }

    public function addCategory(Request $request){
        if(!$request->category_name){
            return response()->json([
                'status'=>false,
                'message'=>'Введіть назву категорії'
            ],500);
        }
        $category = Product_Category::where('category_name',$request->category_name)->first();
        if($category){
            return response()->json([
                'status'=>false,
                'message'=>'Така категорія вже існує'
            ],500);
        }
        $newCategory = Product_Category::create([
            'category_name'=>$request->category_name,
            'slug'=>$request->slug
        ]);

        return response()->json([
            'status'=>true,
            'message'=>'Success',
            'category'=>$newCategory
        ]);
    }

    public function getProduct(Request $request){
        $category = Product_Category::where('category_name',$request->category_name)->first();
        if(!$category){
            return response()->json([
                'status'=>false,
                'message'=>'Category not found'
            ]);
        }
        $products = $category->poduct;
        dd($products);
    }
}
