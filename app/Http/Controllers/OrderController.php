<?php

namespace App\Http\Controllers;

use stdClass;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
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
        $userId = Auth::user()->id;

        // check user info
        if(!Auth::user()->address || !Auth::user()->phone) {
            return response()->json([
                'message' => 'Please fills your profile details first.'
            ], 400);
        }

        $cartItems = Cart::where('user_id', $userId)->get();
        if(count($cartItems) > 0) {
            foreach ($cartItems as $cartItem) {
                $product = Product::where('id', $cartItem->product_id)
                            ->get()
                            ->first();
                $productPrice = preg_replace('/[^0-9]/', '', $product->price);
                $promotionId = $product->promotion_id;
                $promotion = Promotion::where('id', $promotionId)
                                ->get()
                                ->first();

                // calculating cost for promotion
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
                        $totalCost = $productPrice;
                        break;
                }

                $payment = Payment::create([
                    'payment_type' => $request->payment_type,
                    'verified' => 1,
                ]);

                Order::create([
                    'user_id' => $cartItem->user_id,
                    'product_id' => $cartItem->product_id,
                    'product_count' => $cartItem->product_count,
                    'location' => $request->location,
                    'total_cost' => $totalCost,
                    'payment_id' => $payment->id,
                ]);
            }

            Cart::where('user_id', $userId)->delete();

            return response()->json([
                'status' => 200,
                'message' => 'Order successfully created.'
            ]);
        } else {
            return response()->json([
                'status' => 422,
                'message' => 'No cart items to order.'
            ], 422);
        }
    }


    // list of all order
    public function list() {
        // $orderData = Order::join('users', 'orders.user_id', 'users.id')
        //                 ->join('products', 'orders.product_id', 'products.id')
        //                 ->select('orders.*', 'users.name as user_name', 'users.email', 'users.phone',
        //                 'users.address', 'users.image as user_image', 'products.name as product_name',
        //                 'products.price', 'products.image as product_image')
        //                 ->paginate(8);
        $orderData = Order::leftJoin('users', 'orders.user_id', 'users.id')
                        ->leftJoin('products', 'orders.product_id', 'products.id')
                        ->leftJoin('payments', 'orders.payment_id', 'payments.id')
                        ->leftJoin('promotions', 'products.promotion_id', 'promotions.id')
                        ->leftJoin('giveaways', 'promotions.giveaway_id', 'giveaways.id')
                        ->select('orders.*', 'users.name as user_name', 'products.name as product_name', 'payments.verified',
                        'promotions.promotion_type', 'promotions.discount', 'promotions.cashback', 'promotions.giveaway_id', 'giveaways.name')
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

        $orders = Order::leftJoin('products', 'orders.product_id', 'products.id')
                    ->leftJoin('promotions', 'products.promotion_id', 'promotions.id')
                    ->leftJoin('giveaways', 'promotions.giveaway_id', 'giveaways.id')
                    ->select('orders.*', 'products.name', 'products.image', 'promotions.discount',
                    'promotions.promotion_type', 'promotions.cashback', 'giveaways.name as giveaway')
                    ->get();

        return response($orders);
    }

    // Order accept
    public function accept($orderId) {
        $resp = $this->OrderActions($orderId, 'accept');

        return response()->json([
            'status' => $resp->status,
            'message' => $resp->mesg,
        ], $resp->status);
    }

    // Order rject
    public function reject($orderId) {
        $resp = $this->OrderActions($orderId, 'reject');

        return response()->json([
            'status' => $resp->status,
            'message' => $resp->mesg,
        ], $resp->status);
    }

    // Order delete
    public function destory($orderId) {
        $resp = $this->OrderActions($orderId, 'delete');

        return response()->json([
            'status' => $resp->status,
            'message' => $resp->mesg,
        ], $resp->status);
    }

    // Order action handler
    private function OrderActions($id, $action) {
        $order = Order::where('id', $id)->get()->first();
        $resp = new stdClass();

        if($order != null) {
            switch ($action) {
                case 'accept':
                    Order::where('id', $orderId)->update([
                        'status' => 'accepted',
                    ]);
                    $resp->status = 200;
                    $resp->mesg = 'Order successfully accepted.';
                    break;

                case 'reject':
                    Order::where('id', $orderId)->update([
                        'status' => 'rejected',
                    ]);
                    $resp->status = 200;
                    $resp->mesg = 'Order successfully rejected.';
                    break;

                case 'delete':
                    Order::where('id', $id)->delete();
                    $resp->status = 200;
                    $resp->mesg = 'Order successfully deleted.';
                    break;

                default:
                    # code...
                    break;
            }
        } else {
            $resp->status = 422;
            $resp->mesg = 'No item to perform actions';
        }

        return $resp;
    }
}
