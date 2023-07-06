<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function store(Request $request) {
        $userId = Auth::user()->id;

        $cartItems = DB::table('carts')
                        ->join('products', 'carts.product_id', 'products.id')
                        ->where('user_id', $userId)
                        ->get();

        foreach ($cartItems as $cartItem) {
            Order::create([
                'user_id' => $cartItem->user_id,
                'product_id' => $cartItem->product_id,
                'product_count' => $cartItem->product_count,
                'location' => $request->location,
                'total_cost' => $cartItem->price * $cartItem->product_count,
            ]);
        }

        Cart::where('user_id', $userId)->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Order successfully created.'
        ]);
    }


    // public function store(Request $request) {
    //     $fields = $request->validate([
    //         'product_id' => 'required|integer|max:225',
    //         'product_count' => 'required|integer|max:2',
    //         'location' => 'required|string|max:225',
    //         'total_cost' => 'required|string|max:20',
    //     ]);

    //     $userId = Auth::user()->id;

    //     Order::create([
    //         'user_id' => $userId,
    //         'product_id' => $fields['product_id'],
    //         'product_count' => $fields['product_count'],
    //         'location' => $fields['location'],
    //         'total_cost' => $fields['total_cost'],
    //     ]);

    //     return response()->json([
    //         'status' => 201,
    //         'message' => 'Order successfully created',
    //     ], 201);
    // }


    // list of all order
    public function list() {
        $orderData = Order::paginate(8);

        return response($orderData);
    }

    // list of order of a user with unique id
    public function orderListOfAUser() {
        $userId = Auth::user()->id;

        $orderData = DB::table('orders')
                        ->join('products', 'orders.product_id', 'products.id')
                        ->select('orders.*', 'products.name')
                        ->where('user_id', $userId)
                        ->get();

        return response($orderData);
    }

    // Order accept
    public function accept($orderId) {
        Order::where('id', $orderId)->update([
            'status' => 'accepted',
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'Order successfully accepted',
        ]);
    }

    // Order rject
    public function reject($orderId) {
        Order::where('id', $orderId)->update([
            'status' => 'rejected',
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'Order successfully rejected',
        ]);
    }

    // Order delete
    public function destory($orderId) {
        Order::where('id', $id)->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Order successfully deleted',
        ]);
    }
}
