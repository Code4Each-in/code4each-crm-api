<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Notifications\CommonEmailNotification;
use Illuminate\Http\Request;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Validator;

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
        if($feedbackObj){
                // Check if it's an inquiry and an email is provided
                    if ($validate['type'] === 'inquiry' && $validate['email']) {
                        $messages = [
                            'subject' => 'Inquiry Received - ' . config('app.name'),
                            'greeting-text' => 'Hello ' . $validate['name'] . ',',
                            'additional-info' => "If you have any further questions or concerns, feel free to reply to this email.",
                            'lines_array' => [
                                'title' => 'Thank you for reaching out to us! We have received your inquiry and will get back to you as soon as possible.',
                                'body-text' =>'Here are the details of your inquiry:',
                                'special_Type:' => $validate['type'] ,
                                'special_Title:' => $validate['title'] ,
                                'special_Message:' => $validate['message'] ,
                                'special_Rating:' => $validate['rating'] ?? 'N/A',
                            ],
                        ];
                        // Send email for inquiry
                        $notifiable = new AnonymousNotifiable;

                        $notifiable->route('mail', $validate['email'])
                            ->notify(new CommonEmailNotification($messages));
                    }
            $response = [
                "message" => "FeedBack Saved Successfully.",
                "status" => 200,
                "success" => true,
            ];
        }

        return response()->json($response);
    }
}
