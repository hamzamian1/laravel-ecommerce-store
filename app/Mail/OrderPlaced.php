<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderPlaced extends Mailable
{
    use Queueable, SerializesModels;

    public $order;

    /**
     * Create a new message instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order->load('items.product');
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Oxy-Bliss — Order Confirmed #' . $this->order->order_number)
                    ->view('emails.order-placed');
    }
}
