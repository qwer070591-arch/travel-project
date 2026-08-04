<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attraction;
use App\Models\Category;
use Illuminate\Support\Facades\File;

class TaichungAttractionSeeder extends Seeder
{
    public function run()
    {
        // 讀取放在 database/data/ 下的 JSON 檔案
        $jsonPath = database_path('data/taichung.json');
        if (!File::exists($jsonPath)) {
            return;
        }

        $json = File::get($jsonPath);
        $result = json_decode($json, true);
        $items = $result['tourism_data'] ?? [];

        foreach ($items as $item) {
            // 1. 尋找或自動建立對應的分類，取得 category_id
            $categoryName = $item['category'];
            $category = Category::firstOrCreate(['name' => $categoryName]);

            // 2. 建立景點資料（將地址放入 city 欄位，並處理「台」轉「臺」）
            Attraction::create([
                'name' => str_replace('台', '臺', $item['name']),
                'category_id' => $category->id,
                'city' => str_replace('台', '臺', $item['address']),
                'image_url' => 'https://picsum.photos/600/400?random=' . $item['id'], // 給予預設隨機圖片
            ]);
        }
    }
}
