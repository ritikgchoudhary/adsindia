@extends('admin.layouts.app')

@section('panel')
    <div class="row mb-none-30 justify-content-center">
        <div class="col-xl-4 col-md-6 mb-30">
            <div class="card b-radius--10 box--shadow1 overflow-hidden">
                <div class="card-header">
                    <h6>@lang('Advertiser Information')</h6>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                            <span class="fw-bold">@lang('Fullname')</span>
                            <span>{{ __($campaign?->advertiser?->fullname) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                            <span class="fw-bold">@lang('Username')</span>
                            <span>
                                <a href="{{ route('admin.advertisers.detail', $campaign->advertiser_id) }}"><span>@</span>{{ __($campaign?->advertiser?->username) }}</a>
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                            <span class="fw-bold">@lang('Email')</span>
                            <span>{{ $campaign?->advertiser?->email }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                            <span class="fw-bold">@lang('Mobile')</span>
                            <span>{{ $campaign?->advertiser?->mobile }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                            <span class="fw-bold">@lang('Balance')</span>
                            <span>{{ showAmount($campaign?->advertiser?->balance) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                            <span class="fw-bold">@lang('Country')</span>
                            <span>{{ __($campaign->advertiser?->country_name) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                            <span class="fw-bold">@lang('Total Approved Campaign')</span>
                            <span>{{ getAmount($campaign?->advertiser?->campaigns_count) }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            @if ($campaign->status == Status::CAMPAIGN_PENDING)
                <div class="card b-radius--10 box--shadow1 overflow-hidden mt-4">
                    <div class="card-header">
                        <h6>@lang('Confirm Alert')</h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-group">
                            <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                @if ($campaign?->advertiser?->balance >= $campaign->budget)
                                    <span class="fw-bold">
                                        @lang('The advertiser has sufficient balance (:balance) to cover the campaign budget (:budget).', [
                                            'balance' => showAmount($campaign?->advertiser?->balance),
                                            'budget' => showAmount($campaign?->budget),
                                        ])
                                        <br>
                                        <span class="text--success mt-3">@lang('You can safely approve this campaign.')</span>
                                    </span>
                                @else
                                    <span class="fw-bold">
                                        @lang('The advertiser\'s current balance (:balance) is less than the campaign budget (:budget).', [
                                            'balance' => showAmount($campaign?->advertiser?->balance),
                                            'budget' => showAmount($campaign?->budget),
                                        ])
                                        <br>
                                        <span class="text--danger mt-3">@lang('Please ask the advertiser to deposit sufficient funds before approval.')</span>
                                    </span>
                                @endif
                            </li>
                        </ul>
                    </div>
                </div>
            @endif
        </div>
        <div class="col-xl-8 col-md-6 mb-30">
            <div class="card b-radius--10 box--shadow1 overflow-hidden">
                <div class="card-header">
                    <h6>@lang('Campaign Information')</h6>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                            <span class="fw-bold">@lang('Title')</span>
                            <span>{{ __(ucfirst($campaign->title)) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                            <span class="fw-bold">@lang('Category')</span>
                            <span>{{ __(ucfirst($campaign->category->name)) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                            <span class="fw-bold">@lang('Traffic Type')</span>
                            <span>
                                @foreach ($campaign->trafficTypeName ?? [] as $traffic)
                                    <span class="badge badge--dark">{{ __($traffic) }}</span>
                                @endforeach
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                            <span class="fw-bold">@lang('Type')</span>
                            <span>@php echo $campaign->trackingTypeBadge; @endphp</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                            <span class="fw-bold">@lang('Tracking Token')</span>
                            <span>{{ $campaign->tracking_token }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                            <span class="fw-bold">@lang('Start Date')</span>
                            <span>{{ showDateTime($campaign->starts_at) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                            <span class="fw-bold">@lang('End Date')</span>
                            <span>{{ showDateTime($campaign->ends_at) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                            <span class="fw-bold">@lang('Landing URL')</span>
                            <a href="{{ $campaign->url }}"><i class="las la-external-link-alt"></i> @lang('Click Here')</a>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                            <span class="fw-bold">@lang('Conversion Limit')</span>
                            <span>{{ getAmount($campaign->conversion_limit) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                            <span class="fw-bold">@lang('Payout Per Conversion')</span>
                            <span>{{ showAmount($campaign->payout_per_conversion) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                            <span class="fw-bold">@lang('System Commission')</span>
                            <span>{{ showAmount($campaign->admin_commission) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                            <span class="fw-bold">@lang('Budget')</span>
                            <span>{{ showAmount($campaign->budget) }}</span>
                        </li>
                        @if ($campaign->is_paused)
                            <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                <span class="fw-bold">@lang('Pause Status')</span>
                                <span>@lang('Yes')</span>
                            </li>
                        @endif
                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                            <span class="fw-bold">@lang('Payment Status')</span>
                            <span>@php echo $campaign->paymentStatusBadge @endphp</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                            <span class="fw-bold">@lang('Status')</span>
                            <span>@php echo $campaign->statusBadge @endphp</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-12 mb-30">
            <div class="card b-radius--10 box--shadow1 overflow-hidden">
                <div class="card-header">
                    @lang('More Information')
                </div>
                <div class="card-body">
                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="contact-tab" data-bs-toggle="tab"
                                    data-bs-target="#contact-tab-pane" type="button" role="tab"
                                    aria-controls="contact-tab-pane" aria-selected="false">@lang('Description')</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="image-tab" data-bs-toggle="tab" data-bs-target="#image-tab-pane"
                                    type="button" role="tab" aria-controls="image-tab-pane"
                                    aria-selected="false">@lang('Images')</button>
                        </li>
                    </ul>
                    <div class="tab-content mt-3" id="myTabContent">
                        <div class="tab-pane fade show active" id="contact-tab-pane" role="tabpanel" aria-labelledby="contact-tab"
                             tabindex="0">
                            <div>
                                <span class="fw-bold mb-3">@lang('Description')</span>
                                <p>@php echo $campaign->description @endphp</p>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="image-tab-pane" role="tabpanel" aria-labelledby="image-tab"
                             tabindex="0">
                            <ul class="list-group">
                                <li class="list-group-item d-flex justify-content-between flex-wrap align-items-center">
                                    <span class="fw-bold">@lang('Campaign Image')</span>
                                    <div class="user">
                                        <div class="thumb">
                                            <a href="{{ getImage(getFilePath('campaign') . '/' . $campaign->image, getFileSize('campaign')) }}"
                                               class="image-popup">
                                                <img class="plugin_bg" src="{{ getImage(getFilePath('campaign') . '/' . $campaign->image, getFileSize('campaign')) }}" alt="@lang('image')">
                                            </a>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="rejectModal" role="dialog" tabindex="-1">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Reject Campaign Confirmation')</h5>
                    <button class="close" data-bs-dismiss="modal" type="button" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="mt-2">@lang('Reason for Rejection')</label>
                            <textarea class="form-control" name="reason" maxlength="255" rows="5" required>{{ old('reason') }}</textarea>
                        </div>
                        <button class="btn btn--primary w-100 h-45" type="submit">@lang('Submit')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <x-confirmation-modal />
@endsection

@push('style')
    <style>
        .agent-box {
            background-color: var(--bs-light);
            border: 1px solid #e3e6f0;
            word-break: break-word;
            color: #333;
            font-size: 14px;
        }
    </style>
@endpush


@push('breadcrumb-plugins')
    @if ($campaign->status == Status::CAMPAIGN_PENDING)
        <button class="btn btn-sm btn-outline--success confirmationBtn"
                data-action="{{ route('admin.campaign.approve', $campaign->id) }}" data-question="@lang('Are you sure you want to approve this campaign?')">
            <i class="las la-check-circle"></i> @lang('Approve Campaign')
        </button>
        <button class="btn btn-sm btn-outline--danger rejectBtn">
            <i class="las la-times-circle"></i> @lang('Reject Campaign')
        </button>
    @endif
    @if ($campaign->status == Status::CAMPAIGN_APPROVED)
        @if ($campaign->is_paused)
            <button class="btn btn-sm btn-outline--success confirmationBtn" data-action="{{ route('admin.campaign.toggle.pause', $campaign->id) }}" data-question="@lang('Are you sure you want to resume this campaign?')">
                <i class="las la-play-circle"></i>
                @lang('Resume Campaign')
            </button>
        @else
            <button class="btn btn-sm btn-outline--warning confirmationBtn"
                    data-action="{{ route('admin.campaign.toggle.pause', $campaign->id) }}"
                    data-question="@lang('Are you sure you want to pause this campaign?')">
                <i class="las la-pause-circle"></i>
                @lang('Pause Campaign')
            </button>
        @endif
    @endif
    <x-back route="{{ route('admin.campaign.index') }}" />
@endpush

@push('style')
    <style>
        .nav-tabs .nav-item.show .nav-link,
        .nav-tabs .nav-link.active {
            background: transparent;
            color: #4634ff !important;
            border-radius: 5px;
            border: 1px solid #4634ff;
            padding: 5px 15px;
            font-weight: 500;
        }

        .nav-tabs .nav-link {
            margin-bottom: 0;
            background: unset;
            border: unset;
            border-top-left-radius: unset;
            border-top-right-radius: unset;
        }

        .nav-tabs {
            border: unset;
        }

        .nav-link {
            color: #10163A;
        }

        .nav-link:focus,
        .nav-link:hover {
            color: #4634ff !important;
        }

        .plugin_bg {
            border: 1px solid #d5d5d5;
            border-radius: 50%;
            height: 80px;
            width: 80px;
            box-shadow: 0px 0px 3px 0px #757575;
        }
    </style>
@endpush

@push('style-lib')
    <link href="{{ asset($activeTemplateTrue . 'css/magnific-popup.css') }}" rel="stylesheet">
@endpush

@push('script-lib')
    <script src="{{ asset($activeTemplateTrue . 'js/magnific-popup.min.js') }}"></script>
@endpush

@push('script')
    <script>
        (function($) {
            "use strict";

            $('.rejectBtn').on('click', function() {
                var modal = $('#rejectModal');
                modal.find('form').attr('action', `{{ route('admin.campaign.reject', $campaign->id) }}`);
                modal.modal('show');
            });

            $(document).on('click', '.viewConversionBtn', function() {
                const data = $(this).data('resource');

                // Link to user detail
                const userUrl = `{{ route('admin.users.detail', ':id') }}`.replace(':id', data.user.id);
                const userHtml = `<a href="${userUrl}" target="_blank">${data.user.username}</a>`;

                $('#conversionModal').find('.conversion-user').html(userHtml);
                $('#conversionModal').find('.conversion-payout').text(parseFloat(data.user_payout).toFixed(2));
                $('#conversionModal').find('.conversion-commission').text(parseFloat(data.admin_commission).toFixed(2));
                $('#conversionModal').find('.conversion-ip').text(data.ip_address);
                $('#conversionModal').find('.conversion-tracking').text(
                    data.tracking_type == 2 ? 'JS' :
                    data.tracking_type == 3 ? 'Server' : 'HTML'
                );
                $('#conversionModal').find('.conversion-agent').text(data.user_agent);

                $('#conversionModal').find('.conversion-details').text(data.details ? data.details : '-');

                $('#conversionModal').modal('show');
            });

            $(".image-popup").magnificPopup({
                type: "image",
            });


        })(jQuery);
    </script>
@endpush
