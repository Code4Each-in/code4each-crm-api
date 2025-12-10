<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Websites;
use App\Models\WebsiteDatabase;
use App\Models\Domains;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;

class DomainsController extends Controller
{
    /* Save New Domain */
    public function saveNewDomain(Request $request)
    {
        $response = [
            'success' => false,
            'status' => 400,
        ];

        $validator = Validator::make($request->all(), [
            'agency_id'   => 'required',
            'website_id'  => 'required',
            'new_domain'  => 'required|string',
            'user_id'     => 'required',
            'old_domain'  => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $validate = $validator->valid();

        $existingDomain = Domains::where('domain', $validate['new_domain'])->first();
        if ($existingDomain) {
            return response()->json([
                'success' => false,
                'status'  => 409,
                'message' => 'The domain is already used.'
            ], 409);
        }

        // Save new domain
        $newDomain = new Domains();
        $newDomain->agency_id = $validate['agency_id'];
        $newDomain->website_id = $validate['website_id'];
        $newDomain->user_id = $validate['user_id'];
        $newDomain->domain = $validate['new_domain'];
        $newDomain->status = 'Not Verified';
        $newDomain->type = null;
        $newDomain->save();

        // ---------------------------------------------------------------
        // Send Mail to Super Admin
        // ---------------------------------------------------------------

        $superAdmin = User::where('role', 'super_admin')->first();

        if ($superAdmin) {
            $adminMessage = [
                'greeting-text' => "Hello Admin,",
                'subject' => 'New Domain Created',
                'additional-info' => '',
                'lines_array' => [
                    'title' => 'A new domain has been successfully created.',
                    'body-text' => 'Details are given below:',
                    'special_Domain_Name' => $validate['new_domain'],
                    'special_Created_By' => auth()->user()->name . ' (' . auth()->user()->email . ')',
                ],
            ];

            $superAdmin->notify(new CommonEmailNotification($adminMessage));
        }

        if ($newDomain) {
            $response = [
                'message' => "New Domain Added Successfully.",
                'success' => true,
                'status'  => 200,
            ];
        }

        return response()->json($response);
    }

    /* Get Domains */
    public function getDomains(Request $request)
    {
        $response = [
            'success' => false,
            'status' => 400,
        ];

        $websiteId = $request->input('website_id');
        $userId = $request->input('user_id');

        $query = Domains::query();
        $stagingDomain = Websites::where('id', $websiteId)->value('staging_domain');

        if ($websiteId) {
            $query->where('website_id', $websiteId);
        }

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $domains = $query->get();

        $response = [
            'message' => "Domains fetched Successfully.",
            'success' => true,
            'domains' => $domains,
            'status' => 200,
        ];

        return response()->json($response);
    }

    public function checkDomain(Request $request)
{
    $fullDomain = $request->input('domain');
    $fullStagingDomain = $request->input('staging_domain');

    // Clean domains
    $domain = preg_replace(['/^https?:\/\//', '/\/$/'], '', trim($fullDomain));
    $stagingDomain = preg_replace(['/^https?:\/\//', '/\/$/'], '', trim($fullStagingDomain));

    if (empty($domain)) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid domain.'
        ], 400);
    }

    // Expected DNS values
    $expectedARecord = "77.37.32.140";
    $expectedCname   = $stagingDomain;

    // Fetch DNS records
    $aRecords = dns_get_record($domain, DNS_A);
    $cnameRecords = dns_get_record("www.".$domain, DNS_CNAME);

    // Verification flags
    $aVerified = false;
    $cnameVerified = false;

    foreach ($aRecords as $record) {
        if (!empty($record['ip']) && $record['ip'] === $expectedARecord) {
            $aVerified = true;
        }
    }

    foreach ($cnameRecords as $record) {
        if (!empty($record['target']) && $record['target'] === $expectedCname) {
            $cnameVerified = true;
        }
    }

    // DNS final match
    $dnsMatched = ($aVerified && $cnameVerified);

    // Find domain record
    $domainRow = Domains::where('domain', $fullDomain)->first();

    if (!$domainRow) {
        return response()->json(['success' => false, 'message' => 'Domain not found']);
    }

    // DNS NOT MATCHED → Reset timer
    if (!$dnsMatched) {
        $domainRow->update([
            'is_dns_matched' => false,
            'verified_at' => null,
            'status' => 'Not Verified'
        ]);

        return response()->json([
            'success' => true,
            'status' => 'Not Verified',
            'a_record_verified' => $aVerified,
            'cname_verified' => $cnameVerified
        ]);
    }

    // DNS MATCHED → Start timer if new
    if (!$domainRow->verified_at) {
        $domainRow->verified_at = now();
    }

    $hoursPassed = now()->diffInHours($domainRow->verified_at);

    // Check verification status
    $status = $hoursPassed >= 24 ? "Verified" : "Not Verified";

    // Update DB
    $domainRow->update([
        'is_dns_matched' => true,
        'verified_at' => $domainRow->verified_at,
        'status' => $status
    ]);

    // Final response
    return response()->json([
        'success' => true,
        'domain' => $domain,
        'a_record_verified' => $aVerified,
        'cname_verified' => $cnameVerified,
        'status' => $status,
        'hours_passed' => $hoursPassed
    ]);
}


    /* Delete Domain */
    public function deleteDomain(Request $request)
    {
        $response = [
            'success' => false,
            'status' => 400,
        ];

        $validator = Validator::make($request->all(), [
            'domain_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $validate = $validator->valid();

        $domain = Domains::find($validate['domain_id']);
        if (!$domain) {
            return response()->json([
                'success' => false,
                'status'  => 404,
                'message' => 'Domain not found.'
            ], 404);
        }

        $domain->delete();

        $response = [
            'message' => "Domain deleted successfully.",
            'success' => true,
            'status'  => 200,
        ];

        return response()->json($response);
    }

    /* Set Primary Domain */
    public function setPrimaryDomain(Request $request)
    {
        $response = [
            'success' => false,
            'status' => 400,
        ];

        $validator = Validator::make($request->all(), [
            'website_id' => 'required',
            'domain'     => 'required',
            'agency_id'  => 'required',
            'staging_domain' => 'required',
            'domain_name'     => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $validate = $validator->valid();

        $website = Websites::find($validate['website_id']);
        if (!$website) {
            return response()->json([
                'success' => false,
                'status'  => 404,
                'message' => 'Website not found.'
            ], 404);
        }

        $website->website_domain = $validate['domain'];
        $website->save();

        $websiteDatabase = WebsiteDatabase::find($validate['website_id']);
        if ($websiteDatabase) {
            $websiteDatabase->website_domain = $validate['domain'];
            $websiteDatabase->save();
        }

        $data = [
            'old_domain' => $validate['staging_domain'],
            'new_domain' => $validate['domain_name'],
        ];
        $websiteUrl = $request->input('staging_domain');
        $postApiUrl = $websiteUrl . '/wp-json/v1/replace-domain';
        $wpResponse = Http::post($postApiUrl, $data);

        $response = [
            'message' => "Primary domain set successfully.",
            'success' => true,
            'status'  => 200,
        ];

        return response()->json($response);
    }
}
