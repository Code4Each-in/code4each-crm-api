<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agency;
use App\Models\AgencyWebsite;
use App\Models\User;
use App\Models\WebsiteCategory;
use App\Models\Websites;
use App\Notifications\CommonEmailNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Mockery\Undefined;
use Illuminate\Support\Facades\Validator;

class DashboardController extends Controller
{

    public function index()
    {
        $response = [
            'success' => false,
            'status' => 400,
        ];
        //Check if verifyEmail Middleware have verification_notification Message
        if (session()->has('verification_notice')) {
            $response['notification'] = session('verification_notice');
            // $response['resend-link'] = route('verification.resend');
            session()->forget('verification_notice');
        }
        //Get Auth User Using laravel auth method
        $user = User::with('agency')->where('id',auth()->user()->id)->first();
        $agency_id = $user->agency_id;
        $response['user'] = $user;
        $agencyWebsiteInfo= AgencyWebsite::join('websites', 'agency_websites.website_id', '=', 'websites.id')->where('agency_id','=', $agency_id)->get(['websites.id','agency_websites.*', 'websites.website_domain']);
        if($agencyWebsiteInfo->count() > 0){
        $response['agency_website_info'] = $agencyWebsiteInfo;
        }

        $response['message'] =  "Welcome to the dashboard.";
        $response['success'] = true;
        $response['status'] = 200;

        return response()->json($response);
    }

    public function agencyDetails(Request $request)
    {
        $response = [
            'success' => false,
            'status' => 400,
        ];
        $validator = Validator::make($request->all(), [
            'agency_id' => 'required',
            'category_id' => 'required',
            'description' => 'nullable|string',
            'address' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }
        $validate = $validator->valid();

        $updateAgencyDetail = Agency::where("id", $validate['agency_id'])->update([
            'category_id' => $validate['category_id'],
            'address' => $validate['address'],
            'description'  => $validate['description'] ?? null
        ]);
        if ($updateAgencyDetail) {
            $response = [
                'message' => "Agency Detail Saved Successfully.",
                'success' => true,
                'status' => 200,
            ];
        }

      return response()->json($response);
    }

    public function getWebsiteCategories()
    {
        $response = [
            'success' => false,
            'status' => 400,
        ];

        $categories = WebsiteCategory::all();
        $response = [
            'message' => "Categories fetched Successfully.",
            'success' => true,
            'categories' => $categories,
            'status' => 200,
        ];

        return response()->json($response);
    }

    public function getAgencyWebsiteInfo($agency_id)
    {
        $response = [
            'success' => false,
            'status' => 400,
        ];
        $agencyWebsiteInfo= AgencyWebsite::join('websites', 'agency_websites.website_id', '=', 'websites.id')->where('agency_id','=', $agency_id)->get(['agency_websites.business_name', 'websites.website_domain']);
        if($agencyWebsiteInfo){
            $response = [
                'agency_website_info' => $agencyWebsiteInfo,
                'message' => "Agency Website Info fetched.",
                'success' => true,
                'status' => 200,
            ];
        }
        return response()->json($response);
    }
}
