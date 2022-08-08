<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
//メールのファサードとクラスを読み込む。
use Illuminate\Support\Facades\Mail;
use App\Mail\TestMail;
use App\Mail\ThanksMail;



class SendThankMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    //この中にメール送信設定を追加

    public $products;
    public $user;

    //checkoutメソッドから渡ってきたデータを受け取る。s
    public function __construct($products, $user)
    {
        $this->products = $products;
        $this->user = $user;
    }

   

    public function handle()
    {
        Mail::to($this->user) //受信者の指定(送信先)
        ->send(new ThanksMail($this->products, $this->user)); //Mailableクラス

    }
}
