<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // 註冊方法
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:3',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // 🎯 註冊時同時產生 Sanctum Token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => '註冊成功',
            'token' => $token, // 必須回傳 token
            'user' => $user
        ], 201);
    }

    // 登入方法
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        // 檢查使用者是否存在以及密碼是否正確
        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => '電子郵件或密碼錯誤'
            ], 401);
        }

        // 🎯 登入時產生 Sanctum Token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => '登入成功',
            'token' => $token, // 必須回傳 token 讓前端存入 localStorage
            'user' => $user
        ]);
    }

    // 登出方法（清除當前 Token）
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => '登出成功'
        ]);
    }
    // 更新會員基本資訊
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:3',
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return response()->json([
            'message' => '會員資訊更新成功',
            'user' => $user
        ]);
    }
}
