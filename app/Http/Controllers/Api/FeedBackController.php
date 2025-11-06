<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Notifications\CommonEmailNotification;
use Illuminate\Http\Request;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class FeedBackController extends Controller
{
    public function feedback(Request $request)
    {
        $response = [
            "status" => 400,
            "success" => true,
        ];

        $validator = Validator::make($request->all(), [
            'user_id' => 'nullable',
            'website_id' => 'nullable',
            'agency_id' => 'nullable',
            'email' => 'nullable|email',
            'name' => 'nullable',
            'phone' => 'nullable',
            'type' => 'required',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'rating' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $validate = $validator->valid();

        $feedbackObj = new Feedback();
        $feedbackObj->user_id       = $validate['user_id'] ?? null;
        $feedbackObj->website_id    = $validate['website_id'] ?? null;
        $feedbackObj->agency_id     = $validate['agency_id'] ?? null;
        $feedbackObj->type          = $validate['type'];
        $feedbackObj->name          = $validate['name'] ?? null;
        $feedbackObj->email         = $validate['email'] ?? null;
        $feedbackObj->phone         = $validate['phone'] ?? null;
        $feedbackObj->title         = $validate['title'];
        $feedbackObj->message       = $validate['message'];
        $feedbackObj->rating       = $validate['rating'] ?? null;
        $feedbackObj->save();
        // if($feedbackObj){
        //         // Check if it's an inquiry and an email is provided
        //             if ($validate['type'] === 'inquiry' && $validate['email']) {
        //                 $messages = [
        //                     'subject' => 'Inquiry Received - ' . config('app.name'),
        //                     'greeting-text' => 'Hello ' . $validate['name'] . ',',
        //                     'additional-info' => "If you have any further questions or concerns, feel free to reply to this email.",
        //                     'lines_array' => [
        //                         'title' => 'Thank you for reaching out to us! We have received your inquiry and will get back to you as soon as possible.',
        //                         'body-text' =>'Here are the details of your inquiry:',
        //                         'special_Type:' => $validate['type'] ,
        //                         'special_Title:' => $validate['title'] ,
        //                         'special_Message:' => $validate['message'] ,
        //                         'special_Rating:' => $validate['rating'] ?? 'N/A',
        //                     ],
        //                 ];
        //                 // Send email for inquiry
        //                 $notifiable = new AnonymousNotifiable;

        //                 $notifiable->route('mail', $validate['email'])
        //                     ->notify(new CommonEmailNotification($messages));
        //             }
        //     $response = [
        //         "message" => "FeedBack Saved Successfully.",
        //         "status" => 200,
        //         "success" => true,
        //     ];
        // }
        if($feedbackObj){

            $type = strtolower($validate['type']);
            // Common greeting if name exists
            $greetingName = $validate['name'] ? $validate['name'] : 'there';

            // Common email base structure
            $messages = [
                'greeting-text' => 'Hello ' . $greetingName . ',',
                'additional-info' => "If you have any further questions or concerns, feel free to reply to this email.",
                'lines_array' => [
                    'body-text' => 'Here are the details you submitted:',
                    'special_Type' => ucfirst($validate['type']),
                    'special_Title' => $validate['title'],
                    'special_Message' => $validate['message'],
                    'special_Rating' => $validate['rating'] ?? 'N/A',
                ],
            ];

            // Change subject + first line depending on type
            switch ($type) {
                case 'inquiry':
                    $messages['subject'] = 'Inquiry Received - ' . config('app.name');
                    $messages['lines_array']['title'] = "Thank you for contacting us! We have received your inquiry and will get back to you soon.";
                    break;

                case 'review':
                    $messages['subject'] = 'Thank You for Your Review - ' . config('app.name');
                    $messages['lines_array']['title'] = "We appreciate your review! Your feedback helps us improve.";
                    break;

                case 'feedback':
                    $messages['subject'] = 'Feedback Received - ' . config('app.name');
                    $messages['lines_array']['title'] = "Thank you for sharing your feedback with us.";
                    break;

                case 'suggestion':
                    $messages['subject'] = 'Suggestion Noted - ' . config('app.name');
                    $messages['lines_array']['title'] = "Thank you for your suggestion. We value your input and it has been forwarded to our team.";
                    break;

                case 'complaint':
                    $messages['subject'] = 'Complaint Received - ' . config('app.name');
                    $messages['lines_array']['title'] = "We’re sorry to hear about your experience. Your complaint has been recorded and will be reviewed carefully.";
                    break;

                default:
                    $messages['subject'] = 'Response Received - ' . config('app.name');
                    $messages['lines_array']['title'] = "Thank you for reaching out to us.";
            }

            // Send mail to user if email exists
            if (!empty($validate['email'])) {
                (new AnonymousNotifiable)
                    ->route('mail', $validate['email'])
                    ->notify(new CommonEmailNotification($messages));
            }

            // Send email to Super Admin also
            $superAdmin = User::where('role', 'super_admin')->first();

            if ($superAdmin) {

                $adminMessages = [
                    'subject' => 'New Feedback Received - ' . config('app.name'),
                    'greeting-text' => 'Hello Admin,',
                    'lines_array' => [
                        'title' => 'A new feedback has been submitted on the platform.',
                        'body-text' => 'Below are the submitted details:',
                        'special_Submitted_By' => $validate['name'] ?? 'Guest User',
                        'special_Email' => $validate['email'] ?? 'N/A',
                        'special_Type' => ucfirst($validate['type']),
                        'special_Title' => $validate['title'],
                        'special_Message' => $validate['message'],
                        'special_Rating' => $validate['rating'] ?? 'N/A',
                    ],
                    'additional-info' => "Please review it from the dashboard."
                ];

                $superAdmin->notify(new CommonEmailNotification($adminMessages));
            }

            $response = [
                "message" => "Feedback Saved Successfully.",
                "status" => 200,
                "success" => true,
            ];
        }
        return response()->json($response);
    }
}
