<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product_Category extends Model
{
    protected $fillable = ['category_name','product_id'];

    public function product():BelongsTo{
        return $this->belongsTo(User::class);
    }
    use HasFactory;
}
