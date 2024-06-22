<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Validator;
use illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Admin;

class AuthController extends Controller
{
    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'username' => 'required|max:100',
            'email' => 'required|email|unique:user,email',
            'password' => 'required|min:8',
            'confirm_password' => 'required|same:password'
        ]);

        if($validator->fails()) {
            $errors = $validator->errors();
    
            if ($errors->has('email')) {
                return response()->json([
                    'success' => 0,
                    'message' => 'Email is taken'
                ]);
            }

            if ($errors->has('confirm_password')) {
                return response()->json([
                    'success' => 0,
                    'message' => 'Password does not match'
                ]);
            }
    
            return response()->json($errors);
        }

        $user = $validator->validated();

        User::create($user);

        $payload = [
            'username' => $user['username'],
            'role' => 'user',
            'email' => $user['email'],
            'iat' => Carbon::now()->timestamp, 
            'exp' => Carbon::now()->timestamp + 7200
        ];

        $token = JWT::encode($payload, env('JWT_SECRET_KEY'), 'HS256');

        return response()->json([
            'success' => 1,
            'token' => 'Bearer '.$token
        ]);
    }

    public function login(Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if($validator->fails()) {
            return response()->json($validator->messages())->setStatusCode(422);
        }

        $validated = $validator->validated();

        if(Auth::attempt($validated)) {
            $payload = [
                'username' => Auth::user()->username,
                'role' => 'user',
                'email' => Auth::user()->email,
                'iat' => Carbon::now()->timestamp,
                'exp' => Carbon::now()->timestamp + 7200 // 2 jam
            ];

            $token = JWT::encode($payload, env('JWT_SECRET_KEY'), 'HS256');

            return response()->json([
                'success' => 1,
                'token' => 'Bearer '.$token
            ], 200);
        } else {
            return response()->json([
                'success' => 0,
                'message' => 'Wrong email or password'
            ]);
        }
    }

    public function loginAdmin(Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if($validator->fails()) {
            return response()->json($validator->messages())->setStatusCode(422);
        }

        $validated = $validator->validated();

        if(Auth::guard('admin')->attempt($validated)) {
            $admin = Auth::guard('admin')->user();
            $payload = [
                'username' => $admin->username,
                'role' => 'admin',
                'email' => $admin->email,
                'iat' => Carbon::now()->timestamp,
                'exp' => Carbon::now()->timestamp + 7200 // 2 jam
            ];

            $token = JWT::encode($payload, env('JWT_SECRET_KEY'), 'HS256');

            return response()->json([
                'success' => 1,
                'token' => 'Bearer '.$token
            ], 200);
        } else {
            return response()->json([
                'success' => 0,
                'message' => 'Wrong email or password'
            ]);
        }
    }
}
