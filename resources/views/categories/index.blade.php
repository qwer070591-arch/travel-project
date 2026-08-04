<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>分類管理</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto bg-white p-6 rounded-lg shadow-md">
        <!-- 導覽列 -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">🏷️ 景點分類管理</h1>
            <div>
                <a href="/attractions" class="text-gray-600 hover:text-blue-600 mr-4">景點管理</a>
                <a href="/categories" class="text-blue-600 font-semibold underline">分類管理</a>
            </div>
        </div>

        <!-- 成功訊息提示 -->
        @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
            {{ session('success') }}
        </div>
        @endif

        <!-- 新增分類表單 -->
        <form action="{{ route('categories.store') }}" method="POST" class="mb-8 bg-gray-50 p-4 rounded-md border">
            @csrf
            <h2 class="text-lg font-semibold mb-3 text-gray-700">新增分類</h2>
            <div class="flex gap-4">
                <input type="text" name="name" placeholder="請輸入分類名稱 (例如: 自然風景)" required class="flex-1 border-gray-300 rounded-md border p-2 focus:ring focus:ring-blue-200">
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">新增分類</button>
            </div>
        </form>

        <!-- 分類列表 -->
        <h2 class="text-lg font-semibold mb-3 text-gray-700">現有分類列表</h2>
        <table class="w-full border-collapse border border-gray-200">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border border-gray-200 px-4 py-2">#</th>
                    <th class="border border-gray-200 px-4 py-2">分類名稱</th>
                    <th class="border border-gray-200 px-4 py-2">建立時間</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $category)
                <tr>
                    <td class="border border-gray-200 px-4 py-2 text-center">{{ $category->id }}</td>
                    <td class="border border-gray-200 px-4 py-2">{{ $category->name }}</td>
                    <td class="border border-gray-200 px-4 py-2 text-center">{{ $category->created_at }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="border border-gray-200 px-4 py-4 text-center text-gray-500">目前沒有任何分類！</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>

</html>