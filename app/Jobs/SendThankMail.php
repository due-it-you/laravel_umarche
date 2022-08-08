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


class SendThankMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    //この中にメール送信設定を追加

    public function __construct()
    {
        //
    }

   

    public function handle()
    {
        Mail::to('test@example.com') //受信者の指定(送信先)
        ->send(new TestMail()); //Mailableクラス

    }
}
