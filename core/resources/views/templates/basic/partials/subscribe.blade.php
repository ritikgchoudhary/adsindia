@php
    $subscribe = getContent('subscribe_section.content', true);
@endphp

<div class="footer-item">
    <div class="subscribe-item">
        <span class="subscribe-item__badge">{{ __($subscribe?->data_values?->heading ?? '') }}</span>
        <h6 class="subscribe-item__title">{{ __($subscribe?->data_values?->subheading ?? '') }}</h6>
        <form id="subscribe-form" class="subscribe-form" method="POST">
            @csrf
            <input type="email" name="email" class="form--control" placeholder="@lang('Enter email address')" required>
            <button type="submit" class="icon">
                <i class="las la-paper-plane"></i>
            </button>
        </form>
        
    </div>
</div>


@push('script')
    <script>
        (function($) {
            "use strict";
            $('#subscribe-form').on('submit', function(e) {
                e.preventDefault();
                let form = $(this);
                let email = form.find('input[name="email"]').val();
                $.ajax({
                    type: 'POST',
                    url: `{{ route('subscribe') }}`,
                    data: {
                        _token: "{{ csrf_token() }}",
                        email: email
                    },
                    success: function(res) {
                        if (res.status == 'error') {
                            notify('error', res?.message?.error?.[0]);
                        } else {
                            notify('success', res?.message?.success?.[0]);
                        }
                        form[0].reset();
                    },
                });
            });
        })(jQuery);
    </script>
@endpush
