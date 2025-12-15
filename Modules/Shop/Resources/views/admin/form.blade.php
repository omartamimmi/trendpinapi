<meta name="csrf-token" content="{{ csrf_token() }}" />

<div class="row">
    <div class="col-md-6">
        <div class="form-group ">
            <label for="title">{{ __('shop::word.Title') }}</label>
            <input type="text" class="form-control  @error('title') is-invalid @enderror" name="title" id="title"@if (!isset($shop))
            value="{{ old('title' ) }}"
            @endif
                placeholder="{{ __('shop::word.Title') }}"
                @isset($shop)
                value="{{ old('title', $shop->title ?? '') }}"
                @endisset>
            @error('title')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group ">
            <label for="title-ar">{{ __('shop::word.TitleAR') }}</label>
            <input type="text" class="form-control  @error('title-ar') is-invalid @enderror" name="title_ar"
                id="title_ar" placeholder="{{ __('shop::word.Title') }}"@if (!isset($shop))
                value="{{ old('title_ar' ) }}"
                @endif
                @isset($shop) value="{{ old('title-ar', $shop->title ?? '') }}" @endisset>
            @error('title-ar')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <!--  col-md-6   -->

    <div class="col-md-6">
        <div class="form-group">
            <label for="description">{{ __('shop::word.Description') }}</label>
            <textarea class="form-control  @error('description') is-invalid @enderror"
                placeholder="{{ __('shop::word.Description') }}" id="description" name="description">
                @if (!isset($shop))
                {{ old('description' ) }}
                @endif
                @isset($shop)
{{ old('description', $shop->description ?? '') }}
@endisset
            </textarea>
            @error('description')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label for="description_ar">{{ __('shop::word.DescriptionAr') }}</label>
            <textarea class="form-control  @error('description_ar') is-invalid @enderror"
                placeholder="{{ __('shop::word.Description') }}" id="description_ar" name="description_ar">
                @if (!isset($shop))
                {{ old('description_ar' ) }}
                @endif
                    @isset($shop)
{{ old('description_ar', $shop->description_ar ?? '') }}
@endisset
              </textarea>
            @error('description_ar')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">

            <label for="first">{{ __('user::word.Phone') }}</label>
            <input type="tel" class="form-control @error('phone') is-invalid @enderror w-100"
                placeholder="{{ __('user::word.Phone') }}" id="phone" name="phone_number"
                @if (!isset($user))
                value="{{ old('phone_number' ) }}"
                @endif
                @isset($user)
                value="{{ old('phone_number', $shop->phone_number ?? '') }}"
                @endisset>
            @error('phone')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <!--  col-md-6   -->
    <div class="col-md-6 ">
        <div class="form-group">
            <label for="video">{{ __('shop::word.Video') }}</label>
            <input type="url" class="form-control  @error('video') is-invalid @enderror" name="video"
                id="video" placeholder="{{ __('shop::word.Video') }}"
                @if (!isset($shop))
                value="{{ old('video' ) }}"@endif
                @isset($shop) value="{{ old('video', $shop->video ?? '') }}" @endisset>
        </div>
    </div>
    
    <!--  col-md-6   -->
    <div class="col-md-6 ">
        <div class="form-group">
            <label for="video">{{ __('shop::word.website_link') }}</label>
            <input type="url" class="form-control  @error('website_link') is-invalid @enderror" name="website_link"
                id="video" placeholder="{{ __('shop::word.website_link') }}"
                @if (!isset($shop))
                value="{{ old('website_link' ) }}"@endif
                @isset($shop) value="{{ old('website_link', $shop->website_link ?? '') }}" @endisset>
        </div>
    </div>
    <!--  col-md-6   -->
    <div class="col-md-6 ">
        <div class="form-group">
            <label for="insta_link">{{ __('shop::word.insta_link') }}</label>
            <input type="url" class="form-control  @error('insta_link') is-invalid @enderror" name="insta_link"
                id="insta_link" placeholder="{{ __('shop::word.insta_link') }}"
                @if (!isset($shop))
                value="{{ old('insta_link' ) }}"@endif
                @isset($shop) value="{{ old('insta_link', $shop->insta_link ?? '') }}" @endisset>
        </div>
    </div>
    <!--  col-md-6   -->
    <div class="col-md-6 ">
        <div class="form-group">
            <label for="facebook_link">{{ __('shop::word.facebook_link') }}</label>
            <input type="url" class="form-control  @error('facebook_link') is-invalid @enderror" name="facebook_link"
                id="facebook_link" placeholder="{{ __('shop::word.facebook_link') }}"
                @if (!isset($shop))
                value="{{ old('facebook_link' ) }}"@endif
                @isset($shop) value="{{ old('facebook_link', $shop->facebook_link ?? '') }}" @endisset>
        </div>
    </div>
    <?php
    $old = !empty($shop) ? $shop->category_shops : '';
    ?>
    <!--  col-md-6   -->

    <div class="col-md-6 mt-2">
        <div class="form-group">
            <label>{{ __('shop::word.Category') }}</label>
            <select name="category[]" multiple="multiple" id="category"class="form-select js-example-basic-multiple  @error('category') is-invalid @enderror">
                @foreach ($categories as $category)
                    <option
                        @php
                            if(!empty($shop) && !$old->isEmpty()){
                                foreach($old as $value){
                                    if($value->id == $category->id){
                                        echo 'selected';

                                    }
                                }
                            } @endphp
                        value="{{ $category->id }}">

                        {{ $category->name }}</option>
                @endforeach
            </select>
            @error('category')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror
        </div>
    </div>
    
    <div class="col-md-6 mt-2">
        <div class="form-group">
            <label>{{ __('shop::word.tags') }}</label>
            <select name="tags[]" id="tags" class="form-select  @error('location') is-invalid @enderror">
                @isset($shop)
                    <option value="{{ $shop->location_id }}">{{ $shop->location_id }}</option>
                @endisset

            </select>
            @error('tags')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-md-6 mt-2">
        <div class="form-group">
            <label>{{ __('shop::word.business_type') }}</label>
            <select name="type" id="type"class="form-select js-example-basic-multiple  @error('type') is-invalid @enderror">
                <option>--Select Value--</option>
                <option value="in_person">In person</option>
                <option value="online">Online</option>
            </select>
            @error('type')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-md-3 mt-4 ">
        <div class="form-group">
            <label>{{ __('shop::word.Image') }}</label>
            <button type="button" class="btn btn-info btn-m" data-bs-toggle="modal" data-bs-target="#featured-modal"
                name="image_id" id="image_id">Featured image</button>
            <input type="hidden" name="image_id" id="image_id"
            @if (!isset($shop))
            value="{{ old('image_id' ) }}"@endif
                @isset($shop)
                value="{{ old('image_id', $shop->image_id ?? '') }}"
                @endisset />
            @error('image_id')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-3 mt-4 ">
        <div class="form-group">
            <label>{{ __('shop::word.Gallery') }}</label>
            <button type="button" class="btn btn-info btn-m" data-bs-toggle="modal" name="gallery" id="gallery"
                data-bs-target="#myModal">
                Gallery
            </button>
            <input type="hidden" name="gallery" id="gallery"  @if (!isset($shop))
            value="{{ old('gallery' ) }}"@endif
                @isset($shop)
            value="{{ old('gallery', $shop->gallery ?? '') }}"
            @endisset />

            @error('gallery')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <!--  col-md-6   -->



    <div class="col-md-6 mt-2">
        <div class="form-group">
            <label>{{ __('shop::word.Location') }}</label>
            <select name="location_id" id="select2-input" class="form-select  @error('location') is-invalid @enderror">
                @isset($shop)
                    <option value="{{ $shop->location_id }}">{{ $shop->location_id }}</option>
                @endisset

            </select>
            @error('location')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="row">
        <div class=" col-md-12  mt-4" style="position: relative">
            @include('shop::admin.discount')
            <div class="col-md-6 mt-3">
                <label for="status">{{ __('shop::word.Status') }}</label>
                <select class="form-select" id="inputGroupSelect01" name="status" id="status">
                    <option selected>select...</option>
                    <option value="publish"
                        @isset($shop)

                          @if ($shop->status == 'publish')
                            selected
                          @endif  @endisset>
                        publish</option>
                    <option value="draft"
                        @isset($shop)

                          @if ($shop->status == 'draft')
                            selected
                          @endif  @endisset>
                        draft</option>
                </select>
                @error('status')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>


        </div>

    </div>


    <!--  col-md-6   -->
    <div class=" col-md-12 mb-5 mt-3">
        @include('shop::admin.availability')

    </div>
    <?php

    use Modules\Media\Models\MediaFile;

    $media = MediaFile::paginate(5);
    ?>

    <div class="col-md-12">
        <div class="modal fade" id="featured-modal" tabindex="-1" aria-labelledby="exampleModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content" style="width: 36rem">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="exampleModalLabel">Upload Featured image</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-header d-block">
                        <input class="custom-upload" id="file" type="file" name="file" ref="file">
                    </div>
                    @include('shop::admin.media')
                    <input type="hidden" name="gallery" />

                    <div class="modal-body" id="media">
                        <img>
                    </div>

                    <div class="ms-3">
                        {{ $media->links() }}

                    </div>
                    <div class="modal-footer">
                        {{-- <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button> --}}
                        <button type="button" id="select-featured" class="btn btn-primary"
                            data-bs-dismiss="modal">Save
                            changes</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Button trigger modal -->
