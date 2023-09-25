<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function list() {
        $contacts = Contact::paginate(8);

        return response($contacts);
    }


    public function store(Request $request) {
        $fields = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'message' => 'required|string|max:522',
        ]);

        Contact::create([
            'name' => $fields['name'],
            'email' => $fields['email'],
            'message' => $fields['message'],
        ]);

        return response()->json([
            'status' => 201,
            'message' => 'Your message successfully send.',
        ], 201);
    }


    public function destory($id) {
        $contact = Contact::find($id);
        if($contact) {
            $contact->delete();
        }

        return response()->json([
            'status' => 200,
            'message' => 'Contact successfully deleted.'
        ]);
    }
}
