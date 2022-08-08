<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB; //クエリビルダ
use Illuminate\Support\Facades\Mail;
use App\Mail\TestMail;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\PrimaryCategory;
use App\Models\Stock;
use App\jobs\SendThankMail;

class ItemController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:users');

        //停止中（is_selling = 0)の商品を表示しないようにする処理
        $this->middleware(function ($request, $next) {

            
            $id = $request->route()->parameter('item');  //ルートパラメータの取得 
            if(!is_null($id)) { //null判定（この場合は、”空じゃなったら”という分岐）
                $itemId = Product::availableItems()->where('products.id', $id)->exists(); //現在販売中かつ在庫のある商品だけを取ってきて、trueかfalseかの判定を返す。

                if(!$itemId) { //もしtrueじゃなければ
                    abort(404); //404画面を出力
                }
            }
           return $next($request); 
        });
    }


    public function index(Request $request) {

        //非同期に送信（処理を軽くする）
        // SendThankMail::dispatch();

        //N+1問題の解消 : with('')
        $categories = PrimaryCategory::with('secondary')
        ->get();

        //全ての商品を取得する処理（ローカルスコープでまとめている）
        $products = Product::availableItems()
        //選んだカテゴリーのみを取ってくる処理
        ->selectCategory($request->category ?? '0')
        //検索内容に合わせた商品のidだけを取ってくる処理
        ->searchKeyword($request->keyword)
        //選んだ表示順を取ってくる処理
        ->sortOrder($request->sort)
        //選択した表示数だけ表示する処理（ページネーション）
        ->paginate($request->pagination ?? '20');

        return view('user.index', compact('products', 'categories'));
    }



    //ルートパラメータのためにidが入ってくる
    public function show($id) { 
        $product = Product::findOrFail($id);
                //商品の在庫数を取得
                $quantity = Stock::where('product_id', $product->id)
                ->sum('quantity');
            
            //在庫数が9より大きいときは9で固定する。
            if($quantity > 9){
                $quantity = 9;
            }

        return view('user.show', compact('product', 'quantity'));
    }
}