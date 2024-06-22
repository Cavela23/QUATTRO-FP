<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User; //panggil model user
use Firebase\JWT\JWT; //panggil library jwt
use Carbon\Carbon; //panggil library carbon
use Illuminate\Support\Str; //panggil library Str
use Illuminate\Support\Facades\Hash; //panggil library hash
use Laravel\Socialite\Facades\Socialite; //panggil library socialite

class OAuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function callback() {
        $googleUser = Socialite::driver('google')->stateless()->user();

        $password = Hash::make(Str::random(16));

        $user = User::firstOrCreate(
            ['email' => $googleUser->email],
            [
                'username' => $googleUser->name,
                'password' => $password
            ]
        );

        $payload = [
            'username' => $user['username'],
            'email' => $user['email'],
            'role' => 'user',
            'iat' => Carbon::now()->timestamp,
            'exp' => Carbon::now()->timestamp + 7200 // 2 hours
        ];

        $token = JWT::encode($payload, env('JWT_SECRET_KEY'), 'HS256');

        return response()->json([
            'message' => 'Registered and logged in succesfully',
            'data' => [
                'username' => $user['username'],
                'email' => $user['email']
            ],
            'token' => 'Bearer '.$token
        ], 200);
    }
}
