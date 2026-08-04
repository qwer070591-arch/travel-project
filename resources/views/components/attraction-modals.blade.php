<!-- 新增與修改景點的 Modal 集中區 -->

<!-- 1. 新增景點 Modal -->
<div v-if="showCreateModal" class="fixed inset-0 bg-slate-900/50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-xl max-w-lg w-full p-6 space-y-4">
        <div class="flex justify-between items-center border-b pb-3">
            <h3 class="font-bold text-slate-900 text-base">🌄 新增景點</h3>
            <button @click="showCreateModal = false" class="text-slate-400 hover:text-slate-600 font-bold">✕</button>
        </div>

        <form @submit.prevent="storeAttraction" class="space-y-3 text-xs">
            <div>
                <label class="block font-bold text-slate-700 mb-1">景點名稱</label>
                <input v-model="attractionForm.name" required type="text" placeholder="請輸入景點名稱" class="w-full p-2.5 border rounded-lg">
            </div>
            <div>
                <label class="block font-bold text-slate-700 mb-1">分類</label>
                <select v-model="attractionForm.category_id" required class="w-full p-2.5 border rounded-lg">
                    <option value="" disabled>請選擇分類</option>
                    <option v-for="cat in categories" :value="cat.id" :key="cat.id">@{{ cat.name }}</option>
                </select>
            </div>
            <div>
                <label class="block font-bold text-slate-700 mb-1">縣市 / 行政區</label>
                <input v-model="attractionForm.city" required type="text" placeholder="例如：臺中市西屯區" class="w-full p-2.5 border rounded-lg">
            </div>
            <div>
                <label class="block font-bold text-slate-700 mb-1">圖片網址 (Image URL)</label>
                <input v-model="attractionForm.image_url" type="url" placeholder="https://..." class="w-full p-2.5 border rounded-lg">
            </div>
            <div>
                <label class="block font-bold text-slate-700 mb-1">景點內容描述</label>
                <textarea v-model="attractionForm.description" rows="3" placeholder="請輸入景點詳細介紹..." class="w-full p-2.5 border rounded-lg"></textarea>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" @click="showCreateModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-lg">取消</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg shadow">確認新增</button>
            </div>
        </form>
    </div>
</div>

<!-- 2. 編輯景點 Modal -->
<div v-if="showEditModal" class="fixed inset-0 bg-slate-900/50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-xl max-w-lg w-full p-6 space-y-4">
        <div class="flex justify-between items-center border-b pb-3">
            <h3 class="font-bold text-slate-900 text-base">✏️ 編輯景點</h3>
            <button @click="showEditModal = false" class="text-slate-400 hover:text-slate-600 font-bold">✕</button>
        </div>

        <form @submit.prevent="updateAttraction" class="space-y-3 text-xs">
            <div>
                <label class="block font-bold text-slate-700 mb-1">景點名稱</label>
                <input v-model="editForm.name" required type="text" class="w-full p-2.5 border rounded-lg">
            </div>
            <div>
                <label class="block font-bold text-slate-700 mb-1">分類</label>
                <select v-model="editForm.category_id" required class="w-full p-2.5 border rounded-lg">
                    <option value="" disabled>請選擇分類</option>
                    <option v-for="cat in categories" :value="cat.id" :key="cat.id">@{{ cat.name }}</option>
                </select>
            </div>
            <div>
                <label class="block font-bold text-slate-700 mb-1">縣市 / 行政區</label>
                <input v-model="editForm.city" required type="text" class="w-full p-2.5 border rounded-lg">
            </div>
            <div>
                <label class="block font-bold text-slate-700 mb-1">圖片網址 (Image URL)</label>
                <input v-model="editForm.image_url" type="url" class="w-full p-2.5 border rounded-lg">
            </div>
            <div>
                <label class="block font-bold text-slate-700 mb-1">景點內容描述</label>
                <textarea v-model="editForm.description" rows="3" placeholder="請輸入景點詳細介紹..." class="w-full p-2.5 border rounded-lg"></textarea>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" @click="showEditModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-lg">取消</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg shadow">儲存修改</button>
            </div>
        </form>
    </div>
</div>