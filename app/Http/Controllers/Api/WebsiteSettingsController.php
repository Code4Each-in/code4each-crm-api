<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AgencyWebsite;
use App\Models\Websites;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\WordpressComponentController;
use Carbon\Carbon;

class WebsiteSettingsController extends Controller
{
    private $wordpressComponentClass;
    public function __construct()
    {
       $this->wordpressComponentClass = new WordpressComponentController();
    }
    public function settings(Request $request)
    {
        $website_id = $request->input('website_id');
        if(!$website_id){
            return response()->json(["error" => "website id is required."],400);
        }
        $websiteData = Websites::with(['agencyWebsiteDetail', 'agencyWebsiteDetail.websiteCategory'])->where('id', $website_id)->first();
        $agencyWebsiteDetail = $websiteData->agencyWebsiteDetail;
        $websiteCategoryName = $agencyWebsiteDetail->websiteCategory ? $agencyWebsiteDetail->websiteCategory->name : null;
        if(!$agencyWebsiteDetail){
            return response()->json(["error" => "currently website is not assigned."],400);
        }
        $logo = null;
        if($agencyWebsiteDetail->logo){
            $logo = '/storage/'.$agencyWebsiteDetail->logo;
        }
        $websiteDetail = [
            "id" => $websiteData->id,
            "website_domain" => $websiteData->website_domain,
            "assigned" => $websiteData->assigned,
            "agency_website_detail" => [
                "id" => $agencyWebsiteDetail->id,
                "business_name" => $agencyWebsiteDetail->business_name,
                "website_category_id" => $agencyWebsiteDetail->website_category_id,
                "website_category_name" => $websiteCategoryName,
                "phone" => $agencyWebsiteDetail->phone,
                "address" => $agencyWebsiteDetail->address,
                'city' => $agencyWebsiteDetail->city,
                'state' => $agencyWebsiteDetail->state,
                'country' => $agencyWebsiteDetail->country,
                'zip' => $agencyWebsiteDetail->pin,
                "description" => $agencyWebsiteDetail->description,
                "logo" => $logo,
                "agency_id" => $agencyWebsiteDetail->agency_id,
                "website_id" => $agencyWebsiteDetail->website_id,
                "status" => $agencyWebsiteDetail->status,
                "created_by" => $agencyWebsiteDetail->created_by,
            ],
        ];
        // Check if others_category_name is not null
        if ($agencyWebsiteDetail->others_category_name !== null) {
            // Add others_category_name to the array
            $websiteDetail['agency_website_detail']['others_category_name'] = $agencyWebsiteDetail->others_category_name;
        }
        if (!empty($websiteDetail)) {
            $response = [
                'message' => "Detail Fetched Successfully.",
                'success' => true,
                'status' => 200,
                'settings_detail' => $websiteDetail
            ];
        } else {
            $response = [
                'message' => "No details found.",
                'success' => false,
                'status' => 404,
                'settings_detail' => []
            ];
        }

        return response()->json($response);
    }
    public function updateSettings(Request $request)
    {
        $response = [
            'success' => false,
            'status' => 400,
        ];

        $validator = Validator::make($request->all(), [
            'website_id' => 'required',
            'category_id' => 'required',
            'others_category_name' => 'nullable',
            'business_name' => 'required|string',
            'phone' => 'nullable',
            'address' => 'required|string',
            'city' => 'required',
            'state' => 'required',
            'country' => 'required',
            'zip'  => 'required',
            'description' => 'nullable',
            'logo' => 'nullable|file|mimes:jpeg,png|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $validated = $validator->valid();
        $id = $request->website_id;
        $website = Websites::findOrFail($id);
        if(!$website){
            return response()->json(["error" => "Website Not Found."],400);
        }
        $website_url = $website->website_domain;
        $website_id = $website->id;
        if(!$website_id){
            return response()->json(["error" => "website is not assigned yet to any Agency."],400);
        }
        $agencyWebsiteData = AgencyWebsite::where('website_id',$website_id)->first();

        $phone = null;
        if(isset($validated['phone']) && $validated['phone'] !== null){
            $phone = $validated['phone'];
        }

        $description = null;
        if(isset($validated['description']) && $validated['description'] !== null){
            $description = $validated['description'];
        }

        $othersCategoryName = null;
        if(isset($validated['others_category_name'])){
            $othersCategoryName = $validated['others_category_name'];
        }

        $agencyWebsiteDetails = AgencyWebsite::where('website_id',$website_id)->update([
            'website_category_id' => $validated['category_id'],
            'others_category_name' => $othersCategoryName,
            'phone' => $phone,
            'address' => $validated['address'],
            'city' => $validated['city'],
            'state' => $validated['state'],
            'country' => $validated['country'],
            'pin' => $validated['zip'],
            'description'  => $description,
            'business_name' => $validated['business_name'],
            'updated_at' => Carbon::now(),
        ]);
        if($agencyWebsiteDetails){
            $data = [
                "agency_name" => ["value" => $validated['business_name']],
                "phone" => ["value" => $phone],
                "address" => ["value" => $validated['address']],
                "state" => ["value" => $validated['state']],
                "city" => ["value" => $validated['city']],
                "country" => ["value" => $validated['country']],
                "pincode" => ["value" => $validated['zip']]
            ];
            $updateAgencyDetailResponse = $this->wordpressComponentClass->updateGlobalVariables($website_url , $data);
            if($updateAgencyDetailResponse['success'] == false){
                return response()->json(['error' => 'An Error occur While Updating Website Settings.'],400);
            }
        }


        if($request->hasFile('logo')) {
            $uploadedFile = $request->file('logo');
            $filename = time() . '_' . $uploadedFile->getClientOriginalName();
            $uploadedFile->storeAs('public/AgencyWebsiteDetails', $filename);
            $path = 'AgencyWebsiteDetails/' . $filename;
            $updateLogo = AgencyWebsite::where('website_id',$website_id)->update([
                'logo' => $path,
            ]);
            if($updateLogo){
                $updateLogoToWordPressResponse = $this->wordpressComponentClass->uploadLogoToWordpress($path , $website_url);
            }

        }

        $response = [
            "message" => "Settings Updated Successfully.",
            'success' => true,
            'status' => 200,
        ];

        return response()->json($response);

    }
}
