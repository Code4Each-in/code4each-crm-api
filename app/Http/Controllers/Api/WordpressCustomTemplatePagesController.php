<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use App\Models\Component;
use App\Models\ComponentFormFields;
use Illuminate\Support\Arr;

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

                $formFieldsArray[] = [
                    "field_name"    => $formField->field_name,
                    "field_type"    => $formField->field_type,
                    "default_value" => null,
                    "default_meta1" => $formField->meta_key1,
                    "default_meta2" => $formField->meta_key2,
                    "value"         => null,
                    "meta1"         => null,
                    "meta2"         => null,
                    "form_id"       => null,
                ];
            }

            // 3. Collect field names
            $fieldNames = array_map(function ($item) {
                return $this->normalizeFieldName($item['field_name'] ?? null);
            }, $formFieldsArray);
            $fieldNames = array_filter($fieldNames); 

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
                    $field_name = $field_name = $this->normalizeFieldName($formField['field_name']);
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

    private function normalizeFieldName($fieldName) {
        // Replace '-img' with '-image'
        $fieldName = preg_replace('/-img(\d*)$/', '-image$1', $fieldName);
        return $fieldName;
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
            'type'        => 'required',
            'component_id' => 'required',
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
        $fileTypes = ['header', 'about_section', 'service_section', 'footer', 'common_text'];
        if (in_array($request->type, $fileTypes) && $request->hasFile('file')) {
            $file = $request->file('file');
            // Save file to /storage/app/public/HeaderImages
            $path = $file->store('public/HeaderImages');
            $fileUrl = asset(str_replace('public', 'storage', $path));
            // Override "value" with uploaded file URL
            $validatedData['value'] = $fileUrl;
        }

        // === Forward request to WordPress API ===
        $websiteUrl = $request->input('website_url');
        $postApiUrl = $websiteUrl . '/wp-json/v1/add-customcomponentsfieldvalues';

        $http = Http::asMultipart();

        // Only attach raw file if NOT type header
        if ($request->hasFile('file') && !in_array($request->type, $fileTypes)) {
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
    
    /**
     * THIS METHOD IS FOR DELETING WORDPRESS CUSTOM COMPONENTS
     */
    public function deleteCustomComponents(Request $request){
        $response = [
            'success' => false,
            'status' => 400,
        ];

        $validator = Validator::make($request->all(), [
            'component_unique_id'=>'required',
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
        $postApiUrl = $websiteUrl . '/wp-json/v1/delete-customcomponents';
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
     * THIS METHOD IS FOR FETCHING WORDPRESS CUSTOM COMPONENTS BY TYPE
     */
    public function getCustomComponentsByType()
    {
        $response = [
            "success" => false,
            "status"  => 400,
        ];
        $type = request()->input('type');
        if($type){
            $componentData = Component::where('type',$type)->where('status','active')->get();
            $componentDetail = [];
            foreach($componentData as $data){
               $component = [];
               $component['id'] = $data->id;
               $component['component_unique_id'] = $data->component_unique_id;
               $component['preview'] = '/storage/'.$data->preview;
               $component['type'] = $data->type;
               $component['category'] = $data->category;
               $componentDetail[] = $component;
            }
        }else{
             $componentData = Component::where('status','active')->get();
             $componentDetail = [];
             foreach($componentData as $data){
                $component = [];
                $component['id'] = $data->id;
                $component['component_unique_id'] = $data->component_unique_id;
                $component['preview'] = '/storage/'.$data->preview;
                $component['type'] = $data->type;
                $component['category'] = $data->category;
                $componentDetail[] = $component;
             }
        }
        if($componentDetail){
            $response = [
                "message" => "Result Fetched Successfully.",
                'component' => $componentDetail,
                "success" => true,
                "status"  => 200,
            ];
        }

        return $response;
    }

    /**
     * THIS METHOD IS FOR REPLACING WORDPRESS CUSTOM COMPONENT
     */
    public function replaceCustomComponent(Request $request){
        $response = [
            'success' => false,
            'status' => 400,
        ];

        $validator = Validator::make($request->all(), [
            'new_component_id'=>'required',
            'old_component_id'=>'required',
            'type'=>'required',
            'website_domain'=>'required',
            'page_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'response' => $validator->errors(),
                'status' => 400,
                'success' => false
            ], 400);
        }

        $validatedData = $validator->validated();

        $component = Component::with(['formFields' => function ($query) {
            $query->orderBy('field_position', 'asc');
        }])
            ->where('component_unique_id', $validatedData['new_component_id'])
            ->where('status', 'active')
            ->first();

        if (!$component) {
            return response()->json(['errors' => "No such Component found."], 400);
        }

        if(!Component::where('component_unique_id', $validatedData['new_component_id'] )->exists()){
            return response()->json(['errors' => "No such Component found."], 400);
        }
        $websiteUrl = $request->input('website_domain');
        $oldComponentUniqueId['component_unique_id'] = $validatedData['old_component_id'];

        $deleteComponentResponse = WordpressComponentController::deleteComponent($websiteUrl,$oldComponentUniqueId);
        if($deleteComponentResponse['success'] == true && $deleteComponentResponse['response']['status'] == 200 ){
            $componentPosition = $deleteComponentResponse['response']['data']['position'];
            $componentDependencies = $component->dependencies;

            $formFieldsArray  = [];
            $formFieldsValues = [];
            foreach ($component->formFields as $formField) {
                $defaultValue = $formField->default_value;

                // Special handling for image fields
                if ($formField->field_type === 'image' && $defaultValue) {
                    $defaultValue = str_replace('Components/', '', $defaultValue);
                }

                $normalizedFieldName = $this->normalizeFieldName($formField->field_name);

                $fieldEntry = [
                    "field_name"    => $formField->field_name,
                    "field_type"    => $formField->field_type,
                    "default_value" => $defaultValue,
                    "default_meta1" => $formField->meta_key1,
                    "default_meta2" => $formField->meta_key2,
                ];

                $formFieldsArray[] = $fieldEntry;

                if ($normalizedFieldName) {
                    $formFieldsValues[$normalizedFieldName] = $fieldEntry;
                }
            }
            $component = [
                'component_detail' => [
                    'component_name' => $component->component_name,
                    'path' => $component->path,
                    'type' => $component->type,
                    'position' => $componentPosition,
                    'component_unique_id' => $component->component_unique_id,
                    'status' =>  $component->status,
                ],
                'component_dependencies' => $componentDependencies,
                'component_meta_fields' => $formFieldsValues,
                'old_component_id' => $validatedData['old_component_id'],
                'page_id' => $validatedData['page_id'],
            ];
            $postApiUrl = $websiteUrl . '/wp-json/v1/replace-custom-component';
            $wpResponse = Http::post($postApiUrl, $component);

            if ($wpResponse->successful()) {
                $response['response'] = $wpResponse->json();
                $response['status'] = $wpResponse->status();
                $response['success'] = true;
            } else {
                $response['response'] = $wpResponse->json() ?? 'Failed to post';
                $response['status'] = $wpResponse->status() ?? 400;
                $response['success'] = false;
            }
        }
        return response()->json($response, $response['status']);
    }

    /**
     * THIS METHOD IS FOR FETCHING WORDPRESS CUSTOM COMPONENTS FOR NEW SECTION
     */
    public function getCustomComponentsForNewSection()
    {
        $response = [
            "success" => false,
            "status"  => 400,
        ];

        $types = Arr::flatten(request()->input('type', []));
        $excludeIds = Arr::flatten(request()->input('exclude_ids', []));

        if (!empty($types)) {
            $componentData = Component::whereIn('type', $types)
                ->where('status', 'active')
                ->whereNotIn('component_unique_id', $excludeIds)
                ->get();

            $componentDetail = [];
            foreach ($componentData as $data) {
                $component = [];
                $component['id'] = $data->id;
                $component['component_unique_id'] = $data->component_unique_id;
                $component['preview'] = '/storage/' . $data->preview;
                $component['type'] = $data->type;
                $component['category'] = $data->category;
                $componentDetail[] = $component;
            }

            if (!empty($componentDetail)) {
                $response = [
                    "message" => "Components fetched successfully.",
                    "component" => $componentDetail,
                    "success" => true,
                    "status" => 200,
                ];
            } else {
                $response = [
                    "message" => "No components available for the selected types.",
                    "component" => [],
                    "success" => true,
                    "status" => 200,
                ];
            }
        } else {
            $response = [
                "message" => "No type specified.",
                "component" => [],
                "success" => false,
                "status" => 400,
            ];
        }

        return $response;
    }

    /**
     * THIS METHOD IS FOR ADDING NEW CUSTOM COMPONENT SECTION
     */
    public function addNewCustomComponentSection(Request $request)
    {
        $response = [
            'success' => false,
            'status' => 400,
        ];

        // Validate required fields
        $validator = Validator::make($request->all(), [
            'previous_component_id' => 'required',
            'new_component_id' => 'required',
            'website_domain'   => 'required',
            'page_id'          => 'required',
            'previous_section_position' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'response' => $validator->errors(),
                'status' => 400,
                'success' => false
            ], 400);
        }

        $validatedData = $validator->validated();

        // Fetch the component along with its form fields
        $component = Component::with(['formFields' => function ($query) {
            $query->orderBy('field_position', 'asc');
        }])->where('component_unique_id', $validatedData['new_component_id'])
        ->where('status', 'active')
        ->first();

        if (!$component) {
            return response()->json(['errors' => "No such Component found."], 400);
        }

        $componentFormFields = $component->formFields;

        // Prepare form fields array and normalized key => value mapping
        $formFieldsArray = [];
        $formFieldsValues = [];

        foreach ($componentFormFields as $formField) {
            $defaultValue = $formField->default_value;
            
            // Special handling for image fields to remove 'Components/' prefix
            if ($formField->field_type === 'image' && $defaultValue) {
                $defaultValue = str_replace(
                    'Components/',
                    '',
                    $defaultValue
                );
            }

            // Normalize field name
            $normalizedFieldName = $this->normalizeFieldName($formField->field_name);

            // Build the single field entry
            $fieldEntry = [
                "field_name"    => $formField->field_name,
                "field_type"    => $formField->field_type,
                "default_value" => $defaultValue,
                "default_meta1" => $formField->meta_key1,
                "default_meta2" => $formField->meta_key2,
            ];

            // Push into array of all fields
            $formFieldsArray[] = $fieldEntry;

            // Map normalized name → single entry
            if ($normalizedFieldName) {
                $formFieldsValues[$normalizedFieldName] = $fieldEntry;
            }
        }

        // Prepare component payload for WordPress
        $componentPayload = [
            'component_detail' => [
                'component_name'      => $component->component_name,
                'path'                => $component->path,
                'type'                => $component->type,
                'component_unique_id' => $component->component_unique_id,
                'status'              => $component->status,
                'position'            => $validatedData['previous_section_position'] + 1,
            ],
            'component_dependencies' => $component->dependencies,
            'component_meta_fields'  => $formFieldsValues,
            'page_id'                => $validatedData['page_id'],
        ];

        // Send request to WordPress API
        $postApiUrl = $validatedData['website_domain'] . '/wp-json/v1/add-new-custom-component-section';
        $wpResponse = Http::post($postApiUrl, $componentPayload);

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
     * THIS METHOD IS FOR ADDING GLOBAL SWITCH VALUE
     */
    public function addGobalSwitchValue(Request $request){
        $response = [
            'success' => false,
            'status' => 400,
        ];

        $validator = Validator::make($request->all(), [
            'type'  => 'required',
            'value'       => 'required',
            'website_domain' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'response' => $validator->errors(),
                'status'   => 400,
                'success'  => false
            ], 400);
        }

        $validatedData = $validator->validated();

        $websiteUrl = $request->input('website_domain');
        $postApiUrl = $websiteUrl . '/wp-json/v1/add-global-switch-value';
        $wpResponse = Http::post($postApiUrl, $validatedData);

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
