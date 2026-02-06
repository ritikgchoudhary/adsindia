@extends($activeTemplate . 'layouts.master')
@section('content')
    <div class="notice"></div>

    @php
        $kyc = getContent('advertiser_kyc.content', true);
        $advertiser = auth()->guard('advertiser')->user();
    @endphp

    @if ($advertiser->kv == Status::KYC_UNVERIFIED && $advertiser->kyc_rejection_reason)
        <div class="alert alert--danger mb-4" role="alert">
            <div class="alert__icon">
                <i class="fa-solid fa-circle-exclamation"></i>
            </div>

            <div class="alert__content">
                <h4 class="alert__title">@lang('KYC Documents Rejected')</h4>
                <p class="alert__desc">
                    <a class="alert__link" data-bs-toggle="modal" href="#kycRejectionReason">@lang('Show Reason')</a>
                    {{ __($kyc?->data_values?->reject ?? '') }}
                    <a class="alert__link" href="{{ route('advertiser.kyc.form') }}">@lang('Click Here to Re-submit Documents')</a>.
                    <a class="alert__link" href="{{ route('advertiser.kyc.data') }}">@lang('See KYC Data')</a>
                </p>
            </div>
        </div>
    @elseif ($advertiser->kv == Status::KYC_UNVERIFIED)
        <div class="alert alert--info mb-4" role="alert">
            <div class="alert__icon">
                <i class="fas fa-user-check"></i>
            </div>
            <div class="alert__content">
                <h4 class="alert__title">@lang('KYC Verification required')</h4>
                <p class="alert__desc">
                    {{ __($kyc?->data_values?->required ?? '') }}
                    <a class="alert__link" href="{{ route('advertiser.kyc.form') }}">@lang('Click Here to Submit Documents')</a>
                </p>
            </div>
        </div>
    @elseif ($advertiser->kv == Status::KYC_PENDING)
        <div class="alert alert--warning mb-4" role="alert">
            <div class="alert__icon">
                <i class="fa-solid fa-spinner"></i>
            </div>
            <div class="alert__content">
                <h4 class="alert__title">@lang('KYC Verification pending')</h4>
                <p class="alert__desc">
                    {{ __($kyc?->data_values?->pending ?? '') }}
                    <a class="alert__link" href="{{ route('advertiser.kyc.data') }}">@lang('See KYC Data')</a>
                </p>
            </div>
        </div>
    @endif

    <div class="row gy-4 justify-content-center">
        <div class="col-xxl-9 col-lg-12">
            <div class="row gy-4 justify-content-center">
                <div class="col-xl-4 col-md-4 col-sm-6">
                    <div class="dashboard-widget">
                        <div class="dashboard-widget__icon flex-center">
                            <i class="las la-coins"></i>
                        </div>
                        <div class="dashboard-widget__content">
                            <h3 class="dashboard-widget__number">
                                {{ gs('cur_sym') }}{{ getAmount(auth()->guard('advertiser')->user()->balance) }}
                            </h3>
                            <span class="dashboard-widget__text"> @lang('Balance') </span>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 col-md-4 col-sm-6">
                    <div class="dashboard-widget">
                        <div class="dashboard-widget__icon flex-center">
                            <i class="fa-solid fa-spinner"></i>
                        </div>
                        <div class="dashboard-widget__content">
                            <h3 class="dashboard-widget__number"> {{ $runningCampaigns }} </h3>
                            <span class="dashboard-widget__text"> @lang('Running Campaigns') </span>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 col-md-4 col-sm-6">
                    <div class="dashboard-widget">
                        <div class="dashboard-widget__icon flex-center">
                            <i class="fa-regular fa-circle-check"></i>
                        </div>
                        <div class="dashboard-widget__content">
                            <h3 class="dashboard-widget__number"> {{ $completedCampaigns }} </h3>
                            <span class="dashboard-widget__text"> @lang('Completed Campaigns') </span>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Dashboard Card End -->
            <div class="row gy-4 justify-content-center mt-4">
                <div class="col-xxl-8 col-lg-12">
                    <div class="card custom--card">
                        <div class="card-header flex-between gap-3">
                            <h6 class="card-title"> @lang('Last 30 Days Campaign Payout') </h6>
                            <select name="campaign_ids" id="campaign_ids" class="form-select form--control select2">
                                @foreach ($campaigns as $campaign)
                                    <option value="{{ $campaign->id }}">{{ $campaign->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="card-body">
                            <div id="campaignPayoutChart"></div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-4 col-lg-12">
                    <div class="dashboard-statistic-wrapper">
                        <div class="statistic-item">
                            <h6 class="statistic-item__title"> @lang('Statistics') </h6>
                        </div>

                        <!-- Total Conversions -->
                        <div class="statistic-item">
                            <div class="statistic-item__left">
                                <span class="statistic-item__text"> @lang('Total Conversions') </span>
                                <h6 class="statistic-item__title"> {{ getAmount($stats['totalConversions']) }} </h6>
                            </div>
                            <div class="statistic-item__right">
                                <span class="text--success fs-14 text">
                                    <span class="icon"> <i class="las la-2x la-list-alt"></i> </span>
                                </span>
                            </div>
                        </div>

                        <!-- Total Spend -->
                        <div class="statistic-item">
                            <div class="statistic-item__left">
                                <span class="statistic-item__text"> @lang('Total Spend') </span>
                                <h6 class="statistic-item__title"> {{ showAmount($stats['totalSpend']) }} </h6>
                            </div>
                            <div class="statistic-item__right">
                                <span class="text--danger fs-14 text">
                                    <span class="icon"> <i class="las la-2x la-money-bill-alt"></i> </span>
                                </span>
                            </div>
                        </div>

                        <!-- Total Deposit -->
                        <div class="statistic-item">
                            <div class="statistic-item__left">
                                <span class="statistic-item__text"> @lang('Total Deposit') </span>
                                <h6 class="statistic-item__title"> {{ showAmount($stats['totalDeposit']) }} </h6>
                            </div>
                            <div class="statistic-item__right">
                                <span class="text--success fs-14 text">
                                    <span class="icon"> <i class="las la-2x la-wallet"></i> </span>
                                </span>
                            </div>
                        </div>

                        <!-- Total Withdrawal -->
                        <div class="statistic-item">
                            <div class="statistic-item__left">
                                <span class="statistic-item__text"> @lang('Total Withdrawal') </span>
                                <h6 class="statistic-item__title"> {{ showAmount($stats['totalWithdrawal']) }} </h6>
                            </div>
                            <div class="statistic-item__right">
                                <span class="text--danger fs-14 text">
                                    <span class="icon"> <i class="las la-2x la-hand-holding-usd"></i></span>
                                </span>
                            </div>
                        </div>
                        <div class="statistic-item">
                            <div class="statistic-item__left">
                                <span class="statistic-item__text"> @lang('Total Transaction') </span>
                                <h6 class="statistic-item__title"> {{ $stats['totalTransaction'] }} </h6>
                            </div>
                            <div class="statistic-item__right">
                                <span class="text--warning fs-14 text">
                                    <span class="icon"><i class="las la-2x la-exchange-alt"></i></span>
                                </span>
                            </div>
                        </div>
                        <div class="statistic-item">
                            <div class="statistic-item__left">
                                <span class="statistic-item__text"> @lang('Answer Tickets') </span>
                                <h6 class="statistic-item__title"> {{ $stats['answerTicket'] }} </h6>
                            </div>
                            <div class="statistic-item__right">
                                <span class="text--info fs-14 text">
                                    <span class="icon"><i class="las la-2x la-ticket-alt"></i></span>
                                </span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <div class="col-xxl-3 col-lg-12 ps-xxl-4">
            <div class="dashboard-statistic-wrapper">
                <div class="statistic-item">
                    <h6 class="statistic-item__title"> @lang('Latest Transactions') </h6>
                </div>
                @forelse ($transactions->take(8) as $transaction)
                    <div class="statistic-item">
                        <div class="statistic-item__left">
                            <span class="statistic-item__text">{{ __($transaction->details) }}</span>
                            <h6 class="statistic-item__title"> {{ showAmount($transaction->amount) }} </h6>
                        </div>
                        <div class="statistic-item__right">
                            @if ($transaction->trx_type == '+')
                                <span class="text--success fs-14 text">
                                    <span class="icon"> <i class="fa-solid fa-lg fa-arrow-down"></i> </span>
                                </span>
                            @else
                                <span class="text--danger fs-14 text">
                                    <span class="icon"> <i class="fa-solid fa-lg fa-arrow-up"></i> </span>
                                </span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="statistic-item">
                        <div class="text-center">
                            <span class="text--danger">
                                <i class="las la-lg la-list-ul"></i>
                                @lang('Transaction not found!')
                            </span>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
        <div class="col-sm-12">
            <div class="card custom--card">
                <div class="card-header">
                    <h5 class="title mb-0"> @lang('Approved Campaigns') </h5>
                </div>
                <div class="card-body">
                    <table class="table table--responsive--lg">
                        <thead>
                            <tr>
                                <th>@lang('Title')</th>
                                <th>@lang('Category')</th>
                                <th>@lang('Payout | Limit')</th>
                                <th>@lang('Created At')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($approvedCampaigns as $campaign)
                                <tr>
                                    <td>{{ __($campaign->title) }}</td>
                                    <td>{{ __($campaign->category->name ?? '-') }}</td>
                                    <td>
                                        <div>
                                            <span data-bs-toggle="tooltip" data-bs-placement="top" title="@lang('Affiliate Commission')">{{ showAmount($campaign->payout_per_conversion) }}</span> + <span data-bs-toggle="tooltip" data-bs-placement="top" title="@lang('System Commission')">{{ showAmount($campaign->admin_commission) }}</span>
                                            <br>
                                            <span data-bs-toggle="tooltip" data-bs-placement="top" title="@lang('Total Budget')">{{ showAmount($campaign->budget) }} (<span data-bs-toggle="tooltip" data-bs-placement="top" title="@lang('Commission Limit')">{{ $campaign->conversion_limit }}</span>)</span>
                                        </div>
                                    </td>
                                    <td>{{ showDateTime($campaign->created_at, 'd M, Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="100%" class="text-center text-muted">{{ __($emptyMessage) }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @if ($advertiser->kv == Status::KYC_UNVERIFIED && $advertiser->kyc_rejection_reason)
        <div class="modal fade custom--modal" id="kycRejectionReason">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">@lang('KYC Document Rejection Reason')</h5>
                        <span type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <i class="las la-times"></i>
                        </span>
                    </div>
                    <div class="modal-body">
                        <p>{{ $advertiser->kyc_rejection_reason }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('script-lib')
    <script src="{{ asset($activeTemplateTrue . 'js/apexcharts.js') }}"></script>
@endpush

@push('script')
    <script>
        (function($) {
            "use strict";

            let curSym = "{{ gs('cur_sym') }}"

            $('#campaign_ids').on('change', function(e) {
                e.preventDefault();
                const campaignId = $(this).val();
                $.ajax({
                    type: "GET",
                    url: "{{ route('advertiser.chart.payout') }}",
                    data: {
                        campaign_id: campaignId
                    },
                    success: function(response) {
                        renderChart(response.chartData);
                    }
                });
            }).change();


            function renderChart(seriesData) {
                const options = {
                    chart: {
                        type: 'bar',
                        height: 400,
                        toolbar: {
                            show: false
                        },
                        fontFamily: 'Inter, sans-serif',
                        animations: {
                            enabled: true,
                            easing: 'easeinout',
                            speed: 800
                        }
                    },
                    colors: ['#{{ gs('base_color') }}'], // Tailwind blue or customize
                    series: seriesData,
                    xaxis: {
                        type: 'category',
                        labels: {
                            rotate: -45,
                            style: {
                                fontSize: '12px',
                                colors: '#6B7280'
                            }
                        },
                    },
                    yaxis: {
                        labels: {
                            style: {
                                fontSize: '12px',
                                colors: '#6B7280'
                            }
                        },
                        title: {
                            text: 'Total Payout',
                            style: {
                                fontSize: '14px',
                                fontWeight: 600,
                                color: '#6B7280'
                            }
                        }
                    },
                    grid: {
                        show: false,
                    },
                    tooltip: {
                        theme: 'light',
                        style: {
                            fontSize: '13px'
                        },
                        y: {
                            formatter: val => curSym + parseFloat(val).toFixed(2)
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    plotOptions: {
                        bar: {
                            borderRadius: 6,
                            columnWidth: '40%'
                        }
                    },
                    legend: {
                        position: 'top',
                        fontSize: '14px',
                        labels: {
                            colors: '#374151'
                        }
                    }
                };

                const chartDiv = document.querySelector("#campaignPayoutChart");
                chartDiv.innerHTML = ""; // clear old chart
                const chart = new ApexCharts(chartDiv, options);
                chart.render();
            }

        })(jQuery)
    </script>
@endpush
@push('style')
    <style>
        .card-header .form--control {
            max-width: 300px;
        }

        .card-header .select2+.select2-container .select2-selection {
            padding-right: 40px !important;
            height: var(--height) !important;
            min-width: 160px;
        }

        .select2+.select2-container .select2-selection__rendered {
            line-height: unset;
        }

        .select2-container--default .select2-selection--single {
            border-width: 1px !important;
            border-radius: 12px !important;
            border-color: var(--select2-border) !important;
        }

        .select2-container--open .select2-selection.select2-selection--single,
        .select2-container--open .select2-selection.select2-selection--multiple {
            border-radius: 12px !important;
        }

        .select2-container--default .select2-selection--single {
            padding: 16px 24px !important;
        }

        .select2+.select2-container .select2-selection__rendered {
            padding-right: 0px !important;
            padding-left: 0px !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            top: 28px !important;
        }

        .select2-results__option.select2-results__option--selected,
        .select2-results__option--selectable,
        .select2-container--default .select2-results__option--disabled {
            border-bottom-color: hsl(var(--border-color)) !important;
        }

        .select2-results__option.select2-results__option--selected,
        .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable {
            color: hsl(var(--white)) !important;
            background-color: hsl(var(--base)) !important;
        }

        .select2-results__option.select2-results__option--selected,
        .select2-results__option--selectable,
        .select2-container--default .select2-results__option--disabled {
            border-bottom-color: hsl(var(--white)/0.2) !important;
        }

        .select2-container--default.select2-container--focus .select2-selection--single {
            outline: none !important;
            box-shadow: none !important;
            border-color: hsl(var(--base)) !important;
        }
    </style>
@endpush
