<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB; //クエリビルダ
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Stock;

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

        //全ての商品を取得する処理（ローカルスコープでまとめている）
        $products = Product::availableItems()
        ->sortOrder($request->sort)
        ->get();

        return view('user.index', compact('products'));
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
