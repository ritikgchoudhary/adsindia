@php
    $registerContent = getContent('advertiser_register.content', true);
    $aboutContent = getContent('about.content', true);
@endphp
@extends($activeTemplate . 'layouts.app')
@section('app')
    @if (gs('advertiser_registration'))
        <section class="account register">
            <div class="account-inner">
                <div class="container">
                    <div class="row gy-4 flex-wrap-reverse justify-content-between">
                        <div class="col-lg-6 d-xl-block d-none pe-xl-5">
                            <a href="{{ route('home') }}" class="account-logo">
                                <img src="{{ siteLogo() }}" alt="@lang('logo')">
                            </a>

                            <div class="about-thumb-wrapper">
                                <div class="left-thumb-wrapper">
                                    <div class="left-thumb"></div>
                                    <div class="border-shape"></div>
                                </div>

                                <div class="about-thumb-wrapper__thumb">
                                    <img src="{{ frontendImage('about', $aboutContent?->data_values?->image, '625x545') }}" alt="@lang('Affiliate Image')" />
                                </div>
                                <div class="thumb-text-wrapper">
                                    <span class="text base--outline">{{ __($aboutContent?->data_values?->tag_text_one ?? '') }}</span>
                                    <span class="text success--outline">{{ __($aboutContent?->data_values?->tag_text_two ?? '') }}</span>
                                    <span class="text warning--outline">{{ __($aboutContent?->data_values?->tag_text_three ?? '') }}</span>
                                    <span class="text success--outline">{{ __($aboutContent?->data_values?->tag_text_four ?? '') }}</span>
                                    <span class="text base--outline">{{ __($aboutContent?->data_values?->tag_text_five ?? '') }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="right-thumb-wrapper">
                                <div class="right-thumb"></div>
                                <div class="border-shape"></div>
                                <div class="bg-style"></div>
                            </div>

                            <div class="account-form">
                                <a href="{{ route('home') }}" class="d-xl-none account-logo">
                                    <img src="{{ siteLogo() }}" alt="@lang('logo')">
                                </a>
                                <div class="account-form__content">
                                    <span class="account-form__badge">{{ __($registerContent?->data_values?->heading) }}</span>
                                    <h4 class="account-form__title">{{ __($registerContent?->data_values?->subheading) }}</h4>
                                </div>

                                <form action="{{ route('advertiser.register') }}" method="POST" class="verify-gcaptcha disableSubmission">
                                    @csrf
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label class="form--label">@lang('First Name')</label>
                                                <input type="text" name="firstname" class="form--control" value="{{ old('firstname') }}" required>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label class="form--label">@lang('Last Name')</label>
                                                <input type="text" name="lastname" class="form--control" value="{{ old('lastname') }}" required>
                                            </div>
                                        </div>

                                        <div class="col-sm-12">
                                            <div class="form-group">
                                                <label class="form--label">@lang('E-Mail Address')</label>
                                                <input type="email" name="email" class="form--control checkAdvertiser" value="{{ old('email') }}" required>
                                            </div>
                                        </div>

                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label class="form--label">@lang('Password')</label>
                                                <input type="password" name="password" class="form--control @if (gs('secure_password')) secure-password @endif" required>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label class="form--label">@lang('Confirm Password')</label>
                                                <input type="password" name="password_confirmation" class="form--control" required>
                                            </div>
                                        </div>

                                        <div class="col-sm-12">
                                            <x-captcha />
                                        </div>

                                        @if (gs('agree'))
                                            @php $policyPages = getContent('policy_pages.element', false, orderById: true); @endphp
                                            <div class="col-12">
                                                <div class="form--check">
                                                    <input type="checkbox" name="agree" id="agree" class="form-check-input" {{ old('agree') ? 'checked' : '' }} required>
                                                    <label class="form-check-label" for="agree">
                                                        @lang('I agree with')
                                                        @foreach ($policyPages as $policy)
                                                            <a href="{{ route('policy.pages', $policy->slug) }}" target="_blank">
                                                                {{ __($policy?->data_values?->title) }}
                                                            </a>
                                                            @if (!$loop->last)
                                                                ,
                                                            @endif
                                                        @endforeach
                                                    </label>
                                                </div>
                                            </div>
                                        @endif

                                    </div>
                                    <button type="submit" id="recaptcha" class="btn btn--base w-100 btn--lg mt-3">
                                        @lang('Submit')
                                    </button>
                                </form>

                                @include($activeTemplate . 'partials.advertiser_social_login')

                                <div class="have-account text-center mt-3">
                                    <p class="have-account__text">
                                        @lang('Already have an account?')
                                        <a href="{{ route('advertiser.login') }}" class="have-account__link">
                                            @lang('Login Now')
                                        </a>
                                    </p>
                                </div>

                            </div>
                        </div>

                    </div>
                </div>
            </div>



            <div class="modal fade custom--modal" id="existModalCenter" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">@lang('You are with us')</h5>
                            <span type="button" class="close" data-bs-dismiss="modal"><i class="las la-times"></i></span>
                        </div>
                        <div class="modal-body">
                            <h6 class="text-center mb-0">@lang('You already have an account please Login')</h6>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn--danger btn--sm" data-bs-dismiss="modal">@lang('Close')</button>
                            <a href="{{ route('advertiser.login') }}" class="btn btn--base btn--sm">@lang('Login')</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @else
        @include($activeTemplate . 'partials.registration_disabled')
    @endif

@endsection

@push('style')
    <style>
        .custom--modal .modal-header {
            justify-content: space-between;
        }

        .hover-input-popup .input-popup {
            bottom: 80%;
        }
    </style>
@endpush

@if (gs('registration'))

    @if (gs('secure_password'))
        @push('script-lib')
            <script src="{{ asset('assets/global/js/secure_password.js') }}"></script>
        @endpush
    @endif

    @push('script')
        <script>
            "use strict";
            (function($) {
                $('[name=captcha]').removeClass('form-control').siblings('label').addClass('form--label').removeClass('form-label');

                $('.checkAdvertiser').on('focusout', function() {
                    const url = '{{ route('advertiser.checkAdvertiser') }}';
                    const value = $(this).val();
                    const token = '{{ csrf_token() }}';
                    $.post(url, {
                        email: value,
                        _token: token
                    }, function(response) {
                        if (response.data !== false) {
                            $('#existModalCenter').modal('show');
                        }
                    });
                });
            })(jQuery);
        </script>
    @endpush
@endif
