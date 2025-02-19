<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Google_Client;

class AuthController extends Controller
{
    //function for register
    public function register(Request $request)
    {
        //validate the request
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string'
        ]);

        //create user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        //response
        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully',
            'user' => $user
        ], 201);
    }

    function loginGoogle(Request $request)
    {
        //validate the request
        $request->validate([
            'id_token' => 'required|string'
        ]);

        //get the id_token
        $id_token = $request->id_token;
        $client = new Google_Client(['client_id' => env('GOOGLE_CLIENT_ID')]);
        $payload = $client->verifyIdToken($id_token);

        //check if the payload is valid
        if ($payload) {
            $user = User::where('email', $payload['email'])->first();
            $token = $user->createToken('auth_token')->plainTextToken;
            if ($user) {
                //response
                return response()->json([
                    'status' => 'success',
                    'message' => 'User login successfully',
                    'data' => [
                        'user' => $user,
                        'token' => $token,
                    ]
                ], 200);

            } else {
                //if user not found, create new user
                $user = User::create([
                    'name' => $payload['name'],
                    'email' => $payload['email'],
                    'password' => Hash::make($payload['sub']),
                ]);

                //response
                return response()->json([
                    'status' => 'success',
                    'message' => 'User created successfully',
                    'data' => [
                        'user' => $user,
                        'token' => $token,
                    ]
                ], 201);
            }
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid id_token'
            ], 400);
        }
    }

    //function for login
    public function login(Request $request)
    {
        //validate the request
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        //check the user
        $user = User::where('email', $request->email)->first();

        //check if user not found
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid credentials'
            ], 401);
        }

        //generate token
        $token = $user->createToken('auth_token')->plainTextToken;

        //response
        return response()->json([
            'status' => 'success',
            'message' => 'User login successfully',
            'data' => [
                'user' => $user,
                'token' => $token,
            ]
        ], 200);
    }

    //function for logout
    public function logout(Request $request)
    {
        //revoke the token
        $request->user()->currentAccessToken()->delete();

        //response
        return response()->json([
            'status' => 'success',
            'message' => 'User logout successfully'
        ], 200);
    }
}
