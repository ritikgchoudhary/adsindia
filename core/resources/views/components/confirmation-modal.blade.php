@props(['closeBtn' => 'btn--dark', 'submitBtn' => 'btn--primary', 'class' => ''])
<div id="confirmationModal" class="modal fade {{ $class }}" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">@lang('Confirmation Alert!')</h6>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="las la-times"></i>
                </button>
            </div>
            <form method="POST">
                @csrf
                <div class="modal-body">
                    <p class="question"></p>
                    {{ $slot }}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn {{ $closeBtn }}" data-bs-dismiss="modal">@lang('No')</button>
                    <button type="submit" class="btn {{ $submitBtn }}">@lang('Yes')</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('script')
    <script>
        (function($) {
            "use strict";
            $(document).on('click', '.confirmationBtn', function() {
                const modal = $('#confirmationModal');
                const data = $(this).data();
                modal.find('.question').text(data.question ?? '');
                modal.find('form').attr('action', data.action ?? '#');
                modal.modal('show');
            });
        })(jQuery);
    </script>
@endpush
