<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ResetPasswordController extends Controller
{
    public function resetPassword(Request $request)
    {
        $response = [
            'success' => false,
            'status' => 400,
        ];

        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users',
            'token' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }
        $expireTimeInMinutes = config('auth.passwords.users.expire');
        $resetRecord = DB::table('password_resets')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->where('created_at', '>=', now()->subMinutes($expireTimeInMinutes)) // Check if the token has not expired
            ->first();

        if (!$resetRecord) {
            return response()->json(['error' => 'Invalid or expired reset token.'], 400);
        }

        $updatePassword = User::where('email', $request->email)
        ->update(['password' => Hash::make($request->password)]);

        // Delete the reset token record from the password_resets table after the password has been successfully reset
        DB::table('password_resets')->where('email', $request->email)->delete();

        $response = [
            'message' => 'Password reset successfully.',
            'success' => true,
            'status' => 200
        ];
        return response()->json($response);
    }

}