<div class="col-md-12">
    <div class="modal fade" id="myModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content" style="width: 36rem">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Upload Gallery</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-header d-block">
                    <input class="custom-upload" id="file" type="file" name="file" ref="file">
                </div>
                @include('shop::admin.media')
                <input type="hidden" name="gallery"@isset($shop)value="{{ $shop->gallery }}"@endisset />

                <div class="modal-body" id="media">
                    <img>
                </div>

                <div class="ms-3">
                    {{ $media->links() }}

                </div>
                <div class="modal-footer">
                    {{-- <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button> --}}
                    <button type="button" id="select-thumbs" class="btn btn-primary" data-bs-dismiss="modal">Save
                        changes</button>
                </div>
            </div>
        </div>
    </div>
</div>
</div>


<!--  col-md-6   -->
<hr>
<div class="col-md-12 mt-2 p-3">

    <p class="">{{ __('shop::word.map') }}</p>
    <input id="search-box" class="address w-100 mb-2 form-control" name="address"
        value="{{ old('address', $shop->location->address ?? '') }}">
    <div style="height: 300px;" id="map"></div>
    <input type="hidden" name="lat" id="lat" value="{{ old('lat', $shop->location->lat ?? '') }}">
    @error('lat')
        <div class="alert alert-danger">{{ $message }}</div>
    @enderror
    <input type="hidden" name="lng" id="lng" value="{{ old('lng', $shop->location->lng ?? '') }}">
    @error('lng')
        <div class="alert alert-danger">{{ $message }}</div>
    @enderror
    <input type="hidden" name="exact_address" value="Amman">

