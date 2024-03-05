<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\ForgotPasswordNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

class ForgotPasswordController extends Controller
{

    public function forgotPassword(Request $request)
    {
        $response = [
            'success' => false,
            'status' => 400,
        ];

        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }
        $token = Str::random(60);

        DB::table('password_resets')->updateOrInsert(
            ['email' => $request->email],
            ['token' => $token, 'created_at' => Carbon::now()]
        );
                      // send mail to user
                      $recipient = User::where('email',$request->email)->first();
                      $messages = [
                          'username' => $recipient->name,
                          'token' => $token,
                          'email' => $request->email,
                      ];
                      $recipient->notify(new ForgotPasswordNotification($messages));

                      $response = [
                        'message' => 'Password reset Link Sent On Your Email.',
                        'success' => true,
                        'status' => 200
                    ];
                    return response()->json($response);

    }
}
