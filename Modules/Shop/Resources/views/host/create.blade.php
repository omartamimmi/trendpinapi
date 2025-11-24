@include('layout::host.layout')
<div class="wrapper d-flex flex-column min-vh-100 bg-light text-dark ">
    <div class="container-fluid">
        <div class="row d-flex justify-content-center">
            <div class="col-10">
                <div class="card-header mt-5">
                    <h3 class="fw-normal">
                       {{ __('shop::word.Create Shop') }}
                    </h3>
                </div>
                <div class="card design-card">
                    <div class="card-body">
                        <form  id="shop" method="post" action="{{route('user.shop.shop-store')}}" enctype="multipart/form-data">
                            @csrf
                            @include('shop::host.form')

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

      title: "required",
      description:"required",
      status:"required",
      lat:"required",
      lng:"required",
      image_id:"required",
      gallery:"required",
      open_hours:"required"

    },
    // Specify validation error messages
    messages: {

        title: "Please enter your Title",
        description: "Please enter your Description",
        status: "Please check  Status",
        lat: "Please enter your Title",
        lng: "Please enter your Title",
        image_id: "Please upload Image",
        gallery: "Please upload Gallery",
        open_hours: "Please enter your Open Hours",


    },
    // Make sure the form is submitted to the destination defined
    // in the "action" attribute of the form when valid
    submitHandler: function(form) {
      form.submit();
    }
  });
});
</script>
