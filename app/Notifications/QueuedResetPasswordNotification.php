<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class QueuedResetPasswordNotification extends ResetPassword implements ShouldQueue
{
    use Queueable;

    public function __construct(string $token)
    {
        parent::__construct($token);

        $defaultConnection = (string) config('queue.default', 'database');

        $this->onConnection($defaultConnection === 'sync' ? 'database' : $defaultConnection);
        $this->onQueue('mail');
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Reset Password Akun Koperasi')
            ->view('emails.auth.reset-password', [
                'appName' => config('app.name', 'Koperasi'),
                'displayName' => $notifiable->profile?->nama_lengkap ?? $notifiable->username,
                'resetUrl' => $this->resetUrl($notifiable),
                'expireMinutes' => (int) config('auth.passwords.' . config('auth.defaults.passwords') . '.expire', 60),
                'logoUrl' => url('images/guest/koperasi.png'),
            ]);
    }

    protected function resetUrl($notifiable): string
    {
        return url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));
    }
}
