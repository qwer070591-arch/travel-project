<tbody>
    @forelse($attractions as $attraction)
    <tr>
        <td class="border border-gray-200 px-4 py-2 text-center">{{ $attraction->id }}</td>
        <td class="border border-gray-200 px-4 py-2">{{ $attraction->name }}</td>

        <!-- 修改這裡：將分類名稱包在動態顏色的 span 標籤內 -->
        <td class="border border-gray-200 px-4 py-2 text-center">
            <span class="px-2.5 py-0.5 text-xs font-medium rounded-full {{ $attraction->category_color }}">
                {{ $attraction->category->name ?? '未分類' }}
            </span>
        </td>

        <td class="border border-gray-200 px-4 py-2 text-center">{{ $attraction->created_at }}</td>
        <td class="border border-gray-200 px-4 py-2 text-center">
            <form action="{{ route('attractions.destroy', $attraction->id) }}" method="POST" onsubmit="return confirm('確定要刪除這個景點嗎？');">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-500 text-white px-3 py-1 rounded text-sm hover:bg-red-600">刪除</button>
            </form>
        </td>
    </tr>
    @empty
    <tr>
        <td colspan="5" class="border border-gray-200 px-4 py-4 text-center text-gray-500">目前沒有任何景點資料！</td>
    </tr>
    @endforelse
</tbody>