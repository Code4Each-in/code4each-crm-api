<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ForgotPasswordNotification extends Notification
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
        $resetUrl = config('app.frontend_url') . '/reset-password/?token='.$this->messages['token'].'&email='.$this->messages['email'];
        // $link = url( "/reset-password/".$this->messages['token']);
        return (new MailMessage)
                ->subject('Reset Password Link')
                ->greeting('Reset Password')
                ->line('We received a request to reset your account password. To reset your password, please click on the link below:')
                ->action('Reset Password', url($resetUrl))
                ->line("If you didn't request this password reset or believe it's a mistake, you can ignore this email. Your password will not be changed until you access the link above and create a new password.")
                ->line("This password reset link is valid for the next 24 hours. After that, you'll need to request another password reset.")
                ->line('Thank you for using our application!');
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
