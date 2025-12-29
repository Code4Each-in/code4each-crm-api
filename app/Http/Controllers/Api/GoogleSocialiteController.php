<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agency;
use App\Models\AgencyWebsite;
use App\Models\User;
use App\Models\SocialAccount;
use App\Notifications\CommonEmailNotification;
use App\Notifications\VerifyEmail;
use Carbon\Carbon;
use Exception;
use Google_Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;

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

            $client = new Google_Client(['client_id' => '725628821892-6lojkrl63celrm16gh182sdujfpagk5b.apps.googleusercontent.com']);
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

    public function getGoogleReviewLink(Request $request)
    {
        $response = [
            'success' => false,
            'status' => 400,
        ];
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }
        $validatedData = $validator->validated();
        $userObj = User::find($validatedData['user_id']);

        if($userObj){
            $response = [
                'google_review_link' => $userObj->google_review_link,
                'status' => 200,
                'success' => true,
            ];
        } else {
            $response = [
                'message' => "User Not Found.",
                'status' => 404,
                'success' => false,
            ];
        }
     return response()->json($response);
    }

    public function saveGoogleReviewLink(Request $request)
    {
        $response = [
            'success' => false,
            'status' => 400,
        ];
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'google_review_link' => 'required',
            'website_domain' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }
        $validatedData = $validator->validated();
        $userObj = User::find($validatedData['user_id']);
        if($userObj){
            $userObj->google_review_link = $validatedData['google_review_link'];
            $userObj->save();
        }
        $websiteUrl = $request->input('website_domain');
        $postApiUrl = $websiteUrl . '/wp-json/v1/add-google-review-link';
        $wpResponse = Http::post($postApiUrl, $validatedData);
        if ($wpResponse->successful()) {
            $response['response'] = $wpResponse->json();
            $response['status']   = $wpResponse->status();
            $response['success']  = true;
        } else {
            $response['response'] = $wpResponse->json() ?? 'Failed to post';
            $response['status']   = $wpResponse->status() ?? 400;
            $response['success']  = false;
        }
     return response()->json($response);
    }

    public function updateMapAddress(Request $request)
    {
        $response = [
            'success' => false,
            'status' => 400,
        ];

        $validator = Validator::make($request->all(), [
            'address' => 'required',
            'city' => 'required',
            'state' => 'required',
            'country' => 'required',
            'pincode' => 'required',
            'website_domain' => 'required',
            'agency_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 400);
        }
        $validated = $validator->validated();

        $agency = AgencyWebsite::find($validated['agency_id']);

        // Update fields in the user table
        $agency->address = $validated['address'] ?? null;
        $agency->city = $validated['city'] ?? null;
        $agency->state = $validated['state'] ?? null;
        $agency->country = $validated['country'] ?? null;
        $agency->pin = $validated['pincode'] ?? null;

        $agency->save();

        $websiteDomain = $validated['website_domain'];
        $postApiUrl = $websiteDomain . 'wp-json/v1/change_global_variables';
        $data = [
            "address" => ["value" => $validated["address"]],
            "state" => ["value" => $validated["state"]],
            "city" => ["value" => $validated["city"]],
            "country" => ["value" => $validated["country"]],
            "pincode" => ["value" => $validated["pincode"]],
        ];
        $wpResponse = Http::post($postApiUrl, $data);
        if ($wpResponse->successful()) {
            $response['response'] = $wpResponse->json();
            $response['status']   = $wpResponse->status();
            $response['success']  = true;
        } else {
            $response['response'] = $wpResponse->json() ?? 'Failed to post';
            $response['status']   = $wpResponse->status() ?? 400;
            $response['success']  = false;
        }

        return response()->json([
            'message' => "Map Address Updated Successfully.",
            'status' => 200,
            'success' => true,
        ], 200);
    }

}
