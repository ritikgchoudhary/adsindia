@extends($activeTemplate . 'layouts.master')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card custom--card">
                <div class="card-body">
                    <form method="POST">
                        @csrf
                        <div class="form-group">
                            <label class="form--label">@lang('Old Password')</label>
                            <div class="position-relative">
                                <input id="current_password" type="password" class="form-control form--control" name="current_password" required>
                                <span class="password-show-hide fas fa-eye toggle-password fa-eye-slash" data-target="current_password"></span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form--label">@lang('New Password')</label>
                            <div class="position-relative">
                                <input id="password" type="password" class="form-control form--control @if (gs('secure_password')) secure-password @endif" name="password" required>
                                <span class="password-show-hide fas fa-eye toggle-password fa-eye-slash" data-target="password"></span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form--label">@lang('Confirm Password')</label>
                            <div class="position-relative">
                                <input id="password_confirmation" type="password" class="form-control form--control" name="password_confirmation" required>
                                <span class="password-show-hide fas fa-eye toggle-password fa-eye-slash" data-target="password_confirmation"></span>
                            </div>
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn--base w-100">@lang('Change Password')</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@if (gs('secure_password'))
    @push('script-lib')
        <script src="{{ asset('assets/global/js/secure_password.js') }}"></script>
    @endpush
@endif

@push('script')
    <script>
        (function($) {
            "use strict";
            $('.toggle-password').on('click', function() {
                const target = $(this).data('target');
                const input = $('#' + target);
                const type = input.attr('type') === 'password' ? 'text' : 'password';
                input.attr('type', type);
                $(this).toggleClass('fa-eye fa-eye-slash');
            });
        })(jQuery);
    </script>
@endpush
