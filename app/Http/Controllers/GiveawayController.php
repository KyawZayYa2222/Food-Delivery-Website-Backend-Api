<?php

namespace App\Http\Controllers;

use App\Models\Giveaway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GiveawayController extends Controller
{
    public function show($id) {
        $giveaway = Giveaway::where('id', $id)->get()->first();

        return response($giveaway);
    }

    public function list() {
        $giveaways = Giveaway::get();

        return response($giveaways);
    }

    public function paginatedList() {
        $giveaways = Giveaway::paginate(8);

        return response($giveaways);
    }


    public function store(Request $request) {
        $fields = $request->validate([
            'name' => 'required|string|max:225',
            'image' => 'required|image|mimes:png,jpg,jpeg,svg|max:2084',
        ]);

        if($request->hasFile('image')) {
            $imageUrl = $this->StoreImage($request);
        }

        Giveaway::create([
            'name' => $fields['name'],
            'image' => $imageUrl,
        ]);

        return response()->json([
            'status' => 201,
            'message' => 'Giveaway successfully created.',
        ], 201);
    }


    public function update($id, Request $request) {
        $nameValidate = $request->validate([
            'name' => 'required|string|max:225',
        ]);


        if($request->image != 'null') {
            $imageValidate = $request->validate([
                'image' => 'image|mimes:png,jpg,jpeg,svg|max:2084',
            ]);

            if($request->hasFile('image')) {
                $this->DeleteImage($id);
                $imageUrl = $this->StoreImage($request);
            }

            Giveaway::where('id', $id)->update([
                'name' => $nameValidate['name'],
                'image' => $imageUrl,
            ]);
        } else {
            Giveaway::where('id', $id)->update([
                'name' => $nameValidate['name'],
            ]);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Giveaway successfully updated.',
        ], 200);
    }


    public function destory($id) {
        Giveaway::where('id', $id)->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Giveaway successfully deleted',
        ]);
    }


    private function MakeValidation($request) {
        $fields = $request->validate([
            'name' => 'required|string|max:225',
            'image' => 'required|image|mimes:png,jpg,jpeg,svg|max:1024',
        ]);
        return $fields;
    }

    // store image file
    private function StoreImage($request) {
        $image = uniqid() . $request->file('image')->getClientOriginalName();
        $path = $request->file('image')->storeAs('giveaway_image', $image, 'public');
        $domain = 'http://localhost:8000';
        $imageUrl = $domain . Storage::url($path);

        return $imageUrl;
    }

    // deleting image from storage
    private function DeleteImage($id) {
        $oldImgUrl = Giveaway::where('id', $id)->get()->first()->image;
        $segments = explode('/', $oldImgUrl);
        $oldImg = end($segments);
        Storage::delete('public/giveaway_image/'.$oldImg);
    }
}
