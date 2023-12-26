<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AgencyWebsite;
use App\Models\Component;
use App\Models\ComponentColorCombination;
use App\Models\ComponentDependency;
use App\Models\User;
use App\Models\Websites;
use App\Notifications\CommonEmailNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\WordpressComponentController;

class ComponentsControllers extends Controller
{
    private $wordpressComponentClass;
    public function __construct()
    {
       $this->wordpressComponentClass = new WordpressComponentController();
    }

    public function agencyWebsiteDetails(Request $request)
    {
        $response = [
            'success' => false,
            'status' => 400,
        ];
        $validator = Validator::make($request->all(), [
            'agency_id' => 'required',
            'category_id' => 'required',
            'others_category_name' => 'nullable',
            'description' => 'nullable|string',
            'phone' => 'nullable',
            'address' => 'required',
            'city' => 'required',
            'state' => 'required',
            'country' => 'required',
            'zip'  => 'required',
            'business_name' => 'required',
            'logo' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $validate = $validator->valid();
        try {
            DB::beginTransaction();
            //Get unassigned Website from website table
            $websitesData = Websites::where('assigned', null)->first();
            if(!$websitesData){
                return response()->json(['error' => 'An Error occur While Creating Your site. Domain may not exists related to your business name. Ask for the Support.'],500);
            }
            $phone = null;
            if(isset($validate['phone'])){
                $phone = $validate['phone'];
            }
            $description = null;
            if($validate['description']){
                $description = $validate['description'];
            }
            $othersCategoryName = null;
            if($validate['others_category_name']){
                $othersCategoryName = $validate['others_category_name'];
            }
            $agencyWebsiteDetails = AgencyWebsite::create([
                'website_category_id' => $validate['category_id'],
                'others_category_name' => $othersCategoryName,
                'phone' => $phone,
                'address' => $validate['address'],
                'city' => $validate['city'],
                'state' => $validate['state'],
                'country' => $validate['country'],
                'pin' => $validate['zip'],
                'description'  => $description,
                'agency_id' => $validate['agency_id'],
                'business_name' => $validate['business_name'],
                'created_by' => auth()->user()->id,
            ]);
            if ($request->hasFile('logo')) {
                $uploadedFile = $request->file('logo');
                $filename = time() . '_' . $uploadedFile->getClientOriginalName();
                $uploadedFile->storeAs('public/AgencyWebsiteDetails', $filename);
                $path = 'AgencyWebsiteDetails/' . $filename;
                $agencyWebsiteDetails->logo = $path;
                $agencyWebsiteDetails->save();

            }
            DB::commit();
            if($agencyWebsiteDetails->agency_id && $websitesData->website_domain){

                $agency_id = $agencyWebsiteDetails->agency_id;
                $website_domain =  $websitesData->website_domain;
                $dataToSend = [];
                $dataToSend['business_name'] = $agencyWebsiteDetails->business_name;
                $dataToSend['phone'] = $agencyWebsiteDetails->phone;
                $dataToSend['address'] = $agencyWebsiteDetails->address;
                $dataToSend['state'] = $agencyWebsiteDetails->state;
                $dataToSend['city'] = $agencyWebsiteDetails->city;
                $dataToSend['country'] = $agencyWebsiteDetails->country;
                $dataToSend['pincode'] = $agencyWebsiteDetails->pin;

                $result = $this->sendComponentToWordpress($agency_id, $website_domain ,$dataToSend);
                if ($result['success'] == true && $result['response']['status'] == 200) {
                    $websiteUrl = $result['domain'];

                    //assigned domain to agency Website
                    AgencyWebsite::where('id', $agencyWebsiteDetails->id)->update(['website_id' => $websitesData->id]);
                    //add assignee agency website id on domain
                    Websites::where('id', $websitesData->id)->update(['assigned' => $agencyWebsiteDetails->id]);

                    // send mail to user
                    $recipient = User::find($agencyWebsiteDetails->created_by);
                    $supportEmail = env('SUPPORT_EMAIL');
                    $supportPhone = env('SUPPORT_PHONE');
                    $messages = [
                        'greeting-text' => "Hey User,",
                        'subject' => 'Your Domain is Ready',
                        'additional-info' => 'Need assistance? Contact us at ' . $supportEmail . ' or SupportPhone: ' . $supportPhone . '.',
                        'lines_array' => [
                            'title' => 'Your domain is now ready for use after successfully updating your agency details. Enjoy a seamless online presence with the latest information.',
                            'body-text' => 'Here Is The Details For Your Website',
                            'special_Agency_Name' => $agencyWebsiteDetails->business_name ,
                            'special_Domain_Name' => $websiteUrl,
                        ],
                    ];
                    $recipient->notify(new CommonEmailNotification($messages));

                    $response = [
                        'message' => "Agency Website Detail Saved Successfully.",
                        'website_domain' => $website_domain,
                        'success' => true,
                        'status' => 200,
                    ];
                } else {
                    DB::rollBack();
                    $response = [
                        'message' => "An error occurred.",
                        'success' => false,
                        'status' => 400,
                    ];
                }
            }

        } catch (\Exception $e) {
            DB::rollBack();
            $errorMessage = $e->getMessage();
            $errorCode = $e->getCode();
            $errorFile = $e->getFile();
            $errorLine = $e->getLine();
            // Logging the error in log file
            \Log::error("\nError: $errorMessage\nFile: $errorFile\nLine: $errorLine \nCode:$errorCode");

            $response = [
                'message' => "An error occurred.",
                'success' => false,
                'status' => 500,
                'error' => $e->getMessage(),
            ];
        } finally {
            DB::commit();
        }
        return response()->json($response);
    }

    private function uploadLogoToWordpress($agencyId,$url)
    {
        $agencyWebsiteDetail = AgencyWebsite::where('agency_id',$agencyId)->where('status','active')->first();
        $imagePath = $agencyWebsiteDetail->logo;
        $thirdPartyUrl = $url . 'wp-json/v1/logo/';
        $imageFullPath = storage_path('app/public/' . $imagePath);
        if (file_exists($imageFullPath)) {
            $logoResponse = Http::attach(
                'logo',
                file_get_contents($imageFullPath),
                'logo.png'
            )
            ->post($thirdPartyUrl);
            \Log::info("Wordpress Logo Response: " . $logoResponse->body());
                $response = [
                     $logoResponse->json(),
                    'status' =>  $logoResponse->status(),
                ];
        } else {
            $response = [
                'message' => "Error Occurs In Uploading the Logo",

            ];
        }
        return $response;
    }

    public function sendComponentToWordpress($agency_id, $websiteUrl,$Data = false, $regenerateFlag = false)
    {
        $response = [
            'success' => false,
        ];
        $agencyWebsiteDetail = AgencyWebsite::where('agency_id',$agency_id)->first();
        $logo = $agencyWebsiteDetail->logo;
        if ($regenerateFlag) {
            $components = $this->generateComponents($agency_id, $websiteUrl);

        } else {
            $addWebsitesGlobalVariablesUrl = $websiteUrl . 'wp-json/v1/change_global_variables';
            if($Data){
                $data = [
                    "agency_name" => ["value" => $Data["business_name"]],
                    "phone" => ["phone" => $Data["phone"]],
                    "address" => ["value" => $Data["address"]],
                    "state" => ["value" => $Data["state"]],
                    "city" => ["value" => $Data["city"]],
                    "country" => ["value" => $Data["country"]],
                    "pincode" => ["value" => $Data["pincode"]]
                ];
                $addWebsitesGlobalVariablesResponse = Http::post($addWebsitesGlobalVariablesUrl,$data);
                if(!$addWebsitesGlobalVariablesResponse->successful()){
                return response()->json(['error' => 'An Error occur While Updating Details for Website.'],400);
                }

            }
            if($logo){
                $uploadLogo =  $this->uploadLogoToWordpress($agency_id, $websiteUrl);
            }
            $components = $this->generateComponents($agency_id, $websiteUrl);
        }
        $startAddComponentUrl = $websiteUrl . 'wp-json/v1/installation';
        $startAddResponse = Http::post($startAddComponentUrl, ['start' => true]);
        if ($startAddResponse->successful()) {
            $this->addWordpressGlobalColors($websiteUrl);
            //add font family to wordpress site
           $addFontFamilyResponse = $this->wordpressComponentClass->addWordpressFontFamily($websiteUrl);
           if($addFontFamilyResponse['success'] && $addFontFamilyResponse['response']['status'] == 200)
           {
                $activeFontId = $addFontFamilyResponse['response']['font_id'];
                //set  active font family name in wordpress db
                $defaultFontFamilyResponse = $this->wordpressComponentClass->setDefaultColorOrFont($websiteUrl , $activeFontId);
                // if($defaultFontFamilyResponse['success'] == false && $defaultFontFamilyResponse['response']['status'] == 400){

                // }
           }
            $position = 1;
            foreach ($components as $component) {
                $componentData = [
                    'component_detail' => [
                        'component_name' => $component['component_name'],
                        'path' => $component['path'],
                        'type' => $component['type'],
                        'position' => null,
                        'component_unique_id' => $component['component_unique_id'],
                        'status' => $component['status'],
                    ],
                    'component_dependencies' => ComponentDependency::where('component_id', $component['id'])
                        ->select('component_id', 'name', 'type', 'path', 'version')
                        ->get(),
                ];
                if ($component['type'] === 'header') {
                    $componentData['component_detail']['position'] = 1;
                } elseif ($component['type'] === 'footer') {
                    $componentData['component_detail']['position'] = count($components);
                } else {
                    $componentData['component_detail']['position'] = $position + 1;
                    $position++;
                }
                $postUrl = $websiteUrl . 'wp-json/v1/component';
                $componentResponse = Http::post($postUrl, $componentData);
                if ($componentResponse->successful()) {
                    $data['response'] = $componentResponse->json();
                    $data['status'] = $componentResponse->status();
                    $data['domain'] = $websiteUrl;
                } else {
                    $data['status'] = $componentResponse->status();
                    $data['response']  = $componentResponse->json();
                    // $data['error'] = $errorData . ' ' . $errorCode;
                    $data['success'] = false;
                }
            }
        } else {
            $data['response'] = $startAddResponse->json();
            $data['success'] = false;
            $data['status'] = 400;
        }
        $endAddComponentResponse = Http::post($startAddComponentUrl, ['start' => false]);
        if ($endAddComponentResponse->successful()) {
            $data['response'] = $endAddComponentResponse->json();
            $data['success'] = true;
        }
        $response = $data;

        return $response;
    }


    private function generateComponents($agency_id, $websiteUrl)
    {
        $agencyWebsiteDetail = AgencyWebsite::with('websiteCategory')->where('agency_id', $agency_id)->where('status', 'active')->first();
        $websiteCategory = $agencyWebsiteDetail->websiteCategory->name;
        $componentTypes = $agencyWebsiteDetail->websiteCategory->types;
        $types = explode(",",$componentTypes);
        $components = [];
        foreach ($types as $type) {

            $randomComponent = $this->getRandomComponent($type, $websiteCategory);
            if(!$randomComponent){
                return response()->json(["errors"=> "Error Occurs While Generating Random Components."]);
            }
                $randomIndex = array_rand($randomComponent);
                $randomValue = $randomComponent[$randomIndex];
            if ($randomComponent) {
                $components[] = $randomValue;
            }
        }
        return $components;
    }

    private function getRandomComponent($type, $category)
    {
        return Component::where('type', $type)
            ->where('category', 'LIKE', '%' . $category . '%')
            ->where('status','active')
            ->inRandomOrder()->get()->toArray();
    }

    public function regenerateComponents(Request $request)
    {
        $response = [
            'success' => false,
            'status' => 400,
        ];

        $validator = Validator::make($request->all(), [
            'agency_id' => 'required',
            'website_url' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }
        if (isset($request->agency_id, $request->website_url)) {
            $agency_id = $request->agency_id;
            $website_url = $request->website_url;
            $regenerate = true;
            $regenerateComponentResponse = $this->sendComponentToWordpress($agency_id,$website_url,false,$regenerate);
            if($regenerateComponentResponse['response']['status'] == 200 && $regenerateComponentResponse['success'] == true){
                $response = [
                    'message' => "Components Re-generated Successfully.",
                    'success' => true,
                    'status' => 200,
                ];
            }

        } else {
            $response = [
                'message' => "Need to Pass Required Proper Arguments.",
                'success' => false,
                'status' => 400,
            ];
        }
        return $response;
    }

    public function getActiveWordpressComponents($websiteUrl = false)
    {
        $websiteUrl = request()->input('website_url');
        if(!$websiteUrl){
            return response()->json(['error' => "website url is required to process this request."],400);
        }
        $getActiveComponentUrl = $websiteUrl . '/wp-json/v1/components';
        $getActiveComponentResponse = Http::get($getActiveComponentUrl);
            if ($getActiveComponentResponse->successful()) {
                $responseData = $getActiveComponentResponse->json();
                $response['active_components'] = $responseData['data'];
                $response['status'] = $getActiveComponentResponse->status();
                $response['success'] = true;
            }else{
                $response['response'] = $getActiveComponentResponse->json();
                $response['status'] = 400;
                $response['success'] = false;
            }
        return $response;
    }
    public function getWordpressGlobalColors()
    {
        $websiteUrl =  request()->input('website_url');
        if(!$websiteUrl){
            return response()->json(['error' => "website url is required to process this request."],400);
        }
        $getGlobalColorsUrl = $websiteUrl . 'wp-json/v1/global-colors';
        $getGlobalColorsResponse = Http::get($getGlobalColorsUrl);
            if ($getGlobalColorsResponse->successful()) {
                $responseData = $getGlobalColorsResponse->json();
                $response['colors'] = $responseData["data"];
                $response['status'] = $getGlobalColorsResponse->status();
                $response['success'] = true;
            }else{
                $response['response'] = $getGlobalColorsResponse->json();
                $response['status'] = 400;
                $response['success'] = false;
            }
        return response()->json($response);
    }
    public function addWordpressGlobalColors($websiteUrl)
    {
        $addGlobalColorsUrl = $websiteUrl . 'wp-json/v1/change_global_variables';
        $setColorIdUrl = $websiteUrl . 'wp-json/v1/update-options-value';
        $randomColorCombination = ComponentColorCombination::inRandomOrder()->first();
        $colorCombinationId['c4e_default_color_id'] =  $randomColorCombination->id;

        $color = [
            "c4e_primary_color_1" => [
                "value" => $randomColorCombination->color_1,
                "type" => "color"
            ],
            "c4e_primary_color_2" => [
                "value" => $randomColorCombination->color_2,
                "type" => "color"
            ],
            "c4e_primary_color_3" => [
                "value" => $randomColorCombination->color_3,
                "type" => "color"
            ],
            "c4e_primary_color_4" => [
                "value" => $randomColorCombination->color_4,
                "type" => "color"
            ],
            "c4e_primary_color_5" => [
                "value" => $randomColorCombination->color_5,
                "type" => "color"
            ],
            "c4e_primary_color_6" => [
                "value" => $randomColorCombination->color_6,
                "type" => "color"
            ]
        ];
        $addGlobalColorsResponse = Http::post($addGlobalColorsUrl,$color);
            if($addGlobalColorsResponse->successful()){
                $setColorsIdResponse = Http::post($setColorIdUrl,$colorCombinationId);
                if($setColorsIdResponse->successful()){
                    $response['response'] = $addGlobalColorsResponse->json();
                    $response['status'] = $addGlobalColorsResponse->status();
                    $response['success'] = true;
                }
            }else{
                $response['response'] = $addGlobalColorsResponse->json();
                $response['status'] = 400;
                $response['success'] = false;
            }
        return response()->json($response);
    }

    public function updateWordpressGlobalColors(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'website_url' => 'required',
            'colors' => 'required|array'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }
        $colorsArray = $request->colors;
        if (isset($request->website_url)) {
        $websiteUrl = $request->website_url;
        $addGlobalColorsUrl = $websiteUrl . 'wp-json/v1/change_global_variables';
            $colorsData = [];
            foreach ($colorsArray as $index => $color) {
                $colorsData[$index] = [
                    "value" => $color,
                    "type" => "color"
                ];
            }
            $addGlobalColorsResponse = Http::post($addGlobalColorsUrl,$colorsData);
            if($addGlobalColorsResponse->successful()){
                $response['response'] = $addGlobalColorsResponse->json();
                $response['data'] = $colorsData;
                $response['status'] = $addGlobalColorsResponse->status();
                $response['success'] = true;
            }else{
                $response['response'] = $addGlobalColorsResponse->json();
                $response['status'] = 400;
                $response['success'] = false;
            }
        }
        return response()->json($response);
    }

    public function fetchActiveComponentsDetail()
    {
        $response = [
            'success' => false,
            'status' => 400,
        ];

        $websiteUrl = request()->input('website_url');
        if(!$websiteUrl){
            return response()->json(['error' => "website url is required to process this request."],400);
        }
        $activeComponentsDetail =  $this->getActiveWordpressComponents($websiteUrl);
        if($activeComponentsDetail['status'] == 200 && $activeComponentsDetail['success'] == true){
            $activeComponents = $activeComponentsDetail['active_components'];
            $componentDetail = [];
            foreach ($activeComponents as $componentUniqueId) {
                //Getting Components Detail Based On Component Unique Id With the fromFields Relation using Eager Loading
                $componentsData = Component::with('formFields')->where('component_unique_id', $componentUniqueId)->first();
                $formFields = $componentsData->formFields->toArray();
                if($componentsData->preview && $formFields){
                    $components_detail = [];
                    $components_detail['id'] = $componentUniqueId;
                    $components_detail['type'] = $componentsData->type;
                    $previewPath = '/storage/'. $componentsData->preview;
                    $components_detail['preview'] = $previewPath;
                    $components_detail['form_fields'] =  $formFields ;
                    $componentDetail[] = $components_detail;
                }
            }
        }
        if($componentDetail){
            $response = [
                'message' => "Detail Fetched Successfully.",
                'success' => true,
                'status' => 200,
                'components_detail' => $componentDetail
            ];
        }

        return $response;
    }
}


