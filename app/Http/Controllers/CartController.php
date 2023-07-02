<?php

namespace App\Http\Controllers;

use App\Models\Cart;
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
    }

    public function list() {
        $userId = Auth::user()->id;

        $cartItems = Cart::where('user_id', $userId)->get();

        return response($cartItems);
    }

    public function destory($id) {
        Cart::where('id', $id)->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Cart item sucessfully removed.',
        ]);
    }
}
