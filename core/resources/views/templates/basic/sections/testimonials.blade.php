@php
    $testimonialContent = getContent('testimonials.content', true);
    $testimonials = getContent('testimonials.element', orderById: true);
@endphp

<section class="testimonials my-120">
    <div class="testimonial-shape">
        <img src="{{ asset($activeTemplateTrue . 'images/shapes/ts-2.png') }}" alt="@lang('Shape')">
    </div>

    <div class="container">
        <div class="row gy-5">
            <div class="col-lg-5">
                <div class="section-heading style-left">
                    <span class="section-heading__subtitle">
                        {{ __($testimonialContent?->data_values?->heading ?? '') }}
                    </span>
                    <h3 class="section-heading__title">
                        {{ __($testimonialContent?->data_values?->subheading ?? '') }}
                    </h3>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="testimonial-slider">
                    @foreach ($testimonials as $testimonial)
                        <div class="testimonails-card">
                            <div class="testimonial-item">
                                <div class="testimonial-item__content">
                                    <h4 class="testimonial-item__name">
                                        {{ __($testimonial?->data_values?->name ?? '') }}
                                    </h4>
                                    <p class="testimonial-item__designation">
                                        {{ __($testimonial?->data_values?->designation ?? '') }}
                                    </p>
                                    <div class="testimonial-item__rating">
                                        <ul class="rating-list">
                                            @for ($i = 0; $i < ($testimonial?->data_values?->rating ?? 5); $i++)
                                                <li class="rating-list__item"><i class="fas fa-star"></i></li>
                                            @endfor
                                        </ul>
                                    </div>
                                </div>
                                <p class="testimonial-item__desc">
                                    {{ __($testimonial?->data_values?->comment ?? '') }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>
