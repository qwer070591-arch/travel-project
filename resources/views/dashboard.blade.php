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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="bg-slate-50 text-slate-800 min-h-screen flex flex-col">

    <div id="app" class="flex flex-col min-h-screen">
        <!-- 頂部導覽列 -->
        <header class="text-white shadow-md relative z-20" style="background-color: #000000;">
            <div class="max-w-7xl mx-auto px-4 py-3 flex justify-between items-center">
                <h1 class="text-lg font-bold flex items-center gap-2 cursor-pointer" @click="currentView = 'dashboard'">
                    <img src="images/img23.jpg" alt="微風台中 Logo" class="h-10 w-auto object-contain">
                    <span class="text-4xl font-semibold" style="color: #c4c1be;">微風台中</span>
                </h1>
                <div>
                    <template v-if="isLoggedIn">
                        <div class="flex items-center gap-2 sm:gap-3">
                            <span class="text-xs bg-slate-800 px-3 py-1.5 rounded-full border border-slate-700 text-emerald-400 font-medium hidden sm:inline-block">
                                @{{ currentUser.name }}
                            </span>
                            <button @click="switchView('favorites')" :class="currentView === 'favorites' ? 'bg-emerald-600 text-white' : 'bg-slate-800 text-slate-200 hover:bg-slate-700'" class="px-3 py-1.5 rounded-md text-xs font-bold transition border border-slate-700">
                                我的收藏
                            </button>
                            <button @click="openCategoryModal" class="bg-slate-800 hover:bg-slate-700 text-slate-200 px-3 py-1.5 rounded-md text-xs font-bold transition border border-slate-700">
                                分類管理
                            </button>
                            <button @click="openProfileModal" class="bg-slate-800 hover:bg-slate-700 text-slate-200 px-3 py-1.5 rounded-md text-xs font-bold transition border border-slate-700">
                                會員中心
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

            <!-- 未登入畫面 -->
            <template v-if="!isLoggedIn">
                <div class="relative z-10 w-full flex-1 flex items-center justify-center overflow-hidden bg-cover bg-center" style="background-image: url('/images/img06.jpg')">
                    <!-- 半透明遮罩，提升文字對比度 -->
                    <div class="absolute inset-0 bg-black/40"></div>
                    <div class="relative z-10 max-w-md w-full mx-4 bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl p-8 space-y-6 border border-white/25">
                        <div class="text-center space-y-3">
                            <h2 class="text-xl font-extrabold text-slate-900">
                                @{{ isRegisterMode ? '建立新會員帳號' : '歡迎回來，請先登入' }}
                            </h2>
                        </div>
                        <form @submit.prevent="handleAuthSubmit" class="space-y-4 text-xs">
                            <div v-if="isRegisterMode">
                                <label class="block font-bold text-slate-700 mb-1">使用者名稱</label>
                                <input v-model="loginForm.name" :required="isRegisterMode" type="text" class="w-full p-2.5 border rounded-lg bg-white/80">
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">電子郵件 (Email)</label>
                                <input v-model="loginForm.email" required type="email" autocomplete="off" placeholder="user@example.com" class="w-full p-2.5 border rounded-lg bg-white/80">
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">密碼 (Password)</label>
                                <input v-model="loginForm.password" required type="password" autocomplete="new-password" placeholder="請輸入密碼" class="w-full p-2.5 border rounded-lg bg-white/80">
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
                </div>
            </template>

            <!-- 已登入畫面 -->
            <template v-else>
                <div class="max-w-7xl mx-auto px-4 py-6 flex-1 w-full space-y-6">

                    <!-- 1. 本週特輯內頁視圖 -->
                    <template v-if="currentView === 'weeklySpecial'">
                        <div class="space-y-6 p-6 rounded-3xl bg-[#E4EFEA]">
                            <!-- 頂部標題列與返回按鈕 -->
                            <div class="flex justify-between items-center bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                                <div>
                                    <h1 class="text-2xl font-bold text-slate-800">日常の臺中</h1>
                                    <p class="text-xs text-slate-500">聆聽城市與綠意交織的呼吸。</p>
                                </div>
                                <div class="flex items-center gap-4">
                                    <span class="px-3 py-1 bg-slate-800 text-white text-xs rounded-full">本週特輯</span>
                                    <button @click="currentView = 'dashboard'" class="px-4 py-2 bg-emerald-600 text-white text-xs font-bold rounded-lg hover:bg-emerald-700 transition">
                                        &larr; 返回首頁
                                    </button>
                                </div>
                            </div>

                            <div v-for="(item, index) in weeklyData" :key="'weekly-' + index" class="bg-[#FBF9F5] rounded-3xl shadow-sm p-8 border border-slate-200/60 mb-6">
                                <div class="grid grid-cols-1 md:grid-cols-12 gap-8 items-center">
                                    <div class="md:col-span-5">
                                        <img :src="item.image" :alt="item.title" class="w-full h-[300px] object-cover rounded-2xl shadow-md bg-slate-200">
                                    </div>
                                    <div class="md:col-span-7 space-y-4">
                                        <h2 class="text-xl font-bold text-slate-900">@{{ item.title }}</h2>
                                        <div class="flex items-center gap-3 text-xs">
                                            <span class="text-sky-600 font-medium">@{{ item.category }}</span>
                                            <span class="text-slate-300">|</span>
                                            <span class="text-slate-600">📍 @{{ item.location }} | @{{ item.address }}</span>
                                        </div>
                                        <p class="text-slate-600 text-sm leading-relaxed">
                                            @{{ item.description }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- 2. 巷弄咖啡獨立內頁視圖 -->
                    <template v-if="currentView === 'coffeeSpecial'">
                        <div class="space-y-6 p-6 rounded-3xl bg-[#FDF6EC]">
                            <!-- 頂部標題列與返回按鈕 -->
                            <div class="flex justify-between items-center bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                                <div>
                                    <h1 class="text-2xl font-bold text-slate-800">老宅尋香 ｜ 巷弄咖啡</h1>
                                    <p class="text-xs text-slate-500">在城市的轉角，與迷人咖啡香不期而遇。</p>
                                </div>
                                <div class="flex items-center gap-4">
                                    <span class="px-3 py-1 bg-amber-800 text-white text-xs rounded-full">巷弄咖啡</span>
                                    <button @click="currentView = 'dashboard'" class="px-4 py-2 bg-amber-600 text-white text-xs font-bold rounded-lg hover:bg-amber-700 transition">
                                        &larr; 返回首頁
                                    </button>
                                </div>
                            </div>

                            <a v-for="(item, index) in coffeeData" :key="'coffee-' + index"
                                :href="'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(item.title + ' ' + item.address)"
                                target="_blank"
                                class="block bg-[#FBF9F5] rounded-3xl shadow-sm p-8 border border-slate-200/60 mb-6 hover:shadow-md transition-shadow">
                                <div class="grid grid-cols-1 md:grid-cols-12 gap-8 items-center">
                                    <div class="md:col-span-5">
                                        <img :src="item.image" :alt="item.title" class="w-full h-[300px] object-cover rounded-2xl shadow-md bg-slate-200">
                                    </div>
                                    <div class="md:col-span-7 space-y-4">
                                        <h2 class="text-xl font-bold text-slate-900">@{{ item.title }}</h2>
                                        <div class="flex items-center gap-3 text-xs">
                                            <span class="text-amber-600 font-medium">@{{ item.category }}</span>
                                            <span class="text-slate-300">|</span>
                                            <span class="text-slate-600">📍 @{{ item.location }} | @{{ item.address }}</span>
                                        </div>
                                        <p class="text-slate-600 text-sm leading-relaxed">
                                            @{{ item.description }}
                                        </p>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </template>

                    <!-- 3. 一日漫遊足跡 獨立內頁視圖 -->
                    <template v-if="currentView === 'itinerary'">
                        <div class="space-y-6 p-6 rounded-3xl bg-[#EEF2F6]">
                            <!-- 頂部標題列與返回按鈕 -->
                            <div class="flex justify-between items-center bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                                <div>
                                    <h1 class="text-2xl font-bold text-slate-800">日常の臺中 ｜ 慢活散策一日遊</h1>
                                    <p class="text-xs text-slate-500">為你量身打造充滿日雜文青感、結合綠意散策與職人咖啡的西區慢活行程。</p>
                                </div>
                                <div class="flex items-center gap-4">
                                    <span class="px-3 py-1 bg-indigo-800 text-white text-xs rounded-full">一日漫遊足跡</span>
                                    <button @click="currentView = 'dashboard'" class="px-4 py-2 bg-indigo-600 text-white text-xs font-bold rounded-lg hover:bg-indigo-700 transition">
                                        &larr; 返回首頁
                                    </button>
                                </div>
                            </div>

                            <!-- 行程時間軸區塊 -->
                            <div class="bg-[#FBF9F5] rounded-3xl shadow-sm p-8 border border-slate-200/60 mb-6 space-y-10">

                                <!-- 上午 -->
                                <div class="relative pl-6 border-l-2 border-emerald-300 space-y-4">
                                    <div class="absolute -left-[11px] top-0 w-5 h-5 bg-emerald-500 rounded-full border-4 border-[#FBF9F5]"></div>
                                    <h2 class="text-lg font-bold text-emerald-800 mb-2"> 上午：綠意與建築的晨間巡禮</h2>

                                    <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-100 hover:shadow-md transition">
                                        <div class="flex items-center gap-3 mb-2">
                                            <span class="text-xs font-bold bg-slate-100 text-slate-600 px-2 py-1 rounded">09:30 - 11:00</span>
                                            <h3 class="font-bold text-slate-900 text-base">臺中國家歌劇院</h3>
                                        </div>
                                        <p class="text-sm text-slate-600 leading-relaxed"><strong class="text-emerald-700">行程亮點：</strong> 感受曲牆建築的光影流動，頂樓的戶外空中花園非常適合清晨散步，拍出極簡質感的文青照。</p>
                                    </div>

                                    <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-100 hover:shadow-md transition">
                                        <div class="flex items-center gap-3 mb-2">
                                            <span class="text-xs font-bold bg-slate-100 text-slate-600 px-2 py-1 rounded">11:00 - 12:30</span>
                                            <h3 class="font-bold text-slate-900 text-base">草悟道綠帶散策</h3>
                                        </div>
                                        <p class="text-sm text-slate-600 leading-relaxed"><strong class="text-emerald-700">行程亮點：</strong> 從歌劇院沿著草悟道慢慢散步或騎公共單車，感受台中特有的綠意街景與街頭藝術。</p>
                                    </div>
                                </div>

                                <!-- 午間 -->
                                <div class="relative pl-6 border-l-2 border-amber-300 space-y-4">
                                    <div class="absolute -left-[11px] top-0 w-5 h-5 bg-amber-500 rounded-full border-4 border-[#FBF9F5]"></div>
                                    <h2 class="text-lg font-bold text-amber-800 mb-2"> 午間：老宅與咖啡的午後時光</h2>

                                    <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-100 hover:shadow-md transition">
                                        <div class="flex items-center gap-3 mb-2">
                                            <span class="text-xs font-bold bg-slate-100 text-slate-600 px-2 py-1 rounded">12:30 - 14:00</span>
                                            <h3 class="font-bold text-slate-900 text-base">審計新村 ＆ 在地小食</h3>
                                        </div>
                                        <p class="text-sm text-slate-600 leading-relaxed"><strong class="text-amber-700">行程亮點：</strong> 逛逛紅磚老宿舍改建的青創市集，尋找手作選物與特色小店。</p>
                                    </div>

                                    <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-100 hover:shadow-md transition">
                                        <div class="flex items-center gap-3 mb-2">
                                            <span class="text-xs font-bold bg-slate-100 text-slate-600 px-2 py-1 rounded">14:00 - 16:30</span>
                                            <h3 class="font-bold text-slate-900 text-base">巷弄咖啡館午茶</h3>
                                        </div>
                                        <p class="text-sm text-slate-600 leading-relaxed"><strong class="text-amber-700">行程推薦：</strong> 選擇造訪人氣指標老宅咖啡店 Coffee Stopover（民權路巷弄），品嚐職人手沖的獨特配方豆；或是前往附近的風格選物咖啡館，享受不受打擾的安靜午後。</p>
                                    </div>
                                </div>

                                <!-- 傍晚 -->
                                <div class="relative pl-6 border-l-2 border-indigo-300 space-y-4">
                                    <div class="absolute -left-[11px] top-0 w-5 h-5 bg-indigo-500 rounded-full border-4 border-[#FBF9F5]"></div>
                                    <h2 class="text-lg font-bold text-indigo-800 mb-2"> 傍晚：文藝與夕陽的收尾</h2>

                                    <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-100 hover:shadow-md transition">
                                        <div class="flex items-center gap-3 mb-2">
                                            <span class="text-xs font-bold bg-slate-100 text-slate-600 px-2 py-1 rounded">16:30 - 18:00</span>
                                            <h3 class="font-bold text-slate-900 text-base">勤美誠品綠園道 ｜ 獨立書店與選物</h3>
                                        </div>
                                        <p class="text-sm text-slate-600 leading-relaxed"><strong class="text-indigo-700">行程亮點：</strong> 走進商場與周邊巷弄的獨立書店，挑選一本喜歡的小書，為這趟充滿漫步與咖啡香的旅程畫下溫柔的句點。</p>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </template>

                    <!-- 4. 景點列表與儀表板視圖 -->
                    <template v-if="currentView === 'dashboard'">
                        <!-- 🎯 大圖輪播 Banner -->
                        <div v-if="banners.length > 0" class="relative w-full h-96 bg-slate-900 rounded-2xl overflow-hidden shadow-md group">
                            <div v-for="(banner, index) in banners" :key="banner.id"
                                v-show="currentBanner === index"
                                class="absolute inset-0 transition-all duration-700">
                                <img :src="banner.image_url || 'https://placehold.co/1200x400'" class="w-full h-full object-cover opacity-80">
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-transparent to-transparent flex flex-col justify-end p-6 text-white space-y-1">
                                    <span class="px-2.5 py-0.5 rounded font-medium text-xs bg-blue-600 text-white w-fit">精選推薦</span>
                                    <h3 class="text-2xl font-bold">@{{ banner.name }}</h3>
                                    <p class="text-xs text-slate-200">📍 @{{ banner.city }}</p>
                                </div>
                            </div>
                            <button @click="prevBanner" class="absolute left-3 top-1/2 -translate-y-1/2 bg-black/40 hover:bg-black/70 text-white p-2 rounded-full transition opacity-0 group-hover:opacity-100">&lt;</button>
                            <button @click="nextBanner" class="absolute right-3 top-1/2 -translate-y-1/2 bg-black/40 hover:bg-black/70 text-white p-2 rounded-full transition opacity-0 group-hover:opacity-100">&gt;</button>
                            <div class="absolute bottom-3 right-4 flex gap-1.5 z-10">
                                <button v-for="(banner, index) in banners" :key="index"
                                    @click="currentBanner = index"
                                    class="w-2.5 h-2.5 rounded-full transition"
                                    :class="currentBanner === index ? 'bg-white w-6' : 'bg-white/50'"></button>
                            </div>
                        </div>
                        <!-- 🌟 熱門推薦景點與美食（分頁輪播區塊） -->
                        <div class="bg-white rounded-2xl shadow-sm p-6 border border-slate-100 space-y-4">
                            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                                <div>
                                    <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                                        <span>✨</span> 台中必訪熱門景點與美食推薦
                                    </h2>
                                    <p class="text-xs text-slate-500 mt-0.5">探索在地人氣最高、最受歡迎的精選景點</p>
                                </div>

                                <!-- 右側按鈕與分頁控制群組 -->
                                <div class="flex flex-wrap items-center gap-3">
                                    <!-- 三個分類按鈕 (加入 click 事件) -->
                                    <div class="inline-flex rounded-md shadow-sm" role="group">
                                        <button type="button" @click="currentView = 'weeklySpecial'" class="px-3 py-1.5 text-xs font-medium text-emerald-700 bg-white border border-emerald-600 rounded-l-lg hover:bg-emerald-50 transition">本週特輯</button>
                                        <button type="button" @click="currentView = 'coffeeSpecial'" class="px-3 py-1.5 text-xs font-medium text-emerald-700 bg-white border-t border-b border-emerald-600 hover:bg-emerald-50 transition">巷弄咖啡</button>
                                        <button type="button" @click="currentView = 'itinerary'" class="px-3 py-1.5 text-xs font-medium text-emerald-700 bg-white border border-emerald-600 rounded-r-lg hover:bg-emerald-50 transition">一日漫遊足跡</button>
                                    </div>

                                    <!-- 左右切換分頁按鈕 -->
                                    <div class="flex gap-2">
                                        <button @click="prevHotGroup" :disabled="hotPage === 0"
                                            class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-md disabled:opacity-30 disabled:cursor-not-allowed transition border">
                                            &larr; 上一頁
                                        </button>
                                        <button @click="nextHotGroup" :disabled="(hotPage + 1) * 4 >= hotAttractions.length"
                                            class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-md disabled:opacity-30 disabled:cursor-not-allowed transition border">
                                            下一頁 &rarr;
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 4 個一組的熱門景點卡片網格 -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div v-for="item in pagedHotAttractions" :key="item.rank"
                                class="border border-slate-100 rounded-xl p-4 flex flex-col justify-between hover:shadow-md transition bg-gradient-to-br from-white to-slate-50/50">
                                <img :src="item.image_url" :alt="item.name" class="w-full h-32 object-cover rounded-t-lg mb-3">
                                <div>
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-xs font-bold px-2.5 py-0.5 bg-blue-50 text-blue-600 rounded-full">
                                            #@{{ item.rank }} @{{ item.tag }}
                                        </span>
                                    </div>
                                    <h3 class="font-bold text-slate-900 text-base mb-1">@{{ item.name }}</h3>
                                    <p class="text-xs text-slate-600 mb-3 line-clamp-2">@{{ item.feature }}</p>
                                </div>
                                <div class="text-xs bg-orange-50/80 text-orange-800 p-2.5 rounded-lg border border-orange-100">
                                    🍽️ <strong class="font-semibold">特色/美食：</strong>@{{ item.food }}
                                </div>
                            </div>
                        </div>

                        <!-- 篩選列 -->
                        <div class="bg-white rounded-xl shadow-sm p-4 border border-slate-100 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3 items-end">
                            <div class="relative">
                                <label class="block text-xs font-bold text-slate-700 mb-1 flex items-center gap-1">
                                    <!-- 放大鏡 Icon 作為點綴 -->
                                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                    關鍵字搜尋
                                </label>
                                <div class="relative">
                                    <input
                                        v-model="filters.keyword"
                                        @input="handleKeywordSearch"
                                        @keyup.enter="fetchAttractions(1)"
                                        type="text"
                                        placeholder="請輸入景點名稱、地址..."
                                        class="w-full py-2 pl-3 pr-8 text-xs border border-slate-200 rounded-md bg-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all placeholder:text-slate-400 shadow-sm">
                                    <!-- 快速清除按鈕 (只有在有輸入文字時才會顯示) -->
                                    <button
                                        v-show="filters.keyword"
                                        @click="clearKeyword"
                                        type="button"
                                        class="absolute inset-y-0 right-0 flex items-center pr-2 text-slate-400 hover:text-rose-500 transition-colors"
                                        title="清除輸入">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1"> 縣市</label>
                                <select v-model="filters.city" @change="fetchAttractions(1)" class="w-full p-2 text-xs border rounded-md bg-white">
                                    <option value="">臺中市全區</option>
                                    <option value="中區">中區</option>
                                    <option value="東區">東區</option>
                                    <option value="南區">南區</option>
                                    <option value="西區">西區</option>
                                    <option value="北區">北區</option>
                                    <option value="西屯區">西屯區</option>
                                    <option value="南屯區">南屯區</option>
                                    <option value="北屯區">北屯區</option>
                                    <option value="豐原區">豐原區</option>
                                    <option value="大里區">大里區</option>
                                    <option value="太平區">太平區</option>
                                    <option value="清水區">清水區</option>
                                    <option value="沙鹿區">沙鹿區</option>
                                    <option value="大甲區">大甲區</option>
                                    <option value="東勢區">東勢區</option>
                                    <option value="梧棲區">梧棲區</option>
                                    <option value="烏日區">烏日區</option>
                                    <option value="神岡區">神岡區</option>
                                    <option value="大肚區">大肚區</option>
                                    <option value="大雅區">大雅區</option>
                                    <option value="后里區">后里區</option>
                                    <option value="霧峰區">霧峰區</option>
                                    <option value="新社區">新社區</option>
                                    <option value="大安區">大安區</option>
                                    <option value="外埔區">外埔區</option>
                                    <option value="和平區">和平區</option>
                                    <option value="石岡區">石岡區</option>
                                    <option value="龍井區">龍井區</option>
                                    <option value="潭子區">潭子區</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1"> 主題分類</label>
                                <select v-model="filters.category" @change="fetchAttractions(1)" class="w-full p-2 text-xs border rounded-md bg-white">
                                    <option value="">全部分類</option>
                                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">@{{ cat.name }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1"> 排序方式</label>
                                <select v-model="filters.sort" @change="fetchAttractions(1)" class="w-full p-2 text-xs border rounded-md bg-white">
                                    <option value="latest">最新建立</option>
                                    <option value="oldest">最早建立</option>
                                </select>
                            </div>
                            <div>
                                <button @click="resetFilters" class="w-full py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-md transition border">重設篩選</button>
                            </div>
                        </div>

                        <!-- 景點列表區塊 -->
                        <div class="bg-white rounded-xl shadow-sm p-6 border border-slate-100 space-y-4">
                            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                                <div class="flex items-center gap-4">
                                    <h2 class="text-base font-bold text-slate-900"> 景點列表</h2>
                                    <div class="flex items-center gap-1.5 text-xs text-slate-600">
                                        <span>每頁顯示：</span>
                                        <select v-model="perPage" @change="fetchAttractions(1)" class="border rounded-md px-2 py-1 bg-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                                            <option :value="4">4 筆</option>
                                            <option :value="8">8 筆</option>
                                            <option :value="12">12 筆</option>
                                            <option :value="20">20 筆</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2">
                                    <button @click="openCreateCategoryModal" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-3 py-1.5 rounded-md transition shadow">
                                        + 新增分類
                                    </button>
                                    <button @click="openCreateModal" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold px-3 py-1.5 rounded-md transition shadow">
                                        + 新增景點
                                    </button>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                <div v-for="item in attractions" :key="item.id" class="bg-white border rounded-xl overflow-hidden shadow-sm hover:shadow-md transition flex flex-col justify-between">
                                    <div>
                                        <div class="relative h-48 bg-slate-100">
                                            <img :src="item.image_url || 'https://placehold.co/600x400'" class="w-full h-full object-cover">
                                            <button @click="toggleFavorite(item)" class="absolute top-2 right-2 p-1.5 bg-white/80 hover:bg-white rounded-full shadow transition text-sm">
                                                @{{ item.is_favorited ? '❤️' : '🤍' }}
                                            </button>
                                        </div>
                                        <div class="p-4 space-y-2">
                                            <h3 class="font-bold text-slate-900 text-base truncate">@{{ item.name }}</h3>
                                            <div class="space-y-1 text-xs text-slate-500">
                                                <div class="flex items-center">
                                                    <span class="px-2.5 py-0.5 rounded font-medium bg-blue-50 text-blue-700">@{{ item.category_name }}</span>
                                                </div>
                                                <div class="text-slate-600 text-xs line-clamp-2 bg-slate-50 p-2 rounded mt-2">
                                                    @{{ item.description || '點擊編輯以新增或檢視景點詳細內容...' }}
                                                </div>
                                                <div class="flex items-center truncate">
                                                    <span class="w-4 text-center mr-1">📍</span>
                                                    <span class="truncate">@{{ item.city }}</span>
                                                </div>
                                                <div class="flex items-center">
                                                    <span class="w-4 text-center mr-1">📅</span>
                                                    <span>@{{ item.created_at.substring ? item.created_at.substring(0, 10) : item.created_at }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="px-4 pb-4 pt-2 border-t flex justify-end gap-3 text-xs">
                                        <button @click="openEditModal(item)" class="text-blue-600 hover:underline">✏️ 編輯</button>
                                        <button @click="deleteAttraction(item.id)" class="text-rose-600 hover:underline">🗑️ 刪除</button>
                                    </div>
                                </div>
                            </div>

                            <div v-if="pagination.total > 0" class="flex flex-col sm:flex-row justify-between items-center pt-4 border-t text-xs text-slate-600 gap-3">
                                <div>
                                    顯示第 <span class="font-bold">@{{ pagination.from || 0 }}</span> 到 <span class="font-bold">@{{ pagination.to || 0 }}</span> 筆，共 <span class="font-bold">@{{ pagination.total }}</span> 筆資料
                                </div>
                                <div class="flex items-center gap-1">
                                    <button @click="changePage(pagination.current_page - 1)"
                                        :disabled="pagination.current_page === 1"
                                        class="px-3 py-1.5 border rounded-md bg-white hover:bg-slate-50 disabled:opacity-30 disabled:cursor-not-allowed transition">
                                        上一頁
                                    </button>
                                    <span class="px-3 py-1.5 font-bold text-slate-800">
                                        @{{ pagination.current_page }} / @{{ pagination.last_page }} 頁
                                    </span>
                                    <button @click="changePage(pagination.current_page + 1)"
                                        :disabled="pagination.current_page === pagination.last_page"
                                        class="px-3 py-1.5 border rounded-md bg-white hover:bg-slate-50 disabled:opacity-30 disabled:cursor-not-allowed transition">
                                        下一頁
                                    </button>
                                </div>
                            </div>
                            <!-- 📊 數據統計儀表板 -->
                            <div class="mt-12 bg-white p-6 rounded-xl shadow-md border border-gray-100">
                                <div class="flex justify-between items-center mb-6">
                                    <h3 class="text-xl font-bold text-gray-800">景點統計儀表板</h3>
                                </div>

                                <!-- 上方：總數統計卡片 -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                                    <div class="bg-gray-50 p-5 rounded-lg border border-gray-200 flex justify-between items-center">
                                        <div>
                                            <p class="text-sm text-gray-500 font-medium">景點總數</p>
                                            <p class="text-3xl font-bold text-gray-800 mt-1" x-text="totalAttractions">0</p>
                                        </div>
                                        <div class="p-3 bg-blue-100 text-blue-600 rounded-full">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            </svg>
                                        </div>
                                    </div>

                                    <div class="bg-gray-50 p-5 rounded-lg border border-gray-200 flex justify-between items-center">
                                        <div>
                                            <p class="text-sm text-gray-500 font-medium">分類總數</p>
                                            <p class="text-3xl font-bold text-gray-800 mt-1" x-text="totalAttractions">0</p>
                                        </div>
                                        <div class="p-3 bg-amber-100 text-amber-600 rounded-full">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                </div>

                                <!-- 下方：圖表展示區 -->
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                                    <!-- 各城市景點數量 (長條圖) -->
                                    <div class="bg-white p-4 rounded-lg border border-gray-200">
                                        <h4 class="text-md font-semibold text-gray-700 mb-4">各城市景點數量</h4>
                                        <div class="relative h-64">
                                            <canvas id="cityChart"></canvas>
                                        </div>
                                    </div>

                                    <!-- 各分類景點比例 (圓餅圖) -->
                                    <div class="bg-white p-4 rounded-lg border border-gray-200">
                                        <h4 class="text-md font-semibold text-gray-700 mb-4">各分類景點比例</h4>
                                        <div class="relative h-64 flex justify-center">
                                            <canvas id="categoryChart"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- 5. 我的收藏視圖 -->
                    <template v-if="currentView === 'favorites'">
                        <div class="bg-white rounded-xl shadow-sm p-6 border border-slate-100 space-y-6">
                            <div class="flex justify-between items-center border-b pb-4">
                                <h2 class="text-lg font-bold text-slate-900"> 我的收藏景點</h2>
                                <button @click="switchView('dashboard')" class="bg-slate-100 text-slate-700 text-xs font-bold px-3 py-1.5 rounded-md border">&lt; 返回</button>
                            </div>

                            <div v-if="favorites.length === 0" class="text-center py-10 text-slate-400 text-sm">
                                目前沒有收藏任何景點
                            </div>

                            <div v-for="(items, categoryName) in groupedFavorites" :key="categoryName" class="space-y-3">
                                <div class="flex items-center gap-2 border-l-4 border-blue-600 pl-3">
                                    <h3 class="font-bold text-slate-800 text-base">@{{ categoryName }}</h3>
                                    <span class="text-xs bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full font-bold">@{{ items.length }} 個景點</span>
                                </div>

                                <div class="space-y-3">
                                    <div v-for="item in items" :key="item.id" class="bg-white border rounded-xl p-4 shadow-sm hover:shadow-md transition flex items-center justify-between gap-4">
                                        <div class="flex items-center gap-4 flex-1">
                                            <div class="relative w-24 h-24 bg-slate-100 rounded-lg overflow-hidden flex-shrink-0">
                                                <img :src="item.image_url || 'https://placehold.co/600x400'" class="w-full h-full object-cover">
                                            </div>
                                            <div class="space-y-1 flex-1">
                                                <h4 class="font-bold text-slate-900 text-base">@{{ item.name }}</h4>
                                                <div class="flex items-center gap-2 text-xs flex-wrap">
                                                    <span class="px-2.5 py-0.5 rounded font-medium bg-blue-50 text-blue-700">@{{ item.category_name }}</span>
                                                    <span class="text-slate-500">📍 @{{ item.city }}</span>
                                                </div>
                                                <p class="text-slate-600 text-xs mt-1 line-clamp-2 bg-slate-50 p-1.5 rounded">
                                                    @{{ item.description || '尚無景點內容介紹...' }}
                                                </p>
                                            </div>
                                        </div>
                                        <div>
                                            <button @click="removeFavorite(item)" class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-lg text-xs font-bold flex items-center gap-1 transition shadow-sm">
                                                ❤️ 取消收藏
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                </div>
            </template>
        </main>

        <!-- 引入獨立的 Modals 元件 -->
        @include('components.category-modal')
        @include('components.profile-modal')
        @include('components.attraction-modals')

        <footer class="bg-slate-900 text-slate-400 text-xs py-4 text-center mt-auto relative z-20">
            Copyright © 2026 旅遊景點管理與數據統計 Dashboard. All rights reserved.
        </footer>
    </div>

    <!-- 載入獨立出去的 Vue 邏輯檔案 -->
    <script src="{{ asset('js/dashboard.js') }}"></script>
</body>

</html>