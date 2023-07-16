<?php

namespace App\Http\Controllers;

use App\Models\Subscriber;
use Illuminate\Http\Request;

class SubscriberController extends Controller
{
    public function list() {
        $subscribers = Subscriber::all();

        return response($subscribers);
    }

    public function store(Request $request) {
        $fields = $request->validate([
            'email' => 'required|email|unique:subscribers,email'
        ]);

        Subscriber::create($fields);

        return response()->json([
            'status' => 201,
            'message' => 'You subscribed.'
        ], 201);
    }

    public function destory($id) {
        Subscriber::where('id', $id)->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Subscriber was deleted.',
        ]);
    }
}
