<?php

namespace Northstar\Auth\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPassword extends ResetPasswordNotification
{
    /**
     * Build the mail representation of the notification.
     * (This is our custom override of the default email message.)
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->line('You are receiving this email because we received a password reset request for your DoSomething.org account. Here is the link to reset your password:')
            ->action('Reset Password', url(config('app.url').route('password.reset', $this->token, false)))
            ->line('This link will expire in 24 hours. Once you click the button above, you will be asked to reset your password on the page. If you did not request a password reset, you can ignore this email. Your password will not change and your account is safe.')
            ->line('If you have further questions, please reach out to help@dosomething.org.');
    }
}
