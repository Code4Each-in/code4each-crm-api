<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use App\Models\Component; 

class CustomSectionsController extends Controller
{

    public function addComponentSection(Request $request)
    {
        $response = [
            'success' => false,
            'status' => 400,
        ];
       
        try {
            $validator = Validator::make($request->all(), [
                'component_unique_id' => 'required',
                'website_url' => 'required',
                'position' => 'required',
            ]);
    
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 400);
            }
            $validated = $validator->valid();
            $websiteUrl = $validated['website_url'];
    
            $component = Component::where('component_unique_id', $validated['component_unique_id'])
        ->with(['dependencies'])
        ->first();
    
        if ($component) {
            $componentArray = [
                'component_detail' => [
                    'id' => $component->id,
                    'component_unique_id' => $component->component_unique_id,
                    'component_name' => $component->component_name,
                    'path' => $component->path,
                    'status' => $component->status,
                    'type' => $component->type,
                    'position' => $validated['position'],
                ],
                'component_dependencies' => $component->dependencies ? $component->dependencies->toArray() : null,
            ];
    
                $postUrl = $websiteUrl . '/wp-json/v1/add-new-component';
                $addComponentSectionResponse = Http::post($postUrl, $componentArray);
                if ($addComponentSectionResponse->successful()) {
                    $data['response'] = $addComponentSectionResponse->json();
                    $data['status'] = $addComponentSectionResponse->status();
                } else {
                    $data['status'] = $addComponentSectionResponse->status();
                    $data['response']  = $addComponentSectionResponse->json();
                    // $data['error'] = $errorData . ' ' . $errorCode;
                    $data['success'] = false;
                }
                $response = $data;
                $response['success'] = true;
            }else{
                $response = [
                    "error" => "An error occurred while retrieving the component details." 
                ];
            }
            
        } catch (\Exception $e) {
            $error = $e->getMessage();
            return response()->json(['error' => $error], 500);
        }

        return response()->json($response);

    }
}
