@extends('layouts.app')
@section('title', 'Contact List')

@section('content')
<div class="container mx-auto">
    <div id="alertPlaceholder"></div>

    <div class="row" id="filtersContainer">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-2xl font-bold">Contact List</h2>
            <div>
                <button class="btn btn-primary" id="openCreateModal">Create Contact</button>
                <!-- <a href="{{ route('contacts.create') }}" class="btn btn-primary" id="openCreateModal">Create Contact</a> -->
                <a href="{{ route('contact-fields.index') }}" class="btn btn-secondary">List Custom Fields</a>
            </div>
        </div>

        <!-- Filters -->
        <form id="filterForm" method="POST">
            @csrf
            <div class="row mb-3">
                <div class="col-md-3">
                    <input type="text" id="filter_name" name="name" class="form-control" placeholder="Filter by name">
                </div>
                <div class="col-md-3">
                    <input type="text" id="filter_email" name="email" class="form-control" placeholder="Filter by email">
                </div>
                <div class="col-md-3">
                    <select id="filter_gender" name="gender" class="form-control">
                        <option value="">All Genders</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" id="applyFilters" class="btn btn-outline-primary">Apply Filters</button>
                </div>
            </div>
        </form>
    </div>


    <!-- Contact List -->
    <div id="contactsList">
        @include('contacts.list', ['contacts' => $contacts])
    </div>

    <!-- Merge Modal -->
    <div id="mergeModal" class="modal" style="display:none">
        <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered"> 
            <div class="modal-content">
                <div class="modal-header">
                        <h4 class="modal-title">Select Master Contact</h4>
                        <button type="button" class="btn-close" onclick="$('#mergeModal').hide()"></button>
                    </div>
                <form id="mergeForm" method="post" action="{{ route('contacts.merge') }}">
                    @csrf
                    <input type="hidden" id="merge_primary_id" name="primary_id">
                    <label for="merge_secondary_id">Select Secondary Contact</label>
                    <select id="merge_secondary_id" name="secondary_id" class="form-control"></select>
                    <button type="submit" class="btn btn-success mt-5">Confirm Merge</button>
                </form>
            </div>
        </div>
    </div>


    <div id="contactModal" class="modal" tabindex="-1" style="display:none">
        <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered"> 
            <div class="modal-content">
                <div class="modal-header">
                    <h4 id="modalTitle" class="modal-title"></h4>
                    <button type="button" class="btn-close" onclick="$('#contactModal').hide()"></button>
                </div>
                <div class="modal-body" id="modalBody"></div>
            </div>
        </div>
    </div>



</div>
@endsection

