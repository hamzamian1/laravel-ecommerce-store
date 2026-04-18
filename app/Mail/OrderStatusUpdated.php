<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdated extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $status;

    /**
     * Create a new message instance.
     */
    public function __construct(Order $order, $status)
    {
        // Eager load items and their products for the email template
        $this->order = $order->loadMissing('items.product');
        $this->status = $status;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $statusUpper = ucfirst($this->status);
        return $this->subject("Oxy-Bliss — Your Order #{$this->order->order_number} is {$statusUpper}")
                    ->view('emails.status-updated');
    }
}
