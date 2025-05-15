<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;

use App\Models\CustomField;
use App\Models\ContactCustomField;
use App\Models\ContactCustomFieldValue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;


class ContactController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $contacts = Contact::where('is_merged', false)
            ->when($request->name, fn($q) => $q->where('name', 'like', "%{$request->name}%"))
            ->when($request->email, fn($q) => $q->where('email', 'like', "%{$request->email}%"))
            ->when($request->gender, fn($q) => $q->where('gender', $request->gender))
            ->get();

        return view('contacts.index', compact('contacts'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $customFields = CustomField::all();
        return view('contacts.create', compact('customFields'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validate form data
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:contacts,email',
            'phone' => 'nullable|string|max:15',
            'gender' => 'required|in:Male,Female',
            'profile_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'additional_file' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'custom_fields' => 'nullable|array',
        ]);

        // Store the contact data
        $contact = new Contact();
        $contact->name = $request->name;
        $contact->email = $request->email;
        $contact->phone = $request->phone;
        $contact->gender = $request->gender;

        if ($request->hasFile('profile_image')) {
            $contact->profile_image = $request->file('profile_image')->store('profiles', 'public');
        }

        if ($request->hasFile('additional_file')) {
            $contact->additional_file = $request->file('additional_file')->store('files', 'public');
        }

        $contact->save();

        if ($request->has('custom_fields')) {
            foreach ($request->custom_fields as $fieldId => $value) {
                $contact->customFields()->attach($fieldId, ['value' => $value]);
            }
        }

        return response()->json(['status' => 'true', 'message' => 'Contacts saved successfully.']);
    }


    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Contact  $contact
     * @return \Illuminate\Http\Response
     */
    public function show(Contact $contact)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Contact  $contact
     * @return \Illuminate\Http\Response
     */
    // public function edit($id)
    // {
    //     $contact = Contact::with('customFieldsWithValues.customField')->findOrFail($id);
    //     return response()->json(['status' => 'true', 'data' => $contact]);
    // }

    public function edit($id)
    {
        $contact = Contact::with('customFieldsWithValues.customField')->findOrFail($id);

        return response()->json([
            'data' => $contact,
            'html' => view('contacts.form')->render()
        ]);
    }



    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Contact  $contact
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Contact $contact)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'nullable|string|max:20',
            'gender' => 'nullable|in:Male,Female',
            'profile_image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'additional_file' => 'nullable|file|max:2048',
        ]);

        if ($request->hasFile('profile_image')) {
            $validated['profile_image'] = $request->file('profile_image')->store('profile_images', 'public');
        }

        if ($request->hasFile('additional_file')) {
            $validated['additional_file'] = $request->file('additional_file')->store('additional_files', 'public');
        }
        $contact->update($validated);
     
        if ($request->has('custom_fields')) {
            foreach ($request->custom_fields as $fieldId => $value) {
                ContactCustomFieldValue::updateOrCreate(
                    ['contact_id' => $contact->id, 'custom_field_id' => $fieldId],
                    ['value' => $value]
                );
            }
        }

        return response()->json(['status' => 'true', 'message' => 'Contacts updated successfully.']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Contact  $contact
     * @return \Illuminate\Http\Response
     */
    public function destroy(Contact $contact)
    {
        //
    }


    public function filter(Request $request)
    {
        $contacts = Contact::where('is_merged', false)
            ->when($request->name, fn($q) => $q->where('name', 'like', "%{$request->name}%"))
            ->when($request->email, fn($q) => $q->where('email', 'like', "%{$request->email}%"))
            ->when($request->gender, fn($q) => $q->where('gender', $request->gender))
            ->get();

        return view('contacts.list', compact('contacts'))->render();
    }


    public function availableForMerge($contactId)
    {
        return Contact::where('id', '!=', $contactId)
            ->where('is_merged', false)
            ->get(['id', 'name']);
    }

    public function merge(Request $request)
    {
        $request->validate([
            'primary_id' => 'required|exists:contacts,id',
            'secondary_id' => 'required|exists:contacts,id|different:primary_id',
        ]);

        DB::beginTransaction();

        try {
            $primaryContact = Contact::findOrFail($request->primary_id);
            $secondaryContact = Contact::findOrFail($request->secondary_id);

            $secondaryContact->is_merged = true;
            $secondaryContact->merged_into = $primaryContact->id;
            $secondaryContact->save();

            $secondaryFields = ContactCustomFieldValue::where('contact_id', $secondaryContact->id)->get();

            foreach ($secondaryFields as $field) {
                ContactCustomFieldValue::updateOrCreate(
                    [
                        'contact_id' => $primaryContact->id,
                        'custom_field_id' => $field->custom_field_id,
                    ],
                    [
                        'value' => $field->value
                    ]
                );
            }

            DB::commit();

            return response()->json(['status' => 'true', 'message' => 'Contacts merged successfully.']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'false', 'error' => 'Merge failed: ' . $e->getMessage()], 500);
        }
    }

}
