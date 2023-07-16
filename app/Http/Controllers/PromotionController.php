<?php

namespace App\Http\Controllers;

// use Carbon\Carbon;
use App\Models\Product;
use App\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class PromotionController extends Controller
{
    // List
    public function list() {
        $promotions = Promotion::with('giveaway')->get();

        return response($promotions);
    }

    // create
    public function store(Request $request) {
        $fields = $this->MakeValidation($request);

        $this->ValidatePromotion($request);

        // Validate not to date in the past
        $today = Carbon::now()->format('Y-m-d');
        if($request->start_date < $today || $request->end_date < $today) {
            return response()->json([
                'status' => 422,
                'message' => 'Not valid dates before today of start_date and end_date.',
            ], 422);
        }

        $promotion = Promotion::create($fields);

        // promotion active if start today
        $this->ActivePromotion($promotion->id, $request);

        return response()->json([
            'status' => 201,
            'message' => 'Promotion successfully created.'
        ], 201);
    }


    // update
    public function update($id, Request $request) {
        $fields = $this->MakeValidation($request);

        $this->ValidatePromotion($request);

        // Validate not to date in the past
        $today = Carbon::now()->format('Y-m-d');
        if($request->start_date < $today || $request->end_date < $today) {
            return response()->json([
                'status' => 422,
                'message' => 'Not valid dates before today of start_date and end_date.',
            ], 422);
        }

        $promotion = Promotion::where('id', $id)->update($fields);

        // promotion active if start today
        $this->ActivePromotion($promotion->id, $request);

        return response()->json([
            'status' => 200,
            'message' => 'Promotion successfully updated.'
        ], 200);
    }


    // delete
    public function destory($id) {
        Product::where('promotion_id', $id)->update([
            'promotion_id' => null,
        ]);

        Promotion::where('id', $id)->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Promotion successfully deleted.',
        ]);
    }


    // Validation
    private function MakeValidation($request) {
        $fields = $request->validate([
            'promotion_type' => 'required|string|max:10',
            'cashback' => 'string|max:10',
            'giveaway_id' => 'integer|max:10',
            'discount' => 'string|max:10',
            'start_date' => 'required|date|max:10',
            'end_date' => 'required|date|max:10',
        ]);

        return $fields;
    }

    // validate promotion parameter
    private function ValidatePromotion($request) {
        switch ($request->promotion_type) {
            case 'cashback':
                $request->validate([
                    'cashback' => 'required'
                ]);
                break;
            case 'giveaway':
                $request->validate([
                    'giveaway_id' => 'required'
                ]);
                break;
            case 'discount':
                $request->validate([
                    'discount' => 'required'
                ]);
                break;
            default:
                # code...
                break;
        }
    }

    // Active promotion
    private function ActivePromotion($id, $request) {
        if($request->start_date == Carbon::now()->format('Y-m-d')) {
            Promotion::where('id', $id)->update([
                'active' => 1,
            ]);
        } else {
            Promotion::where('id', $id)->update([
                'active' => 0,
            ]);
        }
    }
}
