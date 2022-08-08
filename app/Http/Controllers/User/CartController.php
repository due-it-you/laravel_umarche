<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Stock;
use App\Models\User;
use App\Services\CartService;

class CartController extends Controller
{
    public function index() 
    {
        //ユーザー情報
        $user = User::findOrFail(Auth::id());
        //商品情報
        $products = $user->products;
        $totalPrice = 0;

        foreach($products as $product){
            $totalPrice += $product->price * $product->pivot->quantity;
        }

        return view('user.cart', compact('products', 'totalPrice'));
    }

    public function add(Request $request) 
    {
        $itemInCart = Cart::where('product_id', $request->product_id)
        ->where('user_id', Auth::id())
        ->first();

        //商品があれば数を追加
        if($itemInCart){
            $itemInCart->quantity += $request->quantity;
            $itemInCart->save();

        } else {
            Cart::create([
                'user_id' => Auth::id(),
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
            ]);
        }

        return redirect()->route('user.cart.index');
    }


    public function delete($id)
    {
        Cart::where('product_id', $id)
        ->where('user_id', Auth::id())
        ->delete();

        return redirect()->route('user.cart.index');
    }

    //Stripeに商品情報を渡す処理
    public function checkout()
    {
        /////
        //現在ログインしているユーザーのカート情報を取得
        $items = Cart::where('user_id', Auth::id())->get();

        //そのうち、メール送信時に必要な商品情報を取得
        $products = CartService::getItemsInCart($items);
        /////

        $user = User::findOrFail(Auth::id());
        $products = $user->products;
        
        $lineItems = [];
        foreach ($products as $product) {
            $quantity = '';
            $quantity = Stock::where('product_id', $product->id)->sum('quantity');
            
            //ここで挙動が止まっている。
            if ($product->pivot->quantity > $quantity) {
                return redirect()->route('user.cart.index');
            } else {
                $lineItem = [
                    // 'price_data' => [
                    //     'currency' => 'jpy',
                    //     'unit_amount' => $product->price,
                    //     'product_data' => [
                    //         'name' => $product->name,
                    //         'description' => $product->information,
                    //     ],
                    //     'quantity' => $product->pivot->quantity,
                    // ],
                ];
                // array_push($lineItems, $lineItem);
            }
        }
        
    


        foreach ($products as $product) {
            Stock::create([
                'product_id' => $product->id,
                'type' => \Constant::PRODUCT_LIST['reduce'],
                'quantity' => $product->pivot->quantity * -1
            ]);
        }
        
        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET_KEY'));
     
        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                'currency' => 'jpy',
                'unit_amount' => $product->price,
                'product_data' => [
                    'name' => $product->name,
                    'description' => $product->information,
                ],
            ],
                'quantity' => $product->pivot->quantity,
        ]],
            'mode' => 'payment',
            'success_url' => route('user.cart.success'),
            'cancel_url' => route('user.cart.cancel'),
        ]);
     
        $publicKey = env('STRIPE_PUBLIC_KEY');

     
        return view(
            'user.checkout',
            compact('session', 'publicKey')
        );
    }


    //支払いが成功した時の処理
    public function success()
    {
        //カート内の商品を消す。
        Cart::where('user_id', Auth::id())->delete();

        return redirect()->route('user.items.index');
    }

    //支払いが失敗した時の処理
    public function cancel()
    {
        $user = User::findOrFail(Auth::id());

        //在庫をDBに戻す。
        //この処理の前に在庫がDBから引かれているけれども、支払いに失敗した場合はその在庫をまたDBに戻してあげる必要がある。
        foreach ($user->products as $product) {
            Stock::create([
                'product_id' => $product->id,
                'type' => \Constant::PRODUCT_LIST['add'],
                'quantity' => $product->pivot->quantity 
            ]);

            return redirect()->route('user.cart.index');

        }
    }
}