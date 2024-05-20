<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Role extends Model
{
    protected $fillable = ['role_name','user_id'];
    use HasFactory;

    public function user():BelongsTo{
        return $this->belongsTo(User::class);
    }
}
