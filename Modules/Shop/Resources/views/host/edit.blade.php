@include('layout::host.layout')
<div class="wrapper d-flex flex-column min-vh-100 bg-light text-dark ">

    <div class="container-fluid">
        <div class="row d-flex justify-content-center">
            <div class="col-10 p-2">
                <div>
                    <span class="head-of-table">{{ __('shop::word.Edit Shop') }}</span>
                </div>
                {{-- @dd($categories) --}}
                <div class="card design-card">
                    <div class="card-body">
                        <form id="shop" method="POST" action="{{route('user.shop.shop-update', $shop->id)}}" enctype="multipart/form-data">
                            @csrf
                            @include('shop::host.form')
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
