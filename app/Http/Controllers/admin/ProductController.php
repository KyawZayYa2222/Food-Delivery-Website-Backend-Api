<?php

namespace App\Http\Controllers\admin;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    // category list
    public function list() {
        $products = Product::get()->all();
        return response($products);
    }


    // product creating
    public function store(Request $request) {
        $validator = $this->MakeValidation($request);

        if($request->hasFile('image')) {
            $imageUrl = $this->StoreImage($request);
        }

        Product::create($this->QueryData($request, $imageUrl));

        return response()->json([
            'status' => 201,
            'message' => 'Product successfully created.',
        ], 201);
    }


    // product updating
    public function update($id, Request $request) {
        $validator = $this->MakeValidation($request);

        if($request->hasFile('image')) {
            // deleting old image file
            $this->DeleteImage($id);

            $imageUrl = $this->StoreImage($request);
        }

        Product::where('id', $id)->update($this->QueryData($request, $imageUrl));

        return response()->json([
            'status' => 200,
            'message' => 'Product successfully updated.',
        ], 200);
    }


    public function destory($id) {
        // deleting old image file
        $this->DeleteImage($id);

        Product::where('id', $id)->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Product successfully deleted.',
        ]);
    }


    // validation
    private function MakeValidation($request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'price' => 'required|string|max:10',
            'short_desc' => 'required|string|max:255',
            'long_desc' => 'required|string',
            'image' => 'required|image|mimes:png,jpg,jpeg,svg|max:1024',
            'category_id' => 'required',
        ])->validate();

        return $validator;
    }

    // store image file
    private function StoreImage($request) {
        $image = uniqid() . $request->file('image')->getClientOriginalName();
        $path = $request->file('image')->storeAs('product_image', $image, 'public');
        $domain = 'http://localhost:8000';
        $imageUrl = $domain . Storage::url($path);

        return $imageUrl;
    }

    // Query to insert to db
    private function QueryData($request, $imageUrl) {
        return [
            'name' => $request->name,
            'price' => $request->price,
            'short_desc' => $request->short_desc,
            'long_desc' => $request->long_desc,
            'image' => $imageUrl,
            'category_id' => $request->category_id,
        ];
    }

    // deleting image from storage
    private function DeleteImage($id) {
        $oldImgUrl = Product::where('id', $id)->get()->first()->image;
        $segments = explode('/', $oldImgUrl);
        $oldImg = end($segments);
        Storage::delete('public/product_image/'.$oldImg);
    }
}
