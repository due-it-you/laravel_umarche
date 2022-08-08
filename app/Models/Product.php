<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use App\Models\Shop;
use App\Models\SecondaryCategory;
use App\Models\Image;
use App\Models\Stock;
use App\Models\User;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'shop_id',
        'name',
        'information',
        'price',
        'is_selling',
        'sort_order',
        'secondary_category_id',
        'image1',
        'image2',
        'image3',
        'image4',
    ];

    public function shop() {
        return $this->belongsTo(Shop::class);
    }

    public function category() {
        return $this->belongsTo(SecondaryCategory::class, 'secondary_category_id');
    }

    public function imageFirst() {
        return $this->belongsTo(Image::class, 'image1', 'id');
    }
    public function imageSecond() {
        return $this->belongsTo(Image::class, 'image2', 'id');
    }
    public function imageThird() {
        return $this->belongsTo(Image::class, 'image3', 'id');
    }
    public function imageFourth() {
        return $this->belongsTo(Image::class, 'image4', 'id');
    }


    public function stock() {
        return $this->hasMany(Stock::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'carts')
        ->withPivot(['id','quantity']);
    }

    //現在販売できる商品の取得処理
    public function scopeAvailableItems($query)
    {
        //クエリビルダでの商品情報の取得処理

        //product_idを纏めた上で、合計在庫総数を取ってくる。(ただし在庫数は1以上のものに限る。)
        $stocks = DB::table('t_stocks')
        ->select('product_id',
        DB::raw('sum(quantity) as quantity'))
        ->groupBy('product_id')
        ->having('quantity', '>', 1);

        return $query->
        //全てのテーブルを連結
        //products, shops, secondary_categories, image1のそれぞれのテーブルとカラムを連結させる。
          join('shops', 'products.shop_id', '=', 'shops.id')
        ->join('secondary_categories', 'products.secondary_category_id', '=', 'secondary_categories.id')
        ->join('images as image1', 'products.image1', '=', 'image1.id')
        //"販売中"のもののみを取ってくる。
        ->where('shops.is_selling', true)
        ->where('products.is_selling', true)
        //商品ID、商品名、価格、表示順番、商品情報、小カテゴリー、第一画像のファイル名　を取得 (今回はEloquantで取得するのではなく、クエリビルダで取得するためselectを使う)
        ->select('products.id as id', 'products.name as name', 'products.price', 'products.sort_order as sort_order'
                    ,'products.information', 'secondary_categories.name as category', 'image1.filename as filename');
    }


    //表示順の切り替え・判別処理(スコープ)
    public function scopeSortOrder($query, $sortOrder)
    {
        if($sortOrder === null || $sortOrder === \Constant::SORT_ORDER['recommend']){
            return $query->orderBy('sort_order', 'asc');
        }
        if($sortOrder === \Constant::SORT_ORDER['higherPrice']){
            return $query->orderBy('price', 'desc');
        }
        if($sortOrder === \Constant::SORT_ORDER['lowerPrice']){
            return $query->orderBy('price', 'asc');
        }
        if($sortOrder === \Constant::SORT_ORDER['later']){
            return $query->orderBy('products.created_at', 'desc');
        }
        if($sortOrder === \Constant::SORT_ORDER['older']){
            return $query->orderBy('products.created_at', 'asc');
        }
    }

    //選択したカテゴリーのIDを返す処理
    public function scopeSelectCategory($query, $categoryId)
    {
        if($categoryId !== '0'){
            return $query->where('secondary_category_id', $categoryId);
        } else {
            return;
        }
    }
}
