@extends($activeTemplate . 'layouts.master')

@section('content')
    <div class="row justify-content-center">
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
                        <table class="table table--responsive--lg">
                            <thead>
                                <tr>
                                    <th>@lang('Gateway | Transaction')</th>
                                    <th>@lang('Initiated')</th>
                                    <th>@lang('Amount')</th>
                                    <th>@lang('Conversion')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($withdraws as $withdraw)
                                    @php
                                        $details = [];
                                        foreach ($withdraw->withdraw_information as $key => $info) {
                                            $details[] = $info;
                                            if ($info->type === 'file') {
                                                $details[$key]->value = route('advertiser.download.attachment', encrypt(getFilePath('verify') . '/' . $info->value));
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
                                                <small class="text-muted">{{ diffForHumans($withdraw->created_at) }}</small>
                                            </div>
                                        </td>

                                        <td>
                                            <div>
                                                {{ showAmount($withdraw->amount) }} -
                                                <span class="text--danger" data-bs-toggle="tooltip" title="@lang('Processing Charge')">
                                                    {{ showAmount($withdraw->charge) }}
                                                </span><br>
                                                <strong data-bs-toggle="tooltip" title="@lang('Amount after charge')">
                                                    {{ showAmount($withdraw->amount - $withdraw->charge) }}
                                                </strong>
                                            </div>
                                        </td>

                                        <td>
                                            <div>
                                                {{ showAmount(1) }} = {{ showAmount($withdraw->rate, currencyFormat: false) }} {{ __($withdraw->currency) }}<br>
                                                <strong>{{ showAmount($withdraw->final_amount, currencyFormat: false) }} {{ __($withdraw->currency) }}</strong>
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
                                                        data-advertiser_data="{{ json_encode($details) }}"
                                                        @if ($withdraw->status == Status::PAYMENT_REJECT) data-admin_feedback="{{ $withdraw->admin_feedback }}" @endif>
                                                    <i class="las la-lg la-desktop"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>

                                @empty
                                    <tr>
                                        <td class="text-center text-muted" colspan="100%">
                                            {{ __($emptyMessage) }}
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
                    <ul class="list-group advertiserData"></ul>
                    <div class="feedback"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        (function($) {
            "use strict";

            $('.detailBtn').on('click', function() {
                const modal = $('#detailModal');
                const advertiserData = $(this).data('advertiser_data');
                let html = ``;

                advertiserData.forEach(element => {
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

                modal.find('.advertiserData').html(html);

                const feedback = $(this).data('admin_feedback');
                modal.find('.feedback').html(feedback ? `
                <div class="my-3">
                    <strong>@lang('Admin Feedback')</strong>
                    <p>${feedback}</p>
                </div>` : '');

                modal.modal('show');
            });

            // Bootstrap tooltip init
            const tooltipList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipList.map(t => new bootstrap.Tooltip(t));
        })(jQuery);
    </script>
@endpush
