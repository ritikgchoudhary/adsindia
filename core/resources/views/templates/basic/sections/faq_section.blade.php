@php
    $faqContent = getContent('faq_section.content', true);
    $faqs = getContent('faq_section.element', orderById: true);
    $chunks = $faqs->chunk(ceil($faqs->count() / 2));
@endphp

<div class="faq-section my-120">
    <div class="container">
        <div class="section-heading">
            <div class="left-thumb-wrapper">
                <div class="left-thumb"></div>
                <div class="border-shape"></div>
            </div>
            <div class="right-thumb-wrapper">
                <div class="right-thumb"></div>
                <div class="border-shape"></div>
            </div>
            <span class="section-heading__subtitle">{{ __($faqContent?->data_values?->heading ?? '') }}</span>
            <h3 class="section-heading__title">{{ __($faqContent?->data_values?->subheading ?? '') }}</h3>
            <p class="section-heading__desc">{{ __($faqContent?->data_values?->description ?? '') }}</p>
        </div>

        <div class="accordion custom--accordion" id="accordionExample">
            <div class="row gy-3 justify-content-center">
                @foreach ($chunks as $chunk)
                    <div class="col-lg-6">
                        @foreach ($chunk as $index => $faq)
                            @php
                                $collapseId = 'collapse' . $loop->parent->index . $loop->index;
                                $headingId = 'heading' . $loop->parent->index . $loop->index;
                            @endphp
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="{{ $headingId }}">
                                    <button class="accordion-button"
                                            type="button"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#{{ $collapseId }}"
                                            aria-expanded="false"
                                            aria-controls="{{ $collapseId }}">
                                        {{ __($faq?->data_values?->question ?? '') }}
                                    </button>
                                </h2>
                                <div id="{{ $collapseId }}"
                                     class="accordion-collapse collapse"
                                     aria-labelledby="{{ $headingId }}"
                                     data-bs-parent="#accordionExample">
                                    <div class="accordion-body">
                                        <p class="text">
                                            {{ __($faq?->data_values?->answer ?? '') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
        <div class="faq-section__bottom text-center mt-4">
            <a href="{{ url($faqContent?->data_values?->button_url) }}" class="btn btn--base pill">
                {{ __($faqContent?->data_values?->button_name ?? '') }}
                <span class="btn-icon"><i class="las la-arrow-right"></i></span>
            </a>
        </div>
    </div>
</div>
