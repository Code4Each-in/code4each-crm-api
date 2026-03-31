<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AgencyWebsite;
use App\Models\Agency;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AgenciesController extends Controller
{
    public function index()
    {
        $agencies = Agency::with(['users', 'agencyWebsites.websiteUser', 'currentPlan.plan'])
                    ->orderBy('id', 'desc')
                    ->get()
                    ->map(function ($agency) {

                        $cp = $agency->currentPlan;
            
                        if ($cp) {

                            $start = Carbon::parse($cp->website_start_date);

                            // planexpired = number of days for the plan
                            $end = $start->copy()->addDays($cp->planexpired);

                            // Days left including the invoice day itself
                            $daysLeft = now()->diffInDays($end, false) + 1;
                            $daysLeft = max(0, $daysLeft);

                            // Count number of invoices passed (assuming 15-day cycle as in planexpired?)
                            // Adjust if invoice frequency is different
                            $invoiceCount = ceil(now()->diffInDays($start, false) / $cp->planexpired);
                            $invoiceCount = max(0, $invoiceCount);

                            $agency->plan_name   = $cp->plan->name ?? '—';
                            $agency->plan_start  = $start->format('m-d-Y'); // MM-DD-YYYY
                            $agency->days_left   = $daysLeft;
                            $agency->next_invoice= $end->format('m-d-Y'); // MM-DD-YYYY
                            $agency->invoice_count = $invoiceCount;

                        } else {

                            $agency->plan_name    = '—';
                            $agency->plan_start   = '—';
                            $agency->days_left    = '—';
                            $agency->next_invoice = '—';
                            $agency->invoice_count= '—';
                        }
            
                        return $agency;
                    });

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

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:active,inactive',
        ]);

        $website = AgencyWebsite::findOrFail($id);
        $website->status = $request->status;
        $website->save();

        return response()->json(['success' => true, 'status' => $website->status]);
    }
}
