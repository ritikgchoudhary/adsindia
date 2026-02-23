@extends($activeTemplate . 'layouts.master')

@section('content')
    <div class="d-flex justify-content-end mb-4">
        <a href="{{ route('advertiser.campaign.create') }}" class="btn btn--base">
            <i class="las la-plus"></i> @lang('New Campaign')
        </a>
    </div>
    <div class="campaign-table">
        <div class="card custom--card">
            <div class="card-body">
                <table class="table table--responsive--xxl">
                    <thead>
                        <tr>
                            <th>@lang('Title | Token')</th>
                            <th>@lang('Category')</th>
                            <th>@lang('Payout | Limit')</th>
                            <th>@lang('Status')</th>
                            <th>@lang('Payment Status')</th>
                            <th>@lang('Created At')</th>
                            <th>@lang('Action')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($campaigns as $campaign)
                            <tr>
                                <td>
                                    <div>
                                        {{ __($campaign->title) }}
                                        <br>
                                        <span>{{ substr($campaign->tracking_token, 0, 5) . str_repeat('*', 9) . substr($campaign->tracking_token, -5) }}
                                            <span data-token="{{ $campaign->tracking_token }}" class="copy-token"><i class="las la-copy"></i></span>
                                        </span>
                                    </div>
                                </td>
                                <td>{{ __($campaign->category->name ?? '-') }}</td>
                                <td>
                                    <div>
                                        <span data-bs-toggle="tooltip" data-bs-placement="top" title="@lang('Affiliate Commission')">{{ showAmount($campaign->payout_per_conversion) }}</span> + <span data-bs-toggle="tooltip" data-bs-placement="top" title="@lang('System Commission')">{{ showAmount($campaign->admin_commission) }}</span>
                                        <br>
                                        <span data-bs-toggle="tooltip" data-bs-placement="top" title="@lang('Total Budget')">{{ showAmount($campaign->budget) }} (<span data-bs-toggle="tooltip" data-bs-placement="top" title="@lang('Commission Limit')">{{ $campaign->conversion_limit }}</span>)</span>
                                    </div>
                                </td>
                                <td>
                                    @php echo $campaign->statusBadge; @endphp
                                    @if ($campaign->status == Status::CAMPAIGN_REJECTED)
                                        <button class="btn btn--xsm btn--danger reasonBtn" type="button" data-reason="{{ $campaign->admin_feedback }}"><i class="las la-info-circle"></i></button>
                                    @endif
                                </td>
                                <td>
                                    @php echo $campaign->paymentStatusBadge; @endphp
                                </td>
                                <td>{{ showDateTime($campaign->created_at, 'd M, Y') }}</td>
                                <td class="text-end">
                                    <div class="dropdown">
                                        <button class="btn btn--sm btn-outline--base" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="las la-ellipsis-v"></i> @lang('Action')
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('advertiser.campaign.edit', $campaign->id) }}">
                                                    <i class="las la-pen"></i> @lang('Edit')
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="{{ route('advertiser.campaign.conversions', $campaign->id) }}">
                                                    <i class="las la-list"></i> @lang('Conversions')
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="100%" class="text-center text-muted">{{ __($emptyMessage) }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($campaigns->hasPages())
                <div class="mt-3">
                    {{ paginateLinks($campaigns) }}
                </div>
            @endif
        </div>
    </div>

    <div class="modal fade custom--modal" id="reasonModal" role="dialog" tabindex="-1">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Reject Reason')</h5>
                    <button class="close" data-bs-dismiss="modal" type="button" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="reason"></p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('style')
    <style>
        .dropdown-menu {
            transition: unset !important;
            border-radius: 6px !important;
        }

        .dropdown-item {
            color: hsl(var(--white))
        }

        .dropdown-item:active,
        .dropdown-item:focus {
            background-color: hsl(var(--base)) !important;
            color: hsl(var(--white)) !important;
        }

        .copy-token {
            cursor: pointer;
        }
    </style>
@endpush



@push('script')
    <script>
        (function($) {
            "use strict";
            $('.reasonBtn').on('click', function(e) {
                e.preventDefault();

                let modal = $('#reasonModal');
                let reason = $(this).data('reason');
                modal.find('.reason').text(reason);
                modal.modal('show');
            });

            $('.copy-token').on('click', function() {
                var token = $(this).data('token');
                var temp = $("<input>");
                $("body").append(temp);
                temp.val(token).select();
                try {
                    document.execCommand("copy");
                    notify('success', 'Token copied to clipboard!')
                } catch (err) {
                    notify('error', 'Failed to copy token.')
                }
                temp.remove();
            });
        })(jQuery)
    </script>
@endpush
