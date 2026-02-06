@php
    $cta = getContent('cta_section.content', true);
@endphp

<div class="cta-section my-120">
    <div class="container">
        <div class="cta-wrapper">
            <div class="cta-content">
                <span class="cta-content__badge">{{ __($cta?->data_values?->title ?? '') }}</span>
                <h3 class="cta-content__title">{{ __($cta?->data_values?->subtitle ?? '') }}</h3>
                <p class="cta-content__desc">{{ __($cta?->data_values?->description ?? '') }}</p>

                <div class="cta-content__btn">
                    <a href="{{ url($cta?->data_values?->button_url) }}" class="btn btn--base pill">
                        {{ __($cta?->data_values?->button_name) }}
                        <span class="btn-icon"><i class="las la-arrow-right"></i></span>
                    </a>
                </div>
            </div>

            <div class="cta-thumb-wrapper">
                <div class="thumb">
                    <img src="{{ frontendImage('cta_section', $cta?->data_values?->image ?? '', '520x450') }}" alt="@lang('CTA Image')">
                </div>
            </div>

            <div class="shape">
                <img src="{{ asset($activeTemplateTrue . 'images/shapes/cta-2.png') }}" alt="img" />
            </div>
            <div class="shape-two">
                <img src="{{ asset($activeTemplateTrue . 'images/shapes/cta-3.png') }}" alt="img" />
            </div>
            <div class="shape-three">
                <img src="{{ asset($activeTemplateTrue . 'images/shapes/cta-4.png') }}" alt="img" />
            </div>
            <div class="shape-four">
                <img src="{{ asset($activeTemplateTrue . 'images/shapes/cta-4.png') }}" alt="img" />
            </div>
        </div>
    </div>
</div>
