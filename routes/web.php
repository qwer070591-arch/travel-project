<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AttractionController;

Route::get('/', function () {
    return view('dashboard');
})->name('login');

// 2. 景點相關路由
Route::get('/attractions', [AttractionController::class, 'index']);
Route::post('/attractions', [AttractionController::class, 'store'])->name('attractions.store');
Route::delete('/attractions/{id}', [AttractionController::class, 'destroy'])->name('attractions.destroy');

// 3. 分類相關路由
Route::get('/categories', [AttractionController::class, 'categoriesIndex'])->name('categories.index');
Route::post('/categories', [AttractionController::class, 'categoriesStore'])->name('categories.store');
