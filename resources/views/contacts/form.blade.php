<form id="contactForm" enctype="multipart/form-data" method="POST">
    @csrf
    <input type="hidden" name="id" id="contact_id">

    <div class="mb-3">
        <label for="name">Name</label>
        <input type="text" class="form-control" name="name" id="name">
    </div>

    <div class="mb-3">
        <label for="email">Email</label>
        <input type="email" class="form-control" name="email" id="email">
    </div>

    <div class="mb-3">
        <label for="phone">Phone</label>
        <input type="text" class="form-control" name="phone" id="phone">
    </div>

    <div class="mb-3">
        <label>Gender</label><br>
        <label><input type="radio" name="gender" value="Male"> Male</label>
        <label><input type="radio" name="gender" value="Female"> Female</label>
    </div>

    <div class="mb-3">
        <label for="profile_image">Profile Image</label>
        <input type="file" name="profile_image" id="profile_image">
        <div id="pic"></div>
    </div>

    <div class="mb-3">
        <label for="additional_file">Additional File</label>
        <input type="file" name="additional_file" id="additional_file">
        <div id="add_pic"></div>
    </div>

    <div id="customFieldsContainer"></div>

    <div class="mt-4">
        <button type="submit" class="btn btn-primary" id="submitBtn">Add Contact</button>
        <button type="button" class="btn btn-secondary" onclick="$('#contactModal').hide()">Cancel</button>
    </div>
</form>