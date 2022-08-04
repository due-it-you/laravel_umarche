<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Image;
use App\Models\Shop;
use App\Models\Product;
use App\Models\PrimaryCategory;
use App\Models\Owner;


class ProductController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:owners');

        $this->middleware(function ($request, $next) {

            $id = $request->route()->parameter('product'); //shopのid取得
            if(!is_null($id)) { //null判定（この場合は、”空じゃなったら”という分岐）
                $productsOwnerId = Product::findOrFail($id)->shop->owner->id;
                $productId = (int)$productsOwnerId; //キャスト（文字列 => 数値　に型変換

                if($productId !== Auth::id()) { //shopIdとownerIdが一致していなければ
                    abort(404); //404画面を出力
                }
            }
           return $next($request); 
        });
    }



    public function index()
    {

        //N+1問題が発生する相応しくいないコード
        //$products = Owner::findOrFail(Auth::id())->shop->product;
        
        //Eader Loading
        //※ with('')とすることで、N+1問題を解決
        $ownerInfo = Owner::with('shop.product.imageFirst')
        ->where('id', Auth::id())
        ->get();

        //取得したオーナーの情報を個々のオーナーとしてぶん回す
        // foreach($ownerInfo as $owner) {
        //     //取得した個々のオーナーからリレーションで商品の情報を取り出して更に回す。
        //     foreach($owner->shop->product as $product) {
        //         dd($product->imageFirst->filename);
        //     }
        // }
        
        return view('owner.products.index', compact('ownerInfo'));
    }



    public function create()
    {
        $shops = Shop::where('owner_id', Auth::id())
        ->select('id', 'name')
        ->get();

        $images = Image::where('owner_id', Auth::id())
        ->select('id', 'title', 'filename')
        ->orderBy('updated_at', 'desc')
        ->get();

        //N+1問題の解消 : with('')
        $categories = PrimaryCategory::with('secondary')
        ->get();

        return view('owner.products.create', compact('shops', 'images', 'categories'));
    }


    public function store(Request $request)
    {
        //
    }



    public function show($id)
    {
        //
    }



    public function edit($id)
    {
        //
    }


    public function update(Request $request, $id)
    {
        //
    }



    public function destroy($id)
    {
        //
    }
}
