@extends($activeTemplate . 'layouts.master')
@section('content')
    <div class="card custom--card">
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                @csrf
                <div class="profile-item">
                    <div class="profile-item__thumb">
                        <div class="file-upload">
                            <label class="edit" for="profile-image"><i class="las la-camera"></i></label>
                            <input type="file" name="image" class="form-control form--control" id="profile-image" hidden>
                        </div>
                        <div class="thumb">
                            <img id="profileImagePreview"
                                 src="{{ getImage(getFilePath('userProfile') . '/' . $user->image, getFileSize('userProfile')) }}"
                                 alt="User Image">
                        </div>
                    </div>
                    <div class="profile-item__content">
                        <p class="text">@lang('Hello'), {{ __($user->fullname) }}</p>
                        <p class="mail">{{ $user->email }}</p>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label class="form--label">@lang('First Name')</label>
                            <input type="text" class="form--control" name="firstname" value="{{ $user->firstname }}" required>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="form-group">
                            <label class="form--label">@lang('Last Name')</label>
                            <input type="text" class="form--control" name="lastname" value="{{ $user->lastname }}" required>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="form-group">
                            <label class="form--label">@lang('E-mail Address')</label>
                            <input type="email" class="form--control" value="{{ $user->email }}" disabled>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="form-group">
                            <label class="form--label">@lang('Mobile Number')</label>
                            <input type="text" class="form--control" value="{{ $user->mobile }}" disabled>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="form-group">
                            <label class="form--label">@lang('Address')</label>
                            <input type="text" class="form--control" name="address" value="{{ $user->address }}">
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="form-group">
                            <label class="form--label">@lang('State')</label>
                            <input type="text" class="form--control" name="state" value="{{ $user->state }}">
                        </div>
                    </div>

                    <div class="col-sm-4">
                        <div class="form-group">
                            <label class="form--label">@lang('Zip Code')</label>
                            <input type="text" class="form--control" name="zip" value="{{ $user->zip }}">
                        </div>
                    </div>

                    <div class="col-sm-4">
                        <div class="form-group">
                            <label class="form--label">@lang('City')</label>
                            <input type="text" class="form--control" name="city" value="{{ $user->city }}">
                        </div>
                    </div>

                    <div class="col-sm-4">
                        <div class="form-group">
                            <label class="form--label">@lang('Country')</label>
                            <input type="text" class="form--control" value="{{ $user->country_name }}" disabled>
                        </div>
                    </div>

                </div>
                <button type="submit" class="btn btn--base btn--lg w-100">@lang('Submit')</button>
            </form>
        </div>
    </div>
@endsection


@push('script')
    <script>
        (function($) {
            "use strict";

            $('#profile-image').on('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#profileImagePreview').attr('src', e.target.result);
                    };
                    reader.readAsDataURL(file);
                }
            });

        })(jQuery);
    </script>
@endpush
