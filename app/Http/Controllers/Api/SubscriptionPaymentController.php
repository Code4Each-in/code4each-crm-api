<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Razorpay\Api\Api;
use Carbon\Carbon;
use Razorpay\Api\Errors;
use Illuminate\Support\Facades\Auth;
use App\Models\Plan;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use App\Models\CurrentPlan;
use App\Models\PlanLog;

class SubscriptionPaymentController extends Controller
{
    public function createSubcription()
    {
        // Initialize Razorpay API
        $api = new Api(env('RZP_KEY'), env('RZP_SECRET'));
       
        // Create a subscription plan
        $createRzpPlan = $api->plan->create([
            'period' => "monthly",
            'interval' => "30",
            'item' => [
                'name' => "Speedysite",
                'description' => '',
                'amount' => "200",
                'currency' => 'INR'
            ],
            'notes' => [
                'key1' => 'value3',
                'key2' => 'value2'
            ]
        ]);
       // dd($createRzpPlan);

        // Get plan ID
        $planID = $createRzpPlan->id;

        // Subscription count in months
        $subs_time = "1";

        // Subscription date expired 
        $expired_date = Carbon::now();

        // Date to unix date 
        $expired_unix = strtotime("+" . $subs_time, strtotime($expired_date));

        // Create the subscription
        $createRzpsubscription = $api->subscription->create([
            'plan_id' => $planID,
            'customer_notify' => 1,
            'quantity' => 1,
            'total_count' => $subs_time,
            'notes' => [
                'key1' => 'value3',
                'key2' => 'value2'
            ],
            'notify_info' => [
                'notify_phone' => '+918867898919',
                'notify_email' => 'developersolutions2023@gmail.com'
            ]
        ]);

        // Prepare options for Razorpay checkout
        $options = [
            "key" => env('RZP_KEY'),
            "name" => "Developer Solutions", // Your business name
            "description" => "Test Transaction",
            "image" => "logo.png",
            "subscription_id" => $createRzpsubscription->id, // Subscription ID
            "prefill" => [
                "name" => "Test", // Customer's name
                "email" => "test@example.com",
                "contact" => "9000090000" // Customer's phone number
            ],
            "notes" => [
                "address" => "Razorpay Corporate Office"
            ],
            "theme" => [
                "color" => "#3399cc"
            ]
        ];

        // Return JSON response with checkout options
        return response()->json(['checkoutData' => $options, 'status' => true]);
    }

