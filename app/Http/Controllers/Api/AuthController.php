<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;




class AuthController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function register(){
        $validator = Validator::make(request()->all(),[
            'name' => 'required',
            'username' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required'
        ]);

        if($validator->fails()){
            return response()->json($validator->messages());
        }

        $user = User::create([
            'name' => request('name'),
            'username' => request('username'),
            'email' => request('email'),
            'password' => Hash::make(request('password')),
        ]);
        if($user){
            return response()->json(['message' => 'berhasil register']);
        }else{
            return response()->json(['message' => 'gagal register']);
        }
    }



    public function login()
    {
        $credentials = request(['email', 'password']);

        if (! $token = auth('api')->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    public function me()
    {
    $user = auth('api')->user();
    if (!$user) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }
    return response()->json($user);
    }


    public function logout()
    {
    auth('api')->invalidate(true);
    auth('api')->logout();
    return response()->json(['message' => 'berhasil logged out']);
    }


    public function refresh()
    {
        return $this->respondWithToken(auth('api')->refresh());
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ]);
    }


}
