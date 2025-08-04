<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $response = [
            'success' => false,
            'status' => 400,
        ];

        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $credentials = $request->only('email', 'password');

        $user = User::where('email', $credentials['email'])->first();

        if (!$user) {
            $response['message'] = 'No account found with this email. Please sign up first.';
            $response['status'] = 404;
            return response()->json($response, 404);
        }

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $tokenResult = $user->createToken('access-token');
            $token = $tokenResult->accessToken;

            $response['success'] = true;
            $response['status'] = 200;
            $response['message'] = 'User Login Successfully';
            $response['token'] = $token;

            return response()->json($response);
        } else {
            $response['message'] = 'Invalid email or password. Please try again.';
            $response['status'] = 401;


            return response()->json($response, 401);
        }
    }
    public function logout()
    {
        $user = Auth::user();
        $tokens =  $user->tokens;

        // Revoke (delete) each token
        $tokens->each(function ($token) {
            $token->delete();
        });

        $response = [
            'success' => true,
            'status' => 200,
            'message' => 'User logged out successfully.',
        ];

        return response()->json($response);
    }
}
