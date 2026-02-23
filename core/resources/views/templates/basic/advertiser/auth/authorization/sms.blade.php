@extends($activeTemplate . 'layouts.frontend')
@section('content')
    <div class="py-120">
        <div class="container">
            <div class="d-flex justify-content-center">
                <div class="verification-code-wrapper">
                    <div class="verification-area">
                        <form action="{{ route('advertiser.verify.mobile') }}" method="POST" class="submit-form">
                            @csrf
                            <p class="mb-3">@lang('A 6 digit verification code sent to your mobile number') : +{{ showMobileNumber(auth()->guard('advertiser')->user()->mobileNumber) }}</p>
                            @include($activeTemplate . 'partials.verification_code')
                            <button type="submit" class="btn btn--base w-100">@lang('Submit')</button>
                            <div class="mt-3">
                                <p>
                                    @lang('If you don\'t get any code'), <span class="countdown-wrapper">@lang('try again after') <span id="countdown" class="fw-bold">--</span> @lang('seconds')</span> <a href="{{ route('advertiser.send.verify.code', 'sms') }}" class="try-again-link d-none"> @lang('Try again')</a>
                                </p>
                                <a href="{{ route('advertiser.logout') }}">@lang('Logout')</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('script')
    <script>
        var distance = Number("{{ isset($advertiser->ver_code_send_at) ? $advertiser->ver_code_send_at->addMinutes(2)->timestamp - time() : '' }}");
        var x = setInterval(function() {
            distance--;
            document.getElementById("countdown").innerHTML = distance;
            if (distance <= 0) {
                clearInterval(x);
                document.querySelector('.countdown-wrapper').classList.add('d-none');
                document.querySelector('.try-again-link').classList.remove('d-none');
            }
        }, 1000);

        $('#verification-code').on('input', function() {
            var inputLength = $(this).val().length;
            if (inputLength == 6) {
                $(this).addClass('cursor-color');
            } else {
                $(this).removeClass('cursor-color');
            }
        });
    </script>
@endpush

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
