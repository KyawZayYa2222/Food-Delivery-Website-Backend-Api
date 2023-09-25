<?php

namespace App\Http\Controllers\admin;

use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function totalIncome() {
        $orders = Order::all();
        $totalIncome = 0;
        foreach ($orders as $key => $order) {
            $cost = preg_replace('/[^0-9]/', '', $order->total_cost);
            $totalIncome += $cost;
        }

        return response()->json([
            'status' => 200,
            'totalIncome' => $totalIncome
        ]);
    }

    public function totalOrder() {
        $orders = Order::all();
        $totalOrder = count($orders);

        return response()->json([
            'status' => 200,
            'totalOrder' => $totalOrder
        ]);
    }

    public function totalProduct() {
        $products = Product::all();
        $totalProduct = count($products);

        return response()->json([
            'status' => 200,
            'totalProduct' => $totalProduct
        ]);
    }

    public function totalRegister() {
        $users = User::all();
        $totalRegister = count($users);

        return response()->json([
            'status' => 200,
            'totalRegister' => $totalRegister
        ]);
    }

    public function recentSales() {
        $recentSales = Order::where('status', 'accepted')
                            ->join('products', 'orders.product_id', 'products.id')
                            ->select('orders.*', 'products.name as product_name')
                            // ->where('status', 'accepted')
                            ->latest()
                            ->take(10)
                            ->get();

        return response($recentSales);
    }
}
