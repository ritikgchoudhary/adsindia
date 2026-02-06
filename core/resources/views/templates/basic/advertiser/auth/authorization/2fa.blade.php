@extends($activeTemplate . 'layouts.frontend')
@section('content')
    <div class="py-120">
        <div class="container">
            <div class="d-flex justify-content-center">
                <div class="verification-code-wrapper">
                    <div class="verification-area">
                        <form action="{{ route('advertiser.2fa.verify') }}" method="POST" class="submit-form">
                            @csrf

                            @include($activeTemplate . 'partials.verification_code')

                            <button type="submit" class="btn btn--base w-100">@lang('Submit')</button>
                        </form>
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
