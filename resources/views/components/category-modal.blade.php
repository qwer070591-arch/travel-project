<!-- 1. 原有的「分類管理中心」Modal (給頂部導覽列的「分類管理」使用，包含完整列表、修改與刪除) -->
<div v-if="showCategoryModal" class="fixed inset-0 bg-slate-900/50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6 space-y-4">
        <div class="flex justify-between items-center">
            <h3 class="text-lg font-bold text-slate-900">🏷️ 分類管理中心</h3>
            <button @click="showCategoryModal = false" class="text-slate-400 hover:text-slate-600 font-bold">✕</button>
        </div>
        <div class="space-y-3 text-xs">
            <div>
                <label class="block font-bold text-slate-700 mb-1">新增分類 (中文、英文、數字，無空白及符號)</label>
                <div class="flex gap-2">
                    <input v-model="newCategoryName" @input="handleCategoryInput" @keyup.enter="storeCategory" type="text" placeholder="請輸入分類名稱..." class="w-full p-2 border rounded-md">
                    <button type="button" @click="storeCategory" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-4 py-2 rounded transition whitespace-nowrap">新增</button>
                </div>
            </div>
            <div class="space-y-1.5 max-h-60 overflow-y-auto pt-2 border-t">
                <div v-for="(cat, index) in categories" :key="cat.id" class="text-xs text-slate-700 border rounded p-2.5 bg-slate-50 flex justify-between items-center">
                    <span>@{{ index + 1 }}. @{{ cat.name }}</span>
                    <div class="space-x-2">
                        <button type="button" @click="editCategoryForm = {...cat}; showEditCategoryModal = true;" class="text-blue-600 hover:underline">修改</button>
                        <button type="button" @click="deleteCategory(cat.id)" class="text-rose-600 hover:underline">刪除</button>
                    </div>
                </div>
            </div>
            <!-- 修改分類 Modal -->
            <div v-if="showEditCategoryModal" class="fixed inset-0 bg-slate-900/50 flex items-center justify-center z-50 p-4">
                <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6 space-y-4">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-bold text-slate-900">✏️ 修改分類名稱</h3>
                        <button @click="showEditCategoryModal = false" class="text-slate-400 hover:text-slate-600 font-bold">✕</button>
                    </div>
                    <div class="space-y-3 text-xs">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">分類名稱</label>
                            <input v-model="editCategoryForm.name" @input="handleEditCategoryInput" @keyup.enter="updateCategory" type="text" class="w-full p-2 border rounded-md">
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="showEditCategoryModal = false" class="px-4 py-2 bg-slate-100 rounded-md text-xs font-bold">取消</button>
                        <button type="button" @click="updateCategory" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md text-xs font-bold">儲存</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex justify-end pt-2">
            <button type="button" @click="showCategoryModal = false" class="px-4 py-2 bg-slate-100 rounded-md text-xs font-bold">關閉</button>
        </div>
    </div>
</div>

<!-- 2. 新增的「純新增分類」Modal (給景點列表旁的「+ 新增分類」按鈕使用，絕對沒有修改與刪除) -->
<div v-if="showCreateCategoryModal" class="fixed inset-0 bg-slate-900/50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-xl max-w-sm w-full p-6 space-y-4">
        <div class="flex justify-between items-center border-b pb-3">
            <h3 class="text-base font-bold text-slate-900">➕ 新增分類</h3>
            <button @click="showCreateCategoryModal = false" class="text-slate-400 hover:text-slate-600 font-bold">✕</button>
        </div>
        <div class="space-y-3 text-xs">
            <div>
                <label class="block font-bold text-slate-700 mb-1">分類名稱 (中文、英文、數字)</label>
                <input v-model="newCategoryName" @input="handleCategoryInput" @keyup.enter="storeCategoryAndClose" type="text" placeholder="請輸入分類名稱..." class="w-full p-2.5 border rounded-md">
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" @click="showCreateCategoryModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-md font-bold">取消</button>
                <button type="button" @click="storeCategoryAndClose" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-md font-bold shadow">確認新增</button>
            </div>
        </div>
    </div>
</div>