<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Image;
use App\Models\Product;
use App\Models\SecondaryCategory;
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
        //ログインしているオーナーが作っている商品を取得
        $products = Owner::findOrFail(Auth::id())->shop->product;
        
        return view('owner.products.index', compact('products'));
    }



    public function create()
    {
        //
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
