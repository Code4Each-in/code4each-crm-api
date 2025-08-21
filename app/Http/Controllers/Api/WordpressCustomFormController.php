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
        $websiteUrl = $request->input('website_url');
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

        $websiteUrl = $request->input('website_url');
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
}
