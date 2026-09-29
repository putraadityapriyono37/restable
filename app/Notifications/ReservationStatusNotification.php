<?php

namespace App\Notifications;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class ReservationStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Reservation $reservation,
        public string $status,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $restaurant = $this->reservation->restaurant;
        $title = match ($this->status) {
            'confirmed' => 'Reservasi Dikonfirmasi',
            'cancelled' => 'Reservasi Dibatalkan',
            'completed' => 'Reservasi Selesai',
            default => 'Status Reservasi Diperbarui',
        };

        $mail = (new MailMessage)
            ->subject(config('app.name').' - '.$title)
            ->greeting('Halo, '.$notifiable->name.'!')
            ->line($title.' untuk reservasi #'.$this->reservation->id.' di '.$restaurant->name.'.')
            ->line('Tanggal: '.Carbon::parse($this->reservation->reservation_date)->format('d M Y'))
            ->line('Jam: '.substr($this->reservation->reservation_time, 0, 5))
            ->line('Jumlah tamu: '.$this->reservation->guest_count)
            ->line('Status: '.$this->status);

        if ($this->status === 'confirmed') {
            $mail->action('Lihat Reservasi', route('reservations.show', $this->reservation));
        }

        return $mail;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'reservation_id' => $this->reservation->id,
            'status' => $this->status,
        ];
    }
}
