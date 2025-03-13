<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Exception;
class UserController extends Controller
{
    public function selectProgram(Request $request)
    {
        try{
            $user = JWTAuth::parseToken()->authenticate();

            $request->validate([
                'program_id' => 'required|exists:programs,id'
            ]);

            $user->update([
                'selected_program_id' => $request->program_id
            ]);

            return response()->json(['message' => 'Program selected successfully.']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

}
