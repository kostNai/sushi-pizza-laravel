<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Order extends Model
{
    protected $fillable = (['number','status','user_id','status']);
    use HasFactory;

    public function products():BelongsToMany{
        return $this->belongsToMany(Product::class)->withPivot('id','product_quantity');
    }
    public function user():HasOne{
        return $this->hasOne(User::class,'id','user_id');
    }

}
