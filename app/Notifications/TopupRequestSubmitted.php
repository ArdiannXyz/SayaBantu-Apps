<?php

namespace App\Notifications;

use App\Models\BalanceTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TopupRequestSubmitted extends Notification
{
    use Queueable;

    protected $transaction;

    /**
     * Create a new notification instance.
     */
    public function __construct(BalanceTransaction $transaction)
    {
        $this->transaction = $transaction;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $historyRoute = ($notifiable->role ?? null) === 'mitra' ? 'mitra.topup.history' : 'customer.topup.history';

        return (new MailMessage)
            ->subject('Request Top-Up Saldo Diterima')
            ->greeting('Halo '.$notifiable->name.'!')
            ->line('Request top-up saldo Anda telah berhasil diterima.')
            ->line('Kode Request: '.$this->transaction->request_code)
            ->line('Nominal: '.format_rupiah($this->transaction->amount))
            ->line('Total Pembayaran: '.format_rupiah($this->transaction->total_payment))
            ->line('Request Anda akan diverifikasi oleh Super Admin maksimal 1x24 jam.')
            ->action('Lihat Status', route($historyRoute))
            ->line('Terima kasih telah menggunakan layanan kami!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $historyRoute = ($notifiable->role ?? null) === 'mitra' ? 'mitra.topup.history' : 'customer.topup.history';

        return [
            'type' => 'topup_request_submitted',
            'title' => '⏳ Request Top-Up Terkirim',
            'transaction_id' => $this->transaction->id,
            'request_code' => $this->transaction->request_code,
            'amount' => $this->transaction->amount,
            'total_payment' => $this->transaction->total_payment,
            'message' => 'Request top-up saldo Anda telah diterima dan menunggu verifikasi Super Admin.',
            'url' => route($historyRoute),
        ];
    }
}
