<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AgencyWebsite;
use App\Notifications\CommonEmailNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DeleteWebsiteController extends Controller
{
    public function deleteWebsite(Request $request)
    {
        $response = [
            'success' => false,
            'status' => 400,
        ];

        $validator = Validator::make($request->all(), [
            'agency_website_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $validated = $validator->valid();
        try {

            $agencyWebsiteId = $validated['agency_website_id'];

            // Get agency Website Detail with the detail of its user
            $agencyWebsite = AgencyWebsite::with('websiteUser')->findOrFail($agencyWebsiteId);

            //Use this as resipient of mail
            $recipient = $agencyWebsite->websiteUser;

            $agencyWebsite->delete();

            if($agencyWebsite){

                $messages = [
                    'greeting-text' => "Dear User,",
                    'subject' => 'Important Notice: Your Website Has Been Deleted',
                    'additional-info' => 'We understand the importance of your online presence and assure you that we are here to help in any way we can. Thank you for choosing [Your Company Name] for your web hosting needs.',
                    'lines_array' => [
                        'title' => 'We hope this message finds you well. We regret to inform you that your website hosted with us has been deleted. We understand the importance of your online presence, and we want to ensure that you are aware of the situation.',
                        'body-text' => 'If you did not initiate this deletion and are unsure why your website has been removed, please contact our support team immediately. We are here to assist you in recovering your site.',
                        'recovery-info' => 'Recovery Information:',
                        'special_Deleted_Site:' => '[Your Website URL]' ,
                        'special_Deletion Date:' => '[Date and Time]',
                        'addition-recovery-info' => 'Your website data will be stored in our system for the next 15 days, during which you have the opportunity to recover it. After this period, the data will be permanently erased, and recovery will no longer be possible.',
                        'recover-website' => 'Recovering Your Website:',
                        'recover-website-info' => 'If you believe this deletion was accidental, please [click here] to initiate the recovery process.
                        ',
                        'support-contact-heading' => 'Support Contact Information:',
                        'special_Email' => 'support@speedysites.in',
                        'special_Phone' => '+91-79736 30617',
                        'extra-line' => 'Our support team is available around the clock to assist you. If you have any questions or concerns regarding your website, do not hesitate to reach out.'
                    ],
                ];

                $recipient->notify(new CommonEmailNotification($messages));

                $response = [
                    'message' => 'Website Deleted Successfully.',
                    'success' => false,
                    'status'  => 200,
                ];
            }

            return response()->json($response);

        } catch (\Exception $e) {
            // Handle the case where the model is not found
            dd('AgencyWebsite not found.');
        }


        return response()->json($response);
    }
}
