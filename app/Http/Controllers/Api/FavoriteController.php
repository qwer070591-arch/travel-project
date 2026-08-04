<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function index(Request $request)
    {
        try {
            $userId = $request->query('user_id', 1);
            return Favorite::where('user_id', $userId)->get();
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    public function toggle(Request $request)
    {
        $userId = $request->input('user_id', 1);
        $attractionId = $request->input('attraction_id');

        $favorite = Favorite::where('user_id', $userId)->where('attraction_id', $attractionId)->first();

        if ($favorite) {
            $favorite->delete();
            return response()->json(['status' => 'removed']);
        } else {
            $newFavorite = Favorite::create([
                'user_id' => $userId,
                'attraction_id' => $attractionId,
                'createdAt' => now()->format('Y/m/d')
            ]);
            return response()->json(['status' => 'added', 'data' => $newFavorite], 201);
        }
    }
}
