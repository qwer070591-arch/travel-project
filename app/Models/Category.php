<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'user_id', // 🎯 務必確保這裡有加入 user_id！
    ];

    // 關聯到 User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 關聯到 Attraction
    public function attractions()
    {
        return $this->hasMany(Attraction::class);
    }
}
