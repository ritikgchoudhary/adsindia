@extends($activeTemplate . 'layouts.app')

@section('app')
    <div class="dashboard position-relative">
        <div class="dashboard__inner flex-wrap">
            @if (auth()->check())
                @include($activeTemplate . 'partials.user_sidebar')
            @elseif(auth()->guard('advertiser')->check())
                @include($activeTemplate . 'partials.advertiser_sidebar')
            @endif

            <div class="dashboard__right">
                @include($activeTemplate . 'partials.topbar')
                <div class="container-fluid p-0">
                    <div class="dashboard-body">
                        <h4 class="title">{{ __($pageTitle) }}</h4>
                        @yield('content')
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection



@push('script')
    <script>
        (function($) {
            "use strict";

            function formatState(state) {
                if (!state.id) return state.text;

                let gatewayData = $(state.element).data();

                return $(
                    `<div class="d-flex gap-2">
                        ${gatewayData.imageSrc ? `
                            <div class="select2-image-wrapper">
                                <img class="select2-image" src="${gatewayData.imageSrc}">
                            </div>` : ''
                        }
                        <div class="select2-content">
                            <p class="select2-title">${gatewayData.title}</p>
                            <p class="select2-subtitle">${gatewayData.subtitle}</p>
                        </div>
                    </div>`
                );
            }


            $('.showFilterBtn').on('click', function() {
                $('.responsive-filter-card').slideToggle();
            });


        })(jQuery);
    </script>
@endpush
