<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

class Attraction extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category_id',
        'city',
        'image_url',
        'description', // 🎯 這裡要加上這行，才能夠透過 Mass Assignment 寫入資料庫
        'user_id',
    ];

    // 讓所有 API 輸出的時間自動變成「YYYY-MM-DD」格式
    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('Y-m-d');
    }

    // 關聯到 User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 關聯到 Category
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
