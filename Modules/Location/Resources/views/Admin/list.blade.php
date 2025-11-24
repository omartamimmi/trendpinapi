@include('layout::admin.layout')

<div class="wrapper d-flex flex-column min-vh-100  min-admin">
    <div class="container-fluid">
        <div class="row d-flex justify-content-center">
            <div class="col-10">
                <h3 class="fw-normal">
                    Location List
                </h3>

                <div class="card mt-3 design-card ">
                    <div class="card-body">
                        <div class="filter-div d-flex justify-content-left ">
                            <div class="col-left">
                                {{-- @if(!empty($users)) --}}
                                    <form method="post" action="{{url('#')}}" class="filter-form filter-form-left d-flex justify-content-start">
                                        {{csrf_field()}}
                                        <select name="action" class="form-control">
                                            <option value="">{{__(" Bulk Actions ")}}</option>
                                            <option value="publish">{{__(" Publish ")}}</option>
                                            <option value="draft">{{__(" Move to Draft ")}}</option>
                                            <option value="delete">{{__(" Delete ")}}</option>
                                        </select>
                                        <button data-confirm="{{__("Do you want to delete?")}}" class="btn-info btn btn-icon dungdt-apply-form-btn ms-2" type="button">{{__('Apply')}}</button>
                                    </form>
                                {{-- @endif --}}
                            </div>
                        </div>
                        <table id="datatable" class="table table-striped design-card table-responsive" >
                            <thead>
                        <tr>
                            <th>Id</th>
                            <th >Name</th>
                            <th >Position</th>
                            <th >Office</th>
                            <th >Age</th>
                            <th >Start date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td data-label="id"><input type="checkbox"></td>
                            <td data-label="name">Tiger Nixon</td>
                            <td data-label="Position">System Architect</td>
                            <td data-label="Office">Edinburgh</td>
                            <td data-label="age">61</td>
                            <td data-label="Start date">2011-04-25</td>
                            <td data-label="Actions">
                                <a href="#" class="me-2"> <i class="icon icon-2xl slink-dark mb-2 cil-pencil"></i></a>
                                <a href="#" class="me-2"> <i class="icon fa fa-eye link-dark"></i></a>
                                <a href="#"> <i class="icon link-danger cil-trash"></i></a>


                                 </td>
                        </tr>

                    </tbody>

                </table>
                    </div>
                </div>

            </div>
        </div>

    </div>



</div>

@include('layout::admin.footer')
