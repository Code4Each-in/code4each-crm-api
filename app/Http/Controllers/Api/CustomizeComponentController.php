<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\ComponentController;
use App\Models\Component;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\WordpressComponentController;
use App\Models\ComponentColorCombination;
use App\Models\FontFamily;
use Illuminate\Support\Facades\Http;

class CustomizeComponentController extends Controller
{
    private $wordpressComponentClass;
    public function __construct()
    {
       $this->wordpressComponentClass = new WordpressComponentController();
    }

    public function fetchComponent()
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
    public function updateComponent(Request $request)
    {
        $response = [
            "success" => false,
            "status"  => 400,
        ];

        $validator = Validator::make($request->all(), [
            'website_url' => 'required|url',
            'component_unique_id_old' => 'required',
            'component_unique_id_new' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $validated = $validator->valid();
        if(!Component::where('component_unique_id', $validated['component_unique_id_new'] )->exists()){
            return response()->json(['errors' => "No such Component found."], 400);
        }
        $website_url = $validated['website_url'];
        $oldComponentUniqueId['component_unique_id'] = $validated['component_unique_id_old'];
        $newComponentUniqueId = $validated['component_unique_id_new'];
        $deleteComponentResponse = WordpressComponentController::deleteComponent($website_url,$oldComponentUniqueId);
        if($deleteComponentResponse['success'] == true && $deleteComponentResponse['response']['status'] == 200 ){
            $componentPosition = $deleteComponentResponse['response']['data']['position'];
            $componentData = Component::where('component_unique_id',$newComponentUniqueId)->where('status','active')->first();
            $componentDependencies = $componentData->dependencies;
            $component = [
                'component_detail' => [
                    'component_name' => $componentData->component_name,
                    'path' => $componentData->path,
                    'type' => $componentData->type,
                    'position' => $componentPosition,
                    'component_unique_id' => $componentData->component_unique_id,
                    'status' =>  $componentData->status,
                ],
                'component_dependencies' => $componentDependencies ,
            ];
            $addComponentUrl = $website_url . 'wp-json/v1/component';
            $componentResponse = Http::post($addComponentUrl, $component);
            if ($componentResponse->successful()) {
                $response['response'] = $componentResponse->json();
                $response['status'] = $componentResponse->status();
            } else {
                $response['status'] = $componentResponse->status();
                $response['response']  = $componentResponse->json();
                $response['success'] = false;
            }
        }


        return $response;

    }
    public function getColorCombination()
    {
        $website_url = request()->input('website_url');
        $colorCombinations = ComponentColorCombination::all();
        // $activeColorCombinationId = 1;
        if($website_url){
            $typeKey = 'c4e_default_color_id';
            $defaultColorResponse = $this->wordpressComponentClass->getDefaultColorOrFont($website_url , $typeKey);
            // dd($defaultColorResponse['response']['data'][$typeKey]);
            $activeColorCombinationId = $defaultColorResponse['response']['data'][$typeKey];

        }

        $combinationData = [];
        foreach ($colorCombinations as $colorData) {
            $activeFlag = false;
            if($activeColorCombinationId != '' && $colorData->id == $activeColorCombinationId){
                $activeFlag = true;
            }
            $color = [
                "id" =>  $colorData->id,
                "title" => $colorData->title,
                "colors" => [
                    $colorData->color_1,
                    $colorData->color_2,
                    $colorData->color_3,
                    $colorData->color_4,
                    $colorData->color_5,
                    $colorData->color_6
                ],
                "active" => $activeFlag,
            ];
            $combinationData[] = $color;
        }
        return $combinationData;
    }
    public function updateColorCombination(Request $request)
    {
        $response = [
            "status" => 400,
            "success" => false,
        ];

        $validator = Validator::make($request->all(), [
            'website_url' => 'required|url',
            'color_id' => 'required',
        ]);
        $website_url = $request->website_url;
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $validated = $validator->valid();
        if(!ComponentColorCombination::where('id', $validated['color_id'] )->exists()){
            return response()->json(['errors' => "No such Color Available."], 400);
        }
        $typeKey = 'c4e_default_color_id';
        $typeValue[$typeKey] =$validated['color_id'];
        $defaultColorResponse = $this->wordpressComponentClass->setDefaultColorOrFont($website_url , $typeValue);
        if($defaultColorResponse['success']  && $defaultColorResponse['response']['status'] == 200 )
        {
            $addGlobalColorsUrl = $website_url . 'wp-json/v1/change_global_variables';
            $ColorsData = ComponentColorCombination::where('id',$validated['color_id'])->first();
            $color = [
                "c4e_primary_color_1" => [
                    "value" => $ColorsData->color_1,
                    "type" => "color"
                ],
                "c4e_primary_color_2" => [
                    "value" => $ColorsData->color_2,
                    "type" => "color"
                ],
                "c4e_primary_color_3" => [
                    "value" => $ColorsData->color_3,
                    "type" => "color"
                ],
                "c4e_primary_color_4" => [
                    "value" => $ColorsData->color_4,
                    "type" => "color"
                ],
                "c4e_primary_color_5" => [
                    "value" => $ColorsData->color_5,
                    "type" => "color"
                ],
                "c4e_primary_color_6" => [
                    "value" => $ColorsData->color_6,
                    "type" => "color"
                ]
            ];
            $addGlobalColorsResponse = Http::post($addGlobalColorsUrl,$color);
            if($addGlobalColorsResponse->successful()){
                  $response = [
                        "message" => "Colors Combination Updated Successfully.",
                        "status" => $defaultColorResponse['status'],
                        "success" => true,
                    ];
            }else{
                $response['response'] = $addGlobalColorsResponse->json();
                $response['status'] = 400;
                $response['success'] = false;
            }

        }
        return response()->json($response);
    }

    public function getFont()
    {
        $response = [
            "success" => false,
            "status" => 400,
        ];
        $website_url = request()->input('website_url');
        $activeFontId = '';
        if($website_url){
            $typeKey = 'c4e_font_family_id';
            $defaultFontResponse = $this->wordpressComponentClass->getDefaultColorOrFont($website_url , $typeKey);
            $activeFontId = $defaultFontResponse['response']['data'][$typeKey];
        }
        $fonts = FontFamily::all();
        $fontData = [];
        foreach ($fonts as $font) {
            $activeFlag = false;
            if($activeFontId && $font->id == $activeFontId){
                $activeFlag = true;
            }
            $fontArray = [
                "id" => $font->id,
                "name" => $font->name,
                "preview" =>  '/storage/' .$font->preview_image,
                "active" => $activeFlag,
            ];
            $fontData[] = $fontArray;
        }

                // Sort the $fontData array based on the "active" flag
        usort($fontData, function ($a, $b) {
            // Compare "active" flag in reverse order (true first)
            return ($b['active'] === true) - ($a['active'] === true);
        });

        $response = [
            "message" => "Record Fetched SuccessFully.",
            "status" => 200,
            "success" => true,
            "fonts" => $fontData,
        ];

        return $response;
    }
    public function updateFont(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'website_url' => 'required|url',
            'font_id' => 'required',
        ]);
        $website_url = $request->website_url;
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $validated = $validator->valid();
        if(!FontFamily::where('id', $validated['font_id'] )->exists()){
            return response()->json(['errors' => "Font is Unavailable."], 400);
        }
        $typeKey = 'c4e_font_family_id';
        $typeValue[$typeKey] =$validated['font_id'];
        $updateFontResponse = $this->wordpressComponentClass->setDefaultColorOrFont($website_url , $typeValue);
        if($updateFontResponse['success']  && $updateFontResponse['response']['status'] == 200 )
        {
           $updateFontFamilyResponse = $this->wordpressComponentClass->addWordpressFontFamily($website_url, $validated['font_id']);
           if($updateFontFamilyResponse['success'] && $updateFontFamilyResponse['response']['status'] == 200){
                $response = [
                    "message" => "Font Family Updated Successfully.",
                    "status" => $updateFontFamilyResponse['response']['status'],
                    "success" => true,
                ];
           }else{
            $response['response'] =$updateFontFamilyResponse['response'];
            $response['status'] = 400;
            $response['success'] = false;
           }

        }
        return $response;
    }

    public function updateComponentPosition(Request $request)
    {
        $response = [
            "success" => false,
            "status" => 400,
        ];
        $updatedPositions = $request->updated_positions;
        $website_url = $request->website_url;
        if(!$updatedPositions){
            return response()->json(['errors' => "updated positions required."], 400);
        }
        if(!$website_url){
            return response()->json(['errors' => "website url is required."], 400);
        }
        $updatePositionsResponse = $this->wordpressComponentClass->updateComponentPosition($website_url , $updatedPositions);
        if($updatePositionsResponse['success']  && $updatePositionsResponse['response']['status'] == 200 ){
            $response = [
                "message" => "Component Positions Updated Successfully.",
                "success" => true,
                "status"  => 200,
            ];
        }

        return response()->json($response);
    }
}
