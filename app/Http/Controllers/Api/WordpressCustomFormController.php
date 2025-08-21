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
}
