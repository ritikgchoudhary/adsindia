@extends($activeTemplate . 'layouts.frontend')
@section('content')
    <div class="py-120">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-7 col-xl-5">
                    <div class="d-flex justify-content-center">
                        <div class="verification-code-wrapper card custom--card">
                            <div class="verification-area card-body">
                                <form action="{{ route('user.password.verify.code') }}" method="POST" class="submit-form">
                                    @csrf
                                    <p class="mb-3">@lang('A 6 digit verification code sent to your email address') : {{ showEmailAddress($email) }}</p>
                                    <input type="hidden" name="email" value="{{ $email }}">
                                    @include($activeTemplate . 'partials.verification_code')
                                    <div class="form-group">
                                        <button type="submit" class="btn btn--base w-100">@lang('Submit')</button>
                                    </div>
                                    <p class="mt-3">
                                        @lang('Please check including your Junk/Spam Folder. if not found, you can')
                                        <a href="{{ route('user.password.request') }}">@lang('Try to send again')</a>
                                    </p>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


@push('style')
    <style>
        .verification-code-wrapper {
            border-radius: 32px;
            background-color: linear-gradient(90.08deg, #07090E 28%, hsl(var(--base)) 46%, #050A12 66.11%), linear-gradient(0deg, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0.1));
            border: 1px solid hsl(var(--white)/0.1);
        }

        .verification-code span {
            background: transparent;
            border: solid 1px #{{ gs('base_color') }}73;
            color: #{{ gs('base_color') }};
        }

        .verification-code input {
            color: #cbc9c9 !important;
        }

        .verification-code::after {
            background-color: linear-gradient(90.08deg, #07090E 28%, hsl(var(--base)) 46%, #050A12 66.11%), linear-gradient(0deg, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0.1))
        }

        .verification-code::after {
            display: none;
        }

        .cursor-color {
            caret-color: transparent;
        }
    </style>
@endpush
@push('script')
    <script>
        (function($) {
            "use strict";
            $('#verification-code').on('input', function() {
                var inputLength = $(this).val().length;
                if (inputLength == 6) {
                    $(this).addClass('cursor-color');
                } else {
                    $(this).removeClass('cursor-color');
                }
            });
        })(jQuery)
    </script>
@endpush
