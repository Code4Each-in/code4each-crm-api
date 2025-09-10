<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class WordpressCustomTemplatePagesController extends Controller
{
    /**
     * THIS METHOD IS FOR FETCHING WORDPRESS CUSTOM TEMPLATE PAGES
     */
    public function getWordpressTemplatePages(Request $request) {
        $websiteUrl = $request->input('website_domain');
        $getApiUrl = $websiteUrl . '/wp-json/v1/get-templatepages';
        $getPagesResponse = Http::get($getApiUrl);
        
        if ($getPagesResponse->successful()) {
            $jsonData = $getPagesResponse->json();

            $response['response'] = $jsonData['pages'] ?? [];
            $response['status'] = $getPagesResponse->status();
            $response['success'] = true;
        }else{
            $response['response'] = $getPagesResponse->json();
            $response['status'] = 400;
            $response['success'] = false;
        }
        return $response;
    
    }

    /**
     * THIS METHOD IS FOR CREATING WORDPRESS CUSTOM TEMPLATE PAGES
     */
    public function addWordpressTemplatePages(Request $request){
        $response = [
            'success' => false,
            'status' => 400,
        ];

        $validator = Validator::make($request->all(), [
            'page_name'=>'required',
            'page_slug'=>'required',
            'website_domain'=>'required'
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
        $postApiUrl = $websiteUrl . '/wp-json/v1/add-templatepages';
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
     * THIS METHOD IS FOR UPDATING WORDPRESS CUSTOM TEMPLATE PAGES
     */
    public function updateWordpressTemplatePages(Request $request){
        $response = [
            'success' => false,
            'status' => 400,
        ];

        $validator = Validator::make($request->all(), [
            'page_id'=>'required',
            'page_name'=>'required',
            'page_slug'=>'required',
            'website_domain'=>'required'
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
        $postApiUrl = $websiteUrl . '/wp-json/v1/update-templatepages';
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
     * THIS METHOD IS FOR DELETING WORDPRESS CUSTOM TEMPLATE PAGES
     */
    public function deleteWordpressTemplatePages(Request $request){
        $response = [
            'success' => false,
            'status' => 400,
        ];

        $validator = Validator::make($request->all(), [
            'page_id'=>'required',
            'website_domain'=>'required'
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
        $postApiUrl = $websiteUrl . '/wp-json/v1/delete-templatepages';
        $wpResponse = Http::delete($postApiUrl, $validatedData);

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
