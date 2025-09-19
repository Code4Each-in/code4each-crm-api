<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use App\Models\Component;
use App\Models\ComponentFormFields;

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
            'website_domain'=>'required',
            'status' => 'nullable|in:publish,draft',
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

    /**
     * THIS METHOD IS FOR FETCHING WORDPRESS CUSTOM COMPONENTS FIELDS VALUE
     */
    public function getCustomComponentsAndFieldValues(Request $request)
    {
        $response = [
            'success' => false,
            'status'  => 400,
        ];

        $validated = $request->all();

        $websiteUrl   = $validated['website_domain'];
        $page_id      = $validated['page_id'];
        $componentIds = $validated['component_ids'] ?? [];

        $allComponentsData = [];

        foreach ($componentIds as $key => $componentUniqueId) {
            // 1. Get component + fields from DB
            $component = Component::with([
                'formFields' => function ($query) {
                    $query->orderBy('field_position', 'asc');
                }
            ])->where('component_unique_id', $componentUniqueId)
            ->select('id', 'type', 'category', 'component_unique_id')
            ->first();

            if (!$component) {
                continue;
            }

            $componentFormFields = $component->formFields;
            $formFieldsArray     = [];

            // 2. Build default field structure
            foreach ($componentFormFields as $formField) {
                $defaultValue = $formField->default_value;
                if ($formField->field_type == 'image' && $formField->default_value != null) {
                    $defaultValue = '/storage/' . $defaultValue;
                }

                $formFieldsArray[] = [
                    "field_name"    => $formField->field_name,
                    "field_type"    => $formField->field_type,
                    "default_value" => $formField->field_type === 'image' ? null : $formField->default_value,
                    "default_meta1" => $formField->meta_key1,
                    "default_meta2" => $formField->meta_key2,
                    "value"         => null,
                    "meta1"         => null,
                    "meta2"         => null,
                    "form_id"       => null,
                ];
            }

            // 3. Collect field names
            $fieldNames = array_filter(array_map(function ($item) {
                return $item['field_name'] ?? null;
            }, $formFieldsArray));

            // 4. Call WordPress API (send as query params)
            $getApiUrl = $websiteUrl . '/wp-json/v1/get-custom-components-and-fieldvalues';
            $getPagesValueResponse = Http::get($getApiUrl, [
                'page_id'    => $page_id,
                'fields'    => json_encode($fieldNames),
                'component'  => $componentUniqueId,
            ]);

            // 5. Merge WP response if successful
            if ($getPagesValueResponse->successful()) {
                $jsonData = $getPagesValueResponse->json();
                $replacementArray = $jsonData['data'] ?? [];

                $formFieldsArray = array_map(function ($formField) use ($replacementArray) {
                    $field_name = $formField['field_name'];
                    if (isset($replacementArray[$field_name])) {
                        $formField['value']   = $replacementArray[$field_name]['value'] ?? null;
                        if ($formField['field_type'] === 'image') {
                            $formField['default_value'] = $formField['value'];
                        }
                        $formField['meta1']   = $replacementArray[$field_name]['meta1'] ?? null;
                        $formField['meta2']   = $replacementArray[$field_name]['meta2'] ?? null;
                        $formField['form_id'] = $replacementArray[$field_name]['form_id'] ?? null;
                        $formField['page_id'] = $replacementArray[$field_name]['page_id'] ?? null;
                    }
                    return $formField;
                }, $formFieldsArray);

                $allComponentsData[] = [
                    'component_unique_id' => $component->component_unique_id,
                    'type'                => $component->type,
                    'category'            => $component->category,
                    'fields'              => $formFieldsArray,
                ];
            }
        }

        // 6. Final response
        return response()->json([
            "message" => "Components & Fields fetched successfully.",
            'data'    => $allComponentsData,
            'success' => true,
            'status'  => 200,
        ]);
    }

    /**
     * THIS METHOD IS FOR SAVING WORDPRESS CUSTOM COMPONENTS FIELDS VALUE
     */
    public function addCustomComponentsFieldValues(Request $request)
    {
        $response = [
            'success' => false,
            'status' => 400,
        ];

        $validator = Validator::make($request->all(), [
            'page_id'     => 'required',
            'field_name'  => 'required',
            'value'       => 'required',
            'website_url' => 'required',
            'type'        => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'response' => $validator->errors(),
                'status'   => 400,
                'success'  => false
            ], 400);
        }

        $validatedData = $validator->validated();

        // === Handle header type file upload locally ===
        if ($request->type === 'header' && $request->hasFile('file')) {
            $file = $request->file('file');

            // Save file to /storage/app/public/HeaderImages
            $path = $file->store('public/HeaderImages');

            // Generate public URL (requires `php artisan storage:link`)
            $fileUrl = asset(str_replace('public', 'storage', $path));

            // Override "value" with uploaded file URL
            $validatedData['value'] = $fileUrl;
        }

        // === Forward request to WordPress API ===
        $websiteUrl = $request->input('website_url');
        $postApiUrl = $websiteUrl . '/wp-json/v1/add-customcomponentsfieldvalues';

        $http = Http::asMultipart();

        // Only attach raw file if NOT type header
        if ($request->hasFile('file') && $request->type !== 'header') {
            $file = $request->file('file');
            $fieldName = $request->input('field_name');
            $http->attach(
                $fieldName,
                file_get_contents($file->getRealPath()),
                $file->getClientOriginalName()
            );
        }

        // Send request to WordPress API
        $wpResponse = $http->post($postApiUrl, $validatedData);

        if ($wpResponse->successful()) {
            $response['response'] = $wpResponse->json();
            $response['status']   = $wpResponse->status();
            $response['success']  = true;
        } else {
            $response['response'] = $wpResponse->json() ?? 'Failed to post';
            $response['status']   = $wpResponse->status() ?? 400;
            $response['success']  = false;
        }

        return response()->json($response, $response['status']);
    }

}
