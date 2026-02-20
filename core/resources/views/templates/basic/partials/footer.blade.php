@php
    $contact = getContent('contact_us.content', true);
    $socialIcons = getContent('social_icon.element', orderById: true);
    $policyPages = getContent('policy_pages.element', orderById: true);
    $footerContent = getContent('footer.content', true);
@endphp

<footer class="footer-area">
    <div class="bg-area"></div>

    <div class="footer-area__shape-one">
        <img src="{{ asset($activeTemplateTrue . 'images/shapes/fs-3.png') }}" alt="">
    </div>
    <div class="footer-area__shape-two">
        <img src="{{ asset($activeTemplateTrue . 'images/shapes/fs-4.png') }}" alt="">
    </div>
    <div class="footer-area__shape-three">
        <img src="{{ asset($activeTemplateTrue . 'images/shapes/fs-1.png') }}" alt="">
    </div>
    <div class="footer-area__shape-four">
        <img src="{{ asset($activeTemplateTrue . 'images/shapes/fs-2.png') }}" alt="">
    </div>

    <div class="container">
        <div class="footer-item-wrapper">
            <div class="footer-item">
                <a href="{{ route('home') }}" class="footer-item__logo">
                    <img src="{{ siteLogo() }}" alt="@lang('Site Logo')">
                </a>

                <p class="footer-item__desc">
                    {{ __($footerContent?->data_values?->description ?? '') }}
                </p>

            </div>
            <div class="footer-item">
                <h6 class="footer-item__title">@lang('Quick Links') </h6>
                <ul class="footer-menu">
                    <li class="footer-menu__item"><a href="{{ route('home') }}" class="footer-menu__link"> @lang('Home') </a></li>
                    <li class="footer-menu__item"><a href="{{ route('user.register') }}" class="footer-menu__link">@lang('Join As Affiliate')</a></li>
                    <li class="footer-menu__item"><a href="{{ route('advertiser.register') }}" class="footer-menu__link">@lang('Join As Advertiser')</a></li>
                </ul>
            </div>
            <div class="footer-item">
                <h6 class="footer-item__title"> @lang('Useful Links') </h6>
                <ul class="footer-menu">
                    @foreach ($policyPages as $policy)
                        <li class="footer-menu__item"><a href="{{ route('policy.pages', $policy->slug) }}" class="footer-menu__link"> {{ __($policy?->data_values?->title ?? '') }} </a></li>
                    @endforeach
                </ul>

            </div>
            @include($activeTemplate . 'partials.subscribe')
        </div>
    </div>

    <!-- bottom Footer -->
    <div class="bottom-footer py-3">
        <div class="container">
            <div class="flex-between gap-3">
                <div class="bottom-footer-text">
                    @lang('Copyright') &copy; {{ date('Y') }} <a href="{{ route('home') }}">Ads Skill India</a> @lang('All rights reserved')
                </div>
                <ul class="social-list">
                    @foreach ($socialIcons as $icon)
                        <li class="social-list__item">
                            <a href="{{ $icon?->data_values?->url ?? 'javascript:void(0);' }}" class="social-list__link flex-center" target="_blank">
                                @php echo $icon?->data_values?->social_icon @endphp
                                {{ __($icon?->data_values?->title ?? '') }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</footer>
