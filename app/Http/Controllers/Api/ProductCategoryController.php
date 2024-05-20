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
        $category = Product_Category::create([
            'category_name'=>$request->category_name
        ]);

        return response()->json([
            'status'=>true,
            'message'=>'Success',
            'category'=>$category
        ]);
    }
}
