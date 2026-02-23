@php
    $chooseUsContent = getContent('why_choose_us.content', true);
    $chooseUsItems = getContent('why_choose_us.element', orderById: true);
@endphp
<div class="why-choose-section my-120">
    <div class="container">
        <div class="row gy-5 justify-content-center flex-wrap-reverse">
            <div class="col-xl-6 col-lg-7 pe-xl-5">
                <div class="choose-thumb-container">
                    <div class="why-choose-wrapper"></div>
                    <div class="row gy-4 align-items-center">
                        <div class="col-6">
                            <div class="choose-item">
                                <div class="choose-item__thumb">
                                    <img src="{{ frontendImage('why_choose_us', $chooseUsContent?->data_values?->thumb_one ?? '', '225x220') }}" alt="thumb one">
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="choose-item">
                                <div class="choose-item__thumb">
                                    <img src="{{ frontendImage('why_choose_us', $chooseUsContent?->data_values?->thumb_two ?? '', '225x220') }}" alt="thumb two">
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="item-shape">
                                <div class="shape-one">
                                    <img src="{{ frontendImage('why_choose_us', $chooseUsContent?->data_values?->thumb_four ?? '', '123x123') }}" alt="shape one">
                                </div>
                                <div class="shape-two">
                                    <img src="{{ asset($activeTemplateTrue . 'images/shapes/shape-1.png') }}" alt="shape two">
                                </div>

                                <div class="choose-btn">
                                    <a href="{{ $chooseUsContent?->data_values?->video_url }}" class="play-button popup-youtube" data-rel="lightcase">
                                        <span class="icon"><i class="fas fa-play"></i></span>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="choose-item">
                                <div class="choose-item__thumb">
                                    <img src="{{ frontendImage('why_choose_us', $chooseUsContent?->data_values?->thumb_three ?? '', '225x220') }}" alt="thumb four">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-6 col-lg-5 ps-lg-5">
                <div class="section-heading style-left">
                    <span class="section-heading__subtitle">{{ __($chooseUsContent?->data_values?->heading ?? '') }}</span>
                    <h3 class="section-heading__title">{{ __($chooseUsContent?->data_values?->subheading ?? '') }}</h3>
                    <p class="section-heading__desc">{{ __($chooseUsContent?->data_values?->description ?? '') }}</p>
                </div>
                <div>
                    @php
                        $classes = ['card-base-two-bg', 'card-warning-bg', 'card-success-bg'];
                    @endphp
                    @foreach ($chooseUsItems as $key => $chooseItem)
                        @php
                            $class = $classes[$key % count($classes)];
                        @endphp
                        <div class="choose-card {{ $class }}">
                            <div class="choose-card__icon">
                                <img src="{{ frontendImage('why_choose_us', $chooseItem?->data_values?->image ?? '', '42x42') }}" alt="">
                            </div>
                            <div class="choose-card__content">
                                <h5 class="choose-card__title">{{ __($chooseItem?->data_values?->title ?? '') }}</h5>
                                <p class="choose-card__text">{{ __($chooseItem?->data_values?->subtitle ?? '') }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>


@push('style-lib')
    <link rel="stylesheet" href="{{ asset($activeTemplateTrue . 'css/lightcase.css') }}">
@endpush

@push('script-lib')
    <script src="{{ asset($activeTemplateTrue . 'js/lightcase.js') }}"></script>
@endpush
