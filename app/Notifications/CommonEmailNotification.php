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

        // Greeting & Subject
        $mailMessage->greeting($this->messages['greeting-text'] ?? 'Hello,')
                ->subject($this->messages['subject'] ?? 'Notification Email');
        
        // Lines
        if (!empty($this->messages['lines_array']) && is_array($this->messages['lines_array'])) {
            foreach ($this->messages['lines_array'] as $key => $value) {
                if (strpos($key, 'special_') === 0) {
                    // Format special keys like "special_Agency_Name" → "Agency Name"
                    $specialLabel = ucwords(str_replace('_', ' ', str_replace('special_', '', $key)));
                    $mailMessage->line($specialLabel . ': ' . $value);
                } else {
                    $mailMessage->line($value);
                }
            }
        }

        // Action Button (Optional)
        if (!empty($this->messages['url']) || !empty($this->messages['url-title'])) {
            $mailMessage->action(
                $this->messages['url-title'] ?? 'Action Not Required',
                !empty($this->messages['url']) ? url($this->messages['url']) : '#'
            );
        }

        // Additional Info & Closing
        if (!empty($this->messages['additional-info'])) {
            $mailMessage->line($this->messages['additional-info']);
        }

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
