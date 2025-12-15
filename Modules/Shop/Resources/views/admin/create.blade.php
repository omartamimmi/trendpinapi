@include('layout::admin.layout')

<div class="wrapper d-flex flex-column min-vh-100 min-admin">
    <div class="container-fluid">
        <div class="row d-flex justify-content-center">
            <div class="col-10">
                <h3 class="fw-normal">
                   {{ __('shop::word.Create Shop') }}
                </h3>

                <div class="card design-card">
                    <div class="card-body">
                        <form  id="shop" method="post" action="{{route('admin.shop.shop-store')}}" enctype="multipart/form-data">
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
@section('scripts')

    @endsection

@include('layout::admin.footer')



