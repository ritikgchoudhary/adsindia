@extends($activeTemplate . 'layouts.app')

@section('app')
    @php
        $aboutContent = getContent('about.content', true);
        $loginContent = getContent('login.content', true);
    @endphp

    <section class="account">
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
                                <img src="{{ frontendImage('about', $aboutContent?->data_values?->image ?? '', '625x545') }}" alt="@lang('Affiliate Image')" />
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

                    <div class="col-xl-6 ps-xl-5">
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
                                <span class="account-form__badge">{{ __($loginContent?->data_values?->heading ?? '') }}</span>
                                <h4 class="account-form__title">{{ __($loginContent?->data_values?->subheading ?? '') }}</h4>
                            </div>

                            <form method="POST" action="{{ route('user.login') }}" class="verify-gcaptcha">
                                @csrf
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label class="form--label">@lang('Username or Email')</label>
                                            <input type="text" name="username" value="{{ old('username') }}" class="form--control" required>
                                        </div>
                                    </div>

                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label for="password" class="form--label">@lang('Password')</label>
                                            <div class="position-relative">
                                                <input id="password" type="password" name="password" class="form-control form--control" required>
                                                <span class="password-show-hide fas toggle-password fa-eye-slash" id="#password"></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-sm-12">
                                        <x-captcha />
                                    </div>

                                    <div class="col-sm-12">
                                        <div class="d-flex flex-wrap justify-content-between">
                                            <div class="form--check">
                                                <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                                <label class="form-check-label" for="remember">@lang('Remember Me')</label>
                                            </div>
                                            <a class="forgot-password text--base" href="{{ route('user.password.request') }}">
                                                @lang('Forgot your password?')
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" id="recaptcha" class="btn btn--base w-100 btn--lg mt-3">@lang('Submit')</button>
                            </form>

                            @include($activeTemplate . 'partials.social_login')

                            @if (gs('registration'))
                                <div class="mt-3">
                                    <div class="have-account text-center">
                                        <p class="have-account__text">
                                            @lang('Don\'t have an account?')
                                            <a href="{{ route('user.register') }}" class="have-account__link">
                                                @lang('Create an account')
                                            </a>
                                        </p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
@push('script')
    <script>
        (function($) {
            "use strict";
            $('[name=captcha]').removeClass('form-control').siblings('label').addClass('form--label').removeClass('form-label');

        })(jQuery)
    </script>
@endpush
