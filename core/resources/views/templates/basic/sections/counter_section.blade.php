@php
    $counters = getContent('counter_section.element', orderById: true);
@endphp

<div class="counter-section my-120">
    <div class="container">
        <div class="row g-4 justify-content-center counter-wrapper">
            @foreach ($counters as $counter)
                <div class="col-xl-3 col-lg-4 col-sm-6 col-xsm-6">
                    <div class="counter-item">
                        <div class="counter-item__number">
                            <h3 class="counter-item__title">
                                <span class="odometer" data-odometer-final="{{ $counter?->data_values?->counter_value ?? 0 }}">
                                    0
                                </span>
                                {{ $counter?->data_values?->counter_suffix ?? '' }}
                            </h3>
                        </div>
                        <span class="counter-item__text">
                            {{ __($counter?->data_values?->counter_title ?? '') }}
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

@if (!app()->offsetExists('odometer_script'))
    @push('style-lib')
        <link rel="stylesheet" href="{{ asset($activeTemplateTrue . 'css/odometer.css') }}">
    @endpush

    @push('script-lib')
        <script src="{{ asset($activeTemplateTrue . 'js/odometer.min.js') }}"></script>
        <script src="{{ asset($activeTemplateTrue . 'js/viewport.jquery.js') }}"></script>
    @endpush

    @php app()->offsetSet('odometer_script', true) @endphp
@endif



@push('script')
    <script>
        (function($) {
            "use strict";

            $('.counter-item').each(function() {
                $(this).isInViewport(function(status) {
                    if (status === 'entered') {
                        for (
                            var i = 0; i < document.querySelectorAll('.odometer').length; i++
                        ) {
                            var el = document.querySelectorAll('.odometer')[i];
                            el.innerHTML = el.getAttribute('data-odometer-final');
                        }
                    }
                });
            });

        })(jQuery);
    </script>
@endpush
