<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Rules\Password;

class UserController extends Controller
{
    public function login(Request $request) {
        try {
            //validate request
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            //find user by email
            $credentials = request(['email', 'password']); //get data request

            //check credential
            if(!Auth::attempt($credentials)) {
                return ResponseFormatter::error('Unauthorized', 401);
            }

            $user = User::where('email', $request->email)->first();
            if(!Hash::check($request->password, $user->password)) {
                throw new Exception('Invalid password');
            }

            //generate token
            $tokenResult = $user->createToken('authToken')->plainTextToken;

            //return response
            return ResponseFormatter::success([
                'access_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => $user
            ], 'Authentication successful');

        } catch (Exception $th) {
            return ResponseFormatter::error('Authentication failed');
        }
    }

    public function register(Request $request) {
        try {
            //Validate Request
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'string', new Password],
            ]);

            //create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            //generate token
            $tokenResult = $user->createToken('authToken')->plainTextToken;

            //return response
            return ResponseFormatter::success([
                'access_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => $user
            ], 'Registration successful');

        } catch (Exception $th) {
            return ResponseFormatter::error($th->getMessage());
        }
    }

    public function logout(Request $request) {
        //revoke token
        $token = $request->user()->currentAccessToken()->delete();

        return ResponseFormatter::success($token, 'Logout successful');
    }

    public function fetch(Request $request) {
        //get user
        $user = $request->user();

        return ResponseFormatter::success($user, 'Fetch Successful');
    }
}
