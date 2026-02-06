@extends($activeTemplate . 'layouts.master')
@section('content')
    <div class="d-flex justify-content-end">
        <form>
            <div class="input-group">
                <input type="search" name="search" class="form-control form--control" value="{{ request()->search }}" placeholder="@lang('Search by campaign')">
                <button class="input-group-text bg--base text-white border-0">
                    <i class="las la-search"></i>
                </button>
            </div>
        </form>
    </div>
    <div class="campaign-table">
        <div class="card custom--card">
            <div class="card-body">
                <table class="table table--responsive--lg">
                    <thead>
                        <tr>
                            <th>@lang('Campaign')</th>
                            <th>@lang('System Commission')</th>
                            <th>@lang('Affiliate Commission')</th>
                            <th>@lang('Paid')</th>
                            <th>@lang('Created At')</th>
                            <th>@lang('Action')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($conversions as $conversion)
                            <tr>
                                <td>
                                    {{ $conversion?->campaign?->title ?? '-' }}
                                </td>
                                <td>
                                    {{ showAmount($conversion->admin_commission) }}
                                </td>
                                <td>
                                    {{ showAmount($conversion->user_payout) }}
                                </td>
                                <td>
                                    @if ($conversion->is_paid == Status::PAID)
                                        <span class="badge badge--success">@lang('Paid')</span>
                                    @else
                                        <span class="badge badge--warning">@lang('Unpaid')</span>
                                    @endif
                                </td>
                                <td>
                                    {{ showDateTime($conversion->created_at) }}
                                    <br>
                                    <small>{{ diffForHumans($conversion->created_at) }}</small>
                                </td>
                                <td>
                                    <button type="button"
                                            class="btn btn--sm btn-outline--base viewConversionBtn"
                                            data-resource="{{ json_encode($conversion) }}"
                                            data-user="{{ json_encode($conversion->user) }}">
                                        <i class="las la-eye"></i> @lang('View')
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="100%" class="text-center text-muted">
                                    @lang('No conversions found')
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($conversions->hasPages())
            <nav>
                {{ paginateLinks($conversions) }}
            </nav>
        @endif
    </div>
    <!-- Conversion Details Modal -->
    <div class="modal fade custom--modal" id="conversionModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Conversion Details')</h5>
                    <button type="button" class="close cursor-pointer" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row gy-3">

                        <div class="col-md-6">
                            <div class="d-flex flex-column">
                                <span class="fw-bold">@lang('User')</span>
                                <span class="conversion-user mt-1"></span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex flex-column">
                                <span class="fw-bold">@lang('Payout')</span>
                                <span class="conversion-payout text-muted mt-1"></span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex flex-column">
                                <span class="fw-bold">@lang('Admin Commission')</span>
                                <span class="conversion-commission text-muted mt-1"></span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex flex-column">
                                <span class="fw-bold">@lang('IP Address')</span>
                                <span class="conversion-ip text-muted mt-1"></span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex flex-column">
                                <span class="fw-bold">@lang('Tracking Type')</span>
                                <span class="conversion-tracking text-muted mt-1"></span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex flex-column">
                                <span class="fw-bold">@lang('Paid Status')</span>
                                <span class="conversion-paid text-muted mt-1"></span>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="d-flex flex-column">
                                <span class="fw-bold">@lang('User Agent')</span>
                                <div class="conversion-agent mt-2 p-3 rounded agent-box"></div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="d-flex flex-column">
                                <span class="fw-bold">@lang('Details')</span>
                                <div class="conversion-details mt-2 p-3 rounded agent-box"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('style')
    <style>
        .agent-box {
            background-color: var(--bs-dark);
            border: 1px solid #e3e6f0;
            word-break: break-word;
            color: #e3e6f0;
            font-size: 14px;
        }
    </style>
@endpush

@push('script')
    <script>
        (function($) {
            "use strict";

            $(document).on('click', '.viewConversionBtn', function() {
                const data = $(this).data('resource');
                const user = $(this).data('user');

                $('#conversionModal').find('.conversion-user').html(`${user.username}`);
                $('#conversionModal').find('.conversion-payout').text(`${parseFloat(data.user_payout).toFixed(2)} {{ gs('cur_text') }}`);
                $('#conversionModal').find('.conversion-commission').text(`${parseFloat(data.admin_commission).toFixed(2)} {{ gs('cur_text') }}`);
                $('#conversionModal').find('.conversion-ip').text(data.ip_address);
                $('#conversionModal').find('.conversion-tracking').text(
                    data.tracking_type == 2 ? 'JS' :
                    data.tracking_type == 3 ? 'Server' : 'HTML'
                );
                $('#conversionModal').find('.conversion-paid').text(
                    data.is_paid == {{ Status::PAID }} ? '@lang('Paid')' : '@lang('Unpaid')'
                );
                $('#conversionModal').find('.conversion-agent').text(data.user_agent);

                // Show details text (new)
                $('#conversionModal').find('.conversion-details').text(data.details ?? '-');

                $('#conversionModal').modal('show');
            });

        })(jQuery);
    </script>
@endpush
