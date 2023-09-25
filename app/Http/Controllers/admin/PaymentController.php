<?php

namespace App\Http\Controllers\admin;

use App\Models\Payment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PaymentController extends Controller
{
    public function list() {
        $payment = Payment::orderBy('created_at', 'desc')
                            ->paginate(8);

        return response($payment);
    }

    public function verify($id) {
        $payment = Payment::find($id);
        if($payment) {
            $payment->status = 'verified';
            $payment->save();
        }

        return response()->json([
            'status' => 200,
            'message' => 'Payment was verified.',
        ]);
    }

    public function reject($id) {
        $payment = Payment::find($id);
        if($payment) {
            $payment->status = 'rejected';
            $payment->save();
        }

        return response()->json([
            'status' => 200,
            'message' => 'Payment was rejected.',
        ]);
    }
}
