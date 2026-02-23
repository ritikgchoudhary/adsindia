@php
    $benefitContent = getContent('benefit_section.content', true);
    $benefitItems = getContent('benefit_section.element', orderById: true);
@endphp

<div class="benefit-section my-120">
    <div class="container">
        <div class="row gy-5 justify-content-center">
            <div class="col-lg-6 pe-xl-5">
                <div class="section-heading style-left">
                    <span class="section-heading__subtitle">
                        {{ __($benefitContent?->data_values?->heading ?? '') }}
                    </span>
                    <h3 class="section-heading__title">
                        {{ __($benefitContent?->data_values?->subheading ?? '') }}
                    </h3>
                    <p class="section-heading__desc">
                        {{ __($benefitContent?->data_values?->description ?? '') }}
                    </p>
                </div>

                <ul class="benefit-list">
                    @foreach ($benefitItems as $index => $item)
                        <li class="benefit-list__item">
                            {{ __($item?->data_values?->benefit ?? '') }}
                        </li>
                    @endforeach
                </ul>
            </div>
            <div class="col-lg-6 ps-xl-5">
                <div class="benefit-wrapper">
                    <div class="benefit-wrapper__shape"></div>
                    <div class="benefit-thumb-item">
                        <div class="thumb">
                            <img src="{{ frontendImage('benefit_section', $benefitContent?->data_values?->thumb ?? '', '500x320') }}" alt="@lang('Benefit Image')" class="fit-image">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
