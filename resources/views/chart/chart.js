// 請在你的前端 Vue Composition API 或是 script 標籤中加入這段邏輯
const fetchStatistics = async () => {
    try {
        const response = await axios.get('/api/attractions/statistics');
        const data = response.data;

        // 1. 更新上方數字
        // (如果是 Vue ref 變數可以寫：totalAttractions.value = data.total_attractions)

        // 2. 渲染各城市長條圖
        const cityCtx = document.getElementById('cityChart').getContext('2d');
        new Chart(cityCtx, {
            type: 'bar',
            data: {
                labels: Object.keys(data.city_counts),
                datasets: [{
                    label: '景點數量',
                    data: Object.values(data.city_counts),
                    backgroundColor: '#3B82F6',
                    borderRadius: 4
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
        });

        // 3. 渲染各分類圓餅圖
        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
        new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: Object.keys(data.category_counts),
                datasets: [{
                    data: Object.values(data.category_counts),
                    backgroundColor: ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899']
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });

    } catch (error) {
        console.error('無法取得統計資料', error);
    }
};

// 畫面載入完成後執行
// onMounted(() => { fetchStatistics(); });