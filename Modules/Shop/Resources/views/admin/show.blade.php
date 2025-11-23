@include('layout::admin.layout')

<div class="wrapper d-flex flex-column min-vh-100 min-admin">

    <div class="container-fluid">
        <div class="row d-flex justify-content-center">
            <div class="col-md-6 mt-2">

            <div class="card design-card">
                <div class="card-header card-header-border-bottom">
                    <h2>Details</h2>
                </div>
                <div class="card-body">


                    <div class="row">
                        <div class="col-4">
                            <th><b  class="size-details">Name:</b></th>

                            <span>Zaid</span>

                        </div>

                    </div>
                    <hr class="mt-3">

                    <div class="row">
                        <div class="col-4">
                            <th>Test:</th>
                            <td>zaid</td>
                        </div>


                    </div>



                </div>
            </div>
            </div>
            <div class="col-md-4 ">
                <div class="mb-2">

                </div>
                <div class="card design-card">
                    <div class="card-body">
                        <div class="card-title">
                            <span class="mb-4">Image</span>

                        </div>
                        <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSaO4xQWA69FjuI703TNwXH2UxoiDmOfleiF7Do2hIc-HoqvNXK6zNiPtXmgGdxumdnb6Y&usqp=CAU" width="100%" alt="">
                    </div>
                </div>
                </div>
        </div>
    </div>
</div>

@include('layout::admin.footer')
