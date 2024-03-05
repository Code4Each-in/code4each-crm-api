<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agency;
use App\Models\User;
use App\Notifications\CommonEmailNotification;
use App\Notifications\VerifyEmail;
use Carbon\Carbon;
use Exception;
use Google_Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class GoogleSocialiteController extends Controller
{
    // public function redirectToGoogle()
    // {
    //     return Socialite::driver('google')->stateless()->redirect();
    // }

    // public function handleCallback(Request $request)
    // {
    //     try {
    //        $user = Socialite::driver('google')->stateless()->user();
    //         $findUser = User::where('social_id', $user->id)->first();

    //         if ($findUser) {
    //             $token = $findUser->createToken('access-token')->accessToken;

    //             return response()->json(['message' => 'User logged in successfully', 'user' => $findUser, 'access_token' => $token]);
    //         } else {
    //             $agencyObj = new Agency();
    //             $agencyObj->name = 'Agency Name';
    //             $agencyObj->save();
    //             $agencyId = $agencyObj->id;

    //             if ($agencyObj) {
    //                 $userObj = new User();
    //                 $userObj->name = $user->name;
    //                 $userObj->email = $user->email;
    //                 $userObj->phone = 'NA'; // Adjust as needed
    //                 $userObj->agency_id = $agencyId;
    //                 $userObj->social_id = $user->id;
    //                 $userObj->social_type = 'google';
    //                 $userObj->email_verified_at = $user['email_verified'] ? now() : null;
    //                 $userObj->role = 'admin';
    //                 $userObj->password = bcrypt('my-google');
    //                 $userObj->save();

    //                 $token = $userObj->createToken('access-token')->accessToken;

    //                 return response()->json(['message' => 'User registered and logged in successfully', 'user' => $userObj, 'access_token' => $token]);
    //             }
    //         }
    //     } catch (Exception $e) {
    //         $error = $e->getMessage();
    //         return response()->json(['error' => 'error While Logging in with Google'], 500);
    //     }
    // }


    public function handleGoogleLogin(Request $request)
    {
        $response = [
            'success'=> false,
            'status' => 400,
        ];

        try {
            $idToken = $request->input('id_token');

            $client = new Google_Client(['client_id' => '467874272347-f241lioo004ksju0qudsroorkb5lf6au.apps.googleusercontent.com']);
            $payload = $client->verifyIdToken($idToken);
            if ($payload) {

                $user = $payload;
                $findUser = User::where('social_id', $user['sub'])->first();
                if ($findUser) {
                    $token = $findUser->createToken('access-token')->accessToken;
                    $response = [
                        'message' => 'User logged in successfully',
                        'user' => $findUser,
                        'access_token'=> $token,
                        'success'=> true,
                        'status' => 200,
                    ];
                    return response()->json($response);
                } else {
                    $agencyObj = new Agency();
                    $agencyObj->name = 'Agency Name';
                    $agencyObj->save();
                    $agencyId = $agencyObj->id;

                    if ($agencyObj) {
                        $userObj = new User();
                        $userObj->name = $user['name'];
                        $userObj->email = $user['email'];
                        $userObj->phone = 'NA'; // Adjust as needed
                        $userObj->agency_id = $agencyId;
                        $userObj->social_id = $user['sub'];
                        $userObj->social_type = 'google';
                        $userObj->email_verified_at = $user['email_verified'] ? now() : null;
                        $userObj->role = 'admin';
                        $userObj->password = bcrypt('my-google');
                        $userObj->save();

                        $token = $userObj->createToken('access-token')->accessToken;

                        $messages = [
                            'greeting-text' => 'Hey! '. $userObj->name,
                        ];
                        // Send Verification Email Using Custom Verify Notification
                        $userObj->notify(new VerifyEmail($messages));

                        $messages = [
                            'subject' => 'New Agency Is Register With Our CRM Platform',
                            'url-title' => 'Find Detail',
                            'url' => env('FRONTEND_URL'),
                            'lines_array' => [
                                'title' => 'Dear Admin,',
                                'body-text' => 'We have found that New Agency Is Register With Us. Please Find Detail Below:',
                                'special_Agency_Name' => $agencyObj->name,
                                'special_Email' => $userObj->email,
                            ],
                        ];
                        $admins = User::where('role', 'super_admin')->get();

                        if ($admins->count() > 0) {
                            foreach ($admins as $admin) {
                                $admin->notify(new CommonEmailNotification($messages));
                            }
                        }
                        $response = [
                            'message' => 'User registered and logged in successfully',
                            'user' => $userObj,
                            'access_token'=> $token,
                            'success'=> true,
                            'status' => 200,
                        ];

                        return response()->json($response);
                    }
                }
            } else {

                // The ID token is invalid
                return response()->json(['error' => 'Invalid Google ID token'], 401);
            }
        } catch (\Exception $e) {
            $error = $e->getMessage();
            return response()->json(['error' => 'Error while logging in with Google'], 500);
        }
    }

    public function updateLeftFields(Request $request)
    {
        $response = [
            'success' => false,
            'status' => 400,
        ];
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'agency_id' => 'required',
            'company_name' => 'required|string|max:255',
            'phone' => 'required',
            'description' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }
        $validate = $validator->valid();
        $agencyObj = Agency::find($validate['agency_id']);
        if($agencyObj){
            $agencyObj->name = $validate['company_name'];
            if (!empty($validate['description'])) {
                $agencyObj->description = $validate['description'];
            }
            $agencyObj->updated_at = Carbon::now();
            $agencyObj->save();
        }
        if($agencyObj->save()){
            $userObj = User::find($validate['user_id']);
            $userObj->phone = $validate['phone'];
            $userObj->save();
        }

        $response = [
            'message' => "Detail Saved Successfully.",
            'status' => 200,
            'success' => true,
        ];

     return response()->json($response);

    }
}
