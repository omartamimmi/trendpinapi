<div class="panel">
    <div class="panel-title"><strong>{{__('shop::word.Availability')}}</strong></div>
    <div class="panel-body" >
        <h3 class="panel-body-title">{{__('shop::word.Open Hours')}}</h3>
        <div class="form-group d-none">
            <label>
                <input type="checkbox" name="enable_open_hours" checked value="1"> {{__('Enable Open Hours')}}
            </label>
        </div>
        <?php $old = old('open_hours', $shop->meta->open_hours ?? []);?>
        <div class="table-responsive form-group" style="overflow-x: unset" data-condition="enable_open_hours:is(1)" >
            <table class="table">
                <thead>
                <tr>
                    <th class="text-left">{{__('shop::word.ENABLE ?')}}</th>
                    <th class="text-left">{{__('shop::word.DAY OF WEEK')}}</th>
                    <th class="text-left">{{__('shop::word.TIMES')}}</th>
                </tr>
                </thead>
                @for($i = 1 ; $i <=7 ; $i++)
                    <tr>
                        <td class="text-left">
                            <input style="display: inline-block" type="checkbox" @if(($old[$i]['enable'] ?? false) ) class="@error('open_hours') is-invalid @enderror"   checked @endif  name="open_hours[{{$i}}][enable]" value="1">
                            @error('open_hours')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        </td>
                        <td class="text-left"><strong>
                                @switch($i)
                                    @case(1)
                                    {{__('shop::word.Monday')}}
                                    @break
                                    @case(2)
                                    {{__('shop::word.Tuesday')}}
                                    @break
                                    @case (3)
                                    {{__('shop::word.Wednesday')}}
                                    @break
                                    @case (4)
                                    {{__('shop::word.Thursday')}}
                                    @break
                                    @case (5)
                                    {{__('shop::word.Friday')}}
                                    @break
                                    @case (6)
                                    {{__('shop::word.Saturday')}}
                                    @break
                                    @case (7)
                                    {{__('shop::word.Sunday')}}
                                    @break
                                @endswitch
                            </strong></td>
                        <td>



                        <div class="form-group-item">

                            <div class="g-items-header">
                                <div class="row">
                                    <div class="col-md-5 text-left">{{__('shop::word.From')}}</div>
                                    <div class="col-md-5 text-left">{{__('shop::word.To')}}</div>
                                    <div class="col-md-2"></div>
                                </div>
                            </div>
                            <div class="g-items">
                                    @forelse($old[$i]['hours'] ?? [] as $key => $hours)
                                        <div class="item" data-number="{{$key}}">
                                            <div class="row">
                                                <div class="col-md-5">
                                                    <input type="time" class="form-control open-hours-from @error('open_hours.*.hours.*.from') is-invalid @enderror" name="open_hours[{{$i}}][hours][{{$key}}][from]" value="{{ $hours['from'] ?? '' }}"  >
                                                </div>
                                                <div class="col-md-5">
                                                    <input type="time" class="form-control  open-hours-to @error('open_hours.*.hours.*.from') is-invalid @enderror" name="open_hours[{{$i}}][hours][{{$key}}][to]" value="{{ $hours['to'] ?? '' }}">
                                                </div>

                                                @if(count($errors->get('open_hours.' . $i . '.hours.' . $key . '.from')) || count($errors->get('open_hours.' . $i . '.hours.' . $key . '.to')))
                                                    <div class="col-md-12">
                                                        <div class="alert alert-danger custom-alert">
                                                            <ul>
                                                                @foreach ($errors->get('open_hours.' . $i . '.hours.' . $key . '.from') as $error)
                                                                    <li>{!! $error !!}</li>
                                                                @endforeach
                                                                @foreach ($errors->get('open_hours.' . $i . '.hours.' . $key . '.to') as $error)
                                                                    <li>{!! $error !!}</li>
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @empty
                                        <div class="item" data-number="0">
                                            <div class="row">
                                                <div class="col-md-5">
                                                    <input type="time" class="form-control open-hours-from" name="open_hours[{{$i}}][hours][0][from]" value="{{ $old[$i]['from'] ?? '' }}">
                                                </div>
                                                <div class="col-md-5">
                                                    <input type="time" class="form-control open-hours-to" name="open_hours[{{$i}}][hours][0][to]" value="{{ $old[$i]['to'] ?? '' }}">
                                                </div>

                                            </div>
                                        </div>
                                        @if(count($errors->get('open_hours.' . $i . '.hours.0.from')) || count($errors->get('open_hours.' . $i . '.hours.0.to')))
                                            <div class="col-md-12">
                                                <div class="alert alert-danger custom-alert">
                                                    <ul>
                                                        @foreach ($errors->get('open_hours.' . $i . '.hours.0.from') as $error)
                                                            <li>{!! $error !!}</li>
                                                        @endforeach
                                                        @foreach ($errors->get('open_hours.' . $i . '.hours.0.to') as $error)
                                                            <li>{!! $error !!}</li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            </div>
                                        @endif
                                    @endforelse
                            </div>

                            {{-- <div class="g-more hide">
                                <div class="item" data-number="__number__">
                                    <div class="row">
                                        <div class="col-md-5">
                                            <input type="time" class="form-control open-hours-from" __name__="open_hours[{{$i}}][hours][__number__][from]">
                                        </div>
                                        <div class="col-md-5">
                                            <input type="time" class="form-control open-hours-to" __name__="open_hours[{{$i}}][hours][__number__][to]">
                                        </div>

                                    </div>
                                </div>
                            </div> --}}
                        </div>

                    </tr>
                @endfor
            </table>
        </div>

        @if (!empty($errors->has('time_zone')))
            <div class="alert alert-danger custom-alert">
                {!! clean($errors->first('time_zone')) !!}
            </div>
        @endif

    </div>
</div>
