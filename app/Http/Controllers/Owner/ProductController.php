<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; //クエリビルダ
use App\Models\Image;
use App\Models\Shop;
use App\Models\Stock;
use App\Models\Product;
use App\Models\PrimaryCategory;
use App\Models\Owner;
use App\Http\Requests\ProductRequest;




class ProductController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:owners');

        $this->middleware(function ($request, $next) {

            
            $id = $request->route()->parameter('product');  //ルートパラメータの取得 
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


    public function store(ProductRequest $request)
    {
        // dd($request);

            //StockとProductに同時保存？
            try{
                DB::transaction(function() use($request) {
                    
                    $product = Product::create([
                        'name' => $request->name,
                        'information' => $request->information,
                        'price' => $request->price,
                        'sort_order' => $request->sort_order,
                        'shop_id' => $request->shop_id,
                        'secondary_category_id' => $request->category,
                        'image1' => $request->image1,
                        'image2' => $request->image2,
                        'image3' => $request->image3,
                        'image4' => $request->image4,
                        'is_selling' => $request->is_selling,
                    ]);
    
                    Stock::create([
                        'product_id' => $product->id,
                        'type' => 1,
                        'quantity' => $request->quantity,
                    ]);
    
                }, 2);
            }catch(Throwable $e){
                Log::error($e);
                throw $e;
            }
    
    
            return redirect()
            ->route('owner.products.index')
            ->with([
                'message'=> '商品登録しました。',
                'status' => 'info'
        ]);

    }


    public function edit($id)
    {
        //渡ってきたidに紐づく商品を取ってくる。
        $product = Product::findOrFail($id);

        //商品の在庫数を取得
        $quantity = Stock::where('product_id', $product->id)
                            ->sum('quantity');

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

        return view('owner.products.edit', 
        compact('product', 'quantity', 'shops', 'images', 'categories'));
        
    }


    public function update(ProductRequest $request, $id)
    {
        //ProductRequest以外のバリデーション
        $request->validate([
            'current_quantity' => 'required|integer',
        ]);

        //商品を一つ取ってくる、
        $product = Product::findOrFail($id);
        //その商品の現在の在庫数
        $quantity = Stock::where('product_id', $product->id)
                    ->sum('quantity');

        //Editを開いた後に在庫数に変動があった場合、エラーを返す。
        //在庫数に変動がなければ問題なし。
        if($request->current_quantity !== $quantity){
            //productのルートパラメータの取得
            $id = $request->route()->parameter('product');
            //ルートパラメータを持った状態でリダイレクト
            return redirect()->route('owner.products.edit', ['product' => $id])
            ->with(['message'=> '在庫数が変更されています。再度確認してください。',
                'status' => 'alert']);
        } else {
                        //StockとProductに同時保存
                        try{
                            DB::transaction(function() use($request, $product) {
                                
                               
                                    $product->name = $request->name;
                                    $product->information = $request->information;
                                    $product->price = $request->price;
                                    $product->sort_order = $request->sort_order;
                                    $product->shop_id = $request->shop_id;
                                    $product->secondary_category_id = $request->category;
                                    $product->image1 = $request->image1;
                                    $product->image2 = $request->image2;
                                    $product->image3 = $request->image3;
                                    $product->image4 = $request->image4;
                                    $product->is_selling = $request->is_selling;
                                    $product->save();
                
                                //マジックナンバー回避のため、判定の数値を定数化（場所: App\Constants )
                                //クラス名の前にバックスラッシュをつけると、use文なしでも使える。
                                if($request->type === \Constant::PRODUCT_LIST['add']){
                                    $newQuantity = $request->quantity;
                                }
                                if($request->type === \Constant::PRODUCT_LIST['reduce']){
                                    $newQuantity = $request->quantity * -1;
                                }
                                
                                Stock::create([
                                    'product_id' => $product->id,
                                    'type' => $request->type,
                                    'quantity' => $newQuantity,
                                ]);
                
                            }, 2);
                        }catch(Throwable $e){
                            Log::error($e);
                            throw $e;
                        }
                
                
                        return redirect()
                        ->route('owner.products.index')
                        ->with([
                            'message'=> '商品情報を更新しました。',
                            'status' => 'info'
                    ]);
        }
    }



    public function destroy($id)
    {
        //
    }
}
