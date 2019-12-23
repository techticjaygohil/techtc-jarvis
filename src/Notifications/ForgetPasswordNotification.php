<?php

namespace Jarwis\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\HtmlString;

class ForgetPasswordNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->my_notification = $data;
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
        $data = $this->my_notification;
        return (new MailMessage)
        ->subject(env('APP_NAME').' | Forgot Password')
        ->from('anypol789@gmail.com', env('MAIL_FROM_EMAIL'))
        ->line('Hi '.$data->name.". We're confirming that...")
        ->line('Your password was updated in ' . env('APP_NAME'))
        ->line("We'll always let you know when there is any activity on your " . env('APP_NAME') . " account. This helps keep your account safe.")
        ->line(new HtmlString('Please login with your updated password "<b>'.$data->password.'</b>".'));
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
