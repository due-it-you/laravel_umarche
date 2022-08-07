<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\User;

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
        //ユーザー情報
        $user = User::findOrFail(Auth::id());
        //ユーザーが持つ商品情報
        $products = $user->products;
        //カートに入っている全ての商品情報
        $lineItems =[];

        foreach($products as $product){
            //カートに入っている個々の商品情報 (キーはstripe APIであらかじめ用意されたものに基づく。)
            $lineItem = [

                'name' => $product->name,
                'description' => $product->information,
                'amount' => $product->price,
                'currency' => 'jpy',
                'quantity' => $product->pivot->quantity,

            ];
            array_push($lineItems, $lineItem);
        }

        //stripeを呼び出し(秘密鍵)
        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET_KEY'));

        //Stripe側にセッションとして渡す
        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [$lineItems],
            'mode' => 'payment',
            'success_url' => route('user.items.index'),
            'cancel_url' => route('user.cart.index'),
        ]);

        //公開鍵を渡す
        $publicKey = env('STRIPE_PUBLIC_KEY');

        return view('user.checkout', compact('session', 'publicKey'));
    }
}
