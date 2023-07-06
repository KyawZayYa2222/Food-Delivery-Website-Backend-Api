<?php

namespace App\Http\Controllers\admin;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    // product list
    public function list() {
        $products = DB::table('products')
                        ->join('categories', 'products.category_id', '=', 'categories.id')
                        ->select('products.*', 'categories.name as category_name')
                        ->paginate(8);
        return response($products);
    }

    // product order by descending
    public function orderByDesc() {
        $products = Product::orderBy('id', 'desc')->paginate(8);

        return response($products);
    }

    // Product search
    public function find(Request $request) {
        if($request->search) {
            $products = Product::where('name', 'like', '%' . request('search') . '%')->get();
        } else {
            $products = Product::paginate(8);
        }

        return response($products);
    }

    // Product list by categroy
    public function listByCategory($categoryId) {
        $products = DB::table('products')
                        ->join('categories', 'products.category_id', '=', 'categories.id')
                        ->select('products.*', 'categories.name as category_name')
                        ->where('category_id', $categoryId)
                        ->paginate(8);

        return response($products);
    }

    // product by id
    public function show($id) {
        $productDetails = DB::table('products')
                            ->join('categories', 'products.category_id', '=', 'categories.id')
                            ->select('products.*', 'categories.name as category_name')
                            ->where('id', $id)
                            ->get();

        return response($productDetails);
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
