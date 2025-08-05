<?php

namespace App\Notifications;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class VerifyEmail extends Notification
{
    use Queueable;

    private $messages;


    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($messages)
    {
        $this->messages = $messages;

    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);
        
        Config::set('app.url', 'https://app.speedysites.in');

        // Pass variables to the blade view
        $viewData = [
            'greeting' => $this->messages['greeting-text'] ?? 'Hello',
            'verificationUrl' => $verificationUrl,
            'notifiable' => $notifiable,
        ];

        // Return a mail message with rendered view
        return (new MailMessage)
            ->subject('SpeedySites - Verify Your Email Address')
            ->view('emails.verify_email', $viewData);
    }

    protected function verificationUrl($notifiable)
    {
        // collect and sort url params
        $params = [
            'id' => $notifiable->getKey(),
            'expires' => Carbon::now()
                ->addMinutes(Config::get('auth.verification.expire', 60))
                ->getTimestamp(),
            'hash' => sha1($notifiable->getEmailForVerification()),
        ];
        ksort($params);

        // then create API url for verification. my API have `/api` prefix,
        // so i don't want to show that url to users
        $url = URL::route(
            'verification.verify',
            $params,
            true
        );

        // get APP_KEY from config and create signature
        $key = config('app.key');
        $signature = hash_hmac('sha256', $url, $key);

         // generate url for yous SPA page to send it to user
         return config("app.frontend_url") .
         "/email/verify/" .
         '?id='.
         $params["id"] .
         "&expires=" .
         $params["expires"] .
         "&hash=" .
         $params["hash"] .
         "&signature=" .
         $signature;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
