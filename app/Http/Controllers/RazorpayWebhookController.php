<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;    
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\BillingTransaction; 
use App\Models\UserBilling; 

class RazorpayWebhookController extends Controller
{
    // Add user billing details
    public function addUserBillingDetails(Request $request)
    {
        $response = [
            'success' => false,
            'status' => 400,
        ];
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required',
            'phone' => 'required|string',
            'address' => 'required|string',
            'city' => 'required|string',
            'zip' => 'required|string',
            'country' => 'required|string',
            'agency_id' => 'required',
            'website_id' => 'required',
            'plan_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }
        $validate = $validator->valid();

        $updateBillingDetail = UserBilling::updateOrCreate(
            [
                'name' => $validate['name'],
                'email' => $validate['email'],
                'phone' => $validate['phone'],
                'address' => $validate['address'],
                'city' => $validate['city'],
                'zip_code' => $validate['zip'],
                'country' => $validate['country'],
                'agency_id' => $validate['agency_id'],
                'website_id' => $validate['website_id'],
                'plan_id' => $validate['plan_id'],
            ]
        );

        if ($updateBillingDetail) {
            $response = [
                'message' => "Billing Detail Saved Successfully.",
                'success' => true,
                'status' => 200,
                'billing_id' => $updateBillingDetail->id 
            ];
        }

      return response()->json($response);
    }

    public function handle(Request $request)
    {
        $secret = env('RZP_WEBHOOK_SECRET', 'yourSecretHere');
        $payload = $request->getContent();
        $signature = $request->header('X-Razorpay-Signature', '');

        // Verify signature
        $expected = hash_hmac('sha256', $payload, $secret);
        if (!hash_equals($expected, $signature)) {
            Log::warning('Razorpay webhook signature mismatch', ['expected' => $expected, 'sig' => $signature]);
            return response('Invalid signature', 400);
        }

        $data = json_decode($payload, true);

        if (($data['event'] ?? '') === 'payment.captured') {
            $payment = $data['payload']['payment']['entity'];
            $billing_id = $payment['notes']['billing_id'] ?? null;
            $plan_id    = $payment['notes']['plan_id'] ?? null;
            $payment_details = json_encode($payment);
            $card_details = isset($payment['card']) ? json_encode($payment['card']) : null;

            BillingTransaction::create([
                'user_billing_id' => $billing_id, 
                'plan_id'         => $plan_id, 
                'status'          => $payment['status'],
                'payment_id'      => $payment['id'],
                'order_id'        => $payment['order_id'],
                'amount'          => ($payment['amount'] ?? 0) / 100,
                'payment_details' => $payment_details,
                'card_details'    => $card_details, 
            ]);
        }

        return response('OK', 200);
    }
}
