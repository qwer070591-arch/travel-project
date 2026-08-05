const { createApp, ref, computed, onMounted, onUnmounted } = Vue;

/**
 * 🌟 帶有 JWT Bearer Token 的 Fetch 封裝函式
 */
const authFetch = async (url, options = {}) => {
    const token = localStorage.getItem('token');
    const headers = {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        ...(token ? { 'Authorization': `Bearer ${token}` } : {}),
        ...(options.headers || {})
    };

    try {
        const response = await fetch(url, { ...options, headers });

        // 401 代表 Token 已過期或無效，自動清理登入狀態
        if (response.status === 401 && token) {
            localStorage.removeItem('token');
            window.location.reload();
        }

        return response;
    } catch (error) {
        console.error(`[Fetch Error] ${url}:`, error);
        throw error;
    }
};

// 📊 統計儀表板狀態
const stats = ref({
    total_attractions: 0,
    total_categories: 0,
    city_counts: {},
    category_counts: {}
});

let cityChartInstance = null;
let categoryChartInstance = null;

// 取得統計資料的 API 函式
const fetchStatistics = async () => {
    try {
        const res = await authFetch('/api/attractions/statistics');
        if (res.ok) {
            const data = await res.json();
            stats.value = data;

            // 資料抓回來後，渲染圖表
            renderCharts();
        }
    } catch (err) {
        console.error('取得統計資料失敗', err);
    }
};

