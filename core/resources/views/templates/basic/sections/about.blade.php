@php
    $aboutContent = getContent('about.content', true);
    $aboutElement = getContent('about.element', orderById: true);
@endphp

<div class="about-section my-120">
    <div class="container">
        <div class="row gy-4 justify-content-center">
            <div class="col-xl-6 pe-xxl-5">
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
            <div class="col-xl-6 ps-lg-5">
                <div class="about-content">
                    <span class="about-content__badge">{{ __($aboutContent?->data_values?->badge ?? '') }}</span>
                    <h2 class="about-content__title">{{ __($aboutContent?->data_values?->heading ?? '') }}</h2>
                    <p class="about-content__desc">{{ __($aboutContent?->data_values?->subheading ?? '') }}</p>
                    <ul class="text-list">
                        @foreach ($aboutElement as $elements)
                            <li class="text-list__item">{{ __($elements?->data_values?->feature ?? '') }}</li>
                        @endforeach
                    </ul>
                    <div class="about-content__btn">
                        <a href="{{ url($aboutContent?->data_values?->button_url) }}" class="btn btn--base pill">
                            {{ __($aboutContent?->data_values?->button_name ?? '') }}
                            <span class="btn-icon"><i class="las la-arrow-right"></i></span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
