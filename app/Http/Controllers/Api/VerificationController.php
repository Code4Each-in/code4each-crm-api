<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    public function verify($user_id, Request $request) {

        $response = [
            'success' => false,
            'status' => 400,
        ];

        if (!$request->hasValidSignature()) {
            return response()->json(["error" => "Invalid/Expired URL provided."], 401);
        }

        $user = User::findOrFail($user_id);

        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            $response = [
                'message' => 'Email verified successfully.',
                'success' => true,
                'status' => 200,
            ];
        }else{

            $response = [
                'message' => 'Email Already Verified.',
                'success' => false,
                'status' => 400,
            ];

        }

        return response()->json($response);
    }

    public function resend() {
        if (!auth()->check()) {
            return response()->json(["message" => "Unauthorized"], 401);
        }
        if (auth()->user()->hasVerifiedEmail()) {
            return response()->json(["message" => "Email already verified."], 400);
        }

        auth()->user()->sendEmailVerificationNotification();

        $response = [
            'success' => true,
            'status' => 200,
            'message' => 'Email verification link sent on your email.',
        ];

        return response()->json($response);
    }
}
