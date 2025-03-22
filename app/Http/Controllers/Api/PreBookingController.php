<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PreBooking;
use App\Models\User;
use App\Notifications\CommonEmailNotification;

class PreBookingController extends Controller
{
    public function saveEmailForPreBooking(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:user_notify_emails,email',
        ]);

      $intrestedUser =  PreBooking::create([
            'email' => $request->input('email'),
        ]);
        $email = $intrestedUser->email;

        list($username, $domain) = explode('@', $email, 2);

        $count = PreBooking::count();

        $messages = [
            'subject' => '🚀 Pre-Registered for Beta SpeedySites!',
            'greeting-text' => 'Hi! '.$username,
            'url-title' => 'Speedy Sites',
            'url' => env('FRONTEND_URL'),
            'lines_array' => [
                'title' => "Great news! 🎉 You're pre-registered for Beta SpeedySites. Get ready for lightning-fast websites!",
                'body-text' => "We'll notify you as soon as it goes live. Stay tuned for exclusive updates!",
            ],
        ];

        $intrestedUser->notify(new CommonEmailNotification($messages));

        $messages = [
            'subject' => 'New User Pre-Registered',
            'greeting-text' => 'Hello Admin,',
            'url-title' => 'Speedy Sites',
            'url' => env('FRONTEND_URL'),
            'lines_array' => [
                'title' => "A new user has pre-registered on your platform. Here are the details:",
                // 'body-text' => "We'll notify you as soon as it goes live. Stay tuned for exclusive updates!",
                'special_Email' => $email,
                'special_Total_Pre_Register' => $count,
            ],
        ];

        $admins = User::where('role', 'super_admin')->get();

            if ($admins->count() > 0) {
                foreach ($admins as $admin) {
                    $admin->notify(new CommonEmailNotification($messages));
                }
            }


        return response()->json([
            'message' => 'Email inserted Successfully',
            'data' => ['count' => $count],
        ], 201);
    }

}
