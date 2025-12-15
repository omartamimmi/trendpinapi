<div class="row" id="media">
    @foreach($media as $m)
    <div class="col-md-3 media-column">
        <i class="fa fa-check-square-o d-none"></i>
        <img class=" media-thumb" src="{{asset('storage/'.$m->file_path)}}" data-id="{{$m->id}}">
    </div>
    @endforeach

</div>




