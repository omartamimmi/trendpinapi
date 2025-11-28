@include('layout::admin.layout')

<div class="wrapper d-flex flex-column min-vh-100 min-admin">

    <div class="container-fluid">
        <div class="row d-flex justify-content-center">
            <div class="col-10">
                <h3 class="fw-normal">
                   Create Location
                </h3>

                <div class="card design-card">
                    <div class="card-body">
                        <form  id="shop" enctype="multipart/form-data">
                            @csrf
                            @include('location::Admin.form')
                            <div>
                                <button type="submit" class="btn btn-primary mt-3">submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script >
    $(function() {
  // Initialize form validation on the registration form.
  // It has the name attribute "registration"
  $("#shop").validate({
    // Specify validation rules
    rules: {
      // The key name on the left side is the name attribute
      // of an input field. Validation rules are defined
      // on the right side
      firstname:{
        required:true,
        minlength: 5
      },
      lastname: "required",

    },
    // Specify validation error messages
    messages: {
      firstname:{
        required: "Please enter your firstname",
        minlength:"Your name must be at least 5 characters long"
      },
      lastname: "Please enter your lastname",

    },
    // Make sure the form is submitted to the destination defined
    // in the "action" attribute of the form when valid
    submitHandler: function(form) {
      form.submit();
    }
  });
});
</script>
@include('layout::admin.footer')
