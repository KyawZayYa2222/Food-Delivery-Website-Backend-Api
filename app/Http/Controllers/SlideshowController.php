<?php

namespace App\Http\Controllers;

use App\Models\Slideshow;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class SlideshowController extends Controller
{
    public function activeList() {
        $slideshows = Slideshow::where('active', 1)->get();

        return response($slideshows);
    }

    public function list() {
        $slideshows = Slideshow::all();

        return response($slideshows);
    }

    public function store(Request $request) {
        $fields = $this->MakeValidation($request);

        // Validate not to date in the past
        $today = Carbon::now()->format('Y-m-d');
        if($request->show_date < $today || $request->end_date < $today) {
            return response()->json([
                'status' => 422,
                'message' => 'Not valid dates before today of start_date and end_date.',
            ], 422);
        }

        if($request->hasFile('image')) {
            $imageUrl = $this->StoreImage($request);
        }

        $slideshow = Slideshow::create($this->InsertQuery($fields, $imageUrl));

        $this->ActiveSlideshow($slideshow->id, $request);

        return response()->json([
            'status' => 201,
            'message' => 'Slideshow successfully created.',
        ], 201);
    }

    // update
    public function update($id, Request $request) {
        $fields = $this->MakeValidation($request);

        // Validate not to date in the past
        $today = Carbon::now()->format('Y-m-d');
        if($request->show_date < $today || $request->end_date < $today) {
            return response()->json([
                'status' => 422,
                'message' => 'Not valid dates before today of start_date and end_date.',
            ], 422);
        }

        if($request->hasFile('image')) {
            // delete old image
            $this->DeleteImage($id);
            $imageUrl = $this->StoreImage($request);
        }

        $slideshow = Slideshow::where('id', $id)
                        ->update($this->InsertQuery($fields, $imageUrl));

        $this->ActiveSlideshow($id, $request);

        return response()->json([
            'status' => 200,
            'message' => 'Slideshow successfully updated.',
        ], 200);
    }

    // delete
    public function destroy($id) {
        $slideshow = Slideshow::find($id);
        if($slideshow) {
            $slideshow->delete();
        }

        return response()->json([
            'status' => 200,
            'message' => 'Slideshow successfully deleted.'
        ]);
    }


    // Validation
    private function MakeValidation($request) {
        $fields = $request->validate([
            'name' => 'required|string|max:225',
            'title' => 'required|string|max:225',
            'sub_title' => 'required|string|max:225',
            'description' => 'required|string',
            'image' => 'required|image|mimes:png,jpg,jpeg,svg|max: 3048',
            'show_date' => 'required|date|max:10',
            'end_date' => 'required|date|max:10',
        ]);

        return $fields;
    }

    // insert query
    private function InsertQuery($fields, $imageUrl) {
        return [
            'name' => $fields['name'],
            'title' => $fields['title'],
            'sub_title' => $fields['sub_title'],
            'description' => $fields['description'],
            'image' => $imageUrl,
            'show_date' => $fields['show_date'],
            'end_date' => $fields['end_date'],
        ];
    }

    // store image file
    private function StoreImage($request) {
        $image = uniqid() . $request->file('image')->getClientOriginalName();
        $path = $request->file('image')->storeAs('slideshow_image', $image, 'public');
        $domain = 'http://localhost:8000';
        $imageUrl = $domain . Storage::url($path);

        return $imageUrl;
    }

    // deleting image from storage
    private function DeleteImage($id) {
        $oldImgUrl = Slideshow::where('id', $id)->get()->first()->image;
        $segments = explode('/', $oldImgUrl);
        $oldImg = end($segments);
        Storage::delete('public/slideshow_image/'.$oldImg);
    }

    // Active promotion
    private function ActiveSlideshow($id, $request) {
        if($request->show_date == Carbon::now()->format('Y-m-d')) {
            Slideshow::where('id', $id)->update([
                'active' => 1,
            ]);
        } else {
            Slideshow::where('id', $id)->update([
                'active' => 0,
            ]);
        }
    }
}
