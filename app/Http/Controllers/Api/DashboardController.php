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
use Carbon\Carbon;

class DashboardController extends Controller
{

    // public function index()
    // {
    //     $response = [
    //         'success' => false,
    //         'status' => 400,
    //     ];
    //     //Check if verifyEmail Middleware have verification_notification Message
    //     if (session()->has('verification_notice')) {
    //         $response['notification'] = session('verification_notice');
    //         // $response['resend-link'] = route('verification.resend');
    //         session()->forget('verification_notice');
    //     }
    //     //Get Auth User Using laravel auth method
    //     $user = User::with(['agency','agency.agencyWebsites','agency.agencyWebsites.websiteDetail','currentPlans.plan'])->where('id',auth()->user()->id)->first();
    //     $response['user'] = $user;

    // if ($user && $user->agency) {
    //     $agencyWebsitesInfo = [];
    
    //     foreach ($user->agency->agencyWebsites as $website) {
    //         $agencyWebsitesInfo[] = $website;
    //     }
    // } 
    
    // $response['agency_website_info'] = $agencyWebsitesInfo ?? [];

    //     $response['message'] =  "Welcome to the dashboard.";
    //     $response['success'] = true;
    //     $response['status'] = 200;

    //     return response()->json($response);
    // }

    public function index()
    {
        $response = [
            'success' => false,
            'status' => 400,
        ];

        if (session()->has('verification_notice')) {
            $response['notification'] = session('verification_notice');
            session()->forget('verification_notice');
        }

        $user = User::with(['agency','agency.agencyWebsites','agency.agencyWebsites.websiteDetail','currentPlans.plan'])
                    ->where('id',auth()->user()->id)
                    ->first();

        if ($user && $user->currentPlans && count($user->currentPlans) > 0) {

            foreach ($user->currentPlans as $plan) {

                if (!$plan->website_start_date || !$plan->plan) {
                    $plan->planexpired = 0;
                    continue;
                }

                $startDate = Carbon::parse($plan->website_start_date);
                $today     = Carbon::now();
                $planName = strtolower($plan->plan->name);
                if (strpos($planName, '15 days') !== false) {
                    $totalDays = 15;
                }
                elseif (strpos($planName, '6 month') !== false) {
                    $totalDays = 6 * 30; 
                }
                elseif (strpos($planName, 'year') !== false || strpos($planName, 'yearly') !== false) {
                    $totalDays = 12 * 30; 
                }
                else {
                    $totalDays = 15;
                }

                $usedDays = $startDate->diffInDays($today);
                $remainingDays = $totalDays - $usedDays;
                $plan->planexpired = max($remainingDays, 0);
            }
        }

        $response['user'] = $user;
        $response['agency_website_info'] = $user->agency?->agencyWebsites ?? [];
        $response['message'] = "Welcome to the dashboard.";
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
