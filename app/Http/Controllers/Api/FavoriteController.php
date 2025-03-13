<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\Program;
use App\Models\jobsIn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;

class FavoriteController extends Controller
{
    public function toggleFavorite(Request $request)
    {
        try{
            $user = Auth::guard('api')->user();
            $type = $request->input('type');
            $id = $request->input('id');

            if ($type === 'program') {
                $model = Program::findOrFail($id);
            } elseif ($type === 'job') {
                $model = jobsIn::findOrFail($id);
            } else {
                return response()->json(['error' => 'Invalid type'], 400);
            }

            $existingFavorite = Favorite::where('user_id', $user->id)
                ->where('favoritable_id', $id)
                ->where('favoritable_type', get_class($model))
                ->first();

            if ($existingFavorite) {
                $existingFavorite->delete();
                return response()->json(['message' => 'Removed from favorites']);
            } else {
                Favorite::create([
                    'user_id' => $user->id,
                    'favoritable_id' => $id,
                    'favoritable_type' => get_class($model),
                ]);
                return response()->json(['message' => 'Added to favorites']);
            }
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getFavorites()
    {
        try{
            $user = Auth::guard('api')->user();
            $favorites = Favorite::where('user_id', $user->id)->with('favoritable')->get();

            return response()->json($favorites);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
