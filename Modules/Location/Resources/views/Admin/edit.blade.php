@include('layout::admin.layout')

<div class="wrapper d-flex flex-column min-vh-100 min-admin">

    <div class="container-fluid">
        <div class="row d-flex justify-content-center">
            <div class="col-10">
                <h3 class="fw-normal">
                   Edit Location
                </h3>

                <div class="card design-card">
                    <div class="card-body">
                        <form action="" method="" enctype="multipart/form-data">
                            @csrf
                            @include('location::admin.form')
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@include('layout::admin.footer')
