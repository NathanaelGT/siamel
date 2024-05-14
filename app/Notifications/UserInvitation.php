<?php

namespace App\Notifications;

use App\Models\User;
use App\Service\Auth\Invitation;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class UserInvitation extends Notification
{
    use Queueable;

    public function via(): array
    {
        return ['mail'];
    }

    public function toMail(User $user): MailMessage
    {
        $timeout = (int) config('auth.invitation_timeout');

        $now = CarbonImmutable::now();
        $humanTimeout = $now->diffForHumans(
            $now->addSeconds($timeout),
            syntax: CarbonInterface::DIFF_ABSOLUTE,
            parts: 7
        );

        $url = $this->verificationUrl($user, $timeout);

        return (new MailMessage)
            ->subject('Selamat Datang di SIAMEL')
            ->greeting("Halo, $user->name!")
            ->line('Silakan klik tombol di bawah untuk memproses akun Anda.')
            ->action('Proses Akun', $url)
            ->line("Aksi ini akan kedaluwarsa dalam $humanTimeout.");
    }

    protected function verificationUrl(User $user, int $timeout): string
    {
        return URL::temporarySignedRoute('accept-invitation', $timeout, [
            'userId' => Invitation::encodeId($user),
            'hash'   => Invitation::hashUser($user),
        ]);
    }
}
