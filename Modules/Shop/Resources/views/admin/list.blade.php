@include('layout::admin.layout')

<div class="wrapper d-flex flex-column min-vh-100  min-admin">
    <div class="container-fluid">
        <div class="row d-flex justify-content-center">
            <div class="col-10">
                <h3 class="fw-normal">
                    {{ __('shop::word.Shop List') }}
                </h3>

                <div class="card mt-3 design-card ">
                    <div class="card-body">
                        <div class="filter-div d-flex justify-content-left ">
                            <div class="col-left">
                                {{-- @if (!empty($users)) --}}
                                <form method="post" action="{{ url('#') }}"
                                    class="filter-form filter-form-left d-flex justify-content-start">
                                    {{ csrf_field() }}
                                    <select name="action" class="form-control">
                                        <option value="">{{ __(' Bulk Actions ') }}</option>
                                        <option value="publish">{{ __(' Publish ') }}</option>
                                        <option value="draft">{{ __(' Move to Draft ') }}</option>
                                        <option value="delete">{{ __(' Delete ') }}</option>
                                    </select>
                                    <button data-confirm="{{ __('Do you want to delete?') }}"
                                        class="btn-info btn btn-icon dungdt-apply-form-btn ms-2"
                                        type="button">{{ __('Apply') }}</button>
                                </form>
                                {{-- @endif --}}
                            </div>
                        </div>
                        <table id="datatable" class="table table-striped design-card table-responsive">
                            <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>{{ __('shop::word.Title') }}</th>
                                    <th>{{ __('shop::word.Description') }}</th>
                                    <th>{{ __('shop::word.STATUS') }}</th>
                                    <th>{{ __('shop::word.CREATED AT') }}</th>
                                    <th>{{ __('shop::word.ACTIONS') }}</th>
                                </tr>
                            </thead>
                            <tbody>

                                @foreach ($shops as $shop)
                                    <tr>
                                        <td data-label="id"><input type="checkbox"></td>
                                        <td data-label="title" class="demo-2">{{ $shop->title }}</td>
                                        <td data-label="description">
                                            <div class="wrappers-text-list">
                                                <p class="demo-2"> {{ $shop->description }}</p>
                                            </div>
                                        </td>
                                        <td data-label="status">{{ $shop->status }}</td>
                                        <td data-label="created_at">{{ $shop->created_at }}</td>
                                        <td data-label="Actions" class="shop-action">
                                            <a href="{{ route('admin.shop.shop-edit', ['id' => $shop->id]) }}"
                                                class="me-2"> <i class="icon mt-3 link-dark cil-pencil"></i></a>
                                            <form method="POST"
                                                action="{{ route('admin.shop.shop-destroy', ['id' => $shop->id]) }}"
                                                class="mt-2 delete-shop">
                                                @csrf
                                                <input type="hidden" value="DELETE">
                                                <button type="submit" class="btn btn-default show_confirm "
                                                    data-toggle="tooltip" title='Delete'><i
                                                        class="fa fa-trash fs-4"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach

                            </tbody>

                        </table>
                        <div class="ms-3">
                            {{ $shops->links() }}

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $('.show_confirm').click(function(event) {
        var form = $(this).closest("form");
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

@include('layout::admin.footer')
