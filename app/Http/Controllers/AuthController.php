<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users',
            'password' => 'required',
            'owner' => 'required',
            'name' => 'required',
            'address' => 'required',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json([
                'code' => 422,
                'message' => $error,
                'data' => null
            ], 422);
        } else {
            $email = $request->input('email');
            $password = Hash::make($request->input('password'));
            $owner = $request->input('owner');
            $name = $request->input('name');
            $address = $request->input('address');

            $register = User::create([
                'owner' => $owner,
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'address' => $address,
                'img' => 'default.png',
            ]);

            if ($register) {
                return response()->json([
                    'code' => 200,
                    'message' => 'Register Success!',
                    'data' => $register
                ], 200);
            } else {
                return response()->json([
                    'code' => 400,
                    'message' => 'Register Fail!',
                    'data' => $register
                ], 400);
            }
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);
        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json([
                'code' => 422,
                'message' => $error,
                'data' => null
            ], 422);
        } else {
            $email = $request->input('email');
            $password = $request->input('password');

            $user = User::where('email', $email)->first();
            if (Hash::check($password, $user->password)) {
                $apiToken = base64_encode(Str::random(40));
                $user->update([
                    'api_token' => $apiToken
                ]);

                return response()->json([
                    'code' => 200,
                    'message' => 'Login Success!',
                    'data' => [
                        'user' => $user,
                        'api_token' => $apiToken
                    ]
                ], 200);
            } else {
                return response()->json([
                    'code' => 401,
                    'message' => 'Login Failed!',
                    'data' => []
                ], 401);
            }
        }
    }
}