    public function fetchsubscription()
{
    // Initialize Razorpay API
    $api = new Api(env('RZP_KEY'), env('RZP_SECRET'));
    
    // Fetch subscription details
    $subscriptionId = "sub_O8llmaPwKMxona";
    $subscriptionDetails = $api->subscription->fetch($subscriptionId);

    // Create a new order for the subscription
    $order = $api->order->create([
        
        'payment_capture' => 1 // Auto capture payment
    ]);

    // Prepare options for Razorpay checkout
    $options = [
        "key" => env('RZP_KEY'),
        "name" => "Developer Solutions", // Your business name
        "description" => "Subscription Payment",
        "image" => "logo.png",
        "order_id" => $order->id, // Order ID
        "prefill" => [
            "name" => "Test", // Customer's name
            "email" => "test@example.com",
            "contact" => "9000090000" // Customer's phone number
        ],
        "notes" => [
            "address" => "Razorpay Corporate Office"
        ],
        "theme" => [
            "color" => "#3399cc"
        ]
    ];

    // Return JSON response with checkout options
    return response()->json(['checkoutData' => $options]);
}

public function payment()
{
    $razorpayPaymentId = "plan_O8lfhiPWTWRDKJ";
    $subscriptionId = "sub_O8llmaPwKMxona";
    $secret = 'RAzI1Ia1iMTTPj20SllxF98z'; // Replace this with your actual secret key
    $amount = 100; // Replace this with the actual amount to be charged

    // Generate signature
    $generatedSignature = hash_hmac('sha256', $razorpayPaymentId . "|" . $subscriptionId, $secret);

    // Get signature from request
   // $razorpaySignature = $request->input('razorpay_signature');

    // Compare signatures
    if ($generatedSignature) {
        // Signature verification successful, process payment
        $paymentStatus = $this->processPayment($subscriptionId, $amount);

        if ($paymentStatus === 'success') {
            // Payment is successful
            return "Payment is successful";
        } else {
            // Payment processing failed
            return "Payment processing failed";
        }
    } else {
        // Payment verification failed
        return "Payment verification failed";
    }
}




public function processPayment(Request $request)
{
    $subscriptionId = "sub_O8llmaPwKMxona";
    $amount = 600; 
    $keyId = 'rzp_test_aQGyKg1uq47vTL'; 
    $keySecret = 'YxO6nzm4In7udAoDltqB7LIq'; 

    try {
        $api = new Api($keyId, $keySecret);
        
        // Prepare order data
        $orderData = [
            'amount'         => $amount * 100, // Amount in smallest currency unit (e.g., paisa for INR)
            'currency'       => 'INR',
            'receipt'        => 'receipt_order_' . uniqid(),
            'payment_capture' => 1 // Automatically capture payment when it succeeds
        ];

        // Create an order
        $order = $api->order->create($orderData);

        // Check if order creation was successful
        if ($order->id) {
            // If order creation was successful, redirect the user to the payment page
            return redirect()->to($order->url);
        } else {
            return 'failure: Unable to create Razorpay order';
        }
    } catch (\Exception $e) {
        return 'failure: ' . $e->getMessage();
    }
}

public function handlePaymentNotification(Request $request)
{
    // Verify the request to ensure it comes from Razorpay
    $razorpaySignature = $request->header('X-Razorpay-Signature');

    try {
        // Your webhook secret
        $webhookSecret = 'your_webhook_secret';

        $api = new Api($keyId, $keySecret);

        // Verify the signature
        $api->utility->verifyWebhookSignature($request->getContent(), $razorpaySignature, $webhookSecret);

        // Payment successful, update your database or perform any other necessary actions
        $paymentId = $request->input('payload')['payment']['entity']['id'];
        // Update your database or perform other actions based on the payment status
        // For example, you could update the subscription status for the user

        return response()->json(['success' => true]);
    } catch (\Exception $e) {
        // Handle verification failure or any other errors
        return response()->json(['error' => $e->getMessage()], 400);
    }
}

public function subscriptionPayment(Request $request)
{   

    // dd( $request);
    $payment_id = $request->input('razorpay_payment_id');
    $order_id = $request->input('razorpay_order_id');
    $signature = $request->input('razorpay_signature');
    $user_id = $request->input('user_id');
    $website_id = $request->input('website_id');
    $agency_id = $request->input('agency_id');
    $plan_id = $request->input('plan_id');

    $api = new Api(env('RZP_KEY'), env('RZP_SECRET'));

    try {
        $attributes = [
            'razorpay_order_id' => $order_id,
            'razorpay_payment_id' => $payment_id,
            'razorpay_signature' => $signature,
        ];

        // $api->utility->verifyPaymentSignature($attributes);
      

        // $price = Plan::where('razor_id', $payment_id)->pluck('price')->first();
        $plan = Plan::where('id', $plan_id)->first(['id', 'price', 'type']);
        $price = $plan->price;
        $plan_id_int = $plan->id;
        $planexpired = ($plan->type == 'monthly') ? 30 : (($plan->type == 'yearly') ? 365 : 30);
    
        Transaction::create([
            'payment_id' => $payment_id,
            'order_id' => $order_id,
            'plan_id' => $plan_id_int,
            'user_id' => $user_id,
            'website_id' => $website_id,
            'agency_id' => $agency_id,
            'amount' => $price,
            'signature' => $signature
        ]);

        $CurrentPlan = CurrentPlan::where('user_id', $user_id)->first();

        if ($CurrentPlan) {
            $CurrentPlan->plan_id = $plan_id_int; 
            $CurrentPlan->website_start_date = date('Y-m-d H:i:s'); 
            $CurrentPlan->planexpired = $planexpired;
            $CurrentPlan->save();
        } else {
            $CurrentPlan = CurrentPlan::create([
                'agency_id' => $agency_id,
                'website_id' => $website_id,
                'plan_id' => $plan_id_int,
                'user_id' => $user_id,
                'website_start_date' => date('Y-m-d H:i:s'),
                'status' => 1,
                'planexpired' => $planexpired
            ]);
        }
          // Create a new plan_log record
          $PlanLog = PlanLog::create([
            'agency_id' => $agency_id,
            'user_id' => $user_id,
            'website_id' => $website_id,
            'plan_id' => $plan_id_int,
        ]);

        return response()->json(
            [
                "message" => "Paid successfully!",
                "success" =>true, 
                "status" =>200
            ]
        );

    } catch (\Exception $e) {
        DB::rollback();
        $errorMessage = $e->getMessage();
        $errorCode = $e->getCode();
        $errorFile = $e->getFile();
        $errorLine = $e->getLine();
        // Logging the error in log file
        \Log::error("\nError: $errorMessage\nFile: $errorFile\nLine: $errorLine \nCode:$errorCode");
        $response = [
            'success' => false,
            'status' => 400,
            'message' => 'An error occurred while processing your request.',
            'error' => $e->getMessage(),
        ];
        return response()->json($response,401);
    }
 
}

public function fetchplans()
{
    try {
        $mode = env('APP_MODE');
        $plans = Plan::where("mode", $mode)->get();
        return response()->json(
            [
                'plans' => $plans, 
                "message" => "Plans get successfully!",
                "success" =>true, 
                "status" =>200
            ]
        );
    }catch (\Exception $e) {
        DB::rollback();
        $errorMessage = $e->getMessage();
        $errorCode = $e->getCode();
        $errorFile = $e->getFile();
        $errorLine = $e->getLine();
        // Logging the error in log file
        \Log::error("\nError: $errorMessage\nFile: $errorFile\nLine: $errorLine \nCode:$errorCode");
        $response = [
            'success' => false,
            'status' => 400,
            'message' => 'An error occurred while processing your request.',
            'error' => $e->getMessage(),
        ];
        return response()->json($response,401);
    }
}

public function createOrder(Request $request)
{
    try {
        $api = new Api(env('RZP_KEY'), env('RZP_SECRET'));
        $plan_id = $request->input('plan_id');
        $plan = Plan::where("id", $plan_id)->first();

        if (!$plan) {
            return response()->json(['error' => 'Invalid Plan'], 400);
        }
        
        $orderData = [
            'receipt'         => 'order_rcptid_11',
            'amount'          => $plan->price * 100,
            'currency'        => 'INR',
            'payment_capture' => 1 // Auto capture
        ];

        $razorpayOrder = $api->order->create($orderData);

        return response()->json([
            'order_id' => $razorpayOrder['id'],
            'amount'   => $plan->price * 100,
            "message" => "Order Created Successfully!",
            "success" =>true, 
            "status" =>200
        ]);
    }catch (\Exception $e) {
        DB::rollback();
        $errorMessage = $e->getMessage();
        $errorCode = $e->getCode();
        $errorFile = $e->getFile();
        $errorLine = $e->getLine();
        // Logging the error in log file
        \Log::error("\nError: $errorMessage\nFile: $errorFile\nLine: $errorLine \nCode:$errorCode");
        $response = [
            'success' => false,
            'status' => 400,
            'message' => 'An error occurred while processing your request.',
            'error' => $e->getMessage(),
        ];
        return response()->json($response,401);
    }
}

}