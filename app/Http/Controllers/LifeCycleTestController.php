<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LifeCycleTestController extends Controller
{
    public function showServiceProviderTest() {
        //サービスプロバイダの使用
        $encrypt = app()->make('encrypter');
        //encryptで暗号化
        $password = $encrypt->encrypt('password');
        //サービスコンテナよりサービスプロバイダを取ってくる。
        $sample = app()->make('serviceProviderTest');

        //decryptで復号化
        dd($sample, $password, $encrypt->decrypt($password));
    }


    public function showServiceContainerTest() {
        //サービスコンテナの登録
        app()->bind('lifeCycleTest', function() {
            return 'ライフサイクルテスト';
        });

        //サービスコンテナの呼び出し
        $test = app()->make('lifeCycleTest');

        //サービスコンテナなしのパターン
        //まずそれぞれのクラスをインスタンス化して注入する必要がある。
        // $message = new Message();
        // $sample = new Sample($message);
        // $sample->run();


        //サービスコンテナapp()ありのパターン
        //Sampleクラスを結びつける
        app()->bind('sample', Sample::class);
        //Sampleクラスの呼び出し
        $sample = app()->make('sample');
        //sampleクラス内のrunメソッドを実行
        $sample->run();

        dd($test, app());
    }
}

class Sample {
    public $message;
    //DI（依存性の注入） => 引数にクラスを入れることで自動的にインスタンス化をしてくれる。
    public function __construct(Message $message)
    {
        $this->message = $message;
    }
    public function run() {
        //Messageクラスで定義したsendメソッドを使用できる。
        $this->message->send();
    }
}

class Message {
    public function send() {
        echo('メッセージ表示');
    }
}