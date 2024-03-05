<?php

namespace App\Console\Commands;

use App\Mail\ProductLiveAlertMail;
use Illuminate\Console\Command;
use App\Models\PreBooking;
use Illuminate\Support\Facades\Mail;

class ProductLiveAlert extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'alert:pre-register-user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $PreIntrestedUser = PreBooking::all();
        $emails = $PreIntrestedUser->pluck('email');
        $data = ['subject' => 'Exciting News: Our Beta Site is Now Live!', 'message' => 'Your Message'];
        // You can use the `each` method to loop through each email and send the mail
        $emails->each(function ($email) use ($data) {
            // You may customize the mail sending logic as needed
            Mail::to($email)->send(new ProductLiveAlertMail($data));
        });

        // info("Email Sent To Pre Interested User.");
        // $email = "fuzail.code4each@gmail.com";
        // $data = ['subject' => 'Your Subject', 'message' => 'Your Message'];

        // You can directly use Mail::to with a single email address
        // Mail::to($email)->send(new ProductLiveAlertMail($data));

        info("Email Sent To Pre Interested User.");
        dd("Email Sent");
    }
}
