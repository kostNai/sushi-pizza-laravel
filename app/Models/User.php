<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Tymon\JWTAuth\Contracts\JWTSubject;


class User extends Authenticatable implements JWTSubject
{

    protected $fillable = ['login','email','password','name','phone_number','user_image','role_id','address_id','role'];

    protected $hidden = ['password','refresh_token'];
    use HasFactory;
    public function role():HasOne{

        return $this->hasOne(Role::class,'user_id','id');
    }
    public function token():HasOne{

        return $this->hasOne(Token::class);
    }
    public function address():HasOne{

        return $this->hasOne(Address::class);
    }
    public function order():HasOne{
        return $this->hasOne(Order::class);
    }


    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
