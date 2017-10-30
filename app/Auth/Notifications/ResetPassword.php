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
            ->line('You are receiving this email because we received a password reset request for your account.')
            ->action('Reset Password', url(config('app.url').route('password.reset', $this->token, false)))
            ->line('This token will expire in 24 hours.')
            ->line('If you did not request a password reset, no further action is required.');
    }
}
