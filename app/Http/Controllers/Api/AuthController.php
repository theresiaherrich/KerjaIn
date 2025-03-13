<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Profile;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Exception;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register', 'refresh']]);
        $this->middleware('admin')->only(['index','destroy']);
    }

    public function register(Request $request)
    {
        try{
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:6',
                'username' => 'required|string|unique:users|max:255',
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'username' => $request->username,
            ]);

            Profile::firstOrCreate([
                'user_id' => $user->id,
            ]);

        return response()->json(['message' => 'Registrasi berhasil, silakan login'], 201);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function login(Request $request)
    {
        try{
            $credentials = $request->only(['email', 'password']);

            if (! $token = auth('api')->attempt($credentials)) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $user = auth('api')->user();

            Profile::firstOrCreate([
                'user_id' => $user->id,

            ]);

            return $this->respondWithToken($token);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function me()
    {
        try{
            $user = auth('api')->user();
            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            return response()->json($user);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function logout()
    {
        try{
        auth('api')->invalidate(true);
        auth('api')->logout();
        return response()->json(['message' => 'Berhasil logged out']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function refresh()
    {
        try{
        return $this->respondWithToken(auth('api')->refresh());
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ]);
    }

    public function index()
    {
        try{
            $users = User::all();
            return response()->json($users);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try{
            $user = User::find($id);
            if (!$user) {
                return response()->json(['message' => 'User tidak ditemukan'], 404);
            }

            $user->delete();
            return response()->json(['message' => 'User berhasil dihapus']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

    }

        public function makeAdmin($id)
        {
            try{
            $user = User::find($id);

            if (!$user) {
                return response()->json(['message' => 'User tidak ditemukan'], 404);
            }

            $user->role = 'admin';
            $user->save();

            return response()->json(['message' => 'User berhasil dijadikan admin']);
            } catch (Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }

}
