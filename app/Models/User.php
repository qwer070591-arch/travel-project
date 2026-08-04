<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Attraction; // 🎯 確保有引入這行

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // 分類的關聯
    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    // 景點的關聯
    public function attractions()
    {
        return $this->hasMany(Attraction::class);
    }

    // 收藏景點的多對多關聯
    public function favorites()
    {
        return $this->belongsToMany(Attraction::class, 'favorites', 'user_id', 'attraction_id')->withTimestamps();
    }
}
