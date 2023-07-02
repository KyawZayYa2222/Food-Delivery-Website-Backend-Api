<?php

namespace App\Http\Controllers\admin;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    // category list
    public function list() {
        $categories = Category::get()->all();
        return response($categories);
    }

    public function listById($id) {
        $category = Category::where('id', $id)->get();
        return response($category);
    }

    // category creating
    public function store(Request $request) {
        $validator = $this->MakeValidation($request);

        // store image and path
        if($request->hasFile('image')) {
            $imageUrl = $this->StoreImage($request);
        }

        Category::create([
            'name' => $request->name,
            'image' => $imageUrl,
        ]);

        return response()->json([
            'status' => 201,
            'message' => 'Category successfully created.',
        ], 201);
    }


    // category updating
    public function update($id, Request $request) {
        $validator = $this->MakeValidation($request);

        if($request->hasFile('image')) {
            // deleting old image file
            $oldImgUrl = Category::where('id', $id)->get()->first()->image;
            $segments = explode('/', $oldImgUrl);
            $oldImg = end($segments);
            Storage::delete('public/category_image/'.$oldImg);

            $imageUrl = $this->StoreImage($request);
        }

        Category::where('id', $id)->update([
            'name' => $request->name,
            'image' => $imageUrl,
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'Category was updated sucessfully.',
            'data' => $oldImg
        ], 200);
    }


    // validation
    private function MakeValidation($request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'image' => 'required|image|mimes:png,jpg,jpeg|max:2084'
        ])->validate();

        return $validator;
    }

    // store image file
    private function StoreImage($request) {
        $image = uniqid() . $request->file('image')->getClientOriginalName();
        $path = $request->file('image')->storeAs('category_image', $image, 'public');
        $domain = 'http://localhost:8000';
        $imageUrl = $domain . Storage::url($path);

        return $imageUrl;
    }
}
