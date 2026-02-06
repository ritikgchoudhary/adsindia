@extends($activeTemplate . 'layouts.frontend')
@section('content')
    @php
        $contactContent = getContent('contact_us.content', true);
    @endphp

    <div class="contact-section mb-120 mt-60">
        <div class="container position-relative">
            <div class="contact-section__left">
                <div class="shape-bg-one"></div>
            </div>
            <div class="contact-section__right">
                <div class="shape-bg-two"></div>
            </div>

            <div class="contact-main-container">
                <div class="row gy-4 justify-content-center">
                    <div class="col-xxl-9 col-lg-8">
                        <div class="contact-wrapper">
                            <span class="contact-wrapper__subtitle">@lang('DROP YOUR MESSAGES')</span>
                            <h4 class="contact-wrapper__title">{{ __($contactContent?->data_values?->title ?? '') }}</h4>

                            <form method="POST" action="{{ route('contact') }}" class="contact-form verify-gcaptcha">
                                @csrf
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label class="form--label">@lang('Name')</label>
                                            <input type="text" name="name" class="form--control" value="{{ old('name', $user?->name) }}" @if ($user && $user->profile_complete) readonly @endif required>
                                        </div>
                                    </div>

                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label class="form--label">@lang('Email')</label>
                                            <input type="email" name="email" class="form--control" value="{{ old('email', $user?->email) }}" @if ($user) readonly @endif required>
                                        </div>
                                    </div>

                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label for="subject" class="form--label">@lang('Subject')</label>
                                            <input type="text" name="subject" class="form--control" value="{{ old('subject') }}" required>
                                        </div>
                                    </div>

                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label for="message" class="form--label">@lang('Message')</label>
                                            <textarea name="message" class="form--control" rows="4" required>{{ old('message') }}</textarea>
                                        </div>
                                    </div>

                                    <x-captcha />

                                    <div class="col-sm-12">
                                        <button class="btn--base btn btn--lg w-100" type="submit">@lang('Submit')</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="col-xxl-3 col-lg-4">
                        <div class="contact-sidebar">
                            <span class="contact-sidebar__subtitle">@lang('CONTACT INFO')</span>
                            <div class="contact-item-wrapper">
                                <div class="contact-item bg-base-two">
                                    <span class="contact-item__icon"><i class="las la-map-marker-alt"></i></span>
                                    <div class="contact-item__content">
                                        <h6 class="contact-item__title">@lang('Office address')</h6>
                                        <p class="contact-item__desc">{{ __($contactContent?->data_values?->address ?? '') }}</p>
                                    </div>
                                </div>
                                <div class="contact-item">
                                    <span class="contact-item__icon"><i class="las la-envelope"></i></span>
                                    <div class="contact-item__content">
                                        <h6 class="contact-item__title">@lang('Email address')</h6>
                                        <p class="contact-item__desc">
                                            <a href="mailto:{{ $contactContent?->data_values?->email_address ?? '' }}" class="link">
                                                {{ $contactContent?->data_values?->email_address ?? '' }}
                                            </a>
                                        </p>
                                    </div>
                                </div>
                                <div class="contact-item bg-success">
                                    <span class="contact-item__icon"><i class="las la-phone-alt"></i></span>
                                    <div class="contact-item__content">
                                        <h6 class="contact-item__title">@lang('Phone number')</h6>
                                        <p class="contact-item__desc">
                                            <a href="tel:{{ $contactContent?->data_values?->contact_number ?? '' }}" class="link">
                                                {{ $contactContent?->data_values?->contact_number ?? '' }}
                                            </a>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if (isset($sections->secs) && $sections->secs != null)
        @foreach (json_decode($sections->secs) as $sec)
            @include($activeTemplate . 'sections.' . $sec)
        @endforeach
    @endif

@endsection

@push('script')
    <script>
        (function($) {
            "use strict";
            $('[name=captcha]').removeClass('form-control').siblings('label').addClass('form--label').removeClass('form-label');
        })(jQuery)
    </script>
@endpush
