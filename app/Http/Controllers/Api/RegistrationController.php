<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agency;
use App\Models\User;
use App\Notifications\CommonEmailNotification;
use App\Notifications\VerifyEmail;
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
        ], [
            'email.unique' => 'This email is already in use, please try with some other email address.',
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
            $userObj->user_type = "user";
            $userObj->password = Hash::make($validate['password']);
            $userObj->save();

            DB::commit();

            $token = $userObj->createToken('access-token')->accessToken;

            $messages = [
                'greeting-text' => 'Hey! '. $userObj->name,
            ];
            // Send Verification Email Using Custom Verify Notification
            $userObj->notify(new VerifyEmail($messages));

            $messages = [
                'subject' => 'New Agency Is Register With Our CRM Platform',
                'url-title' => 'Find Detail',
                'url' => '/',
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
                'message' => 'Company Register Successfully.',
                'token' => $token,
                'success' => true,
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
                'message' => 'An error occurred while processing your request.',
                'error' => $e->getMessage(),
            ];
            return response()->json($response,401);
        }
    }
}
