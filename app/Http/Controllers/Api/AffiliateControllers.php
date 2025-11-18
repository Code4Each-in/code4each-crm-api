<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\ReferralClick;
use App\Models\AffiliateEarnings;
use App\Models\AffiliateWithdrawals;
use Illuminate\Support\Facades\DB;
use App\Models\CurrentPlan;
use App\Models\Plan;
use App\Models\AffiliateBankAccountDetails;
use App\Notifications\CommonEmailNotification;

class AffiliateControllers extends Controller
{
    /**
     * THIS METHOD IS TO FETCH REFERRED USERS LIST
     */
    public function getReferredUsers(Request $request)
    {
        $response = [
            'success' => false,
            'status' => 400,
        ];

        $validator = Validator::make($request->all(), [
            'referral_code' => 'required',
            'user_id' => 'required|integer|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'response' => $validator->errors(),
                'status' => 400,
                'success' => false
            ], 400);
        }

        $validatedData = $validator->validated();

        // Get the user
        $user = User::find($validatedData['user_id']);
        if (!$user) {
            return response()->json([
                'response' => 'User not found.',
                'status' => 404,
                'success' => false
            ], 404);
        }

        $referredUsers = User::where('referred_by', $user->id)->get();

        $response = [
            'success' => true,
            'status' => 200,
            'response' => [
                'referrer' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'referral_code' => $user->referral_code,
                ],
                'referred_users' => $referredUsers,
            ]
        ];

        return response()->json($response, 200);
    }

    /**
     * THIS METHOD IS TO TRACK REFERRAL CLICK
     */
    public function trackReferralClick(Request $request)
    {
        $response = [
            'success' => false,
            'status' => 400,
        ];
        $referralCode = $request->input('referral_code');
        $ipAddress = $request->ip();

        ReferralClick::create([
            'referral_code' => $referralCode,
            'ip_address' => $ipAddress,
        ]);

        $response = [
            'success' => true,
            'status' => 200,
            'response' => 'Referral click tracked successfully.',
        ];

        return response()->json($response, 200);
    }

    /**
     * FETCH TOTAL REFERRED USERS
     */
    public function getTotalReferredUsers(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'referral_code' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'status' => 400,
                'response' => $validator->errors()
            ], 400);
        }

        $user = User::where('referral_code', $request->referral_code)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'status' => 404,
                'response' => 'User not found'
            ], 404);
        }

        // CORRECTED LOGIC (referred_by stores user_id)
        $totalReferredUsers = User::where('referred_by', $user->id)->count();

        return response()->json([
            'success' => true,
            'status' => 200,
            'response' => [
                'total_referred_users' => $totalReferredUsers
            ]
        ]);
    }

    /**
     * TOTAL EARNINGS
     */
    public function getTotalEarnings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'referral_code' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'status' => 400,
                'response' => $validator->errors()
            ], 400);
        }

        $user = User::where('referral_code', $request->referral_code)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'status' => 404,
                'response' => 'User not found'
            ], 404);
        }

        $totalEarnings = AffiliateEarnings::where('agent_id', $user->id)->sum('amount');

        return response()->json([
            'success' => true,
            'status' => 200,
            'response' => [
                'total_earnings' => $totalEarnings
            ]
        ]);
    }

    /**
     * TOTAL WITHDRAWAL AMOUNT
     */
    public function getTotalWithdrawalAmount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'referral_code' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'status' => 400,
                'response' => $validator->errors()
            ], 400);
        }

        $user = User::where('referral_code', $request->referral_code)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'status' => 404,
                'response' => 'User not found'
            ], 404);
        }

        $totalWithdrawalAmount = AffiliateWithdrawals::where('agent_id', $user->id)
            ->where('status', 'completed')
            ->sum('amount');

        return response()->json([
            'success' => true,
            'status' => 200,
            'response' => [
                'total_withdrawal_amount' => $totalWithdrawalAmount
            ]
        ]);
    }

    /**
     * MONTHLY EARNINGS
     */
    public function getMonthlyEarnings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'referral_code' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'status' => 400,
                'response' => $validator->errors()
            ], 400);
        }

        $user = User::where('referral_code', $request->referral_code)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'status' => 404,
                'response' => "User not found"
            ], 404);
        }

        $earnings = AffiliateEarnings::select(
            DB::raw("DATE_FORMAT(created_at, '%b') as month"),
            DB::raw("SUM(amount) as total")
        )
            ->where('agent_id', $user->id)
            ->groupBy('month')
            ->orderByRaw("MIN(created_at)")
            ->take(6)
            ->get();

        return response()->json([
            'success' => true,
            'status' => 200,
            'response' => [
                'monthly_earnings' => $earnings
            ]
        ]);
    }

    /**
     * WITHDRAWAL HISTORY
     */
    public function getWithdrawalHistory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'referral_code' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'status' => 400,
                'response' => $validator->errors()
            ], 400);
        }

        $user = User::where('referral_code', $request->referral_code)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'status' => 404,
                'response' => 'User not found'
            ], 404);
        }

        $history = AffiliateWithdrawals::where('agent_id', $user->id)
            ->whereNotNull('status')
            ->orderBy('created_at', 'DESC')
            ->get(['id', 'amount', 'status', 'created_at']);

        return response()->json([
            'success' => true,
            'status' => 200,
            'response' => [
                'withdrawal_history' => $history
            ]
        ]);
    }

    /**
     * REFERRED USERS PLAN HISTORY
     */
    public function getReferredUsersPlanHistory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'referral_code' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'status' => 400,
                'response' => $validator->errors()
            ], 400);
        }

        // Find user by referral code
        $user = User::where('referral_code', $request->referral_code)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'status' => 404,
                'response' => 'User not found'
            ], 404);
        }

        // Get referred users
        $referredUsers = User::where('referred_by', $user->id)->get();

        $planHistory = [];

        foreach ($referredUsers as $referredUser) {

            // Fetch current plan from CurrentPlans table
            $currentPlan = CurrentPlan::where('status', 1)
                ->where('user_id', $referredUser->id)
                ->first();
            

            $planName = null;
            $planId = null;

            if ($currentPlan) {
                $planId = $currentPlan->plan_id;
                $creteatedAt = $currentPlan->created_at;

                // Fetch plan name from plans table
                $plan = Plan::where('id', $planId)->first();
                $planName = $plan ? $plan->name : null;
                $planAmount = $plan ? $plan->price : null;
                if($planName === "6 Months Plan"){
                    $planDuration = 6;
                    $totalPayable = $planAmount * 6;
                    $commission = $totalPayable * 0.10;
                } elseif($planName === "Yearly Plan"){
                    $planDuration = 12;
                    $totalPayable = $planAmount * 12;
                    $commission = $totalPayable * 0.10;
                } else {
                    $planDuration = "15 Days";
                }
            }

            $planHistory[] = [
                'user_id'       => $referredUser->id,
                'user_name'     => $referredUser->name,
                'plan_id'       => $planId,
                'plan_name'     => $planName,
                'plan_amount'   => isset($totalPayable) ? number_format($totalPayable, 2, '.', '') : null,
                'commission_paid' => isset($commission) ? number_format($commission, 2, '.', '') : null,
                'created_at'   => isset($creteatedAt) ? $creteatedAt->toDateTimeString() : null,
            ];
        }

        return response()->json([
            'success' => true,
            'status' => 200,
            'response' => [
                'referred_users_plan_history' => $planHistory
            ]
        ]);
    }

    /*
    * THIS METHOD IS FOR AFFILIATE STATS
    */
    public function getAffiliateStats(Request $request)
    {
        // total affiliates
        $totalAffiliates = User::where('user_type', 'agent')->count();

        // total paid
        $totalPaidAffiliates = AffiliateEarnings::sum('amount') ?? 0;

        // Start from the beginning of the month 11 months ago (12 months total)
        $startDate = now()->subMonths(11)->startOfMonth();

        // Sum all earnings from this period
        $totalLast12Months = AffiliateEarnings::where('created_at', '>=', $startDate)
            ->sum('amount') ?? 0;

        // Divide by 12 to get monthly average
        $averageMonthlyEarnings = round($totalLast12Months / 12, 2);

        // --- FETCH PLANS ---
        $sixMonthPlan = Plan::where('type', 'monthly')->first();
        $yearlyPlan   = Plan::where('type', 'yearly')->first();

        $sixMonthPrice = $sixMonthPlan ? $sixMonthPlan->price : 0;
        $yearlyPrice   = $yearlyPlan ? $yearlyPlan->price : 0;

        $sixMonthPrice = $sixMonthPrice * 6;  
        $yearlyPrice   = $yearlyPrice * 12;

        $sixMonthCommission = $sixMonthPrice * 0.10;
        $yearlyCommission   = $yearlyPrice * 0.10;

        $averageCommission = round((($sixMonthCommission + $yearlyCommission) / 2), 2);

        $sale5  = round($averageCommission * 5, 2);
        $sale10 = round($averageCommission * 10, 2);
        $sale20 = round($averageCommission * 20, 2);

        return response()->json([
            'success' => true,
            'status' => 200,
            'response' => [
                'total_affiliates'        => $totalAffiliates,
                'total_paid_affiliates'   => $totalPaidAffiliates,
                'average_monthly_earnings'=> $averageMonthlyEarnings,
                'earning_calculator' => [
                    '5_sales'   => $sale5,
                    '10_sales'  => $sale10,
                    '20_sales'  => $sale20,
                ]
            ]
        ]);
    }

    /* 
     * THIS METHOD IS TO POST WITHDRAWAL DATA AND REQUEST
    */
    public function postWithdrawalData(Request $request) 
    {
        // 1. Validate incoming fields
        $validator = Validator::make($request->all(), [
            'agent_id'     => 'required',
            'account_name'    => 'required',
            'bank_name'  => 'required',
            'account_number'    => 'required',
            'ifsc'     => 'required',
            'amount'       => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'status'  => 400,
                'response' => $validator->errors()
            ], 400);
        }

        try {
            $existingAccount = AffiliateBankAccountDetails::where('agent_id', $request->agent_id)->first();

            if ($existingAccount) {
                $needsUpdate = 
                    $existingAccount->account_name   !== $request->account_name ||
                    $existingAccount->bank_name      !== $request->bank_name ||
                    $existingAccount->account_number !== $request->account_number ||
                    $existingAccount->ifsc           !== $request->ifsc;
                if ($needsUpdate) {
                    $existingAccount->update([
                        'account_name'    => $request->account_name,
                        'bank_name'       => $request->bank_name,
                        'account_number'  => $request->account_number,
                        'ifsc'            => $request->ifsc,
                        'updated_at'      => now(),
                    ]);
                }
                $accountDetailsId = $existingAccount->id;

            } else {
                $newAccount = AffiliateBankAccountDetails::create([
                    'agent_id'        => $request->agent_id,
                    'account_name'    => $request->account_name,
                    'bank_name'       => $request->bank_name,
                    'account_number'  => $request->account_number,
                    'ifsc'            => $request->ifsc,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);

                $accountDetailsId = $newAccount->id;
            }

            AffiliateWithdrawals::create([
                'agent_id'          => $request->agent_id,
                'amount'            => $request->amount,
                'status'            => 'pending',
                'account_details_id'=> $accountDetailsId,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | SEND EMAIL NOTIFICATION TO ALL ADMINS FOR WITHDRAWAL REQUEST
            |--------------------------------------------------------------------------
            */

            $agent = User::find($request->agent_id);

            $adminMessage = [
                'subject'   => 'New Withdrawal Request Submitted',
                'url-title' => 'Review Request',
                'url'       => '/',
                'lines_array' => [
                    'title'      => 'Dear Admin,',
                    'body-text'  => 'A new withdrawal request has been submitted. Below are the details:',
                    'special_Agent_Name' => $agent ? $agent->name : 'Unknown Agent',
                    'special_Email'      => $agent ? $agent->email : 'N/A',
                    'special_Amount'     => '₹' . $request->amount,
                ],
            ];

            // Notify All Super Admins
            $admins = User::where('role', 'super_admin')->get();

            foreach ($admins as $admin) {
                $admin->notify(new CommonEmailNotification($adminMessage));
            }

            return response()->json([
                'success' => true,
                'status'  => 200,
                'response' => 'Withdrawal request submitted successfully!',
                'account_details_id' => $accountDetailsId
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'status'  => 500,
                'response' => 'Something went wrong',
                'error'    => $e->getMessage()
            ], 500);
        }
    }

    /* 
    * THIS METHOD IS FOR FETCHING THE AGENT'S ACCOUNT DETAILS
    */
    public function getAffiliateAccountDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'agent_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'status'  => 400,
                'response' => $validator->errors()
            ], 400);
        }

        try {

            // Fetch details from affiliate_bank_account_details
            $accountDetails = AffiliateBankAccountDetails::where('agent_id', $request->agent_id)
                                ->orderBy('id', 'desc') // latest record
                                ->first();

            if (!$accountDetails) {
                return response()->json([
                    'success' => false,
                    'status'  => 404,
                    'response' => 'No account details found for this agent.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'status'  => 200,
                'response' => $accountDetails,
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'status'  => 500,
                'response' => 'Something went wrong',
                'error'    => $e->getMessage()
            ], 500);
        }
    }

}