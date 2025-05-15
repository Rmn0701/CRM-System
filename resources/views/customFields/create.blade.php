@extends('layouts.app')
@section('title', 'Create Custom Contact Field')
@section('content')
<div class="container mx-auto">
    <h2 class="text-2xl font-bold mb-4">Manage Custom Fields</h2>

    <form action="{{ route('contact-fields.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="name">Custom Field Name</label>
            <input type="text" id="name" name="name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="field_type">Select Field Type</label>
            <select id="field_type" name="field_type" class="form-control" required>
                <option value="">Choose a Field Type</option>
                <option value="text">Text</option>
                <option value="number">Number</option>
                <option value="email">Email</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Add Custom Field</button>
        <a href="{{ route('contacts.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
