@extends($activeTemplate . 'layouts.master')

@section('content')
    <div class="row justify-content-center mt-2">
        <div class="col-lg-12">
            <div class="d-flex justify-content-end">
                <form>
                    <div class="mb-3">
                        <div class="input-group">
                            <input type="search" name="search" class="form-control form--control" value="{{ request()->search }}" placeholder="@lang('Search by transactions')">
                            <button class="input-group-text bg--base text-white border-0">
                                <i class="las la-search"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="campaign-table mt-3">
                <div class="card custom--card">
                    <div class="card-body">
                        <table class="table table--responsive--xl">
                            <thead>
                                <tr>
                                    <th>@lang('Gateway | Transaction')</th>
                                    <th class="text-center">@lang('Initiated')</th>
                                    <th class="text-center">@lang('Amount')</th>
                                    <th class="text-center">@lang('Status')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($withdraws as $withdraw)
                                    @php
                                        $details = [];
                                        foreach ($withdraw->withdraw_information as $key => $info) {
                                            $details[] = $info;
                                            if ($info->type == 'file') {
                                                $details[$key]->value = route('user.download.attachment', encrypt(getFilePath('verify') . '/' . $info->value));
                                            }
                                        }
                                    @endphp
                                    <tr>
                                        <td>
                                            <div>
                                                <span class="fw-bold text--base">{{ __($withdraw->method->name) }}</span><br>
                                                <small>{{ $withdraw->trx }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                {{ showDateTime($withdraw->created_at) }} <br>
                                                {{ diffForHumans($withdraw->created_at) }}
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                {{ showAmount($withdraw->amount) }} -
                                                <span class="text--danger" data-bs-toggle="tooltip" title="@lang('Processing Charge')">
                                                    {{ showAmount($withdraw->charge) }}
                                                </span>
                                                <br>
                                                <strong data-bs-toggle="tooltip" title="@lang('Amount after charge')">
                                                    {{ showAmount($withdraw->amount - $withdraw->charge) }}
                                                </strong>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                @php echo $withdraw->statusBadge @endphp
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <button class="btn btn--sm btn--base detailBtn"
                                                        data-user_data="{{ json_encode($details) }}"
                                                        @if ($withdraw->status == Status::PAYMENT_REJECT) data-admin_feedback="{{ $withdraw->admin_feedback }}" @endif>
                                                    <i class="la la-lg la-desktop"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-muted text-center" colspan="100%">
                                            <div>{{ __($emptyMessage) }}</div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($withdraws->hasPages())
                        <div class="mt-3">
                            {{ paginateLinks($withdraws) }}
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>
    {{-- Detail Modal --}}
    <div id="detailModal" class="modal custom--modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Details')</h5>
                    <span type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </span>
                </div>
                <div class="modal-body">
                    <ul class="list-group userData"></ul>
                    <div class="feedback"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('style')
<style>
  /* Fix: some theme styles make withdraw history table text turn white on hover */
  .campaign-table .table--responsive--xl tbody tr:hover td,
  .campaign-table .table--responsive--xl tbody tr:hover td div,
  .campaign-table .table--responsive--xl tbody tr:hover td small,
  .campaign-table .table--responsive--xl tbody tr:hover td strong,
  .campaign-table .table--responsive--xl tbody tr:hover td span:not(.badge) {
    color: #0f172a !important; /* slate-900 */
  }
  .campaign-table .table--responsive--xl tbody tr:hover td a:not(.btn) {
    color: #4f46e5 !important; /* indigo-600 */
  }
</style>
@endpush

@push('script')
    <script>
        (function($) {
            "use strict";
            $('.detailBtn').on('click', function() {
                const modal = $('#detailModal');
                const userData = $(this).data('user_data');
                let html = ``;

                userData.forEach(element => {
                    if (element.type !== 'file') {
                        html += `
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>${element.name}</span>
                            <span>${element.value}</span>
                        </li>`;
                    } else {
                        html += `
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>${element.name}</span>
                            <span><a href="${element.value}"><i class="fa-regular fa-file"></i> @lang('Attachment')</a></span>
                        </li>`;
                    }
                });

                modal.find('.userData').html(html);

                const feedback = $(this).data('admin_feedback');
                modal.find('.feedback').html(feedback ? `
                <div class="my-3">
                    <strong>@lang('Admin Feedback')</strong>
                    <p>${feedback}</p>
                </div>` : '');

                modal.modal('show');
            });

            // Enable tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title], [data-title], [data-bs-title]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        })(jQuery);
    </script>
@endpush
