<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AttractionController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\AuthController;

// 🟢 1. 公開路由（不需要 token 即可訪問）
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// 🔒 2. 需要登入驗證的保護路由
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);

    // ⚙️ 會員資料更新
    Route::put('/profile', [AuthController::class, 'updateProfile']);

    // ⭐ 收藏功能相關路由
    Route::get('/favorites', [AttractionController::class, 'favorites']);
    Route::post('/attractions/{attraction}/favorite', [AttractionController::class, 'toggleFavorite']);

    // 📊 統計圖表 API（必須放在 apiResource 上方，避免被 id 攔截）
    Route::get('/attractions/statistics', [AttractionController::class, 'statistics']);

    // 景點與分類相關 API
    Route::apiResource('attractions', AttractionController::class);
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{category}', [CategoryController::class, 'update']);
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);
});
