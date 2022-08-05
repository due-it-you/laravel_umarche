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
        // dd($request);

        //渡ってきたデータをバリデーション
        $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'information' => ['required', 'string','max:1000'],
            'price' => ['required', 'integer'],
            'sort_order' => ['nullable', 'integer'],
            'quantity' => ['required', 'integer'],
            'shop_id' => ['required', 'exists:shops,id'],
            'category' => ['required', 'exists:secondary_categories,id'],
            'image1' => ['nullable', 'exists:images,id'],
            'image2' => ['nullable', 'exists:images,id'],
            'image3' => ['nullable', 'exists:images,id'],
            'image4' => ['nullable', 'exists:images,id'],
            'is_selling' => ['required']
            ]);

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


    public function update(Request $request, $id)
    {
        //
    }



    public function destroy($id)
    {
        //
    }
}
