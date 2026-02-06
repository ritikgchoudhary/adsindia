@extends($activeTemplate . 'layouts.app')

@section('app')
    @include($activeTemplate . 'partials.header')

    <main>
        @if (showBreadcrumb())
            @include($activeTemplate . 'partials.breadcrumb')
        @endif

        @yield('content')
    </main>

    @include($activeTemplate . 'partials.footer')
@endsection

@push('script')
    <script>
        (function($) {
            "use strict";

            $('.policy').on('click', function() {
                $.get('{{ route('cookie.accept') }}', function() {
                    $('.cookies-card').addClass('d-none');
                });
            });

            setTimeout(function() {
                $('.cookies-card').removeClass('hide');
            }, 2000);

            $.each($('input, select, textarea'), function(i, element) {
                var elementType = $(element);
                if (elementType.attr('type') != 'checkbox') {
                    if (element.hasAttribute('required')) {
                        $(element).closest('.form-group').find('label').addClass('required');
                    }
                }

            });

        })(jQuery);
    </script>
@endpush
