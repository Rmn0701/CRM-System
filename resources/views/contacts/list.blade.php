<table class="table table-bordered">
    <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Gender</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($contacts as $contact)
            <tr @if($contact->is_merged) class="table-warning" @endif>
                <td>{{ $contact->name }}</td>
                <td>
                    {{ $contact->email }}
                    @if($contact->mergedContacts->isNotEmpty())
                        , {{ $contact->mergedContacts->pluck('email')->implode(', ') }}
                    @endif
                </td>
                <td>
                    {{ $contact->phone }}
                    @if($contact->mergedContacts->isNotEmpty())
                        , {{ $contact->mergedContacts->pluck('phone')->implode(', ') }}
                    @endif
                </td>
                <td>{{ $contact->gender }}</td>
                <td>
                    <button id="openCreateEditModal" class="btn btn-sm btn-info editBtn" data-id="{{ $contact->id }}">Edit</button>
                    @if(!$contact->is_merged)
                        <button class="btn btn-sm btn-secondary mergeBtn" data-id="{{ $contact->id }}">Merge</button>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="9">No contacts found.</td>
            </tr>
        @endforelse
    </tbody>
</table>
