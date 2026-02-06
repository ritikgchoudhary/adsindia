@extends($activeTemplate . 'layouts.master')

@section('content')
    <div class="card custom--card">
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                @csrf
                <div class="profile-item">
                    <div class="profile-item__thumb">
                        <div class="file-upload">
                            <label class="edit" for="profile-image" data-bs-toggle="tooltip" data-bs-placement="top" title="@lang('Supported Files: .png, .jpg, .jpeg. Image will be resized into :size', ['size' => getFileSize('advertiserProfile')])">
                                <i class="las la-camera"></i>
                            </label>
                            <input type="file" name="image" class="form-control form--control image-uploader" id="profile-image" data-target="#profileImagePreview" accept="image/*" hidden>
                        </div>
                        <div class="thumb">
                            <img id="profileImagePreview" src="{{ getImage(getFilePath('advertiserProfile') . '/' . $advertiser->image, getFileSize('advertiserProfile')) }}" alt="Advertiser Image">
                        </div>
                    </div>
                    <div class="profile-item__content">
                        <p class="text">@lang('Hello'), {{ __($advertiser->fullname) }}</p>
                        <p class="mail">{{ $advertiser->email }}</p>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label class="form--label">@lang('First Name')</label>
                            <input type="text" class="form--control" name="firstname" value="{{ $advertiser->firstname }}" required>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="form-group">
                            <label class="form--label">@lang('Last Name')</label>
                            <input type="text" class="form--control" name="lastname" value="{{ $advertiser->lastname }}" required>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="form-group">
                            <label class="form--label">@lang('E-mail Address')</label>
                            <input type="email" class="form--control" value="{{ $advertiser->email }}" disabled>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="form-group">
                            <label class="form--label">@lang('Mobile Number')</label>
                            <input type="text" class="form--control" value="{{ $advertiser->mobile }}" disabled>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="form-group">
                            <label class="form--label">@lang('Address')</label>
                            <input type="text" class="form--control" name="address" value="{{ $advertiser->address }}">
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="form-group">
                            <label class="form--label">@lang('State')</label>
                            <input type="text" class="form--control" name="state" value="{{ $advertiser->state }}">
                        </div>
                    </div>

                    <div class="col-sm-4">
                        <div class="form-group">
                            <label class="form--label">@lang('Zip Code')</label>
                            <input type="text" class="form--control" name="zip" value="{{ $advertiser->zip }}">
                        </div>
                    </div>

                    <div class="col-sm-4">
                        <div class="form-group">
                            <label class="form--label">@lang('City')</label>
                            <input type="text" class="form--control" name="city" value="{{ $advertiser->city }}">
                        </div>
                    </div>

                    <div class="col-sm-4">
                        <div class="form-group">
                            <label class="form--label">@lang('Country')</label>
                            <input type="text" class="form--control" value="{{ $advertiser->country_name }}" disabled>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn--base btn--lg w-100 mt-3">@lang('Submit')</button>
            </form>
        </div>
    </div>
@endsection


@push('script')
    <script>
        (function($) {
            "use strict";
            $(".image-uploader").on('change', function() {
                proPicURL(this);
            });

            function proPicURL(input) {
                if (input.files && input.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        const result = reader.result;
                        $("#profileImagePreview").attr('src', result);
                    }
                    reader.readAsDataURL(input.files[0]);
                }
            }

        })(jQuery)
    </script>
@endpush
