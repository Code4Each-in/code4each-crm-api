<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AgencyWebsite;
use App\Models\FontFamily;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WordpressComponentController extends Controller
{
    public static function deleteComponent($websiteUrl = false, $componentUniqueId = false)
    {
        if(!$websiteUrl){
            return response()->json(["error" => "website Url is required to Proceed."]);
        }
        if(!$componentUniqueId){
            return response()->json(["error" => "Component Unique Id is required to Proceed."]);
        }
        $deleteComponentUrl = $websiteUrl . '/wp-json/v1/component';
        $deleteComponentResponse = Http::delete($deleteComponentUrl,$componentUniqueId);
            if ($deleteComponentResponse->successful()) {
                // $responseData = $getActiveComponentResponse->json();
                $response['response'] =$deleteComponentResponse->json();
                $response['status'] = $deleteComponentResponse->status();
                $response['success'] = true;
            }
            else{
                $response['response'] = $deleteComponentResponse->json();
                $response['status'] = 400;
                $response['success'] = false;
            }
        return $response;
    }
    public function getDefaultColorOrFont($websiteUrl,$typeKey) {

      $bodyJson = json_encode([
                $typeKey
            ]) ;
        $getDefaultColorOrFontUrl = $websiteUrl . '/wp-json/v1/get-options-value';
        $colorOrFontValueResponse = Http::withBody($bodyJson, 'application/json')->get($getDefaultColorOrFontUrl);
            if ($colorOrFontValueResponse->successful()) {
                $response['response'] =$colorOrFontValueResponse->json();
                $response['status'] = $colorOrFontValueResponse->status();
                $response['success'] = true;
            }
            else{
                $response['response'] = $colorOrFontValueResponse->json();
                $response['status'] = 400;
                $response['success'] = false;
            }
        return $response;
    }

    public function setDefaultColorOrFont($websiteUrl,$typeValue) {

        if(!$websiteUrl){
            return response()->json(["error" => "website Url is required to Proceed."]);
        }
        if(!$typeValue){
            return response()->json(["error" => "Value is required to Update."]);
        }
          $setDefaultColorOrFontUrl = $websiteUrl . '/wp-json/v1/update-options-value';
          $setDefaultColorOrFontResponse = Http::post($setDefaultColorOrFontUrl,$typeValue);
              if ($setDefaultColorOrFontResponse->successful()) {
                  $response['response'] =$setDefaultColorOrFontResponse->json();
                  $response['status'] = $setDefaultColorOrFontResponse->status();
                  $response['success'] = true;
              }
              else{
                  $response['response'] = $setDefaultColorOrFontResponse->json();
                  $response['status'] = 400;
                  $response['success'] = false;
              }
          return $response;
      }

      public function addWordpressFontFamily($websiteUrl, $id= false)
      {
        if(!$websiteUrl){
            return response()->json(["error" => "website Url is required to Proceed."]);
        }
        if ($id) {
            $randomFontFamily = FontFamily::where('id',$id)->first();
        } else {
            $randomFontFamily = FontFamily::inRandomOrder()->first();
        }

        $fontId['c4e_font_family_id'] =  $randomFontFamily->id;
        $fontFamilyName =  $randomFontFamily->name;
        $fontFamilyData = array('c4e_font_family' =>["value" => $fontFamilyName]);
        $addFontFamilyUrl = $websiteUrl . 'wp-json/v1/change_global_variables';
        $addFontFamilyResponse = Http::post($addFontFamilyUrl,$fontFamilyData);
        if($addFontFamilyResponse->successful()){
                 $response['response'] =$addFontFamilyResponse->json();
                 $response['response']['font_id'] = $fontId;
                 $response['status'] = $addFontFamilyResponse->status();
                 $response['success'] = true;
          } else{
            $response['response'] = $addFontFamilyResponse->json();
            $response['status'] = 400;
            $response['success'] = false;
        }
        return $response;
     }

     public function agencyName($websiteUrl,$website_name)
     {
        $addWebsiteNameUrl = $websiteUrl . 'wp-json/v1/change_global_variables';

            $data = array(
                "agency_name" => array("value" => $website_name)
            );
            $addWebsiteNameResponse = Http::post($addWebsiteNameUrl,$data);
            if($addWebsiteNameResponse->successful()){
                $response['response'] =$addWebsiteNameResponse->json();
                $response['status'] = $addWebsiteNameResponse->status();
                $response['success'] = true;
         } else{
           $response['response'] = $addWebsiteNameResponse->json();
           $response['status'] = 400;
           $response['success'] = false;
       }
       return $response;
     }

     public function updateGlobalVariables($websiteUrl,$Data)
     {

        $addWebsitesGlobalVariablesUrl = $websiteUrl . 'wp-json/v1/change_global_variables';
            $addWebsitesGlobalVariablesResponse = Http::post($addWebsitesGlobalVariablesUrl,$Data);
            if($addWebsitesGlobalVariablesResponse->successful()){
                $response['response'] =$addWebsitesGlobalVariablesResponse->json();
                $response['status'] = $addWebsitesGlobalVariablesResponse->status();
                $response['success'] = true;
         } else{
           $response['response'] = $addWebsitesGlobalVariablesResponse->json();
           $response['status'] = 400;
           $response['success'] = false;
       }
       return $response;
     }

     public function uploadLogoToWordpress($logoPath,$url)
     {
        if(!$logoPath){
            return response()->json(["error" => "logo path is required to upload logo."]);
        }
        if(!$url){
            return response()->json(["error" => "url is required to upload logo."]);
        }
         $thirdPartyUrl = $url . 'wp-json/v1/logo/';
         $imageFullPath = storage_path('app/public/' . $logoPath);
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
    public function insertOrUpdateComponentFields($fieldsData, $websiteUrl)
    {
        $dataToSend = [];

        foreach ($fieldsData as $data) {
            $dataToSend[$data['field_name']] = [
                "value" => $data['field_value'],
                "type" => $data['type'],
                "field_type" => $data['field_type'],
            ];

            if (isset($data['meta1'])) {
                $dataToSend[$data['field_name']]['meta1'] = $data['meta1'];
            }

            if (isset($data['meta2'])) {
                $dataToSend[$data['field_name']]['meta2'] = $data['meta2'];
            }
        }


        $addOrUpdateComponentFieldsUrl = $websiteUrl . 'wp-json/v1/update-component-fields';
        $addOrUpdateComponentFieldsResponse = Http::post($addOrUpdateComponentFieldsUrl, $dataToSend);
        if ($addOrUpdateComponentFieldsResponse->successful()) {
            $response['response'] = $addOrUpdateComponentFieldsResponse->json();
            $response['status'] = $addOrUpdateComponentFieldsResponse->status();
            $response['success'] = true;
        } else {
            $response['response'] = $addOrUpdateComponentFieldsResponse->json();
            $response['status'] = 400;
            $response['success'] = false;
        }
        return $response;
    }

    public function getInsertedComponentFields($websiteUrl, $allFields)
    {
        $bodyJson = json_encode(
            $allFields
        );
        $getInsertedComponentFieldsUrl = $websiteUrl . '/wp-json/v1/get-component-fields';
        $componentFieldsResponse = Http::withBody($bodyJson, 'application/json')->get($getInsertedComponentFieldsUrl);
        if ($componentFieldsResponse->successful()) {
            $response['response'] = $componentFieldsResponse->json();
            $response['status'] = $componentFieldsResponse->status();
            $response['success'] = true;
        } else {
            $response['response'] = $componentFieldsResponse->json();
            $response['status'] = 400;
            $response['success'] = false;
        }
        return $response;
    }

    public function updateComponentPosition($websiteUrl, $data)
    {
        $updateComponentPositionsUrl = $websiteUrl . '/wp-json/v1/change-component-position';
        $updateComponentResponse = Http::post($updateComponentPositionsUrl,$data);
        if ($updateComponentResponse->successful()) {
            $response['response'] = $updateComponentResponse->json();
            $response['status'] = $updateComponentResponse->status();
            $response['success'] = true;
        } else {
            $response['response'] = $updateComponentResponse->json();
            $response['status'] = 400;
            $response['success'] = false;
        }
        return $response;
    }

    public function uploadComponentImages($websiteUrl, $data)
    {
        $uploadComponentImagesUrl = $websiteUrl . '/wp-json/v1/update-component-images';
        $uploadComponentImagesResponse = Http::post($uploadComponentImagesUrl,$data);
        if ($uploadComponentImagesResponse->successful()) {
            $response['response'] = $uploadComponentImagesResponse->json();
            $response['status'] = $uploadComponentImagesResponse->status();
            $response['success'] = true;
        } else {
            $response['response'] = $uploadComponentImagesResponse->json();
            $response['status'] = 400;
            $response['success'] = false;
        }
        return $response;

    }

    public function getUploadedImages($websiteUrl,$type)
    {
        $data = [
            'type' => $type
        ];
        $jsonData = json_encode($data);
        $getUploadedImagesUrl = $websiteUrl . '/wp-json/v1/get-component-images';

        $getUploadedImagesUrl = Http::withBody($jsonData, 'application/json')->get($getUploadedImagesUrl);

        if ($getUploadedImagesUrl->successful()) {
            $response['response'] = $getUploadedImagesUrl->json();
            $response['status'] = $getUploadedImagesUrl->status();
            $response['success'] = true;
        } else {
            $response['response'] = $getUploadedImagesUrl->json();
            $response['status'] = 400;
            $response['success'] = false;
        }
        return $response;

    }

    public function deleteComponentUploadedImages($websiteUrl, $data)
    {
        $deleteUploadImagesUrl = $websiteUrl . '/wp-json/v1/delete-component-images';
        $deleteUploadedImagesResponse = Http::delete($deleteUploadImagesUrl,$data);
        if ($deleteUploadedImagesResponse->successful()) {
            $response['response'] = $deleteUploadedImagesResponse->json();
            $response['status'] = $deleteUploadedImagesResponse->status();
            $response['success'] = true;
        } else {
            $response['response'] = $deleteUploadedImagesResponse->json();
            $response['status'] = 400;
            $response['success'] = false;
        }
        return $response;

    }


    public function getSocialLinks($websiteUrl) {

          $getSocialLinksUrl = $websiteUrl . '/wp-json/v1/social-links';
          $getSocialLinksResponse = Http::get($getSocialLinksUrl);
              if ($getSocialLinksResponse->successful()) {
                  $response['response'] =$getSocialLinksResponse->json();
                  $response['status'] = $getSocialLinksResponse->status();
                  $response['success'] = true;
              }
              else{
                  $response['response'] = $getSocialLinksResponse->json();
                  $response['status'] = 400;
                  $response['success'] = false;
              }
          return $response;
      }



}
