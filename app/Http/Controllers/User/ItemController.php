<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB; //クエリビルダ
use Illuminate\Http\Request;
use App\Models\Product;

class ItemController extends Controller
{
    public function index() {
        //クエリビルダでの商品情報の取得処理

        //product_idを纏めた上で、合計在庫総数を取ってくる。(ただし在庫数は1以上のものに限る。)
        $stocks = DB::table('t_stocks')
        ->select('product_id',
        DB::raw('sum(quantity) as quantity'))
        ->groupBy('product_id')
        ->having('quantity', '>', 1);

        $products = DB::table('products')
            ->joinSub($stocks, 'stock', function($join){
                $join->on('products.id', '=', 'stock.product_id');
            })
            //products, shops, secondary_categories, image1~4のそれぞれのテーブルとカラムを連結させる。
            ->join('shops', 'products.shop_id', '=', 'shops.id')
            ->join('secondary_categories', 'products.secondary_category_id', '=', 'secondary_categories.id')
            ->join('images as image1', 'products.image1', '=', 'image1.id')
            ->join('images as image2', 'products.image2', '=', 'image2.id')
            ->join('images as image3', 'products.image3', '=', 'image3.id')
            ->join('images as image4', 'products.image4', '=', 'image4.id')

            //"販売中"のもののみを取ってくる。
            ->where('shops.is_selling', true)
            ->where('products.is_selling', true)
            //商品ID、商品名、価格、表示順番、商品情報、小カテゴリー、第一画像のファイル名　を取得 (今回はEloquantで取得するのではなく、クエリビルダで取得するためselectを使う)
            ->select('products.id as id', 'products.name as name', 'products.price', 'products.sort_order as sort_order'
                        ,'products.information', 'secondary_categories.name as category', 'image1.filename as filename')
            ->get();


        return view('user.index', compact('products'));
    }
}
