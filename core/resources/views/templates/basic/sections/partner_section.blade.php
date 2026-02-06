@php
    $partnerContent = getContent('partner_section.content', true);
    $partners = getContent('partner_section.element', orderById: true);
@endphp

<div class="partner-section my-120">
    <div class="container">
        <div class="section-heading text-center">
            <span class="section-heading__subtitle">{{ __($partnerContent?->data_values?->heading ?? '') }}</span>
            <h3 class="section-heading__title">{{ __($partnerContent?->data_values?->subheading ?? '') }}</h3>
            <p class="section-heading__desc">{{ __($partnerContent?->data_values?->description ?? '') }}</p>
        </div>

        <div class="partner-wrapper">
            <div class="company-list">
                @foreach ($partners as $partner)
                    <div class="company-name">
                        <div class="thumb">
                            <img src="{{ frontendImage('partner_section', $partner?->data_values?->logo ?? '', '160x45') }}" alt="@lang('Partner Logo')">
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
