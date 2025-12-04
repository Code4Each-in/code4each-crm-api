<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Websites;
use App\Models\WebsiteDatabase;
use App\Models\Domains;
use Illuminate\Support\Facades\Validator;

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
        $newDomain->status = 'pending';
        $newDomain->type = null;
        $newDomain->save();

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
        $domain = trim($fullDomain);
        $domain = preg_replace(['/^https?:\/\//', '/\/$/'], '', $domain);

        if (empty($domain)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid domain.'
            ], 400);
        }

        // Your expected DNS values
        $expectedARecord = "77.37.32.140";
        $expectedCname   = "alphafive.speedysites.in";

        // Fetch DNS records
        $aRecords = dns_get_record($domain, DNS_A);
        $cnameRecords = dns_get_record("www.".$domain, DNS_CNAME);
        print_r($cnameRecords);

        $aVerified = false;
        $cnameVerified = false;

        // Check A record
        foreach ($aRecords as $record) {
            if (isset($record['ip']) && $record['ip'] === $expectedARecord) {
                $aVerified = true;
            }
        }

        // Check CNAME
        foreach ($cnameRecords as $record) {
            if (isset($record['target']) && $record['target'] === $expectedCname) {
                $cnameVerified = true;
            }
        }

        // Final status
        $status = ($aVerified && $cnameVerified) ? "verified" : "pending";

        // Update DB
        Domains::where('domain', $domain)->update([
            'status' => $status
        ]);

        return response()->json([
            'success' => true,
            'domain' => $domain,
            'a_record_verified' => $aVerified,
            'cname_verified' => $cnameVerified,
            'status' => $status,
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

        $response = [
            'message' => "Primary domain set successfully.",
            'success' => true,
            'status'  => 200,
        ];

        return response()->json($response);
    }
}
