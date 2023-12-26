<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Component;
use App\Models\ComponentFormFields;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\WordpressComponentController;
use App\Http\Requests\getFormFields;
use App\Http\Requests\UpdateFormFields;
use GrahamCampbell\ResultType\Success;
use Illuminate\Support\Facades\Validator;

class CustomComponentFieldsController extends Controller
{
    private $wordpressComponentClass;
    public function __construct()
    {
       $this->wordpressComponentClass = new WordpressComponentController();
    }

    public function getFormFields(getFormFields $request)
    {
        $response = [
            'success' => false,
            'status' => 400,
        ];

        $validated = $request->validated();

        $componentUniqueId =  $validated['component_unique_id'];
        $website_url =  $validated['website_url'];

        $component = Component::with('formFields')->where('component_unique_id', $componentUniqueId)->select('id', 'type', 'category')->first();

        $componentFormFields = $component->formFields;

        foreach ($componentFormFields as $formField) {
            $defaultValue = $formField->default_value;
            if($formField->field_type == 'image' &&  $formField->default_value != null ){
                $defaultValue = '/storage/'. $defaultValue;
            }

            $formFieldArray = [
                "field_name" =>  $formField->field_name,
                "field_type" => $formField->field_type,
                "default_value" => $defaultValue,
                "default_meta1" => $formField->meta_key1,
                "default_meta2" => $formField->meta_key2,
                "value"=> null,
                "meta1"=> null,
                "meta2"=> null,

            ];


            $formFieldsArray[] = $formFieldArray;
        }

        $fieldNames = array_filter(array_map(function($item) {
            return isset($item['field_name']) ? $item['field_name'] : null;
        }, $formFieldsArray));


        $getComponentFieldsResponse = $this->wordpressComponentClass->getInsertedComponentFields(
                $website_url,
                $fieldNames
            );
            if ($getComponentFieldsResponse['success'] && $getComponentFieldsResponse['response']['status'] == 200) {

                $replacementArray = $getComponentFieldsResponse['response']['data'];

                $formFieldsArray = array_map(function($formField) use ($replacementArray) {
                    $field_name = $formField['field_name'];
                    if (isset($replacementArray[$field_name])) {
                        $formField['value'] = $replacementArray[$field_name]['value'];
                        $formField['meta1'] = $replacementArray[$field_name]['meta1'];
                        $formField['meta2'] = $replacementArray[$field_name]['meta2'];
                    }
                    return $formField;
                }, $formFieldsArray);
            }


        $response = [
            "message" => "Detail Fetched Successfully.",
            'data' => $formFieldsArray,
            'success' => true,
            'status' => 200,

        ];

        return response()->json($response);
    }

    public function updateComponentFormFields(UpdateFormFields $request)
    {
        $response = [
            'success' => false,
            'status' => 400,
        ];

        $validated = $request->validated();
        $componentUniqueId = $validated['component_unique_id'];
        $website_url =  $validated['website_url'];

            $formFields = $validated['form_fields'];
            $component = Component::where('status','active')->where('component_unique_id', $componentUniqueId)->select('id', 'type', 'category')->first();

            if ($component) {
                $updateFieldsResponse = $this->wordpressComponentClass->insertOrUpdateComponentFields($formFields, $website_url);

                if ($updateFieldsResponse['success'] && $updateFieldsResponse['response']['status'] == 200) {
                    $response = [
                                "message" => "Record Updated Successfully.",
                                "success" => true,
                                "status" => 200,
                            ];
                }

            } else {
                $error_message = "Error while updating component form fields details";
                    $response = [
                        "success" => false,
                        "message" => $error_message,
                        "status" => 400,
                    ];
            }



        return response()->json($response);
    }

    public function getSocialLinks(Request $request)
    {
        $response = [
            'success' => false,
            'status' => 400,
        ];

        $websiteUrl = $request->input('website_url');
        if(!$websiteUrl){
            return response()->json(["error" => "website url is required."],400);
        }
        $getSocialLinksResponse = $this->wordpressComponentClass->getSocialLinks($websiteUrl);
        if ($getSocialLinksResponse['success'] && $getSocialLinksResponse['response']['status'] == 200) {
            $socialLinks = $getSocialLinksResponse['response']['data'];
            $response = [
                        "message" => "Detail Fetched Successfully.",
                        "social_links" => $socialLinks,
                        "success" => true,
                        "status" => 200,
                    ];
        }

        return response()->json($response);
    }


    public function updateSocialLinks(Request $request)
    {
        $response = [
            'success' => false,
            'status' => 400,
        ];

        $validator = Validator::make($request->all(), [
            'website_url' => 'required',
            'social_links' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $validate = $validator->valid();

        $socialLinksArray = $validate['social_links'];
        $websiteUrl = $validate['website_url'];
        $socialLinksData = [];
        foreach ($socialLinksArray as $index => $socialLink) {
            $socialLinksData[$index] = [
                "value" => $socialLink,
                "type" => "social_links"
            ];
        }

        $updateSocialLinksResponse = $this->wordpressComponentClass->updateGlobalVariables($websiteUrl , $socialLinksData);
        if($updateSocialLinksResponse['success'] && $updateSocialLinksResponse['response']['status'] == 200){
            $response = [
                "message" => "Social Links Updated Successfully.",
                'success' => true,
                'status' => 200,
            ];
        }


        return response()->json($response);
    }
}
