@include('layout::host.layout')

<div class="wrapper d-flex flex-column min-vh-100 bg-light text-dark ">
    <div class="container-fluid">
        <div class="row d-flex justify-content-center">
            <div class="col-10 col-lg-10 col-md-10 col-ms-10 ">

                <div class="card-header mt-5">
                    <h3 class="fw-normal">
                        {{ __('shop::word.Shop List') }}
                    </h3>
                </div>
                <table class="table table-responsive product-dashboard-table shadow p-3 mt-2  mb-5 bg-body rounded " >
                    <thead>
                        <tr>
                            <th>Id</th>
                            <th >{{ __('shop::word.TITLE') }}</th>
                            <th >{{ __('shop::word.DESCRIPTION') }}</th>
                            <th >{{ __('shop::word.STATUS') }}</th>
                            <th >{{ __('shop::word.CREATED AT') }}</th>
                            <th>{{ __('shop::word.ACTIONS') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($shops as $shop)
                            <tr>
                                <td data-label="id">{{$shop->id}}</td>
                                <td data-label="TITLE">{{$shop->title}}</td>
                                <td data-label="DESCRIPTION">{{$shop->description}}</td>
                                <td data-label="STATUS">{{$shop->status}}</td>
                                <td data-label="Start date">{{$shop->created_at}}</td>
                                <td data-label="Actions">
                                  <div  class="d-flex justify-content-end">
                                    <a href="{{route('user.shop.shop-edit', ['id'=>$shop->id])}}" class="me-2 mt-2"> <i class="icon  style-icon link-dark  mb-2 cil-pencil"></i></a>
                                    <form method="POST" action="{{ route('user.shop.shop-destroy',$shop->id) }}">
                                        @csrf
                                        <input name="_method" type="hidden" value="POST">
                                        <button type="submit" class="btn btn-default show_confirm " data-toggle="tooltip" title='Delete'><i class="fa fa-trash fs-4" ></i></button>
                                </form>

                                  </div>

                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                {{-- <div class="ms-3">
                    {{ $shops->links() }}

                </div> --}}
            </div>
        </div>
    </div>
</div>




<script type="text/javascript">

    $('.show_confirm').click(function(event) {
         var form =  $(this).closest("form");
         var name = $(this).data("name");
         event.preventDefault();
         swal({
             title: `Are you sure you want to delete this record?`,
             text: "If you delete this, it will be gone forever.",
             icon: "warning",
             buttons: true,
             dangerMode: true,
         })
         .then((willDelete) => {
           if (willDelete) {
             form.submit();
           }
         });
     });

</script>


