@include('layout::admin.layout')
<div class="wrapper d-flex flex-column min-vh-100 min-admin">
  <div class="container-fluid">
    <div class="row d-flex justify-content-center">
      <div class="col-10">
        <h3 class="fw-normal">
            {{ __('shop::word.Edit Shop') }}
        </h3>
        <div class="card design-card">
          <div class="card-body">
            <form id="shop" method="POST" action="{{route('admin.shop.shop-update', $shop->id)}}" enctype="multipart/form-data">
                @csrf
                @include('shop::admin.form')
                {{-- <div>
                    <button type="submit" class="btn btn-primary mt-3">submit</button>
                </div> --}}
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
    title:{
        required:true,
        maxlength:255,
    },

    description :{
        required:true,
        maxlength:1000
    },

    status:"required",
    days:"required",
    },
    // Specify validation error messages
    messages: {

      title:{
        required:"The Title field is required",
        maxlength:"The Title field cannot be more than 255 characters",
      },

      description:{
        required:"The description field is required",
        maxlength:"The description field cannot be more than 255 characters",
      },
      status:"The Status field is required",
      days:"The Days & Conditions field is required",
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
