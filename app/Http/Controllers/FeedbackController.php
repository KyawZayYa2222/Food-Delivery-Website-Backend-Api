<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeedbackController extends Controller
{
    public function list() {
        $feedbacks = Feedback::leftJoin('users', 'feedbacks.user_id', 'users.id')
                            ->select('feedbacks.*', 'users.name')
                            ->orderBy('feedbacks.created_at', 'desc')
                            ->paginate(8);

        return response($feedbacks);
    }

    public function publicList() {
        $feedbacks = Feedback::join('users', 'feedbacks.user_id', 'users.id')
                            ->select('feedbacks.message', 'feedbacks.public', 'users.name as user_name', 'users.image as user_image')
                            ->where('public', 1)
                            ->get();

        return response($feedbacks);
    }

    public function controlPublic($id) {
        $feedback = Feedback::find($id);
        if($feedback->public == 0) {
            $feedback->public = 1;
        } else {
            $feedback->public = 0;
        }
        $feedback->save();
    }

    public function store(Request $request) {
        $request->validate(['message' => 'required|string']);

        $userId = Auth::user()->id;
        Feedback::create([
            'user_id' => $userId,
            'message' => $request->message,
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'Feedback successfully created.'
        ]);
    }

    public function destroy($id) {
        $feedback = Feedback::find($id);
        if($feedback) {
            $feedback->delete();
        }

        return response()->json([
            'status' => 200,
            'message' => 'Feedback successfully deleted.'
        ]);
    }
}
