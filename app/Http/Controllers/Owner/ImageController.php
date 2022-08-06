<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Image;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\UploadImageRequest;
use App\Services\ImageService;
use Illuminate\Support\Facades\Storage;






class ImageController extends Controller
{


    public function __construct()
    {
        $this->middleware('auth:owners');

        $this->middleware(function ($request, $next) {

            $id = $request->route()->parameter('image'); //shopのid取得
            if(!is_null($id)) { //null判定（この場合は、”空じゃなったら”という分岐）
                $imagesOwnerId = Image::findOrFail($id)->owner->id;
                $imageId = (int)$imagesOwnerId; //キャスト（文字列 => 数値　に型変換

                if($imageId !== Auth::id()) { //shopIdとownerIdが一致していなければ
                    abort(404); //404画面を出力
                }
            }
           return $next($request); 
        });
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $images = Image::where('owner_id', Auth::id())
        ->orderBy('updated_at', 'desc')
        ->paginate(20);

        return view('owner.images.index', compact('images'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('owner.images.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(UploadImageRequest $request)
    {   
        //file() => 複数のファイルを取得
        $imageFiles = $request->file('files');

        if(!is_null($imageFiles)) {
            foreach($imageFiles as $imageFile) {
                $fileNameToStore = ImageService::upload($imageFile, 'products');
                Image::create([
                    'owner_id' => Auth::id(),
                    'filename' => $fileNameToStore
                ]);
            }
        }

        return redirect()
        ->route('owner.images.index')
        ->with([
            'message'=> '画像登録を実施しました。',
            'status' => 'info'
            ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $image = Image::findOrFail($id);
        
        return view('owner.images.edit', compact('image'));
    }


    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => ['string', 'max:50'],
        ]);

        $image = Image::findOrFail($id);
        $image->title = $request->title;

        $image->save();

        return redirect()
        ->route('owner.images.index')
        ->with([
            'message'=> '画像情報を更新しました。',
            'status' => 'info'
            ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //画像のデータを一つ取得
        $image = Image::findOrFail($id);

        //商品に既に添付されている画像を取得
        $imageInProducts = 
        Product::where('image1', $image->id)
        ->orWhere('image2', $image->id)
        ->orWhere('image3', $image->id)
        ->orWhere('image4', $image->id)
        ->get();

        //削除時、外部キー制約のエラーを回避するため、image1~4のデータをnullとする。
        if($imageInProducts)
        {
            $imageInProducts->each(function($product) use($image) {

                if($product->image1 === $image->id){
                    $product->image1 = null;
                    $product->save();
                }

                if($product->image2 === $image->id){
                    $product->image2 = null;
                    $product->save();
                }

                if($product->image3 === $image->id){
                    $product->image3 = null;
                    $product->save();
                }

                if($product->image4 === $image->id){
                    $product->image4 = null;
                    $product->save();
                }
            });
        }

        //ストレージ内に画像があれば、ファイル内の画像を先に削除
        $filePath = 'public/products/' . $image->filename;
        if(Storage::exists($filePath)) {
            Storage::delete($filePath);
        };

        //画面の画像を削除
        Image::findOrFail($id)->delete(); 

        return redirect()
        ->route('owner.images.index')
        ->with([
            'message' => '画像を削除しました。',
            'status' => 'alert'
            ]);
    }
}