</div>
<div class="col-md-12 p-3">
    <button type="submit" class="btn btn-primary mt-3">{{ __('user::word.Submit') }}</button>

</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@18.1.1/build/js/intlTelInput.min.js"></script>

<script>
        var input = document.querySelector("#phone");
        window.intlTelInput(input, {
            utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@18.1.1/build/js/utils.js",
        });
</script>
<script>
    $(document).ready(function() {
        $('#select2-input').select2({
            ajax: {
                url: '/location',
                dataType: 'json',
                data: function(params) {
                    return {
                        q: params.term
                    };
                },
                processResults: function(data) {
                    // console.log(data);
                    return {
                        results: data
                    };
                },
            },
            minimumInputLength: 1
        });
    });
</script>

<script src="{{ asset('js/admin.js') }}"></script>

<script>
    let currentLocation = {!! json_encode(getLocation()) !!}
    let storeFile = '{{ route('file.store') }}'

    $('#category').on('select2:select', function(e){
        var cats = $('#category').val();
        console.log(cats)
        var form = $(this).closest('.form-login');
        $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': form.find('input[name="_token"]').val()
          }
        });
        $.ajax({
          'url': '{{route('v1.tag.getTagBasedCategory')}}',
          'data': {
            'category_ids': cats,
          },
          'type': 'get',
  
          success: function success(data) {
            const $selectElement = $('#tags');
console.log(data)
            $.each(data.tag, function(index, optionText) {
                console.log(index, optionText)
                // Create new option element with value and text
                const $optionElement = $('<option>', {
                    value: optionText.id,
                    text: optionText.name
                });
                
                // Append the option to the select element
                $selectElement.append($optionElement);
            });
          },
          error: function error(data) {
            console.log(data)
          }
        });
        
    })
</script>

<script src="{{ asset('js/map.js') }}"></script>
<script type="text/javascript"
    src="https://maps.google.com/maps/api/js?key=AIzaSyCZblEanmdyy7LfPc44XmLKnBoxN7l1Tio&callback=initMap&libraries=places">
</script>


<style>
    img {
        max-width: 100px;

    }

    .media-column {
        margin: 10px 20px;
        position: relative;

    }

    .iti{
        width: 100%;
    }
</style>
