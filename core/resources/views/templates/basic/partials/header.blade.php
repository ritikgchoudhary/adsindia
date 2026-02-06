<header class="header" id="header">
    <div class="container position-relative">
        <nav class="navbar navbar-expand-xl navbar-light">
            <a class="navbar-brand logo" href="{{ route('home') }}">
                <img src="{{ siteLogo() }}" alt="{{ gs('site_name') }}">
            </a>
            <button class="navbar-toggler header-button" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false"
                    aria-label="Toggle navigation">
                <span id="hiddenNav"><i class="las la-bars"></i></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav nav-menu w-100 align-items-xl-center justify-content-center">
                    <li class="nav-item d-block d-xl-none">
                        <div class="top-button d-flex flex-wrap justify-content-between align-items-center">
                            <ul class="login-registration-list d-flex flex-wrap align-items-center">
                                <li class="login-registration-list__item">
                                    <a href="{{ route('user.login') }}" class="login-registration-list__link login">
                                        <span class="login-registration-list__icon">
                                            <i class="las la-file-export"></i>
                                        </span>
                                        @lang('Login')
                                    </a>
                                </li>
                            </ul>

                            @include($activeTemplate . 'partials.language_selector')
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ menuActive('home') }}" href="{{ route('home') }}">
                            @lang('Home')
                        </a>
                    </li>
                    @foreach ($pages as $page)
                        <li class="nav-item">
                            <a class="nav-link {{ menuActive(route('pages', [$page->slug])) }}" href="{{ route('pages', [$page->slug]) }}">
                                {{ __($page->name) }}
                            </a>
                        </li>
                    @endforeach

                    <li class="nav-item">
                        <a class="nav-link {{ menuActive('campaigns') }}" href="{{ route('campaigns') }}">
                            @lang('Campaigns')
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ menuActive('blogs') }}" href="{{ route('blogs') }}">
                            @lang('Blog')
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ menuActive('contact') }}" href="{{ route('contact') }}">
                            @lang('Contact')
                        </a>
                    </li>
                </ul>
            </div>
            <div class="header-right">
                <div class="top-button d-flex flex-wrap justify-content-between align-items-center">

                    @include($activeTemplate . 'partials.language_selector')

                    @if (!(auth()->check() || auth()->guard('advertiser')->check()))
                        <a href="#accountModal" data-bs-toggle="modal" class="btn btn--base pill">
                            @lang('Account')
                            <span class="btn-icon">
                                <i class="las la-user"></i>
                            </span>
                        </a>
                    @endif

                    @auth
                        <a href="{{ route('user.home') }}" class="btn btn--base pill">
                            @lang('Dashboard')
                            <span class="btn-icon">
                                <i class="las la-user"></i>
                            </span>
                        </a>
                    @endauth

                    @auth('advertiser')
                        <a href="{{ route('advertiser.home') }}" class="btn btn--base pill">
                            @lang('Dashboard')
                            <span class="btn-icon">
                                <i class="las la-user"></i>
                            </span>
                        </a>
                    @endauth

                </div>
            </div>
        </nav>
    </div>
</header>


@php
    $accountContent = getContent('account_modal.content', true);
@endphp
<!-- Modal -->
<div class="modal fade custom--modal account--modal" id="accountModal" tabindex="-1" aria-labelledby="accountModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-body">
                <div class="row gy-3">
                    <div class="col-sm-6">
                        <div class="account-modal-form">
                            <div class="account-modal-form-top">
                                <div class="image">
                                    <img src="{{ frontendImage('account_modal', $accountContent?->data_values?->affiliate_image ?? '', '64x64') }}" alt="logo">
                                </div>
                                <p class="title">
                                    {{ __($accountContent?->data_values?->affiliate_heading) }}
                                </p>
                            </div>

                            <div class="account-modal-form-body">
                                <p class="text">
                                    {{ __($accountContent?->data_values?->affiliate_description) }}
                                </p>



                                <div class="flex-center gap-2">
                                    <a href="{{ route('user.login') }}" class="btn btn--base">@lang('Login')</a>
                                    <a href="{{ route('user.register') }}" class="btn btn-outline--white">@lang('Register')</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="account-modal-form advertiser-form">
                            <div class="account-modal-form-top">
                                <div class="image">
                                    <img src="{{ frontendImage('account_modal', $accountContent?->data_values?->advertiser_image ?? '', '64x64') }}" alt="logo">
                                </div>

                                <p class="title">
                                    {{ __($accountContent?->data_values?->advertiser_heading) }}
                                </p>
                            </div>

                            <div class="account-modal-form-body">
                                <p class="text">
                                    {{ __($accountContent?->data_values?->advertiser_description) }}
                                </p>

                                <div class="flex-center gap-2">
                                    <a href="{{ route('advertiser.login') }}" class="btn btn--base">@lang('Login')</a>
                                    <a href="{{ route('advertiser.register') }}" class="btn btn-outline--white">@lang('Register')</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
