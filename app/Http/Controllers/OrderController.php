<?php

namespace App\Http\Controllers;

use stdClass;
use App\Models\Cart;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function store(Request $request) {
        $validator = $request->validate([
            'location' => 'required|string|max:225',
        ]);

        $userId = Auth::user()->id;

        $cartItems = DB::table('carts')
                        ->join('products', 'carts.product_id', 'products.id')
                        ->select('carts.*', 'products.name', 'products.price')
                        ->where('user_id', $userId)
                        ->get();

        if(count($cartItems) > 0) {
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
        } else {
            return response()->json([
                'status' => 422,
                'message' => 'No cart items to order.'
            ], 422);
        }
    }


    // list of all order
    public function list() {
        $orderData = Order::join('users', 'orders.user_id', 'users.id')
                        ->join('products', 'orders.product_id', 'products.id')
                        ->select('orders.*', 'users.name as user_name', 'users.email', 'users.phone',
                        'users.address', 'users.image as user_image', 'products.name as product_name',
                        'products.price', 'products.image as product_image')
                        ->paginate(8);

        return response($orderData);
    }

    // list of order of a user with unique id
    public function orderListOfAUser() {
        $userId = Auth::user()->id;

        $orderData = Order::join('users', 'orders.user_id', 'users.id')
                        ->join('products', 'orders.product_id', 'products.id')
                        ->select('orders.*', 'users.name as user_name', 'users.email', 'users.phone',
                        'users.address', 'users.image as user_image', 'products.name as product_name',
                        'products.price', 'products.image as product_image')
                        ->where('orders.user_id', $userId)
                        ->get();

        return response($orderData);
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