@section('scripts')
<script type="text/javascript">
    const storeUrl = '{{ route("contacts.store") }}';
    const updateUrlTemplate = '{{ url("contacts") }}/:id'; 
    const storageUrl = "{{ asset('storage') }}";

    $(document).ready(function() {
        loadContacts(); 
        loadCustomFields();

        $('#openCreateModal').on('click', function (e) {
            e.preventDefault();
            $.ajax({
                url: '{{ route("contacts.ajax.create") }}',
                success: function(html) {
                    $('#modalBody').html(html);
                    $('#contactForm')[0].reset();
                    $('#contact_id').val('');
                    $('#modalTitle').text('Create Contact');
                    loadCustomFields(); 
                    $('#contactModal').show();
                },
                error: function() {
                    alert('Failed to load form');
                }
            });
        });

        $(document).on('click', '.editBtn', function () {
            const id = $(this).data('id');

            $.ajax({
                url: `/contacts/${id}/edit`, 
                method: 'GET',
                success: function (response) {
                    $('#modalTitle').text('Edit Contact');
                    $('#modalBody').html(response.html);
                    $('#submitBtn').text('Update Contact');

                    let contact = response.data;
                    $('#contact_id').val(contact.id);
                    $('#name').val(contact.name);
                    $('#email').val(contact.email);
                    $('#phone').val(contact.phone);
                    $(`input[name="gender"][value="${contact.gender}"]`).prop('checked', true);
                    
                    $('#pic').html('');
                    if (contact.profile_image != null && contact.profile_image !== '') {
                        let img = `<img src="${storageUrl}/${contact.profile_image}" width="50" height="50">`;
                        $('#pic').html(img);
                    }

                    $('#add_pic').html('');
                    if (contact.additional_file != null && contact.additional_file !== '') {
                        let file = `<img src="${storageUrl}/${contact.additional_file}" width="50" height="50">`;
                        $('#add_pic').html(file);
                    }

                    let html = '';
                    contact.custom_fields_with_values.forEach(item => {
                        html += `
                            <div class="mb-3">
                                <label>${item.custom_field.name}</label>
                                <input type="text" class="form-control" name="custom_fields[${item.custom_field.id}]" value="${item.value}">
                            </div>
                        `;
                    });
                    $('#customFieldsContainer').html(html);
                    $('#contactModal').show();
                },
                error: function () {
                    alert('Failed to load contact data.');
                }
            });
        });

        // Submit form via AJAX
        $(document).on('submit', '#contactForm', function(e) {
            e.preventDefault();
            let formData = new FormData(this);

            let contactId = $('#contact_id').val();

            console.log({contactId});
            

            let ajaxUrl = contactId 
                ? updateUrlTemplate.replace(':id', contactId)
                : storeUrl;

            if (contactId) {
                formData.append('_method', 'PUT'); // Laravel method spoofing
            }
            // console.log({ajaxUrl})

            $.ajax({
                url: ajaxUrl,
                method: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    $('#alertPlaceholder').html(`
                        <div class="alert alert-success alert-dismissible fade show" role="alert" id="ajaxSuccessAlert">
                            ${response.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `);

                    loadContacts();
                    $('#contactForm')[0].reset();
                    $('#contactModal').hide();
                    $('.text-danger').remove();
                    $('#submitBtn').text('Update Contact');

                    setTimeout(function () {
                        $('#ajaxSuccessAlert').alert('close');
                    }, 3000);
                },
                error: function(xhr) {
                    $('#contactForm .text-danger').remove();
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        let firstErrorField = null;
                        let shownFields = {}; 

                        $.each(errors, function(field, messages) {
                            if (shownFields[field]) return;

                            let inputField = $('#contactForm').find(`[name="${field}"]`);

                            if (inputField.attr('type') === 'radio' || inputField.attr('type') === 'checkbox') {
                                let group = inputField.closest('.mb-3');
                                group.append(`<div class="text-danger">${messages[0]}</div>`);
                                if (!firstErrorField) {
                                    firstErrorField = inputField.first();
                                }
                            }
                            else if (field.includes('.')) {
                                const parts = field.split('.');
                                inputField = $('#contactForm').find(`[name="custom_fields[${parts[1]}]"]`);
                                inputField.after(`<div class="text-danger">${messages[0]}</div>`);
                                if (!firstErrorField) {
                                    firstErrorField = inputField;
                                }
                            }
                            else {
                                inputField.after(`<div class="text-danger">${messages[0]}</div>`);
                                if (!firstErrorField) {
                                    firstErrorField = inputField;
                                }
                            }

                            shownFields[field] = true; // Mark field as shown
                        });

                        if (firstErrorField) {
                            firstErrorField.focus();
                        }
                    } else {
                        // alert('Something went wrong. Please try again.');4
                        console.log('err -- ' , xhr.responseJSON.errors)
                    }
                }

            });
        });

        // Merge contacts
        $(document).on('click', '.mergeBtn', function() {
            const contactId = $(this).data('id');
            $('#merge_primary_id').val(contactId);

            $.ajax({
                url: '{{ url("/contacts/available-for-merge") }}/' + contactId,
                success: function(data) {
                    let options = '<option value="">Select Secondary User</option>';
                    data.forEach(contact => {
                        options += `<option value="${contact.id}">${contact.name}</option>`;
                    });
                    $('#merge_secondary_id').html(options);
                    $('#mergeModal').show();
                }
            });
        });

        $('#mergeForm').on('submit', function(e) {
            e.preventDefault();
            const data = $(this).serialize();

            // Clear previous errors
            $('#mergeForm .text-danger').remove();

            $.ajax({
                url: '{{ url("/contacts/merge") }}',
                method: 'POST',
                data: data,
                success: function(response) {
                    $('#alertPlaceholder').html(`
                        <div class="alert alert-success alert-dismissible fade show" role="alert" id="ajaxSuccessAlert">
                            ${response.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `);

                    $('#mergeModal').hide();
                    loadContacts();

                    setTimeout(function () {
                        $('#ajaxSuccessAlert').alert('close');
                    }, 3000);
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        let firstErrorField = null;
                        let shownFields = {};

                        $.each(errors, function(field, messages) {
                            if (shownFields[field]) return;

                            let inputField = $('#mergeForm').find(`[name="${field}"]`);
                            if (inputField.length) {
                                inputField.after(`<div class="text-danger">${messages[0]}</div>`);
                                if (!firstErrorField) {
                                    firstErrorField = inputField;
                                }
                            }

                            shownFields[field] = true;
                        });

                        if (firstErrorField) {
                            firstErrorField.focus();
                        }
                    } else {
                        console.log('Unexpected error:', xhr);
                    }
                }
            });
        });

        $('#applyFilters').on('click', function(e) {
            e.preventDefault();
            loadContacts();
        });


        // Load contacts with AJAX
        function loadContacts() {
            $.ajax({
                url: '{{ url("contacts/filter") }}',
                method: 'POST',
                data: $('#filterForm').serialize(),
                success: function(response) {
                    $('#contactsList').html(response);
                }
            });
        }


        function loadCustomFields() {
            $.ajax({
                url: '{{ url("/contact-fields/list") }}',
                method: 'GET',
                success: function(fields) {
                    let html = '';
                    fields.data.forEach(field => {
                        html += `<div class="mb-3">
                                    <label>${field.name}</label>
                                    <input type="text" name="custom_fields[${field.id}]" class="form-control">
                                    </div>`;
                    });
                    $('#customFieldsContainer').html(html);
                }
            });
        }
    });

    
</script>
@endsection