<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Exception;

class CategoryController extends Controller
{
    // 取得當前使用者的分類
    public function index(Request $request)
    {
        return Category::where('user_id', $request->user()->id)->get();
    }

    // 新增分類
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
            ]);

            // 透過關聯建立分類並自動繫結 user_id
            $category = $request->user()->categories()->create($validated);

            return response()->json([
                'message' => '新增分類成功',
                'data' => $category
            ], 201);
        } catch (Exception $e) {
            // 🎯 如果還有 500 錯誤，直接將詳細錯誤訊息回傳給前端與 Console
            return response()->json([
                'message' => '伺服器錯誤',
                'error' => $e->getMessage(),
                'line' => $e->getLine()
            ], 500);
        }
    }
    // 修改分類
    public function update(Request $request, Category $category)
    {
        // 檢查權限：確認該分類屬於當前登入的使用者
        if ($category->user_id !== $request->user()->id) {
            return response()->json(['message' => '無權限修改此分類'], 403);
        }

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
            ]);

            $category->update($validated);

            return response()->json([
                'message' => '分類修改成功',
                'data' => $category
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => '伺服器錯誤',
                'error' => $e->getMessage(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    // 刪除分類
    public function destroy(Request $request, Category $category)
    {
        // 1. 權限檢查
        if ($category->user_id !== $request->user()->id) {
            return response()->json(['message' => '無權限刪除此分類'], 403);
        }

        // 2. 檢查該分類底下是否還有關聯的景點（假設景點關聯名稱為 attractions）
        if ($category->attractions()->count() > 0) {
            return response()->json([
                'message' => '此分類底下還有景點，無法刪除！請先移除或更改相關景點的分類。'
            ], 422); // 422 Unprocessable Entity 表示業務邏輯驗證失敗
        }

        // 3. 通過檢查後才執行刪除
        $category->delete();

        return response()->json(['message' => '分類刪除成功']);
    }
}
