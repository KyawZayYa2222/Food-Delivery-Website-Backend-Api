<?php

namespace App\Http\Controllers;

use stdClass;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Feedback;
use App\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    // order creating
    public function store(Request $request) {
        $validator = $request->validate([
            'location' => 'required|string|max:100',
            'payment_type' => 'required|string|max:10',
        ]);

        if(!Auth::user()->address || !Auth::user()->phone) {
            return response()->json([
                'message' => 'Please fills your profile details first.'
            ], 400);
        }

        $userId = Auth::user()->id;
        $cartItems = Cart::where('user_id', $userId)->get();
        if(count($cartItems) == 0) {
            return response()->json([
                'status' => 422,
                'message' => 'No cart items to order.'
            ], 422);
        }

        $payment = Payment::create([
            'payment_type' => $request->payment_type,
        ]);

        foreach ($cartItems as $cartItem) {
            $product = Product::where('id', $cartItem->product_id)
                        ->get()
                        ->first();
            $productPrice = preg_replace('/[^0-9]/', '', $product->price);
            $promotionId = $product->promotion_id;
            $promotion = Promotion::where('id', $promotionId)
                            ->get()
                            ->first();
            $totalCost = $productPrice;

            // calculating cost for promotion
            if($promotion != null) {
                switch ($promotion->promotion_type) {
                    case 'discount':
                        $discount = preg_replace('/[^0-9]/', '', $promotion->discount);
                        $totalCost = ($productPrice * $cartItem->product_count) * ($discount / 100);
                        break;

                    case 'cashback':
                        $cashback = preg_replace('/[^0-9]/', '', $promotion->cashback);
                        $totalCost = ($productPrice * $cartItem->product_count) - $cashback;
                        break;

                    default:
                        break;
                }
            }

            Order::create([
                'user_id' => $cartItem->user_id,
                'product_id' => $cartItem->product_id,
                'product_count' => $cartItem->product_count,
                'location' => $request->location,
                'total_cost' => $totalCost.'Ks',
                'payment_id' => $payment->id,
            ]);
        }

        Cart::where('user_id', $userId)->delete();

        $feedback = Feedback::where('user_id', $userId)->first();
        if($feedback != '') {
            $feedback = false;
        } else {
            $feedback = true;
        }

        return response()->json([
            'status' => 200,
            'message' => 'Order successfully created.',
            'feedback' => $feedback,
        ]);
    }


    // list of all order
    public function list() {
        $orderData = Order::leftJoin('users', 'orders.user_id', 'users.id')
                        ->leftJoin('products', 'orders.product_id', 'products.id')
                        ->leftJoin('promotions', 'products.promotion_id', 'promotions.id')
                        ->leftJoin('giveaways', 'promotions.giveaway_id', 'giveaways.id')
                        ->select('orders.*', 'users.name as user_name', 'products.name as product_name', 'promotions.promotion_type',
                        'promotions.discount', 'promotions.cashback', 'promotions.giveaway_id', 'giveaways.name as giveaway_name')
                        ->paginate(8);

        return response($orderData);
    }

    // User order cancel
    public function orderCancel($id) {
        $userId = Auth::user()->id;

        Order::where('user_id', $userId)
               ->where('id', $id)
               ->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Your order item successfully canceled.',
        ]);
    }

    // list of order of a user with unique id
    public function orderListOfAUser() {
        $userId = Auth::user()->id;

        $orders = Order::where('user_id', $userId)
                    ->leftJoin('products', 'orders.product_id', 'products.id')
                    ->leftJoin('promotions', 'products.promotion_id', 'promotions.id')
                    ->leftJoin('giveaways', 'promotions.giveaway_id', 'giveaways.id')
                    ->select('orders.*', 'products.name', 'products.image', 'promotions.discount',
                    'promotions.promotion_type', 'promotions.cashback', 'giveaways.name as giveaway')
                    ->get();

        return response($orders);
    }

    // Order accept
    public function accept($id) {
        $order = Order::find($id);
        if($order) {
            $order->status = 'accepted';
            $order->save();
        }

        return response()->json([
            'status' => 200,
            'message' => 'Order was accepted.',
        ]);
    }

    // Order rject
    public function reject($id) {
        $order = Order::find($id);
        if($order) {
            $order->status = 'rejected';
            $order->save();
        }

        return response()->json([
            'status' => 200,
            'message' => 'Order was rejected.',
        ]);
    }

    // Order delete
    public function destory($id) {
        $order = Order::find($id);
        if($order) {
            $order->delete();
        }

        return response()->json([
            'status' => 200,
            'message' => 'Order was deleted.',
        ]);
    }
}
