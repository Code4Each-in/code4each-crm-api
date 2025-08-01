<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agency;
use App\Models\User;
use App\Notifications\CommonEmailNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Laravel\Passport\PersonalAccessTokenResult;

class RegistrationController extends Controller
{



    public function store(Request $request)
    {
        $response = [
            'success' => false,
            'status' => 400,
        ];
        $validator = Validator::make($request->all(), [
            'company_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'phone' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }
        $validate = $validator->valid();
        try {
            DB::beginTransaction();

            $agencyObj = new Agency();
            $agencyObj->name = $validate['company_name'];
            if (!empty($validate['description'])) {
                $agencyObj->description = $validate['description'];
            }
            $agencyObj->save();

            $userObj = new User();
            $userObj->agency_id = $agencyObj->id;
            $userObj->name = $validate['name'];
            $userObj->email = $validate['email'];
            $userObj->phone = $validate['phone'];
            $userObj->role = "admin";
            $userObj->password = Hash::make($validate['password']);
            $userObj->save();

            DB::commit();

            $token = $userObj->createToken('access-token')->accessToken;

            $verificationLink = URL::temporarySignedRoute(
                'verification.verify',
                now()->addMinutes(config('auth.verification.expire', 60)),
                ['id' => $userObj->id, 'hash' => sha1($userObj->getEmailForVerification())]
            );

            $messages = [
                'subject' => 'Welcome to SpeedySites – Verify Your Email to Get Started!',
                'additional-info' => 'If the button doesn’t work, you can also copy and paste the following link into your browser:',
                'url-title' => 'Verify Email Address',
                'url' => $verificationLink,
                'lines_array' => [
                    'title' => 'Thank you for registering with SpeedySites – your platform to launch beautiful websites without writing a single line of code!',
                    'body-text' => 'To complete your registration and activate your account, please verify your email address by clicking the link below:',
                ],
            ];
            $userObj->notify(new CommonEmailNotification($messages));
            $messages = [
                'subject' => 'New Agency Is Register With SpeedySites Platform',
                'url-title' => 'Find Detail',
                'url' => '/',
                'lines_array' => [
                    'title' => 'Dear Admin,',
                    'body-text' => 'We have found that New Agency Is Register With Us. Please Find Detail Below:',
                    'special_Agency_Name' => $agencyObj->name,
                    'special_Email' => $userObj->email,
                ],
            ];
            $admin = User::where('role','super_admin')->first();
            $admin->notify(new CommonEmailNotification($messages));
            $response = [
                'success' => true,
                'message' => 'Company Register Successfully.',
                'token' => $token,
                'status' => 200,
            ];

            return response()->json($response,200);

        } catch (\Exception $e) {
            DB::rollback();
            $errorMessage = $e->getMessage();
            $errorCode = $e->getCode();
            $errorFile = $e->getFile();
            $errorLine = $e->getLine();
            // Logging the error in log file
            \Log::error("\nError: $errorMessage\nFile: $errorFile\nLine: $errorLine \nCode:$errorCode");
            $response = [
                'success' => false,
                'status' => 400,
                'message' => "\nError: $errorMessage\nFile: $errorFile\nLine: $errorLine \nCode:$errorCode",
                'error' => $e->getMessage(),
            ];
            return response()->json($response,401);
        }
    }
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
            $response['message'] = 'Invalid credentials';
            $response['status'] = 401;


            return response()->json($response, 401);
        }
    }
    public function logout()
    {
        $user = Auth::user();
        $user->tokens()->delete();

        $response = [
            'success' => true,
            'status' => 200,
            'message' => 'User logged out successfully.',
        ];

        return response()->json($response);
    }
}
