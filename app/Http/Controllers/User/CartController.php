<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Stock;
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
            'success_url' => route('user.items.index'),
            'cancel_url' => route('user.cart.index'),
        ]);
     
        $publicKey = env('STRIPE_PUBLIC_KEY');

     
        return view(
            'user.checkout',
            compact('session', 'publicKey')
        );
    }

}