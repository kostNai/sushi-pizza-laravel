<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    protected $fillable = ['product_name','product_desc','product_weight','product_price','product_image','sale_count','category_id'];


    public function category():HasOne{

        return $this->hasOne(Product_Category::class,'product_id','id');
    }
    public function discount():HasMany{

        return $this->hasMany(Product_Category::class);
    }
    use HasFactory;
}
