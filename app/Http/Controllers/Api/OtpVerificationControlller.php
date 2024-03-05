<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\VerificationCode;
use App\Notifications\CommonEmailNotification;
use Carbon\Carbon;
use Google\Service\BinaryAuthorization\Check;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class OtpVerificationControlller extends Controller
{
    public function generateOtp(Request $request)
    {
        // Generate a random OTP (you may use a more robust OTP generation logic)
        $otp = rand(000000, 999999);

        // Handle email OTP generation logic
        $email = $request->input('email');
        $user = User::where('email', $email)->first();

        if ($user) {
            $userId = $user->id;
            $userName = $user->name;

            // Update existing or create a new verification code
            $verificationCode = VerificationCode::updateOrCreate(
                ['user_id' => $userId],
                [
                    'otp' => $otp,
                    'expires_at' => Carbon::now()->addMinutes(10),
                ]
            );

            if ($verificationCode) {
                $messages = [
                    'subject' => 'Account Verification - One-Time Password (OTP)',
                    'greeting-text' => 'Hello ' . $userName . ',',
                    'additional-info' => "This OTP is valid for the next 10 minutes. Please use it promptly to complete your action. If you didn't request this OTP, please ignore this message.",
                    'lines_array' => [
                        'title' => 'Your One-Time Password (OTP) for ' . env('APP_NAME') . ' is:',
                        'special_OTP' => $otp,
                    ],
                ];

                // Send the OTP to the user's email
                $user->notify(new CommonEmailNotification($messages));
            }
        }

        // Return a success response or redirect to the verification page
        return response()->json(['message' => 'OTP sent to email'], 200);
    }

    public function verifyOtp(Request $request)
    {
            // Get email and OTP from the request
            $email = $request->input('email');
            $otp = $request->input('otp');

            $user = User::where('email', $email)->with('verificationCode')->first();
            if ($user) {
                $verificationCode = $user->verificationCode;

                if ($verificationCode) {
                    // Check if the OTP has not expired
                    $currentTime = now(); // Assuming you are using Laravel's Carbon for timestamps

                    if ($verificationCode->expires_at > $currentTime) {
                        // Check if the OTP matches
                        if ($otp == $verificationCode->otp) {
                            // OTP is valid, and it has not expired
                            return response()->json(['message' => "Otp Verified!"],200);
                        } else {
                            // OTP does not match
                            return response()->json(['error' => "Invalid Otp!"],400);
                        }
                    } else {
                        // OTP has expired
                        return response()->json(['error' => "Otp Expired!"],400);
                    }
                } else {
                    // No verification code found for the user
                    return response()->json(['error' => "Verification Code Not Found!"],400);
                }
            } else {
                // User not found
                return response()->json(['error' => "User Not Found!"],400);
            }

    }

}
