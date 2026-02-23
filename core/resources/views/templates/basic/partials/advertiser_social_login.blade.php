@php
    $text = isset($register) ? 'Register' : 'Login';
    $socials = gs('socialite_credentials');
@endphp

@if ($socials->google->status == Status::ENABLE || $socials->facebook->status == Status::ENABLE || $socials->linkedin->status == Status::ENABLE)
    <div class="col-sm-12">
        <div class="social-login-wrapper">
            <div class="another-login">
                <span class="text">@lang('Or')</span>
            </div>
            <ul class="social-login-list">
                @if ($socials->google->status == Status::ENABLE)
                    <li class="social-login-list__item">
                        <a href="{{ route('advertiser.social.login', 'google') }}" class="w-100 social-login-btn google">
                            <span class="social-login-btn__icon">
                                <i class="fab fa-google"></i>
                            </span>
                            @lang('Continue with Google')
                        </a>
                    </li>
                @endif

                @if ($socials->facebook->status == Status::ENABLE)
                    <li class="social-login-list__item">
                        <a href="{{ route('advertiser.social.login', 'facebook') }}" class="w-100 social-login-btn facebook">
                            <span class="social-login-btn__icon">
                                <i class="fab fa-facebook-f"></i>
                            </span>
                            @lang('Continue with Facebook')
                        </a>
                    </li>
                @endif

                @if ($socials->linkedin->status == Status::ENABLE)
                    <li class="social-login-list__item">
                        <a href="{{ route('advertiser.social.login', 'linkedin') }}" class="w-100 social-login-btn linkedin">
                            <span class="social-login-btn__icon">
                                <i class="fab fa-linkedin-in"></i>
                            </span>
                            @lang('Continue with Linkedin')
                        </a>
                    </li>
                @endif
            </ul>
        </div>
    </div>
@endif
