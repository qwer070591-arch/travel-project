# AI 輔助旅遊景點推薦平台 (AI Travel Guide Website)

## 專案簡介

本專案為一款結合 AI 應用與現代網頁開發技術的**旅遊景點推薦系統**。專案完整涵蓋前端介面設計、互動式使用者體驗、後端 API 資料處理，以及基礎資料統計。系統提供景點瀏覽、關鍵字搜尋、多維度分類篩選、景點資料管理（CRUD）、AI 旅遊建議及數據圖表展示等核心功能，致力於打造直覺且充滿靈感的旅遊規劃體驗。

---

## 技術堆疊 (Tech Stack)

### 前端技術 (Frontend)
* **核心框架**：Vue.js (Composition API)
* **UI 樣式與排版**：HTML5, Tailwind CSS
* **非同步請求**：JavaScript (Fetch / Axios)

### 後端技術 (Backend)
* **核心框架**：PHP, Laravel Framework

### 資料庫與工具 (Database & Tools)
* **資料庫**：MySQL (透過 phpMyAdmin 進行視覺化管理)
* **AI 輔助工具**：ChatGPT / Gemini (文案生成、提示詞設計)、AI 圖像生成工具 (網站 Banner 與高解析度景點封面圖產出)

---

## 系統核心功能 (Core Features)

### 1. 互動式首頁與導覽
提供高質感的品牌視覺 Banner、熱門景點與美食推薦區塊，並具備直覺的快速導覽列，引導使用者快速尋找旅遊靈感。
<!-- 請將下方連結替換為你的實際圖片路徑 -->
![首頁與Banner展示](./screenshots/home-banner.png)

### 2. 智慧化景點檢索系統
支援關鍵字即時搜尋、城市與地區篩選、分類標籤（如：自然、人文、美食）與排序功能，協助使用者精準定位行程。
<!-- 請將下方連結替換為你的實際圖片路徑 -->
![景點檢索與篩選](./screenshots/search-filter.png)

### 3. 後台景點管理 (CRUD)
提供景點新增 (Create)、讀取 (Read)、修改 (Update) 與刪除 (Delete) 功能。表單內建完整的前端防呆驗證，並提供明確的操作成功/失敗狀態回饋。
<!-- 請將下方連結替換為你的實際圖片路徑 -->
![後台景點管理](./screenshots/admin-crud.png)

### 4. 數據統計與視覺化
直觀展示各城市景點的數量分佈、熱門程度及不同景點分類的佔比統計，將繁雜數據轉化為視覺化圖表。
<!-- 請將下方連結替換為你的實際圖片路徑 -->
![數據統計圖表](./screenshots/data-dashboard.png)

---

## 資料庫架構 (Database Schema)

系統核心資料庫包含以下重要資料表設計：

**`attractions` (景點資料表)**
* `id` (INT, Primary Key) - 景點唯一識別碼
* `name` (VARCHAR) - 景點名稱
* `city` (VARCHAR) - 所在城市/地區
* `category` (VARCHAR) - 景點分類（如：自然、人文、美食）
* `image_url` (VARCHAR) - 景點圖片網址
* `feature` (TEXT) - 景點特色介紹
* `created_at` / `updated_at` (TIMESTAMP) - 建立與更新時間

**`favorites` (關聯資料表)**
* 用以支援會員收藏或分類管理機制。

<!-- 請將下方連結替換為你的實際圖片路徑 -->
![資料庫架構圖](./screenshots/database-schema.png)

---

## API 路由設計 (API Endpoints)

後端提供以下 RESTful API 供前端互動與資料非同步請求：

* `GET /api/attractions` - 取得所有景點資料列表
* `GET /api/attractions/{id}` - 取得指定單一景點詳細資訊
* `POST /api/attractions` - 新增一筆景點資料
* `PUT /api/attractions/{id}` - 更新指定景點資料
* `DELETE /api/attractions/{id}` - 刪除指定景點資料

<!-- 請將下方連結替換為你的實際圖片路徑 -->
![Postman API 測試紀錄](./screenshots/api-testing.png)

---

## AI 功能應用 (AI Integration)

* **文案生成**：運用生成式 AI（ChatGPT / Gemini）協助撰寫吸引人的景點介紹文案與旅遊推薦標語，降低內容產出成本並提升吸睛度。
* **視覺素材**：使用 AI 圖像生成工具產出高解析度（1024x576 以上）的網站 Banner 與景點封面圖，大幅提升整體視覺質感與一致性。

---

## 系統測試紀錄 (Testing Documentation)

### 測試項目一：景點新增表單防呆與驗證測試
* **測試情境**：於後台嘗試送出空白或格式錯誤的景點名稱/欄位。
* **預期與實際結果**：系統成功阻擋無效請求，並於前端介面對應之輸入框下方顯示紅色錯誤提示文字；填寫正確完畢後，成功送出並平順跳轉回列表頁，確保資料庫寫入的完整性。
<!-- 請將下方連結替換為你的實際圖片路徑 -->
![表單驗證測試](./screenshots/form-validation.png)

### 測試項目二：RWD 響應式排版與跨裝置斷點測試
* **測試情境**：使用瀏覽器開發者工具，分別模擬桌面版 (1200px)、平板版 (768px) 及手機版 (375px) 檢視系統介面。
* **預期與實際結果**：各尺寸斷點下的版面元素皆能自動適應縮放，無跑版或水平捲軸溢出狀況。導覽列於行動裝置上順利轉換為收合式漢堡選單，確保跨裝置體驗的一致性。
<!-- 請將下方連結替換為你的實際圖片路徑 -->
![RWD測試結果](./screenshots/rwd-testing.png)

---

## 快速啟動 (Getting Started)

若想在本地端運行此專案，請參考以下步驟：

1. **克隆專案**：
   ```bash
   git clone [https://github.com/你的帳號/你的專案名稱.git](https://github.com/你的帳號/你的專案名稱.git)