<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attraction;
use Illuminate\Http\Request;

class AttractionController extends Controller
{
    // 取得當前登入使用者的景點（含原本的篩選與分頁）
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Attraction::with('category')->where('user_id', $user->id);

        // 1. 關鍵字搜尋（名稱或地址）
        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('city', 'like', "%{$keyword}%");
            });
        }

        // 2. 縣市 / 行政區篩選
        if ($request->filled('city')) {
            $city = $request->city;
            $query->where('city', 'like', "%{$city}%");
        }

        // 3. 分類篩選
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // 4. 排序
        if ($request->input('sort') === 'oldest') {
            $query->oldest();
        } else {
            $query->latest();
        }

        $perPage = $request->input('per_page', 4);
        $paginated = $query->paginate($perPage);

        // 取得當前使用者所有收藏的景點 ID 陣列
        $favoriteIds = $user->favorites()->pluck('attractions.id')->toArray();

        $paginated->getCollection()->transform(function ($item) use ($favoriteIds) {
            $item->category_name = $item->category ? $item->category->name : '未分類';
            $item->is_favorited = in_array($item->id, $favoriteIds);
            return $item;
        });

        return response()->json($paginated);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',

            'city' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    // 1. 必須包含縣市相關字眼
                    $hasCity = str_contains($value, '縣') || str_contains($value, '市') || str_contains($value, '臺') || str_contains($value, '台');

                    // 2. 必須包含行政區相關字眼
                    $hasDistrict = str_contains($value, '區') || str_contains($value, '鄉') || str_contains($value, '鎮');

                    // 🎯 兩者必須同時成立（用 &&），缺一不可！
                    if (!$hasCity || !$hasDistrict) {
                        $fail('請輸入完整的地址格式（必須同時包含縣市與行政區）。');
                    }
                },
            ],

            'image_url' => 'nullable|url',
            'description' => 'nullable|string|max:1000',
        ], [
            'address.required' => '請輸入完整的地址格式（必須包含縣市與行政區）',
        ]);

        // 🎯 驗證透過後，統一將「台」轉為「臺」並去除前後空白
        if (isset($validated['city'])) {
            $city = str_replace('台', '臺', $validated['city']);
            $validated['city'] = trim(preg_replace('/\s+/', ' ', $city));
        }

        // 🎯 建立景點並關聯當前使用者
        $validated['user_id'] = $request->user()->id;
        $attraction = Attraction::create($validated);

        return response()->json([
            'message' => '新增景點成功',
            'data' => $attraction
        ], 201);
    }

    // 🎯 更新景點
    public function update(Request $request, Attraction $attraction)
    {
        // 確保只能修改自己的景點
        if ($attraction->user_id !== $request->user()->id) {
            return response()->json(['message' => '無權限修改'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',

            // 🎯 修改這裡：同步套用嚴格的雙重檢查（必須同時包含縣市與行政區）
            'city' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    $hasCity = str_contains($value, '縣') || str_contains($value, '市') || str_contains($value, '臺') || str_contains($value, '台');
                    $hasDistrict = str_contains($value, '區') || str_contains($value, '鄉') || str_contains($value, '鎮');

                    if (!$hasCity || !$hasDistrict) {
                        $fail('請輸入完整的地址格式（必須同時包含縣市與行政區）。');
                    }
                },
            ],

            'image_url' => 'nullable|url',
            'description' => 'nullable|string|max:1000',
        ], [
            'city.required' => '請輸入完整的地址格式（必須包含縣市與行政區）',
        ]);

        // 🎯 驗證透過後，統一將「台」轉為「臺」並去除前後空白
        if (isset($validated['city'])) {
            $city = str_replace('台', '臺', $validated['city']);
            $validated['city'] = trim(preg_replace('/\s+/', ' ', $city));
        }

        $attraction->update($validated);

        return response()->json([
            'message' => '更新景點成功',
            'data' => $attraction
        ]);
    }

    // 🎯 刪除景點
    public function destroy(Request $request, Attraction $attraction)
    {
        // 確保只能刪除自己的景點
        if ($attraction->user_id !== $request->user()->id) {
            return response()->json(['message' => '無權限刪除'], 403);
        }

        $attraction->delete();

        return response()->json([
            'message' => '刪除景點成功'
        ]);
    }

    // 取得當前使用者的收藏景點清單
    public function favorites(Request $request)
    {
        $user = $request->user();
        $favorites = $user->favorites()->with('category')->get();

        $favorites->transform(function ($item) {
            $item->category_name = $item->category ? $item->category->name : '未分類';
            $item->is_favorited = true;
            return $item;
        });

        return response()->json($favorites);
    }

    // 切換收藏 / 取消收藏
    public function toggleFavorite(Request $request, Attraction $attraction)
    {
        $user = $request->user();
        $user->favorites()->toggle($attraction->id);

        return response()->json([
            'message' => '操作成功'
        ]);
    }
}
