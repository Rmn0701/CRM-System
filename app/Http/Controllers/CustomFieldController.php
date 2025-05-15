<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CustomField;

class CustomFieldController extends Controller
{
    public function index()
    {
        $data = CustomField::all();
        return view('customFields.index', compact('data'));
    }

    public function list()
    {
        $data = CustomField::all();
        return response()->json([
            'status' => 'true', 
            'data' => $data
        ]);
    }

    public function create()
    {
        return view('customFields.create');
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);

        $field = new CustomField();
        $field->name = $request->name;
        $field->save();

        return redirect()->route('contact-fields.index')->with('success', 'Custom field added successfully.');
    }

    public function show(CustomField $field)
    {
        //
    }

    public function destroy(CustomField $id)
    {
        $field = CustomField::findOrFail($id);
        $field->delete();

        return redirect()->route('contact-fields')->with('success', 'Custom field deleted successfully!');
    }

}
