<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BnfNotification extends Notification
{
    use Queueable;
    private $data;

    /**
     * Create a new notification instance.
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('BNF Warning')
            ->greeting('Hello!')
            ->line('Terdapat BNF dengan detail sebagai berikut:')
            ->line('Supplier: ' . $this->data['supplier'])
            ->line('Part Number: ' . $this->data['part_number'])
            ->line('Part Name: ' . $this->data['part_name'])
            ->line('Masalah: ' . $this->data['problem'])
            ->line('Jumlah Quantity: ' . $this->data['qty'])
            ->line('Tanggal Penerbitan: ' . $this->data['issuance_date'])
            ->action('Lihat Detail', url('/')) // Update the URL to the relevant page
            ->salutation('Terima kasih,');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'data' => $this->data,
            'part_number' => $this->data['part_number'],
            'part_name' => $this->data['part_name'],
            'supplier' => $this->data['supplier'],
            'problem' => $this->data['problem'],
            'qty' => $this->data['qty'],
            'issuance_date' => $this->data['issuance_date'],
        ];
    }
}
