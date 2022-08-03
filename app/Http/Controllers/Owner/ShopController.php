<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Shop;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use InterventionImage;
use App\Http\Requests\UploadImageRequest;
use App\Services\ImageService;



class ShopController extends Controller
{
    //自動的にオーナーでログインできているかの認証確認
    public function __construct()
    {
        $this->middleware('auth:owners');

        $this->middleware(function ($request, $next) {
            // dd($request->route()->parameter('shop')); //文字列としてidを取得
            // dd(Auth::id()); //数字

            //現在ログインしているオーナーと同じidの店のみ表示させる処理
            //owner_id = 1 なら　shop_id = 1　の場合のみ表示ができる。（それ以外は404でエラーを返す)s
            $id = $request->route()->parameter('shop'); //shopのid取得
            if(!is_null($id)) { //null判定（この場合は、”空じゃなったら”という分岐）
                $shopOwnerId = Shop::findOrFail($id)->owner->id;
                $shopId = (int)$shopOwnerId; //キャスト（文字列 => 数値　に型変換
                $ownerId = Auth::id();

                if($shopId !== $ownerId) { //shopIdとownerIdが一致していなければ
                    abort(404); //404画面を出力
                }
            }
           return $next($request); 
        });
    }


    public function index() {
        //Auth::id() => ログインしているオーナーのidを取得
        $shops = Shop::where('owner_id', Auth::id())->get();

        return view('owner.shops.index', compact('shops'));
    }



    public function edit($id) {

        $shop = Shop::findOrFail($id);
        
        return view('owner.shops.edit', compact('shop'));

        // dd(Shop::findOrFail($id));
    }


    //使いたいフォームリクエストを引数に取っている。（今回は自分で作ったUploadImageRequestを使用してバリデーションを行なっている）
    public function update(UploadImageRequest $request, $id) {

             //フォームで入力された値をバリデーション => OKだったら次に進める
            $request->validate([
                'name' => ['required', 'string', 'max:50'],
                'information' => ['required', 'string', 'max:1000'],
                'is_selling' => ['required'],
            ]);
        
        //画像のアップロード処理
        $imageFile = $request->image;
        if(!is_null($imageFile) && $imageFile->isValid()) {

            //画像のリサイズから保存までの処理(サービスにまとめたので、それを用いる。)
            $fileNameToStore = ImageService::upload($imageFile, 'shops');


        }

        $shop = Shop::findOrFail($id);
        $shop->name = $request->name;
        $shop->information = $request->information;
        $shop->is_selling = $request->is_selling;

        if(!is_null($imageFile) && $imageFile->isValid()){
            $shop->filename = $fileNameToStore;
        }

        $shop->save();

        return redirect()
        ->route('owner.shops.index')
        ->with([
            'message'=> '店舗情報を更新しました。',
            'status' => 'info'
            ]);
    } 
}
