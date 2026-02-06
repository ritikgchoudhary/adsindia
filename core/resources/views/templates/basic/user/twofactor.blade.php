@extends($activeTemplate . 'layouts.master')

@section('content')
    <div class="card custom--card">
        <div class="card-body">
            <div class="row gy-4">
                @if (!auth()->user()->ts)
                    {{-- QR and Setup Key --}}
                    <div class="col-xxl-3 col-lg-5 col-md-6">
                        <div class="setting-wrapper">
                            <p class="text-white mb-3">
                                @lang('Use the QR code or setup key on your Google Authenticator app to add your account.')
                            </p>
                            <div class="qr-code text-center border border-dashed p-2 rounded">
                                <img class="mx-auto" src="{{ $qrCodeUrl }}" alt="QR" width="200">
                            </div>
                        </div>
                    </div>

                    <div class="col-xxl-4 col-lg-5 col-md-6">
                        <div class="setting-wrapper__right">
                            <div class="form-group mb-3">
                                <label class="form--label text-white fw-bold">@lang('Setup Key')</label>
                                <div class="input-group">
                                    <input type="text" name="key" value="{{ $secret }}" class="form-control form--control referralURL" readonly>
                                    <button type="button" class="btn bg--base text-white" id="copyBoard" style="border-radius: 0 5px 5px 0;">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </div>

                            <p class="text-white mt-3">
                                @lang('Google Authenticator is a multifactor app for mobile devices. It generates timed codes used during the 2-step verification process. To use Google Authenticator, install the Google Authenticator application on your mobile device.')
                                <a class="text--base" href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2&hl=en" target="_blank">@lang('Download')</a>
                            </p>
                        </div>
                    </div>
                @endif

                <div class="col-12 mt-5">
                    @if (auth()->user()->ts)
                        <h5 class="text-white mb-3">@lang('Disable 2FA Security')</h5>
                        <form action="{{ route('user.twofactor.disable') }}" method="POST" class="twofa-enable-wrapper">
                            @csrf
                            <input type="hidden" name="key" value="{{ $secret }}">
                            <div class="form-group mb-3">
                                <label class="form--label text-white">@lang('Google Authenticator OTP')</label>
                                <input type="text" class="form--control" name="code" required>
                            </div>
                            <button type="submit" class="btn btn--base w-100">@lang('Submit')</button>
                        </form>
                    @else
                        <h5 class="text-white mb-3">@lang('Enable 2FA Security')</h5>
                        <form action="{{ route('user.twofactor.enable') }}" method="POST" class="twofa-enable-wrapper">
                            @csrf
                            <input type="hidden" name="key" value="{{ $secret }}">
                            <div class="form-group mb-3">
                                <label class="form--label text-white fw-bold">
                                    @lang('Google Authenticator OTP')
                                </label>
                                <input type="text" name="code" class="form--control" required>
                            </div>
                            <button type="submit" class="btn btn--base w-100">@lang('Submit')</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('style')
    <style>
        .copied::after {
            background-color: #{{ gs('base_color') }};
        }

        .qr-code img {
            max-width: 100%;
            height: auto;
        }

        .border-dashed {
            border-style: dashed !important;
        }


        .twofa-enable-wrapper {
            background: linear-gradient(to bottom, #000000, #051d35);
            padding: 25px;
            border-radius: 12px;
        }
    </style>
@endpush

@push('script')
    <script>
        (function($) {
            "use strict";

            $('#copyBoard').on('click', function() {
                const copyText = document.querySelector(".referralURL");
                copyText.select();
                copyText.setSelectionRange(0, 99999);
                document.execCommand("copy");
                copyText.blur();

                $(this).addClass('copied');
                setTimeout(() => $(this).removeClass('copied'), 1500);
            });

        })(jQuery);
    </script>
@endpush
