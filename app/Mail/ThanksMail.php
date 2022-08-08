<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ThanksMail extends Mailable
{
    use Queueable, SerializesModels;


    public $products;
    public $user;

    // Controller => Jobs => "Mail => Blade" と渡していく。
    public function __construct($products, $user)
    {   
        $this->products = $products;
        $this->user = $user;
    }


    public function build()
    {
        return $this->view('emails.thanks')
                    ->subject('ご購入ありがとうございます。');
    }
}
