<!DOCTYPE html>
<html lang="zh-Hant">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>旅遊景點管理與數據統計 Dashboard</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Vue 3 CDN -->
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
</head>

<body class="bg-slate-50 text-slate-800 min-h-screen flex flex-col">

    <div id="app" class="flex flex-col min-h-screen">
        <!-- 頂部導覽列 -->
        <header class="bg-slate-900 text-white shadow-md relative z-20">
            <div class="max-w-7xl mx-auto px-4 py-3 flex justify-between items-center">
                <h1 class="text-lg font-bold flex items-center gap-2 cursor-pointer" @click="currentView = 'dashboard'">
                    <svg class="w-7 h-7 shrink-0 drop-shadow" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <linearGradient id="header-tw-grad" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#34d399" />
                                <stop offset="100%" stop-color="#059669" />
                            </linearGradient>
                        </defs>
                        <path d="M55 12 C67 30, 70 50, 58 72 C48 88, 36 88, 32 80 C27 70, 34 40, 46 15 Z" fill="url(#header-tw-grad)" />
                        <circle cx="50" cy="30" r="3" fill="#fef08a" class="animate-ping" />
                        <circle cx="50" cy="30" r="3" fill="#fef08a" />
                    </svg>
                    <span>旅遊景點管理與數據統計 Dashboard</span>
                </h1>
                <div>
                    <template v-if="isLoggedIn">
                        <div class="flex items-center gap-3">
                            <span class="text-xs bg-slate-800 px-3 py-1.5 rounded-full border border-slate-700 text-emerald-400 font-medium hidden sm:inline-block">
                                🟢 @{{ currentUser.name }}
                            </span>
                            <!-- 新增：我的收藏按鈕 -->
                            <button @click="switchView('favorites')" :class="currentView === 'favorites' ? 'bg-emerald-600 text-white' : 'bg-slate-800 text-slate-200 hover:bg-slate-700'" class="px-3 py-1.5 rounded-md text-xs font-bold transition border border-slate-700">
                                ⭐ 我的收藏
                            </button>
                            <!-- 新增：會員中心 / 修改資訊按鈕 -->
                            <button @click="openProfileModal" class="bg-slate-800 hover:bg-slate-700 text-slate-200 px-3 py-1.5 rounded-md text-xs font-bold transition border border-slate-700">
                                ⚙️ 會員中心
                            </button>
                            <button @click="handleLogout" class="bg-rose-600 hover:bg-rose-700 text-white px-3 py-1.5 rounded-md text-xs font-bold transition">
                                登出
                            </button>
                        </div>
                    </template>
                    <template v-else>
                        <button @click="openLoginModal" class="bg-amber-500 hover:bg-amber-600 text-slate-900 px-4 py-1.5 rounded-md text-xs font-bold transition shadow">
                            會員登入 / 註冊
                        </button>
                    </template>
                </div>
            </div>
        </header>

        <!-- 主要內容區 -->
        <main class="flex-1 w-full flex flex-col relative" :class="{'bg-slate-50': isLoggedIn, 'flex items-center justify-center overflow-hidden': !isLoggedIn}">

            <!-- 未登入時的滿版背景與登入引導 -->
            <template v-if="!isLoggedIn">
                <div class="absolute inset-0 z-0 bg-slate-900 overflow-hidden">
                    <img src="https://picsum.photos/id/1015/1920/1080" alt="背景圖"
                        class="w-full h-full object-cover object-center pointer-events-none select-none opacity-80"
                        style="filter: brightness(0.75) contrast(1.1);" onerror="this.style.display='none'">
                    <div class="absolute inset-0 bg-slate-950/50 backdrop-blur-[2px]"></div>
                </div>

                <div class="relative z-10 max-w-md w-full mx-4 bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl p-8 space-y-6 border border-white/25">
                    <div class="text-center space-y-3">
                        <svg class="w-16 h-16 mx-auto drop-shadow-md" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <defs>
                                <linearGradient id="login-tw-grad" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" stop-color="#34d399" />
                                    <stop offset="100%" stop-color="#059669" />
                                </linearGradient>
                            </defs>
                            <path d="M55 12 C67 30, 70 50, 58 72 C48 88, 36 88, 32 80 C27 70, 34 40, 46 15 Z" fill="url(#login-tw-grad)" />
                            <circle cx="50" cy="30" r="3.5" fill="#fef08a" class="animate-ping" />
                            <circle cx="50" cy="30" r="3.5" fill="#fef08a" />
                        </svg>
                        <h2 class="text-xl font-extrabold text-slate-900">
                            @{{ isRegisterMode ? '建立新會員帳號' : '歡迎回來，請先登入' }}
                        </h2>
                        <p class="text-xs text-slate-500">
                            @{{ isRegisterMode ? '填寫下方資訊以完成註冊' : '登入以解鎖所有景點管理與收藏功能' }}
                        </p>
                    </div>

                    <form @submit.prevent="handleAuthSubmit" class="space-y-4 text-xs">
                        <div v-if="isRegisterMode">
                            <label class="block font-bold text-slate-700 mb-1">使用者名稱 (Username)</label>
                            <input v-model="loginForm.name" :required="isRegisterMode" type="text" placeholder="請輸入您的暱稱" class="w-full p-2.5 border rounded-lg bg-white/80">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">電子郵件 (Email)</label>
                            <input v-model="loginForm.email" required type="email" placeholder="user@example.com" class="w-full p-2.5 border rounded-lg bg-white/80">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">密碼 (Password)</label>
                            <input v-model="loginForm.password" required type="password" placeholder="請輸入密碼" class="w-full p-2.5 border rounded-lg bg-white/80">
                        </div>

                        <div v-if="authError" class="text-red-500 text-xs font-bold bg-red-50 p-2.5 rounded-lg border border-red-100">
                            @{{ authError }}
                        </div>

                        <div class="pt-2 flex flex-col gap-3">
                            <button type="submit" class="w-full py-3 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold rounded-lg transition shadow-lg text-sm">
                                @{{ isRegisterMode ? '確認註冊' : '確認登入' }}
                            </button>
                            <button type="button" @click="toggleAuthMode" class="text-center text-slate-600 hover:text-slate-900 underline text-xs font-medium">
                                @{{ isRegisterMode ? '已經有帳號？點此登入' : '沒有帳號？點此註冊新會員' }}
                            </button>
                        </div>
                    </form>
                </div>
            </template>

            <!-- 已登入時的儀表板內容 -->
            <template v-else>
                <div class="max-w-7xl mx-auto px-4 py-6 flex-1 w-full space-y-6">

                    <!-- 視圖 A：一般景點管理儀表板 -->
                    <template v-if="currentView === 'dashboard'">
                        <!-- 搜尋與篩選列 -->
                        <div class="bg-white rounded-xl shadow-sm p-4 border border-slate-100 grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1">🔍 關鍵字 (keyword)</label>
                                <input v-model="filters.keyword" @input="fetchAttractions" type="text" placeholder="搜尋名稱、地址或分類..." class="w-full p-2 text-xs border rounded-md">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1">📍 縣市 / 行政區 (city)</label>
                                <select v-model="filters.city" @change="fetchAttractions" class="w-full p-2 text-xs border rounded-md">
                                    <option value="">全部地區</option>
                                    <option value="臺中市">臺中市 (全區)</option>
                                    <option disabled>--- 熱門行政區 ---</option>
                                    <option value="西屯區">西屯區</option>
                                    <option value="南屯區">南屯區</option>
                                    <option value="北屯區">北屯區</option>
                                    <option value="西區">西區</option>
                                    <option value="北區">北區</option>
                                    <option value="中區">中區</option>
                                    <option value="東區">東區</option>
                                    <option value="南區">南區</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1">🏷️ 分類 (category)</label>
                                <select v-model="filters.category" @change="fetchAttractions" class="w-full p-2 text-xs border rounded-md">
                                    <option value="">全部分類</option>
                                    <option v-for="cat in categories" :value="cat.id">@{{ cat.name }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1">↕️ 排序</label>
                                <select v-model="filters.sort" @change="fetchAttractions" class="w-full p-2 text-xs border rounded-md">
                                    <option value="latest">最新上架 (由新到舊)</option>
                                    <option value="oldest">最早上架 (由舊到新)</option>
                                </select>
                            </div>
                            <div>
                                <button @click="resetFilters" class="w-full py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-md transition border">
                                    重設
                                </button>
                            </div>
                        </div>

                        <!-- 左右雙欄：景點列表 (佔 2 欄) 與分類管理 (佔 1 欄) -->
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            <!-- 左側：景點列表 -->
                            <div class="lg:col-span-2 bg-white rounded-xl shadow-sm p-6 border border-slate-100 space-y-4">
                                <div class="flex justify-between items-center">
                                    <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">🌄 景點列表</h2>
                                    <div class="flex items-center gap-3">
                                        <div class="flex items-center gap-1 text-xs text-slate-600">
                                            <span>每頁筆數：</span>
                                            <select v-model="perPage" @change="fetchAttractions" class="p-1 border rounded text-xs">
                                                <option :value="4">4 筆</option>
                                                <option :value="6">6 筆</option>
                                                <option :value="10">10 筆</option>
                                            </select>
                                        </div>
                                        <button @click="openCreateModal" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold px-3 py-1.5 rounded-md transition shadow">
                                            + 新增景點
                                        </button>
                                    </div>
                                </div>

                                <template v-if="attractions.length > 0">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div v-for="item in attractions" :key="item.id" class="bg-white border rounded-xl overflow-hidden shadow-sm hover:shadow-md transition flex flex-col justify-between">
                                            <div>
                                                <div class="relative h-48 bg-slate-100">
                                                    <img :src="item.image_url || 'https://placehold.co/600x400'" alt="預覽圖" class="w-full h-full object-cover">
                                                    <span class="absolute top-2 left-2 bg-emerald-600 text-white text-[10px] font-bold px-2 py-0.5 rounded shadow">
                                                        USER ITEM
                                                    </span>
                                                    <!-- 收藏按鈕 -->
                                                    <button @click="toggleFavorite(item)" class="absolute top-2 right-2 p-1.5 bg-white/80 hover:bg-white rounded-full shadow transition text-sm">
                                                        @{{ item.is_favorited ? '❤️' : '🤍' }}
                                                    </button>
                                                </div>
                                                <div class="p-4 space-y-2">
                                                    <h3 class="font-bold text-slate-900 text-base">@{{ item.name }}</h3>
                                                    <div class="flex items-center gap-2 text-xs flex-wrap">
                                                        <span :class="getCategoryBadgeClass(item.category_name)" class="px-2.5 py-0.5 rounded font-medium text-xs">
                                                            @{{ item.category_name }}
                                                        </span>
                                                        <span class="text-slate-500">📍 @{{ item.city || '未提供地址' }}</span>
                                                    </div>
                                                    <div class="text-[11px] text-slate-400">
                                                        <span>📅 建立時間：@{{ item.created_at ? item.created_at.replace('T', ' ').substring(0, 16) : '' }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="px-4 pb-4 pt-2 border-t flex justify-end gap-3 text-xs">
                                                <button @click="openEditModal(item)" class="text-blue-600 hover:underline font-medium">✏️ 編輯</button>
                                                <button @click="deleteAttraction(item.id)" class="text-rose-600 hover:underline font-medium">🗑️ 刪除</button>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                                <template v-else>
                                    <div class="text-center py-12 text-slate-400 text-xs border border-dashed rounded-lg bg-slate-50">
                                        查無符合條件的景點資料
                                    </div>
                                </template>

                                <!-- 分頁 -->
                                <div class="flex justify-between items-center py-2 px-3 text-xs text-slate-600 border rounded-lg bg-slate-50 mt-4">
                                    <button @click="changePage(pagination.current_page - 1)" :disabled="pagination.current_page <= 1" class="px-2 py-1 bg-white border rounded disabled:opacity-50">
                                        &lt; 上一頁
                                    </button>
                                    <span>頁碼 @{{ pagination.current_page }} / @{{ pagination.last_page || 1 }}</span>
                                    <button @click="changePage(pagination.current_page + 1)" :disabled="pagination.current_page >= pagination.last_page" class="px-2 py-1 bg-white border rounded disabled:opacity-50">
                                        下一頁 &gt;
                                    </button>
                                </div>
                            </div>

                            <!-- 右側：分類管理 -->
                            <div class="space-y-6">
                                <div class="bg-white rounded-xl shadow-sm p-5 border border-slate-100 space-y-3">
                                    <h2 class="text-sm font-bold text-slate-900 flex items-center gap-1">🏷️ 分類管理</h2>
                                    <div class="space-y-2">
                                        <input v-model="newCategoryName" @input="handleCategoryInput" @keyup.enter="storeCategory" type="text" placeholder="輸入新分類名稱 (僅限中文)..." class="w-full p-2 text-xs border rounded-md">
                                        <button type="button" @click="storeCategory" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold py-2 rounded transition shadow">
                                            + 新增分類
                                        </button>
                                    </div>
                                    <div class="space-y-1.5 max-h-48 overflow-y-auto pt-2">
                                        <div v-for="(cat, index) in categories" :key="cat.id" class="text-xs text-slate-700 border rounded p-2 bg-slate-50 flex justify-between items-center">
                                            <span>@{{ index + 1 }}. @{{ cat.name }}</span>
                                            <div class="space-x-1">
                                                <button type="button" @click="openEditCategoryModal(cat)" class="text-blue-600 hover:underline">修改</button>
                                                <button type="button" @click="deleteCategory(cat.id)" class="text-rose-600 hover:underline">刪除</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- 視圖 B：我的收藏景點列表 -->
                    <template v-if="currentView === 'favorites'">
                        <div class="bg-white rounded-xl shadow-sm p-6 border border-slate-100 space-y-4">
                            <div class="flex justify-between items-center">
                                <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">⭐ 我的收藏景點</h2>
                                <button @click="switchView('dashboard')" class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold px-3 py-1.5 rounded-md transition border">
                                    &lt; 返回景點列表
                                </button>
                            </div>

                            <template v-if="favorites.length > 0">
                                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
                                    <div v-for="item in favorites" :key="item.id" class="bg-white border rounded-xl overflow-hidden shadow-sm hover:shadow-md transition flex flex-col justify-between">
                                        <div>
                                            <div class="relative h-40 bg-slate-100">
                                                <img :src="item.image_url || 'https://placehold.co/600x400'" alt="預覽圖" class="w-full h-full object-cover">
                                                <button @click="toggleFavorite(item)" class="absolute top-2 right-2 p-1.5 bg-white/90 hover:bg-white rounded-full shadow transition text-sm">
                                                    ❤️
                                                </button>
                                            </div>
                                            <div class="p-3 space-y-1.5">
                                                <h3 class="font-bold text-slate-900 text-sm">@{{ item.name }}</h3>
                                                <div class="flex items-center gap-2 text-xs flex-wrap">
                                                    <span :class="getCategoryBadgeClass(item.category_name)" class="px-2 py-0.5 rounded font-medium text-[10px]">
                                                        @{{ item.category_name }}
                                                    </span>
                                                    <span class="text-slate-500 text-[11px]">📍 @{{ item.city || '未提供地址' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                            <template v-else>
                                <div class="text-center py-16 text-slate-400 text-xs border border-dashed rounded-lg bg-slate-50 space-y-2">
                                    <p class="text-sm">您目前還沒有收藏任何景點</p>
                                    <button @click="switchView('dashboard')" class="text-blue-600 underline font-medium">去瀏覽並收藏喜歡的景點吧！</button>
                                </div>
                            </template>
                        </div>
                    </template>

                </div>
            </template>
        </main>

        <!-- 會員中心 Modal (修改基本資料) -->
        <div v-if="showProfileModal" class="fixed inset-0 bg-slate-900/50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-xl shadow-xl max-w-sm w-full p-6 space-y-4">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-bold text-slate-900">⚙️ 修改會員基本資訊</h3>
                    <button @click="showProfileModal = false" class="text-slate-400 hover:text-slate-600">✕</button>
                </div>
                <form @submit.prevent="updateProfile" class="space-y-3 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">使用者名稱 (Username)</label>
                        <input v-model="profileForm.name" required type="text" class="w-full p-2 border rounded-md">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">電子郵件 (Email)</label>
                        <input v-model="profileForm.email" required type="email" class="w-full p-2 border rounded-md">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">新密碼 (若不修改請保持空白)</label>
                        <input v-model="profileForm.password" type="password" placeholder="留空則不變更密碼" class="w-full p-2 border rounded-md">
                    </div>
                    <div v-if="profileError" class="text-red-500 text-xs font-bold bg-red-50 p-2 rounded">@{{ profileError }}</div>
                    <div v-if="profileSuccess" class="text-emerald-600 text-xs font-bold bg-emerald-50 p-2 rounded">@{{ profileSuccess }}</div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="showProfileModal = false" class="px-4 py-2 bg-slate-100 rounded-md">取消</button>
                        <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-md font-bold">儲存變更</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 登入 / 註冊 Modal -->
        <div v-if="showLoginModal && !isLoggedIn" class="fixed inset-0 bg-slate-900/50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-xl shadow-xl max-w-sm w-full p-6 space-y-4">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-bold text-slate-900">@{{ isRegisterMode ? '📝 會員註冊' : '🔐 會員登入' }}</h3>
                    <button @click="showLoginModal = false" class="text-slate-400 hover:text-slate-600">✕</button>
                </div>
                <form @submit.prevent="handleAuthSubmit" class="space-y-3 text-xs">
                    <div v-if="isRegisterMode">
                        <label class="block font-bold text-slate-700 mb-1">使用者名稱</label>
                        <input v-model="loginForm.name" :required="isRegisterMode" type="text" class="w-full p-2 border rounded-md">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">電子郵件</label>
                        <input v-model="loginForm.email" required type="email" class="w-full p-2 border rounded-md">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">密碼</label>
                        <input v-model="loginForm.password" required type="password" class="w-full p-2 border rounded-md">
                    </div>
                    <div v-if="authError" class="text-red-500 text-xs font-bold bg-red-50 p-2 rounded">@{{ authError }}</div>
                    <button type="submit" class="w-full py-2 bg-amber-500 hover:bg-amber-600 text-slate-900 font-bold rounded-md">@{{ isRegisterMode ? '確認註冊' : '確認登入' }}</button>
                </form>
            </div>
        </div>

        <!-- 新增景點 Modal -->
        <div v-if="showCreateModal" class="fixed inset-0 bg-slate-900/50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6 space-y-4">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-bold text-slate-900">🌄 新增旅遊景點</h3>
                    <button @click="showCreateModal = false" class="text-slate-400">✕</button>
                </div>
                <form @submit.prevent="submitForm('create')" class="space-y-3 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">景點名稱 * (可輸入中文、英文與數字)</label>
                        <input v-model="attractionForm.name" @input="handleNameInput('create')" type="text" placeholder="請輸入名稱" class="w-full p-2 border rounded-md">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">所屬分類 *</label>
                        <select v-model="attractionForm.category_id" class="w-full p-2 border rounded-md">
                            <option value="">請選擇分類</option>
                            <option v-for="cat in categories" :value="cat.id">@{{ cat.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">詳細地址 * (僅限中文與門牌號碼數字)</label>
                        <input v-model="attractionForm.city" @input="handleCityInput('create')" type="text" placeholder="請輸入詳細地址" class="w-full p-2 border rounded-md">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">圖片網址</label>
                        <input v-model="attractionForm.image_url" type="url" class="w-full p-2 border rounded-md">
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="showCreateModal = false" class="px-4 py-2 bg-slate-100 rounded-md">取消</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md">確認新增</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 修改景點 Modal -->
        <div v-if="showEditModal" class="fixed inset-0 bg-slate-900/50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6 space-y-4">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-bold text-slate-900">✏️ 修改景點</h3>
                    <button @click="showEditModal = false" class="text-slate-400">✕</button>
                </div>
                <form @submit.prevent="submitForm('edit')" class="space-y-3 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">景點名稱 * (可輸入中文、英文與數字)</label>
                        <input v-model="editForm.name" @input="handleNameInput('edit')" type="text" class="w-full p-2 border rounded-md">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">所屬分類 *</label>
                        <select v-model="editForm.category_id" class="w-full p-2 border rounded-md">
                            <option value="">請選擇分類</option>
                            <option v-for="cat in categories" :value="cat.id">@{{ cat.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">詳細地址 * (僅限中文與門牌號碼數字)</label>
                        <input v-model="editForm.city" @input="handleCityInput('edit')" type="text" class="w-full p-2 border rounded-md">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">圖片網址</label>
                        <input v-model="editForm.image_url" type="url" class="w-full p-2 border rounded-md">
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="showEditModal = false" class="px-4 py-2 bg-slate-100 rounded-md">取消</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md">確認修改</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 修改分類 Modal -->
        <div v-if="showEditCategoryModal" class="fixed inset-0 bg-slate-900/50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-xl shadow-xl max-w-sm w-full p-6 space-y-4">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-bold text-slate-900">✏️ 修改分類</h3>
                    <button @click="showEditCategoryModal = false" class="text-slate-400">✕</button>
                </div>
                <form @submit.prevent="updateCategory" class="space-y-3 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">分類名稱 (僅限中文)</label>
                        <input v-model="editCategoryForm.name" @input="handleEditCategoryInput" required type="text" class="w-full p-2 border rounded-md">
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="showEditCategoryModal = false" class="px-4 py-2 bg-slate-100 rounded-md">取消</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md">確認修改</button>
                    </div>
                </form>
            </div>
        </div>

        <footer class="bg-slate-900 text-slate-400 text-xs py-4 text-center mt-auto relative z-20">
            Copyright © 2026 旅遊景點管理與數據統計 Dashboard. All rights reserved.
        </footer>
    </div>

    <!-- Vue 互動邏輯 -->
    <script>
        const {
            createApp,
            ref,
            onMounted
        } = Vue;

        const authFetch = async (url, options = {}) => {
            const token = localStorage.getItem('token');
            const headers = {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                ...(token ? {
                    'Authorization': `Bearer ${token}`
                } : {}),
                ...(options.headers || {})
            };
            return fetch(url, {
                ...options,
                headers
            });
        };

        createApp({
            setup() {
                const isLoggedIn = ref(false);
                const currentUser = ref({
                    id: null,
                    name: '',
                    email: ''
                });
                const currentView = ref('dashboard'); // 'dashboard' 或 'favorites'

                const showLoginModal = ref(false);
                const showCreateModal = ref(false);
                const showEditModal = ref(false);
                const showEditCategoryModal = ref(false);
                const showProfileModal = ref(false);

                const isRegisterMode = ref(false);
                const authError = ref('');
                const profileError = ref('');
                const profileSuccess = ref('');

                const loginForm = ref({
                    name: '',
                    email: '',
                    password: ''
                });
                const profileForm = ref({
                    name: '',
                    email: '',
                    password: ''
                });

                const attractionForm = ref({
                    name: '',
                    category_id: '',
                    city: '',
                    image_url: ''
                });
                const editForm = ref({
                    id: null,
                    name: '',
                    category_id: '',
                    city: '',
                    image_url: ''
                });
                const editCategoryForm = ref({
                    id: null,
                    name: ''
                });

                const attractions = ref([]);
                const favorites = ref([]);
                const categories = ref([]);
                const perPage = ref(4);
                const pagination = ref({
                    current_page: 1,
                    last_page: 1
                });
                const newCategoryName = ref('');

                const filters = ref({
                    keyword: '',
                    city: '',
                    category: '',
                    sort: 'latest'
                });

                const getCategoryBadgeClass = (categoryName) => {
                    switch (categoryName) {
                        case '美食小吃':
                            return 'bg-rose-100 text-rose-800';
                        case '主題樂園':
                            return 'bg-sky-100 text-sky-800';
                        case '歷史古蹟':
                            return 'bg-amber-100 text-amber-800';
                        case '藝文展館':
                        case '藝文展覽':
                            return 'bg-emerald-100 text-emerald-800';
                        case '自然風景':
                            return 'bg-teal-100 text-teal-800';
                        default:
                            return 'bg-slate-100 text-slate-800';
                    }
                };

                const switchView = (view) => {
                    currentView.value = view;
                    if (view === 'favorites') {
                        fetchFavorites();
                    }
                };

                const openProfileModal = () => {
                    profileForm.value = {
                        name: currentUser.value.name,
                        email: currentUser.value.email,
                        password: ''
                    };
                    profileError.value = '';
                    profileSuccess.value = '';
                    showProfileModal.value = true;
                };

                const updateProfile = async () => {
                    profileError.value = '';
                    profileSuccess.value = '';
                    try {
                        const res = await authFetch('/api/profile', {
                            method: 'PUT',
                            body: JSON.stringify(profileForm.value)
                        });
                        const data = await res.json();
                        if (res.ok) {
                            currentUser.value = data.user;
                            profileSuccess.value = '基本資訊更新成功！';
                            setTimeout(() => {
                                showProfileModal.value = false;
                            }, 1200);
                        } else {
                            profileError.value = data.message || '更新失敗';
                        }
                    } catch (e) {
                        profileError.value = '連線伺服器失敗';
                    }
                };

                const toggleFavorite = async (item) => {
                    try {
                        const res = await authFetch(`/api/attractions/${item.id}/favorite`, {
                            method: 'POST'
                        });
                        if (res.ok) {
                            const data = await res.json();
                            item.is_favorited = data.is_favorited;
                            if (currentView.value === 'favorites') {
                                fetchFavorites();
                            }
                        }
                    } catch (e) {
                        console.error('收藏操作失敗', e);
                    }
                };

                const fetchFavorites = async () => {
                    try {
                        const res = await authFetch('/api/favorites');
                        if (res.ok) {
                            favorites.value = await res.json();
                        }
                    } catch (e) {
                        console.error('取得收藏失敗', e);
                    }
                };

                const handleNameInput = (mode) => {
                    let target = mode === 'create' ? attractionForm.value : editForm.value;
                    let val = target.name || '';
                    val = val.replace(/台/g, '臺');
                    val = val.replace(/[^\u4e00-\u9fa5a-zA-Z0-9]/g, '');
                    target.name = val;
                };

                const handleCityInput = (mode) => {
                    let target = mode === 'create' ? attractionForm.value : editForm.value;
                    let val = target.city || '';
                    val = val.replace(/台/g, '臺');
                    val = val.replace(/[^\u4e00-\u9fa50-9]/g, '');
                    target.city = val;
                };

                const handleCategoryInput = () => {
                    let val = newCategoryName.value || '';
                    val = val.replace(/台/g, '臺');
                    val = val.replace(/[^\u4e00-\u9fa5]/g, '');
                    newCategoryName.value = val;
                };

                const handleEditCategoryInput = () => {
                    let val = editCategoryForm.value.name || '';
                    val = val.replace(/台/g, '臺');
                    val = val.replace(/[^\u4e00-\u9fa5]/g, '');
                    editCategoryForm.value.name = val;
                };

                const submitForm = (mode) => {
                    const form = mode === 'create' ? attractionForm.value : editForm.value;
                    if (!form.name || form.name.trim() === '') {
                        alert('請輸入正確的景點名稱！');
                        return;
                    }
                    if (!form.category_id) {
                        alert('請選擇正確的分類！');
                        return;
                    }
                    if (!form.city || form.city.trim() === '') {
                        alert('請輸入正確的詳細地址！');
                        return;
                    }

                    if (mode === 'create') storeAttraction();
                    else updateAttraction();
                };

                const openLoginModal = () => {
                    authError.value = '';
                    showLoginModal.value = true;
                };
                const openCreateModal = () => {
                    showCreateModal.value = true;
                };
                const openEditModal = (item) => {
                    editForm.value = {
                        ...item
                    };
                    showEditModal.value = true;
                };
                const openEditCategoryModal = (cat) => {
                    editCategoryForm.value = {
                        ...cat
                    };
                    showEditCategoryModal.value = true;
                };
                const toggleAuthMode = () => {
                    isRegisterMode.value = !isRegisterMode.value;
                    authError.value = '';
                };

                const handleAuthSubmit = async () => {
                    authError.value = '';
                    const endpoint = isRegisterMode.value ? '/api/register' : '/api/login';
                    try {
                        const res = await authFetch(endpoint, {
                            method: 'POST',
                            body: JSON.stringify(loginForm.value)
                        });
                        const data = await res.json();
                        if (res.ok) {
                            isLoggedIn.value = true;
                            currentUser.value = data.user;
                            if (data.token) localStorage.setItem('token', data.token);
                            showLoginModal.value = false;
                            fetchCategories();
                            fetchAttractions();
                        } else {
                            authError.value = data.message || '帳號或密碼錯誤';
                        }
                    } catch (e) {
                        authError.value = '連線伺服器失敗';
                    }
                };

                const handleLogout = () => {
                    localStorage.removeItem('token');
                    isLoggedIn.value = false;
                    currentUser.value = {
                        id: null,
                        name: '',
                        email: ''
                    };
                    loginForm.value = {
                        name: '',
                        email: '',
                        password: ''
                    };
                    attractions.value = [];
                    currentView.value = 'dashboard';
                };

                const fetchAttractions = async (page = 1) => {
                    if (!isLoggedIn.value) return;
                    try {
                        const query = new URLSearchParams({
                            keyword: filters.value.keyword || '',
                            city: filters.value.city || '',
                            category: filters.value.category || '',
                            sort: filters.value.sort || 'latest',
                            per_page: perPage.value,
                            page: page
                        });
                        const res = await authFetch(`/api/attractions?${query}`);
                        if (res.ok) {
                            const data = await res.json();
                            attractions.value = data.data || data;
                            if (data.current_page) {
                                pagination.value = {
                                    current_page: data.current_page,
                                    last_page: data.last_page
                                };
                            }
                        }
                    } catch (e) {
                        console.error('取得景點失敗', e);
                    }
                };

                const storeAttraction = async () => {
                    try {
                        const res = await authFetch('/api/attractions', {
                            method: 'POST',
                            body: JSON.stringify(attractionForm.value)
                        });
                        if (res.ok) {
                            showCreateModal.value = false;
                            fetchAttractions(pagination.value.current_page);
                        } else {
                            const data = await res.json();
                            alert('新增失敗：' + (data.message || '未知錯誤'));
                        }
                    } catch (e) {
                        console.error('新增失敗', e);
                    }
                };

                const updateAttraction = async () => {
                    try {
                        const res = await authFetch(`/api/attractions/${editForm.value.id}`, {
                            method: 'PUT',
                            body: JSON.stringify(editForm.value)
                        });
                        if (res.ok) {
                            showEditModal.value = false;
                            fetchAttractions(pagination.value.current_page);
                        } else {
                            const data = await res.json();
                            alert('修改失敗：' + (data.message || '未知錯誤'));
                        }
                    } catch (e) {
                        console.error('修改失敗', e);
                    }
                };

                const deleteAttraction = async (id) => {
                    if (!confirm('確定要刪除此景點嗎？')) return;
                    try {
                        const res = await authFetch(`/api/attractions/${id}`, {
                            method: 'DELETE'
                        });
                        if (res.ok) {
                            fetchAttractions(pagination.value.current_page);
                        }
                    } catch (e) {
                        console.error('刪除失敗', e);
                    }
                };

                const fetchCategories = async () => {
                    if (!isLoggedIn.value) return;
                    try {
                        const res = await authFetch('/api/categories');
                        if (res.ok) categories.value = await res.json();
                    } catch (e) {
                        console.error('取得分類失敗', e);
                    }
                };

                const storeCategory = async () => {
                    if (!newCategoryName.value.trim()) return;
                    try {
                        const res = await authFetch('/api/categories', {
                            method: 'POST',
                            body: JSON.stringify({
                                name: newCategoryName.value
                            })
                        });
                        if (res.ok) {
                            newCategoryName.value = '';
                            fetchCategories();
                        }
                    } catch (e) {
                        console.error('新增分類失敗', e);
                    }
                };

                const updateCategory = async () => {
                    try {
                        const res = await authFetch(`/api/categories/${editCategoryForm.value.id}`, {
                            method: 'PUT',
                            body: JSON.stringify({
                                name: editCategoryForm.value.name
                            })
                        });
                        if (res.ok) {
                            showEditCategoryModal.value = false;
                            fetchCategories();
                            fetchAttractions();
                        }
                    } catch (e) {
                        console.error('修改分類失敗', e);
                    }
                };

                const deleteCategoryMethod = async (id) => {
                    if (!confirm('確定要刪除此分類嗎？')) return;
                    try {
                        const res = await authFetch(`/api/categories/${id}`, {
                            method: 'DELETE'
                        });
                        const data = await res.json();
                        if (res.ok) fetchCategories();
                        else alert(data.message || '刪除失敗');
                    } catch (e) {
                        console.error('刪除分類失敗', e);
                    }
                };

                const changePage = (page) => {
                    if (page >= 1 && page <= pagination.value.last_page) fetchAttractions(page);
                };

                const resetFilters = () => {
                    filters.value = {
                        keyword: '',
                        city: '',
                        category: '',
                        sort: 'latest'
                    };
                    fetchAttractions();
                };

                return {
                    isLoggedIn,
                    currentUser,
                    currentView,
                    showLoginModal,
                    showCreateModal,
                    showEditModal,
                    showEditCategoryModal,
                    showProfileModal,
                    isRegisterMode,
                    authError,
                    profileError,
                    profileSuccess,
                    loginForm,
                    profileForm,
                    attractionForm,
                    editForm,
                    editCategoryForm,
                    attractions,
                    favorites,
                    categories,
                    perPage,
                    pagination,
                    newCategoryName,
                    filters,
                    getCategoryBadgeClass,
                    switchView,
                    openProfileModal,
                    updateProfile,
                    toggleFavorite,
                    handleNameInput,
                    handleCityInput,
                    handleCategoryInput,
                    handleEditCategoryInput,
                    submitForm,
                    openLoginModal,
                    openCreateModal,
                    openEditModal,
                    openEditCategoryModal,
                    toggleAuthMode,
                    handleAuthSubmit,
                    handleLogout,
                    fetchAttractions,
                    storeAttraction,
                    updateAttraction,
                    deleteAttraction,
                    storeCategory,
                    updateCategory,
                    deleteCategory: deleteCategoryMethod,
                    changePage,
                    resetFilters
                };
            }
        }).mount('#app');
    </script>
</body>

</html>