<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Validator;
use illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\User; 

class UserController extends Controller
{
    public function getUser(Request $request) {
        $email = $request->email;
        $user = User::where('email', $email)->first();

        if ($user) {
            return response()->json($user);
        } else {
            return response()->json(['message' => 'User not found'], 404);
        }
    }

    public function updateUser(Request $request, $id) {
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'phone_number' => 'required',
            'address' => 'required',
            'email' => 'required|email'
        ]);

        if($validator->fails()) {
            return response()->json($validator->messages())->setStatusCode(422);
        }

        $validated = $validator->validated();

        $user = User::where('user_id', $id)->first();

        if($user) {
            $user->update([
                'username' => $validated['username'],
                'phone_number' => $validated['phone_number'],
                'address' => $validated['address'],
                'email' => $validated['email']
            ]);

            $payload = [
                'username' => $validated['username'],
                'role' => 'user',
                'email' => $validated['email'],
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
                'message' => 'User not found'
            ], 404);
        }
    }

    public function updatePassword(Request $request, $id) {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:8',
            'confirm_password' => 'required|same:password'
        ]);

        if($validator->fails()) {
            $errors = $validator->errors();

            if ($errors->has('confirm_password')) {
                return response()->json([
                    'success' => 0,
                    'message' => 'Password does not match'
                ]);
            }
    
            return response()->json($errors);
        }

        $validated = $validator->validated();

        $user = User::where('user_id', $id)->first();

        if($user) {
            $user->update([
                'password' => $validated['password']
            ]);
            return response()->json([
                'success' => 1,
                'message' => 'Password changed successfully'
            ], 200);

        } else {
            return response()->json([
                'success' => 0,
                'message' => 'User not found'
            ], 404);
        }
    }
}
