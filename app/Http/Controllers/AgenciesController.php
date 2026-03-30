<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AgencyWebsite;
use App\Models\Agency;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AgenciesController extends Controller
{
    public function index()
    {
        $agencies = Agency::with(['users', 'agencyWebsites.websiteUser'])
                    ->orderBy('id', 'desc')
                    ->get();

        return view('agencies.index', compact('agencies'));
    }

    /**
     * Admin ko "impersonate" karke agency ka API access token generate karta hai
     * aur Vue dashboard par redirect karta hai.
     */
    public function loginAs(Agency $agency)
    {
        $admin = Auth::user();
        abort_unless($admin && ($admin->role ?? null) == 'super_admin', 403);

        // Listing me jo first agency website use hoti hai, uske created_by user ko prefer karein.
        $website = $agency->agencyWebsites()->orderByDesc('id')->first();

        $agencyUser = null;
        if ($website?->created_by) {
            $agencyUser = User::find($website->created_by);
        }

        // Fallback: agency ki users relation se first user.
        if (!$agencyUser) {
            $agencyUser = $agency->users()->orderByDesc('id')->first();
        }

        abort_if(!$agencyUser, 404, 'No user found for this agency.');

        // Vue dashboard ko API auth:api (Passport) se chahiye hota hai.
        $tokenResult = $agencyUser->createToken('access-token');
        $token = $tokenResult->accessToken;

        $frontendDashboardUrl = env('FRONTEND_DASHBOARD_URL', '');
        return redirect()->away(
            rtrim($frontendDashboardUrl, '/').'?access_token='.urlencode($token)
        );
    }
}
