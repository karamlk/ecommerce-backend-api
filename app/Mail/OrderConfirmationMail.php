<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

      public function __construct(
        public int    $orderId,
        public float  $total,
        public string $status,
    ) {}

   public function build()
    {
        return $this->view('emails.order_confirmation')
            ->with([
                'orderId' => $this->orderId,
                'total'   => $this->total,
                'status'  => $this->status,
            ])
            ->subject('Order Confirmation #' . $this->orderId);
    }
}