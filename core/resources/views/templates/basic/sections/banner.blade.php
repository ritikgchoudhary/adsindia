@php
    $banner = getContent('banner.content', true);
    $bannerFeatures = getContent('banner.element', orderById: true);
@endphp
<section class="banner-section bg-img" data-background-image="{{ asset($activeTemplateTrue . 'images/shapes/banner-1.png') }}">
    <div class="shape-one">
        <img src="{{ asset($activeTemplateTrue . 'images/shapes/bs-1.png') }}" alt="img" />
    </div>
    <div class="shape-two">
        <img src="{{ asset($activeTemplateTrue . 'images/shapes/bs-2.png') }}" alt="img" />
    </div>
    <div class="shape-three">
        <img src="{{ asset($activeTemplateTrue . 'images/shapes/bs-3.png') }}" alt="img" />
    </div>
    <div class="banner-thumb">
        <img src="{{ frontendImage('banner', $banner?->data_values?->image ?? '', '540x550') }}" alt="img" />
    </div>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-12">
                <div class="banner-content">
                    <h4 class="banner-content__subtitle">
                        {{ __($banner?->data_values?->subtitle ?? 'Affiliate Marketing Platform') }}
                    </h4>
                    <h1 class="banner-content__title">
                        {{ __($banner?->data_values?->title ?? 'Earn Money With Us') }}
                        <span class="icon">
                            <img src="{{ frontendImage('banner', $banner?->data_values?->image_three ?? '', '32x18') }}" alt="img" />
                            {{ __($banner?->data_values?->affiliate_text ?? '') }}
                        </span>
                        <span class="text--base text">
                            <span class="text-container">
                                @php
                                    $marketingText = str_split(strip_tags($banner?->data_values?->marketing_text ?? ''));
                                @endphp
                                @foreach ($marketingText as $key => $char)
                                    <span class="letter">
                                        @if ($key == 2)
                                            <img src="{{ asset($activeTemplateTrue . 'images/thumbs/st.png') }}" alt="img" />
                                        @endif
                                        {{ $char }}
                                    </span>
                                @endforeach
                            </span>
                        </span>
                    </h1>
                    <p class="banner-content__desc">
                        {{ __($banner?->data_values?->description ?? 'Join our affiliate program, promote campaigns and earn commission on every conversion. No investment required.') }}
                    </p>
                    <div class="banner-content__btn">
                        <a href="{{ url($banner?->data_values?->button_url ?? route('user.register')) }}" class="btn btn--base pill">
                            {{ __($banner?->data_values?->button_name ?? 'Get Started') }}
                            <span class="btn-icon">
                                <i class="las la-arrow-right"></i>
                            </span>
                        </a>
                    </div>
                    <div class="banner-content__shape">
                        <img src="{{ frontendImage('banner', $banner?->data_values?->image_two ?? '', '123x123') }}" alt="img" />
                    </div>
                    <div class="banner-content__shape-two">
                        <img src="{{ frontendImage('banner', $banner?->data_values?->image_one ?? '', '39x40') }}" alt="img" />
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="brand-wrapper">
        @foreach ($bannerFeatures as $feature)
            <span class="title">
                {{ __($feature?->data_values?->feature_title ?? '') }}
            </span>
        @endforeach
    </div>
</section>
