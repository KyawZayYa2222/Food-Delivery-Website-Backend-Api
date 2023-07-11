<?php

namespace App\Http\Controllers;

use stdClass;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer|max:20',
        ])->validate();

        $userId = Auth::user()->id;
        $product = Product::where('id', $request->product_id)->get()->first();

        if($product != null) {
            $currentItem = Cart::where('user_id', $userId)
                            ->where('product_id', $request->product_id)
                            ->get()
                            ->first();

            // if exist item, increase produc_count else create
            if($currentItem != null) {
                $increaseCount = $currentItem->product_count + 1;
                Cart::where('user_id', $userId)
                    ->where('product_id', $request->product_id)
                    ->update(['product_count' => $increaseCount]);
            } else {
                Cart::create([
                    'product_id' => $request->product_id,
                    'user_id' => $userId,
                ]);
            }

            return response()->json([
                'status' => 200,
                'message' => 'Your product added to cart sucessfully.',
            ]);
        } else {
            return response()->json([
                'status' => 422,
                'message' => 'No product to add your cart.'
            ]);
        }
    }


    public function list() {
        $userId = Auth::user()->id;

        $cartItems = Cart::join('products', 'carts.product_id', 'products.id')
                        ->select('carts.*', 'products.name', 'products.price', 'products.image')
                        ->where('user_id', $userId)
                        ->get();

        return response($cartItems);
    }

    // Item increase
    public function increaseCount($id) {
        $resp = $this->ChangeCount($id, 'increase');

        return response()->json([
            'status' => $resp->status,
            'message' => $resp->mesg,
        ], $resp->status);
    }


    // Item decrease
    public function decreaseCount($id) {
        $resp = $this->ChangeCount($id, 'decrease');

        return response()->json([
            'status' => $resp->status,
            'message' => $resp->mesg,
        ], $resp->status);
    }


    public function destory($id) {
        Cart::where('id', $id)->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Cart item sucessfully removed.',
        ]);
    }


    // Handle increase & decrease logic
    private function ChangeCount($id, $change) {
        $userId = Auth::user()->id;
        $cartItems = Cart::where('user_id', $userId)
                           ->where('id', $id)
                           ->get()
                           ->first();
        $resp = new stdClass();

        if($cartItems != null) {
            if($change === 'increase') {
                $newCount = $cartItems->product_count < 6 ? $cartItems->product_count + 1 : $cartItems->product_count;
            } else {
                $newCount = $cartItems->product_count > 1 ? $cartItems->product_count - 1 : $cartItems->product_count;
            }

            Cart::where('id', $id)->update([
                'product_count' => $newCount,
            ]);

            $resp->status = 200;
            $resp->mesg = $change === 'increase' ? 'Cart item increased.' : 'Cart item decreased.';
        } else {
            $resp->status = 422;
            $resp->mesg = $change === 'increase' ? 'No cart item to increase count.' : 'No cart item to decrease count.';
        }

        return $resp;
    }
}
