<?php

namespace App\Http\Controllers\admin;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function list() {
        $products = Product::with([
                        'category' => fn($q) => $this->GetCategory($q),
                        'promotion' => fn($q) => $this->GetPromotion($q)
                        ])
                        ->when(request('search'), function($q) {
                            $q->where('name', 'like', '%' . request('search') . '%');
                        })
                        ->when(request('category_id'), function($q) {
                            $q->where('category_id', request('category_id'));
                        })
                        ->when(request('order_by'), function($q) {
                            if(request('order_by')==='desc') {
                                $q->orderBy('id', 'desc');
                            }
                        })
                        ->paginate(8);

        return response($products);
    }

    // product by id
    public function show($id) {
        $productDetails = Product::with(['category' => fn($query) => $this->GetCategory($query),
                            'promotion' => fn($query) => $this->GetPromotion($query)])
                            ->where('id', $id)
                            ->get()->first();

        return response($productDetails);
    }

    // product creating
    public function store(Request $request) {
        $validator = $this->MakeValidation($request);

        if($request->hasFile('image')) {
            $imageUrl = $this->StoreImage($request);
        }

        Product::create($this->QueryData($request, $imageUrl));
        $this->IncreaseProductCount($request->category_id);

        return response()->json([
            'status' => 201,
            'message' => 'Product successfully created.',
        ], 201);
    }

    // product updating
    public function update($id, Request $request) {
        $validator = $this->MakeValidation($request);
        $product = Product::find($id);

        if($product) {
            $oldCategoryId = $product->category_id;
            if($request->hasFile('image')) {
                $this->DeleteImage($product->image);
                $imageUrl = $this->StoreImage($request);
            }
            Product::where('id', $id)
                    ->update($this->QueryData($request, $imageUrl));

            $this->DecreaseProductCount($oldCategoryId);
            $this->IncreaseProductCount($request->category_id);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Product successfully updated.',
        ], 200);
    }

    public function destory($id) {
        $product = Product::find($id);
        if($product) {
            $this->DeleteImage($product->image);
            $categoryId = $product->category_id;
            $product->delete();
            $this->DecreaseProductCount($categoryId);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Product successfully deleted.',
        ]);
    }

    // Product data
    // private function GetProduct() {
    //     $products = Product::with(['category' => fn($query) => $this->GetCategory($query),
    //                         'promotion' => fn($query) => $this->GetPromotion($query)])
    //                         ->paginate(8);

    //     return $products;
    // }

    // Related category
    private function GetCategory($query) {
        $data = $query->select('id', 'name', 'image');
        return $data;
    }

    // Related promotion
    private function GetPromotion($query) {
        $data = $query->where('active', 1)
                      ->select('id', 'promotion_type', 'cashback',
                      'giveaway_id', 'discount', 'start_date', 'end_date');

        return $data;
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

    private function IncreaseProductCount($categoryId) {
        $category = Category::find($categoryId);
        if($category) {
            $category->increment('product_count');
            $category->save();
        }
    }

    private function DecreaseProductCount($categoryId) {
        $category = Category::find($categoryId);
        if($category) {
            $category->decrement('product_count');
            $category->save();
        }
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
            'price' => $request->price . 'Ks',
            'short_desc' => $request->short_desc,
            'long_desc' => $request->long_desc,
            'image' => $imageUrl,
            'category_id' => $request->category_id,
            'promotion_id' => $request->promotion_id,
        ];
    }

    // deleting image from storage
    private function DeleteImage($imageUrl) {
        $segments = explode('/', $imageUrl);
        $oldImg = end($segments);
        Storage::delete('public/product_image/'.$oldImg);
    }
}
