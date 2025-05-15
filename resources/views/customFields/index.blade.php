@extends('layouts.app')
@section('title', 'Custom Contact Fields List')


@section('content')
<div class="container mx-auto">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    
    
    <div class="row">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-2xl font-bold">Custom Fields List</h2>
            <div>
                <a href="{{ route('contacts.index') }}" class="btn btn-secondary">Contacts List</a>
                <a href="{{ route('contact-fields.create') }}" class="btn btn-info">Create Custom Field</a>
            </div>
        </div>
    </div>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Name</th>
                <th>Field Type</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $customField)
            <tr>
                <td>{{ $customField->name }}</td>
                <td>{{ $customField->field_type }}</td>
            </tr>
            @empty
            @endforelse
        </tbody>
    </table>
</div>
@endsection
