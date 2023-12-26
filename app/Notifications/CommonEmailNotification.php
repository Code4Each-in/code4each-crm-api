<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CommonEmailNotification extends Notification
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
            $mailMessage = new MailMessage;
            $mailMessage->greeting(isset($this->messages['greeting-text']) ? $this->messages['greeting-text'] : '');
            $mailMessage->subject(isset($this->messages['subject']) ? $this->messages['subject'] : 'Notification Email');
                // ->line(isset($this->messages['title']) ? $this->messages['title'] : 'Title')
                // ->line(isset($this->messages['body-text']) ? $this->messages['body-text'] : 'Body Text')
                $lineItems = $this->messages['lines_array'];

                foreach ($lineItems as $key => $value) {
                    if (strpos($key, 'special_') === 0) {
                        $specialLabel = ucwords(str_replace('_', ' ', str_replace('special_', '', $key)));
                        $mailMessage->line( $specialLabel . ': ' . $value);
                    } else {
                        $mailMessage->line($value);
                    }
                }
                if (isset($this->messages['url-title']) || isset($this->messages['url'])) {
                    $mailMessage->action(
                        isset($this->messages['url-title']) ? $this->messages['url-title'] : 'Action Not Required',
                        isset($this->messages['url']) ? url($this->messages['url']) : '#'
                    );
                }
                $mailMessage->line(isset($this->messages['additional-info']) ? $this->messages['additional-info'] : '');
                $mailMessage->line('Thank you for using our Platform!');

                return $mailMessage;

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
