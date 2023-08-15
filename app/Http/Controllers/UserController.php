<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    // User list
    public function list() {
        $users = User::paginate(8);

        return response($users);
    }

    // User details
    public function show() {
        $userId = $this->UserId();

        $userDetails = User::where('id', $userId)->get()->first();

        return response($userDetails);
    }


    // User info update
    public function infoUpdate(Request $request) {
        $userId = $this->UserId();

        $fields = $request->validate([
            'name' => 'required|string|max:225',
            'email' => 'required|email|unique:users,email,except,'.$userId,
            'phone' => 'required|string|max:14',
            'address' => 'required|string|max:255',
        ]);

        User::where('id', $userId)->update($fields);

        return response()->json([
            'status' => 200,
            'message' => 'User info successfully updated.'
        ]);
    }


    // User profile update
    public function profileImageUpdate(Request $request) {
        return response($request->image);
        $field = $request->validate([
            'image' => 'required|image|mimes:png,jpg,jpeg,svg|max:2048'
        ]);

        $userId = $this->UserId();

        $oldProfileImageUrl = User::where('id', $userId)->get()->first()->image;

        if($oldProfileImageUrl != null) {
            $segments = explode('/', $oldProfileImageUrl);
            $oldImg = end($segments);
            Storage::delete('public/profile_image/'.$oldImg);
        }

        // store image
        $domain = 'http://localhost:8000';
        $image = uniqid() . $request->file('image')->getClientOriginalName();
        $path = $request->file('image')->storeAs('profile_image', $image, 'public');
        $imageUrl = $domain . Storage::url($path);

        User::where('id', $userId)->update([
            'image' => $imageUrl,
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'User profile successfully updated.'
        ]);
    }

    // Change password
    public function passwordUpdate(Request $request) {
        $fields = $request->validate([
            'current_password' => 'required|string|max:255',
            'new_password' => 'required|string|confirmed',
        ]);

        $userId = $this->UserId();

        $hashedPassword = User::where('id', $userId)->get()->first()->password;

        if(!Hash::check($request->current_password, $hashedPassword)) {
            return response()->json([
                'status' => 422,
                'message' => 'Password does not match!'
            ], 422);
        }

        $password = Hash::make($request->password);

        User::where('id', $userId)->update([
            'password' => $password,
        ]);

        Auth()->user()->tokens()->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Your password successfully updated. Please Login again!'
        ]);
    }

    // User id
    private function UserId() {
        return Auth::user()->id;
    }
}
