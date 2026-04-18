<?php

namespace App\Mail;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewProductNotification extends Mailable
{
    use Queueable, SerializesModels;

    public Product $product;

    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '✨ New Arrival: ' . $this->product->name . ' | Oxy-Bliss',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.new_product',
        );
    }
}
