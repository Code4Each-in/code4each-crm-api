<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class WordpressCustomFormController extends Controller
{
    /**
     * THIS METHOD IS FOR FETCHING WORDPRESS CUSTOM FORM DATA
     */
    public function getWordpressForms(Request $request) {
        $websiteUrl = $request->input('website_domain');
        $getApiUrl = $websiteUrl . '/wp-json/v1/get-forms';
        $getFormsResponse = Http::get($getApiUrl);
        
        if ($getFormsResponse->successful()) {
            $response['response'] =$getFormsResponse->json()['data'];
            $response['status'] = $getFormsResponse->status();
            $response['success'] = true;
        }
            else{
                $response['response'] = $getFormsResponse->json();
                $response['status'] = 400;
                $response['success'] = false;
            }
        return $response;
    
    }

    /**
     * THIS METHOD IS FOR CREATING WORDPRESS CUSTOM FORM FIELDS
     */
    public function postWordpressFormField(Request $request) {
        $response = [
            'success' => false,
            'status' => 400,
        ];

        // Validate form data
        $validator = Validator::make($request->all(), [
            'website_domain' => 'required|url',
            'name' => 'required|string',
            'fields' => 'required|array|min:1',
            'fields.*.type' => 'required|string',
            'fields.*.label' => 'required|string',
            'fields.*.placeholder' => 'nullable|string',
            'fields.*.required' => 'nullable|boolean',
            'fields.*.position' => 'nullable|integer',
            'fields.*.options' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'response' => $validator->errors(),
                'status' => 400,
                'success' => false
            ], 400);
        }

        $validatedData = $validator->validated();

        $websiteUrl = $request->input('website_domain');
        $postApiUrl = $websiteUrl . '/wp-json/v1/add-formfields';
        $wpResponse = Http::post($postApiUrl, $validatedData);

        if ($wpResponse->successful()) {
            $response['response'] = $wpResponse->json();
            $response['status'] = $wpResponse->status();
            $response['success'] = true;
        } else {
            $response['response'] = $wpResponse->json() ?? 'Failed to post';
            $response['status'] = $wpResponse->status() ?? 400;
            $response['success'] = false;
        }

        return response()->json($response, $response['status']);
    }

    /**
     * THIS METHOD IS FOR UPDATING THE STATUS OF A WORDPRESS FORM
     */
    public function updateWordpressFormStatus(Request $request) {
        $response = [
            'success' => false,
            'status' => 400,
        ];

        $validator = Validator::make($request->all(), [
            'website_domain' => 'required|url',
            'form_id' => 'required|integer',
            'status' => 'required|string|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json(['response' => $validator->errors(), 'status' => 400, 'success'=> false], 400);
        }

        $validatedData = $validator->validated();
        
        $websiteUrl = $request->input('website_domain');
        $postApiUrl = $websiteUrl . '/wp-json/v1/update-form-status';
        $updateResponse = Http::post($postApiUrl, $validatedData);

        if ($updateResponse->successful()) {
            $response['response'] = $updateResponse->json()['success'];
            $response['status'] = $updateResponse->status();
            $response['success'] = true;
        } else {
            $response['response'] = $updateResponse->json();
            $response['status'] = 400;
            $response['success'] = false;
        }

        return response()->json($response, $response['status']);
    }

    /**
     * THIS METHOD IS FOR DELETING A WORDPRESS FORM
     */
    public function deleteWordpressForm(Request $request) {
        $response = [
            'success' => false,
            'status' => 400,
        ];

        $validator = Validator::make($request->all(), [
            'website_domain' => 'required|url',
            'form_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['response' => $validator->errors(), 'status' => 400, 'success'=> false], 400);
        }

        $validatedData = $validator->validated();
        
        $websiteUrl = $request->input('website_domain');
        $postApiUrl = $websiteUrl . '/wp-json/v1/delete-form';
        $deleteResponse = Http::post($postApiUrl, $validatedData);

        if ($deleteResponse->successful()) {
            $response['response'] = $deleteResponse->json()['success'];
            $response['status'] = $deleteResponse->status();
            $response['success'] = true;
        } else {
            $response['response'] = $deleteResponse->json();
            $response['status'] = 400;
            $response['success'] = false;
        }

        return response()->json($response, $response['status']);
    }

    /**
     * THIS METHOD IS FOR UPDATING WORDPRESS FORM FIELDS
     */
    public function updateWordpressFormField(Request $request)
    {
        $response = [
            'success' => false,
            'status' => 400,
        ];

        // Validation
        $validator = Validator::make($request->all(), [
            'website_domain' => 'required|url',
            'form_id' => 'required|integer', 
            'name' => 'required|string',
            'fields' => 'required|array|min:1',
            'fields.*.type' => 'required|string',
            'fields.*.label' => 'required|string',
            'fields.*.placeholder' => 'nullable|string',
            'fields.*.required' => 'nullable|boolean',
            'fields.*.position' => 'nullable|integer',
            'fields.*.options' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'response' => $validator->errors(),
                'status' => 400,
                'success' => false
            ], 400);
        }

        $validatedData = $validator->validated();
        $websiteUrl = $request->input('website_domain');
        $postApiUrl = $websiteUrl . '/wp-json/v1/update-customfields'; 
        $wpResponse = Http::post($postApiUrl, $validatedData);

        if ($wpResponse->successful()) {
            $response['response'] = $wpResponse->json();
            $response['status'] = $wpResponse->status();
            $response['success'] = true;
        } else {
            $response['response'] = $wpResponse->json() ?? 'Failed to update';
            $response['status'] = $wpResponse->status() ?? 400;
            $response['success'] = false;
        }

        return response()->json($response, $response['status']);
    }

    /**
     * THIS METHOD IS FOR FETCHING WORDPRESS FORM ENTRIES/SUBMISSIONS
     */
    public function getWordpressFormEntries(Request $request) {
        $websiteUrl = $request->input('website_domain');
        $form_id = $request->input('form_id');
        $getApiUrl = $websiteUrl . '/wp-json/v1/get-form-submissions';
        $getFormsResponse = Http::get($getApiUrl, [
            'form_id' => $form_id
        ]);

        if ($getFormsResponse->successful()) {
            $data = $getFormsResponse->json();  
            $submissionsFlat = $data['submissions'] ?? [];

            // Step 1: Group fields by submission_id
            $groupedSubmissions = [];
            $allFields = []; // for dynamic headers

            foreach ($submissionsFlat as $item) {
                $subId = $item['submission_id'];
                $groupedSubmissions[$subId]['ID'] = $subId;

                // Use field_name as key
                $fieldName = $item['field_name'];
                $fieldValue = $item['field_value'];

                $groupedSubmissions[$subId]['fields'][$fieldName] = $fieldValue;
                $allFields[$fieldName] = true;
            }

            $headers = array_keys($allFields);

            // Step 2: Build row-wise submissions
            $rows = [];
            foreach ($groupedSubmissions as $subId => $submission) {
                $row = [
                    'ID' => $submission['ID'],
                ];

                foreach ($headers as $field) {
                    $row[$field] = $submission['fields'][$field] ?? '';
                }
                $rows[] = $row;
            }

            $response = [
                'headers' => array_merge(['ID'], $headers),
                'rows'    => $rows,
                'status'  => $getFormsResponse->status(),
                'success' => true,
            ];
        } else {
            $response = [
                'response' => $getFormsResponse->json(),
                'status'   => 400,
                'success'  => false,
            ];
        }

        return response()->json($response);
    }

}
