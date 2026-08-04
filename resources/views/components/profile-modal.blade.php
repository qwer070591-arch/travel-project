<!-- 會員中心 Modal -->
<div v-if="showProfileModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6 space-y-4 relative border">
        <div class="flex justify-between items-center border-b pb-3">
            <h3 class="font-bold text-slate-900 text-base">⚙️ 修改會員基本資訊</h3>
            <button @click="showProfileModal = false" class="text-slate-400 hover:text-slate-600 font-bold text-lg">✕</button>
        </div>

        <form @submit.prevent="updateProfile" class="space-y-3 text-xs">
            <div>
                <label class="block font-bold text-slate-700 mb-1">使用者名稱</label>
                <input v-model="profileForm.name" type="text" required class="w-full p-2.5 border rounded-lg">
            </div>
            <div>
                <label class="block font-bold text-slate-700 mb-1">電子郵件</label>
                <input v-model="profileForm.email" type="email" required class="w-full p-2.5 border rounded-lg">
            </div>
            <div>
                <label class="block font-bold text-slate-700 mb-1">新密碼 (若不修改請留空)</label>
                <input v-model="profileForm.password" type="password" class="w-full p-2.5 border rounded-lg" placeholder="不修改請保持空白">
            </div>

            <div v-if="profileError" class="text-rose-500 font-bold bg-rose-50 p-2 rounded border border-rose-100">
                @{{ profileError }}
            </div>
            <div v-if="profileSuccess" class="text-emerald-600 font-bold bg-emerald-50 p-2 rounded border border-emerald-100">
                @{{ profileSuccess }}
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" @click="showProfileModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-lg">取消</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg shadow">儲存變更</button>
            </div>
        </form>
    </div>
</div>