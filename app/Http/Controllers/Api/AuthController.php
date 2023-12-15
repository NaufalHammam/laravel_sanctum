<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\PersonalAccessToken as PersonalAccessTokenModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Str;

use Laravel\Sanctum\PersonalAccessToken;
use Laravel\Sanctum\NewAccessToken;

class AuthController extends Controller
{

    protected function manual_createToken($tokenable_id, $name, $abilities = ['*'])
    {
        if ($name == "Auth token") {
            $token = PersonalAccessTokenModel::create([
                'tokenable_type'    => "App\Models\User",
                'tokenable_id'      => $tokenable_id,
                'name'              => $name,
                'token'             => hash('sha256', $plainTextToken = Str::random(40)),
                'abilities'         => $abilities,
                'expired_at'        => now()->addHours(24)
            ]);
        }elseif ($name == "Action token") {
            $token = PersonalAccessTokenModel::create([
                'tokenable_type'    => "App\Models\User",
                'tokenable_id'      => $tokenable_id,
                'name'              => $name,
                'token'             => hash('sha256', $plainTextToken = Str::random(40)),
                'abilities'         => $abilities,
                'expired_at'        => now()->addMinutes(3)  
            ]);
        }
            

        return new NewAccessToken($token, $token->getKey().'|'.$plainTextToken);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:3'
        ]);

        if ($validator->fails()) {
            return response()->json([
                // 'data' => $user,
                'status'    => 0,
                'message'   => "Register failed"
            ]);
            // return response()->json($validator->errors());
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        return response()->json([
            'status'    => 1,
            'data'      => $user,
            'message'   => "Register success"
        ]);
    }

    public function login(Request $request)
    {
        
        $validator = Validator::make($request->all(), [
            'email'         => 'required',
            'password'      => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        if (!Auth::attempt($request->only(['email', 'password']))) {
            return response()->json([
                'message' => 'These credentials do not match our records'
            ], 401);
        }

        $user = User::where('email', $request->email)->first();
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'These credentials do not match our records'
            ], 401);
        }

        // $token = auth()->user()->createToken('Auth token');
        $token = $this->manual_createToken($user->id, "Auth token");
        return response()->json([
            // 'token' => $token,
            'user'  => $user->name,
            'token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'expired_at' => date_format(date_create($token->accessToken->expired_at), "Y-m-d H:i:s")
        ]);
    }

    public function logout()
    {
        Auth::user()->tokens()->delete();
        // Auth::user()->tokens()->where('personal_access_tokens.name', 'auth_token2')->delete();
        
        return response()->json([
            'message' => 'logout success'
        ]);
    }

    public function create_token(Request $request)
    {
        // $token = auth()->user()->createToken('Action token');

        $bearer_token = $request->bearerToken();
        
        $token = PersonalAccessToken::findToken($bearer_token);
        $user = $token->tokenable;
        
        $token = $this->manual_createToken($user->id, "Action token");
        return response()->json([
            'token' => $token->plainTextToken,
            'expired_at' => date_format(date_create($token->accessToken->expired_at), "Y-m-d H:i:s")
        ]);
    }


    public function get_user(Request $request)
    {
        $bearer_token = $request->bearerToken();
        
        $token = PersonalAccessToken::findToken($bearer_token);
        $user = $token->tokenable;

        return response()->json([
            'message' => $user
        ]);
    }


}