// 渲染 Chart.js 圖表
const renderCharts = () => {
    // 1. 城市景點數量長條圖
    const cityCtx = document.getElementById('cityChart');
    if (cityCtx) {
        if (cityChartInstance) cityChartInstance.destroy(); // 避免重複渲染

        const cityLabels = Object.keys(stats.value.city_counts || {});
        const cityData = Object.values(stats.value.city_counts || {});

        cityChartInstance = new Chart(cityCtx, {
            type: 'bar',
            data: {
                labels: cityLabels,
                datasets: [{
                    label: '景點數量',
                    data: cityData,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }

    // 2. 分類景點比例圓餅圖
    const categoryCtx = document.getElementById('categoryChart');
    if (categoryCtx) {
        if (categoryChartInstance) categoryChartInstance.destroy();

        const catLabels = Object.keys(stats.value.category_counts || {});
        const catData = Object.values(stats.value.category_counts || {});

        categoryChartInstance = new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: catLabels,
                datasets: [{
                    data: catData,
                    backgroundColor: [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }
};

createApp({
    setup() {
        // =========================================================================
        // 1. 核心與 UI 狀態管理 (State Management)
        // =========================================================================
        const isLoggedIn = ref(false);
        const currentUser = ref({ id: null, name: '', email: '' });
        const currentView = ref('dashboard');

        // Modal 顯隱狀態
        const showCategoryModal = ref(false);
        const showCreateCategoryModal = ref(false);
        const showEditCategoryModal = ref(false);
        const showProfileModal = ref(false);
        const showCreateModal = ref(false);
        const showEditModal = ref(false);
        const showLoginModal = ref(false);

        // 身份驗證與會員資料表單
        const isRegisterMode = ref(false);
        const authError = ref('');
        const profileError = ref('');
        const profileSuccess = ref('');
        const loginForm = ref({ name: '', email: '', password: '' });
        const profileForm = ref({ name: '', email: '', password: '' });

        // 景點與分類表單
        const attractionForm = ref({ name: '', category_id: '', city: '', image_url: '', description: '' });
        const editForm = ref({ id: null, name: '', category_id: '', city: '', image_url: '', description: '' });
        const editCategoryForm = ref({ id: null, name: '' });
        const newCategoryName = ref('');

        // 資料清單與篩選
        const attractions = ref([]);
        const favorites = ref([]);
        const categories = ref([]);
        const filters = ref({ keyword: '', city: '', category: '', sort: 'latest' });
        const perPage = ref(4);

        // 分頁狀態
        const pagination = ref({
            current_page: 1,
            last_page: 1,
            total: 0,
            from: 0,
            to: 0
        });

        // =========================================================================
        // 2. 靜態展示資料 (Weekly, Coffee, Banners, Top 10)
        // =========================================================================
        const weeklyData = ref([
            {
                title: "臺中國家歌劇院",
                category: "建築美學",
                location: "西屯區",
                address: "惠來路二段 101 號",
                image: "images/img06.jpg",
                description: "由日本建築大師伊東豊雄所設計的遠見之作，以「美聲涵洞」與無直角曲牆概念打造的會呼吸建築。漫步其中，光影隨著流線型空間自然流轉；登上頂樓的空中花園，則能在城市喧囂中尋得一片寧靜的綠意與浪漫。"
            },
            {
                title: "審計新村",
                category: "舊城新生 ｜ 青創聚落",
                location: "西區",
                address: "民生路 356 巷",
                image: "images/img07.jpg",
                description: "老舊省府宿舍的重生，斑駁紅磚與木造窗框交織出的青創聚落。隨性轉進巷弄，總能與獨立選物、手作咖啡與職人的溫熱心意不期而遇，感受老時光與當代創意的完美交融。"
            },
            {
                title: "勤美誠品綠園道",
                category: "城市綠洲 ｜ 生活美學",
                location: "西區",
                address: "公益路 68 號",
                image: "images/img08.jpg",
                description: "當大片綠意與當代城市生活完美融合成日常風景。漫步在草悟道街區，感受獨立書店的墨香、微風與在地慢活的節奏，是午後散策與尋找靈感的最佳去處。"
            }

        ]);

        const coffeeData = ref([
            {
                title: "奉咖啡 (A-Feng Cafe)",
                category: "老宅尋香 ｜ 自家烘焙",
                location: "西區",
                address: "中興街巷弄",
                image: "images/img09.jpg",
                description: "藏身在勤美綠園道附近的靜謐老宅裡，沒有過多喧嘩，只有陣陣樸實的咖啡香。推開木門，彷彿時間慢了下來，是尋找靈感與品味職人手沖咖啡的絕佳去處。"
            },
            {
                title: "小路咖啡 (Trois Cafe)",
                category: "隱密閣樓 ｜ 城市避難所",
                location: "西區",
                address: "美村路巷弄",
                image: "images/img10.jpg",
                description: "隱身於老社區的二樓小閣樓，帶有濃厚的昭和復古與日系雜貨選物感。點一杯手沖，配上一本書或筆電，就能在這裡安靜度過一個無人打擾的溫柔午後。"
            },
            {
                title: "coffee Stopover",
                category: "自由咖啡實驗室 ｜ 日常停泊",
                location: "西區",
                address: "華美街巷弄",
                image: "images/img11.jpg",
                description: "座落在向上市場附近的轉角巷弄，純白簡約的建築外觀與隨性自在的氛圍，深受在地人喜愛。這裡提供豐富的烘焙度與豆款選擇，像是一座隨時為城市旅人敞開的咖啡驛站。"
            }

        ]);

        const topTenAttractions = ref([
            { id: 1, name: '逢甲夜市（美食與逛街首選）', feature: '全台規模最大的夜市之一，集結了各式創意小吃與流行服飾。', highlight: '推薦必吃：明倫蛋餅、官芝霖大腸包小腸、逢甲路邊的起司地瓜球、日船章魚燒。' },
            { id: 2, name: '勤美誠品綠園道與草悟道（文青與休閒勝地）', feature: '結合綠意植栽、商場與文創市集的藝文生活圈，假日經常有街頭藝人表演與手作市集。', highlight: '周邊亮點：國立自然科學博物館、勤美術館（PARK2 草悟廣場）。' },
            { id: 3, name: '宮原眼科（打卡與伴手禮）', feature: '由日治時期眼科醫院改建而成的超人氣景點，內部裝潢充滿濃厚的《哈利波特》魔法學院風格。', highlight: '推薦必買：招牌宮原眼科冰淇淋（配料豐富）、土鳳梨酥、各式精美包裝的喜餅與巧克力。' },
            { id: 4, name: '審計新村（文創聚落）', feature: '由老舊省府宿舍改造而成的青年創業基地，紅磚牆與老樹交織出獨特的復古文青氛圍。', highlight: '特色小店：各式手作飾品、獨立設計品牌、甜點冰品（如冰山咖央、艸水木堂）。' },
            { id: 5, name: '高美濕地（自然生態與夕陽美景）', feature: '擁有豐富的濕地生態與絕美的高美燈塔，是全台欣賞夕陽落日最浪漫的景點之一。', highlight: '貼心提醒：出發前建議查詢當日的潮汐時間，退潮時更能拍出絕美的天空之鏡倒影。' },
            { id: 6, name: '台中國家歌劇院（世界級建築地標）', feature: '由日本建築師伊東豊雄設計的「美聲涵洞」曲牆建築，顛覆了傳統直角建築的概念。', highlight: '亮點：內部空間流線優雅，頂樓有空中花園，常設有免費參觀的展覽與文創商店。' },
            { id: 7, name: '土木公社炭烤土司 ：黃記崇德店 / 高沐 Café', feature: '台中擁有全台聞名的豐富早午餐文化，從巷弄小吃到大氣裝潢的餐館應有盡有。', highlight: '推薦美食：肉蛋吐司（創始老店）、各種融合義式與在地風味的早午餐拼盤。' },
            { id: 8, name: '第二市場（傳統市場美食寶庫）', feature: '百年歷史的傳統市場，保留了許多日治時期流傳至今的美味老攤販。', highlight: '推薦必吃：山河魯肉飯、王記菜頭粿糯米腸、老賴茶棧（紅茶冰）、天天饅頭。' },
            { id: 9, name: '麗寶樂園渡假區（休閒娛樂與購物）', feature: '結合了大型主題樂園、麗寶OUTLET MALL（全台最大室外百貨）以及賽車場的綜合度假區。', highlight: '亮點：搭乘「天空之夢」摩天輪可將台中市景與大甲溪風光盡收眼底。' },
            { id: 10, name: '台中第六市場（質感文青傳統市場）', feature: '全台首座進駐百貨商場（金典綠園道）的傳統市場，乾淨明亮且空調舒適，結合傳統攤商與現代文創。', highlight: '推薦美食：各種手作熟食、新鮮蔬果及質感小吃，逛起來舒適度極高。' }
        ]);

        const hotPage = ref(0);
        const hotAttractions = ref([
            { rank: 1, name: '逢甲夜市', feature: '全台規模最大的夜市之一。', food: '明倫蛋餅、官芝森大腸包小腸', tag: '美食與逛街', image_url: '/images/img07.jpg' },
            { rank: 2, name: '勤美誠品綠園道', feature: '結合綠意植栽與文創市集。', food: '國立自然科學博物館', tag: '文青與休閒', image_url: '/images/img08.jpg' },
            { rank: 3, name: '宮原眼科', feature: '日治時期眼科醫院改建。', food: '招牌冰淇淋、土鳳梨酥', tag: '打卡與伴手禮', image_url: '/images/img09.jpg' },
            { rank: 4, name: '審計新村', feature: '老舊省府宿舍改造的青創基地。', food: '手作飾品、獨立設計', tag: '文創聚落', image_url: '/images/img10.jpg' },
            { rank: 5, name: '高美濕地', feature: '豐富濕地生態與絕美夕陽。', food: '天空之鏡倒影', tag: '自然生態', image_url: '/images/img11.jpg' },
            { rank: 6, name: '台中國家歌劇院', feature: '世界級美聲涵洞曲牆建築。', food: '空中花園與展覽', tag: '世界建築', image_url: '/images/img12.jpg' },
            { rank: 7, name: '土木公社 炭烤土司', feature: '台中聞名的早午餐文化。', food: '肉蛋吐司、義式拼盤', tag: '在地美味', image_url: '/images/img13.jpg' },
            { rank: 8, name: '第二市場', feature: '百年歷史的傳統市場。', food: '山河魯肉飯、王記菜頭粿', tag: '傳統美食', image_url: '/images/img14.jpg' }
        ]);

        // =========================================================================
        // 3. 輪播圖與熱門分頁邏輯 (Banner & Hot Attractions Logic)
        // =========================================================================
        const banners = ref([
            { id: 1, name: '探索臺中美麗風光', city: '臺中市全區', image_url: '/images/img02.jpg' },
            { id: 2, name: '享受悠閒週末時光', city: '西屯區', image_url: '/images/img03.jpg' },
            { id: 3, name: '體驗在地人文景點', city: '南屯區', image_url: '/images/img04.jpg' }
        ]);
        const currentBanner = ref(0);
        let bannerTimer = null;

        const startBannerTimer = () => {
            stopBannerTimer();
            if (banners.value.length > 0) {
                bannerTimer = setInterval(() => nextBanner(), 5000);
            }
        };

        const stopBannerTimer = () => {
            if (bannerTimer) {
                clearInterval(bannerTimer);
                bannerTimer = null;
            }
        };

        const nextBanner = () => {
            if (banners.value.length === 0) return;
            currentBanner.value = (currentBanner.value + 1) % banners.value.length;
        };

        const prevBanner = () => {
            if (banners.value.length === 0) return;
            currentBanner.value = (currentBanner.value - 1 + banners.value.length) % banners.value.length;
        };

        const pagedHotAttractions = computed(() => {
            const start = hotPage.value * 4;
            return hotAttractions.value.slice(start, start + 4);
        });

        const nextHotGroup = () => {
            if ((hotPage.value + 1) * 4 < hotAttractions.value.length) {
                hotPage.value++;
            }
        };

        const prevHotGroup = () => {
            if (hotPage.value > 0) {
                hotPage.value--;
            }
        };

        // =========================================================================
        // 4. 身份驗證與個人資料 (Auth & Profile)
        // =========================================================================
        const handleAuthSubmit = async () => {
            authError.value = '';
            const endpoint = isRegisterMode.value ? '/api/register' : '/api/login';
            try {
                const res = await fetch(endpoint, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify(loginForm.value)
                });
                const data = await res.json();
                if (!res.ok) throw new Error(data.message || '操作失敗');

                localStorage.setItem('token', data.token);
                currentUser.value = data.user;
                isLoggedIn.value = true;
                showLoginModal.value = false;

                await fetchCategories();
                await fetchAttractions(1);
                await fetchStatistics();
                startBannerTimer();
            } catch (err) {
                authError.value = err.message;
            }
        };

        const handleLogout = () => {
            localStorage.removeItem('token');
            isLoggedIn.value = false;
            currentUser.value = { id: null, name: '', email: '' };
            loginForm.value = { name: '', email: '', password: '' };
            attractions.value = [];
            currentView.value = 'dashboard';
            stopBannerTimer();
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
                if (!res.ok) throw new Error(data.message || '更新失敗');

                currentUser.value = data.user;
                profileSuccess.value = '會員資訊更新成功！';
                setTimeout(() => {
                    showProfileModal.value = false;
                    profileSuccess.value = '';
                }, 1500);
            } catch (err) {
                profileError.value = err.message;
            }
        };

        // 5. 分類管理 (Category Management)
        const fetchCategories = async () => {
            try {
                const res = await authFetch('/api/categories');
                if (res.ok) categories.value = await res.json();
            } catch (err) {
                console.error('取得分類失敗', err);
            }
        };

        const storeCategory = async (shouldCloseModal = false) => {
            const name = newCategoryName.value.trim();
            if (!name) return;

            try {
                const res = await authFetch('/api/categories', {
                    method: 'POST',
                    body: JSON.stringify({ name })
                });

                if (res.ok) {
                    newCategoryName.value = '';
                    await fetchCategories();
                    await fetchAttractions(1); // 💡 補上這行：讓景點與下拉選單同步更新
                    await fetchStatistics();    // 更新數字與圓餅圖
                    if (shouldCloseModal) showCreateCategoryModal.value = false;
                } else {
                    const data = await res.json();
                    alert(data.message || '新增分類失敗');
                }
            } catch (err) {
                console.error('新增分類錯誤', err);
            }
        };

        const storeCategoryAndClose = () => storeCategory(true);

        const updateCategory = async () => {
            if (!editCategoryForm.value.name.trim()) return;
            try {
                const res = await authFetch(`/api/categories/${editCategoryForm.value.id}`, {
                    method: 'PUT',
                    body: JSON.stringify({ name: editCategoryForm.value.name })
                });

                if (res.ok) {
                    showEditCategoryModal.value = false;
                    await fetchCategories();
                    await fetchAttractions(1); // 💡 補上這行
                    await fetchStatistics();    // 更新統計與圖表
                } else {
                    const data = await res.json();
                    alert(data.message || '修改分類失敗');
                }
            } catch (err) {
                console.error('修改分類錯誤', err);
            }
        };

        const deleteCategory = async (id) => {
            if (!confirm('確定要刪除此分類嗎？')) return;
            try {
                const res = await authFetch(`/api/categories/${id}`, { method: 'DELETE' });
                if (res.ok) {
                    await fetchCategories();
                    await fetchAttractions(1); // 💡 補上這行
                    await fetchStatistics();    // 更新統計與圖表
                } else {
                    const data = await res.json();
                    alert(data.message || '刪除失敗');
                }
            } catch (err) {
                console.error('刪除分類失敗', err);
            }
        };

        // =========================================================================
        // 6. 景點 CRUD 與搜尋 (Attractions CRUD & Search)
        // =========================================================================
        const fetchAttractions = async (page = 1) => {
            const query = new URLSearchParams({
                ...filters.value,
                per_page: perPage.value,
                page: page
            });
            try {
                const res = await authFetch(`/api/attractions?${query}`);
                if (res.ok) {
                    const data = await res.json();
                    if (data.data) {
                        attractions.value = data.data;
                        pagination.value = {
                            current_page: data.current_page,
                            last_page: data.last_page,
                            total: data.total,
                            from: data.from,
                            to: data.to
                        };
                    } else {
                        attractions.value = data;
                    }
                }
            } catch (err) {
                console.error('取得景點失敗', err);
            }
        };

        const handleKeywordSearch = () => {
            fetchAttractions(1);
        };

        const clearKeyword = () => {
            filters.value.keyword = '';
            fetchAttractions(1);
        };

        const storeAttraction = async () => {
            try {
                const res = await authFetch('/api/attractions', {
                    method: 'POST',
                    body: JSON.stringify(attractionForm.value)
                });
                if (res.ok) {
                    showCreateModal.value = false;
                    attractionForm.value = { name: '', category_id: '', city: '', image_url: '', description: '' };
                    await fetchAttractions(1);
                    await fetchStatistics();
                } else {
                    const data = await res.json();
                    alert(data.message || '新增失敗');
                }
            } catch (err) {
                console.error('新增景點錯誤', err);
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
                    await fetchAttractions(pagination.value.current_page);
                    await fetchStatistics();
                } else {
                    const data = await res.json();
                    alert(data.message || '更新失敗');
                }
            } catch (err) {
                console.error('更新景點錯誤', err);
            }
        };

        const deleteAttraction = async (id) => {
            if (!confirm('確定要刪除此景點嗎？')) return;
            try {
                const res = await authFetch(`/api/attractions/${id}`, { method: 'DELETE' });
                if (res.ok) {
                    await fetchAttractions(pagination.value.current_page);
                    await fetchStatistics();
                }
            } catch (err) {
                console.error('刪除景點失敗', err);
            }
        };

        // =========================================================================
        // 7. 收藏清單管理 (Favorites Management)
        // =========================================================================
        const fetchFavorites = async () => {
            try {
                const res = await authFetch('/api/favorites');
                if (res.ok) favorites.value = await res.json();
            } catch (err) {
                console.error('取得收藏清單失敗', err);
            }
        };

        const toggleFavorite = async (item) => {
            try {
                const res = await authFetch(`/api/attractions/${item.id}/favorite`, { method: 'POST' });
                if (res.ok) item.is_favorited = !item.is_favorited;
            } catch (err) {
                console.error('收藏操作失敗', err);
            }
        };

        const removeFavorite = async (item) => {
            try {
                const res = await authFetch(`/api/attractions/${item.id}/favorite`, { method: 'POST' });
                if (res.ok) {
                    favorites.value = favorites.value.filter(fav => fav.id !== item.id);
                }
            } catch (err) {
                console.error('取消收藏失敗', err);
            }
        };

        const groupedFavorites = computed(() => {
            return favorites.value.reduce((groups, item) => {
                const category = item.category_name || '未分類';
                if (!groups[category]) groups[category] = [];
                groups[category].push(item);
                return groups;
            }, {});
        });

        // =========================================================================
        // 8. 介面操作輔助 (UI Helpers & Navigation)
        // =========================================================================
        const switchView = async (view) => {
            currentView.value = view;

            if (view === 'favorites') {
                await fetchFavorites();
            } else {
                // 確保非收藏視圖時，一定會重新抓取景點列表
                await fetchAttractions(1);
            }
            await fetchStatistics();
        };

        const changePage = (page) => {
            if (page >= 1 && page <= pagination.value.last_page) {
                fetchAttractions(page);
            }
        };

        const resetFilters = () => {
            filters.value = { keyword: '', city: '', category: '', sort: 'latest' };
            fetchAttractions(1);
        };

        const openCategoryModal = () => { fetchCategories(); showCategoryModal.value = true; };
        const openCreateCategoryModal = () => { newCategoryName.value = ''; showCreateCategoryModal.value = true; };
        const openEditCategoryModal = (cat) => {
            editCategoryForm.value = { id: cat.id, name: cat.name };
            showEditCategoryModal.value = true;
        };

        const openCreateModal = () => {
            fetchCategories();
            attractionForm.value = { name: '', category_id: '', city: '', image_url: '', description: '' };
            showCreateModal.value = true;
        };

        const openEditModal = (item) => {
            fetchCategories();
            editForm.value = {
                id: item.id,
                name: item.name,
                category_id: item.category_id,
                city: item.city,
                image_url: item.image_url,
                description: item.description || ''
            };
            showEditModal.value = true;
        };

        const openProfileModal = () => {
            profileForm.value = { name: currentUser.value.name, email: currentUser.value.email, password: '' };
            showProfileModal.value = true;
        };

        const openLoginModal = () => { authError.value = ''; showLoginModal.value = true; };
        const toggleAuthMode = () => { isRegisterMode.value = !isRegisterMode.value; authError.value = ''; };

        const handleCategoryInput = (e) => {
            newCategoryName.value = e.target.value.replace(/[^\w\u4e00-\u9fa5]/g, '');
        };

        const handleEditCategoryInput = (e) => {
            editCategoryForm.value.name = e.target.value.replace(/[^\w\u4e00-\u9fa5]/g, '');
        };

        // =========================================================================
        // 9. 初始化與生命週期 Hook (Initialization)
        // =========================================================================
        const init = async () => {
            const token = localStorage.getItem('token');
            if (token) {
                try {
                    const res = await authFetch('/api/user');
                    if (res.ok) {
                        currentUser.value = await res.json();
                        isLoggedIn.value = true;
                        await fetchCategories();
                        await fetchAttractions(1);
                        await fetchStatistics();
                        startBannerTimer();
                    } else {
                        localStorage.removeItem('token');
                    }
                } catch (e) {
                    console.error('初始化驗證失敗', e);
                }
            }
        };

        onMounted(() => {
            init();
        });

        onUnmounted(() => {
            stopBannerTimer();
        });

        return {
            isLoggedIn,
            currentUser,
            currentView,
            weeklyData,
            coffeeData,
            showCategoryModal,
            showCreateCategoryModal,
            showEditCategoryModal,
            showProfileModal,
            showCreateModal,
            showEditModal,
            showLoginModal,
            isRegisterMode,
            authError,
            profileError,
            profileSuccess,
            loginForm,
            profileForm,
            attractionForm,
            editForm,
            attractions,
            favorites,
            categories,
            newCategoryName,
            filters,
            perPage,
            pagination,
            stats,
            fetchStatistics,
            switchView,
            openCategoryModal,
            openCreateCategoryModal,
            openEditCategoryModal,
            openProfileModal,
            openLoginModal,
            toggleAuthMode,
            handleAuthSubmit,
            handleLogout,
            updateProfile,
            fetchAttractions,
            handleKeywordSearch,
            clearKeyword,
            changePage,
            toggleFavorite,
            fetchFavorites,
            removeFavorite,
            groupedFavorites,
            resetFilters,
            openCreateModal,
            openEditModal,
            storeCategory,
            storeCategoryAndClose,
            deleteCategory,
            deleteAttraction,
            storeAttraction,
            updateAttraction,
            editCategoryForm,
            handleCategoryInput,
            handleEditCategoryInput,
            updateCategory,
            banners,
            currentBanner,
            nextBanner,
            prevBanner,
            topTenAttractions,
            hotPage,
            hotAttractions,
            pagedHotAttractions,
            nextHotGroup,
            prevHotGroup
        };
    }
}).mount('#app');