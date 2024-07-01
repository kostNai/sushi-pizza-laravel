<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    protected $fillable = ['city','street_name','house_number','flat_number','user_id'];

    public function user():BelongsTo{
        return $this->belongsTo(User::class);
    }
    use HasFactory;
}
