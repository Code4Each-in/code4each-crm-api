<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use GrahamCampbell\ResultType\Success;

class WordpressMenusController extends Controller
{
    /**
     * THIS METHOS IS FOR CREATING WORDPRESS MENUS
     */

    public function getWordpressMenus(Request $request) {
        $websiteUrl = $request->input('website_url');
        $getApiUrl = $websiteUrl . '/wp-json/v1/get-menus';
        $getMenusResponse = Http::get($getApiUrl);
        
        if ($getMenusResponse->successful()) {
            $response['response'] =$getMenusResponse->json()['data'];
            $response['status'] = $getMenusResponse->status();
            $response['success'] = true;
        }
            else{
                $response['response'] = $getMenusResponse->json();
                $response['status'] = 400;
                $response['success'] = false;
            }
        return $response;
    
    }

    /**
     * ADD POST MENUS
     */

     public function postWordpressMenus(Request $request) {
        $response = [
            'success' => false,
            'status' => 400,
        ];

        $validator = Validator::make($request->all(), [
            'website_url'=>'required',
            'menu_data.name' => 'required',
            'menu_data.value' => 'required', 
            'menu_data.menu_type' => 'required', 
            'menu_data.menu_value_type' => 'required',

        ]);

        if ($validator->fails()) {
           return response()->json(['response' => $validator->errors(),'status' => 400, 'success'=> false], 400);
        }

        $validate = $validator->valid();
        
        $postData = $validate['menu_data'];
       
        //INSERT DATA HERE
        $websiteUrl = $request->input('website_url');
        $postApiUrl = $websiteUrl . '/wp-json/v1/add-menu';
        $postMenuResponse = Http::post($postApiUrl, $postData);
        if($postMenuResponse->successful()){
            //dump($postMenuResponse->json()); dd('---');
            $response['response'] =$postMenuResponse->json()['success'];
            $response['status'] = $postMenuResponse->status();
            $response['success'] = true;
        } else{
            $response['response'] = $postMenuResponse->json();
            $response['status'] = 400;
            $response['success'] = false;
        }
        return $response;

    }

    /**
     * UPDATE MENUS
     */

     public function updateWordpressMenu(Request $request){
        $response = [
            'success' => false,
            'status' => 400,
        ];

        $validator = Validator::make($request->all(), [
            'website_url'=>'required',
            'menu_data.id' => 'required',
            'menu_data.name' => 'required',
            'menu_data.value' => 'required', 
            'menu_data.menu_value_type' => 'required',

        ]);

        if ($validator->fails()) {
           return response()->json(['response' => $validator->errors(),'status' => 400, 'success'=> false], 400);
        }

        $validate = $validator->valid();
        
        $updateData = $validate['menu_data'];

        //UPDATE DATA RELATED TO MENUS
        $websiteUrl = $request->input('website_url');
        $updateApiUrl = $websiteUrl . '/wp-json/v1/edit-menu';
        $updateMenuResponse = Http::post($updateApiUrl, $updateData);
        if($updateMenuResponse->successful()){
            $response['response'] =$updateMenuResponse->json()['success'];
            $response['status'] = $updateMenuResponse->status();
            $response['success'] = true;
        } else{
            $response['response'] = $updateMenuResponse->json();
            $response['status'] = 400;
            $response['success'] = false;
        }
        return $response;
    }

    /**
     * DELETE MENU USING MENU ID
     */

    public function deleteWordpressMenu(Request $request){
        $response = [
            'success' => false,
            'status' => 400,
        ];

        $validator = Validator::make($request->all(), [
            'website_url'=>'required',
            'menu_data.id' => 'required',

        ]);

        if ($validator->fails()) {
            return response()->json(['response' => $validator->errors(),'status' => 400, 'success'=> false], 400);
         }
 
        $validate = $validator->valid();
        $deleteData = $validate['menu_data'];
       //dump($deleteData); dd('hdsgfhdsg');
         //UPDATE DATA RELATED TO MENUS
        $websiteUrl =  $validate['website_url'];
        $deleteApiUrl = $websiteUrl . '/wp-json/v1/delete-menu';
        $deleteMenuResponse = Http::delete($deleteApiUrl, $deleteData);
        if($deleteMenuResponse->successful()){
            $response['response'] =$deleteMenuResponse->json()['success'];
            $response['status'] = $deleteMenuResponse->status();
            $response['success'] = true;
        } else{
            $response['response'] = $deleteMenuResponse->json();
            $response['status'] = 400;
            $response['success'] = false;
        }
        return $response;
    }

    /**
     * CHANGE MENU POSITON
     */
    public function changeMenuPosition(Request $request){
        $response = [
            'success' => false,
            'status' => 400,
        ];

        $validator = Validator::make($request->all(), [
            'website_url' => 'required',
            'menu_data' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['response' => $validator->errors(),'status' => 400, 'success'=> false], 400);
        }

        $validate = $validator->valid();
        $menuData = $validate['menu_data'];

        $websiteUrl =  $validate['website_url'];
        $changePositionApiUrl = $websiteUrl . '/wp-json/v1/change-menu-position';
        $changeMenuResponse = Http::post($changePositionApiUrl, $menuData);

        if($changeMenuResponse->successful()){
            $response['response'] =$changeMenuResponse->json()['success'];
            $response['status'] = $changeMenuResponse->status();
            $response['success'] = true;
        } else{
            $response['response'] = $changeMenuResponse->json();
            $response['status'] = 400;
            $response['success'] = false;
        }
        return $response;

    }

}  