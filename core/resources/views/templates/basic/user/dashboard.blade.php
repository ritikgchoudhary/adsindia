@extends($activeTemplate . 'layouts.master')
@section('content')
    @php
        $kyc = getContent('kyc.content', true);
        $user = auth()->user();
    @endphp
    <div class="notice"></div>
    @if ($user->kv == Status::KYC_UNVERIFIED && $user->kyc_rejection_reason)
        <div class="alert alert--danger mb-4" role="alert">
            <div class="alert__icon">
                <i class="fa-solid fa-circle-exclamation"></i>
            </div>

            <div class="alert__content">
                <h4 class="alert__title">@lang('KYC Documents Rejected')</h4>
                <p class="alert__desc">
                    <a class="alert__link" data-bs-toggle="modal" href="#kycRejectionReason">@lang('Show Reason')</a>
                    {{ __($kyc?->data_values?->reject ?? '') }}
                    <a class="alert__link" href="{{ route('user.kyc.form') }}">@lang('Click Here to Re-submit Documents')</a>.
                    <a class="alert__link" href="{{ route('user.kyc.data') }}">@lang('See KYC Data')</a>
                </p>
            </div>
        </div>
    @elseif ($user->kv == Status::KYC_UNVERIFIED)
        <div class="alert alert--info mb-4" role="alert">
            <div class="alert__icon">
                <i class="fas fa-user-check"></i>
            </div>
            <div class="alert__content">
                <h4 class="alert__title">@lang('KYC Verification required')</h4>
                <p class="alert__desc">
                    {{ __($kyc?->data_values?->required ?? '') }}
                    <a class="alert__link" href="{{ route('user.kyc.form') }}">@lang('Click Here to Submit Documents')</a>
                </p>
            </div>
        </div>
    @elseif ($user->kv == Status::KYC_PENDING)
        <div class="alert alert--warning mb-4" role="alert">
            <div class="alert__icon">
                <i class="fa-solid fa-spinner"></i>
            </div>
            <div class="alert__content">
                <h4 class="alert__title">@lang('KYC Verification pending')</h4>
                <p class="alert__desc">
                    {{ __($kyc?->data_values?->pending ?? '') }}
                    <a class="alert__link" href="{{ route('user.kyc.data') }}">@lang('See KYC Data')</a>
                </p>
            </div>
        </div>
    @endif

    <div class="row gy-4 justify-content-center">
        <div class="col-xxl-9 col-lg-12">
            <div class="row gy-4 justify-content-center">
                <div class="col-xl-3 col-md-4 col-sm-6">
                    <div class="dashboard-widget">
                        <div class="dashboard-widget__icon flex-center">
                            <i class="las la-coins"></i>
                        </div>
                        <div class="dashboard-widget__content">
                            <h3 class="dashboard-widget__number">
                                {{ gs('cur_sym') }}{{ getAmount($widget['balance']) }}
                            </h3>
                            <span class="dashboard-widget__text"> @lang('Balance') </span>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-4 col-sm-6">
                    <div class="dashboard-widget">
                        <div class="dashboard-widget__icon flex-center">
                            <i class="fas fa-money-bill-wave-alt"></i>
                        </div>
                        <div class="dashboard-widget__content">
                            <h3 class="dashboard-widget__number"> {{ gs('cur_sym') }}{{ getAmount($widget['total_earning']) }} </h3>
                            <span class="dashboard-widget__text"> @lang('Affiliate Income') </span>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-4 col-sm-6">
                    <div class="dashboard-widget">
                        <div class="dashboard-widget__icon flex-center">
                            <i class="fas fa-video"></i>
                        </div>
                        <div class="dashboard-widget__content">
                            <h3 class="dashboard-widget__number"> {{ gs('cur_sym') }}{{ getAmount($widget['ads_income'] ?? 0) }} </h3>
                            <span class="dashboard-widget__text"> @lang('Ads Income') </span>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-4 col-sm-6">
                    <div class="dashboard-widget">
                        <div class="dashboard-widget__icon flex-center">
                            <i class="fas fa-hand-holding-usd"></i>
                        </div>
                        <div class="dashboard-widget__content">
                            <h3 class="dashboard-widget__number"> {{ gs('cur_sym') }}{{ getAmount($widget['total_withdraw']) }} </h3>
                            <span class="dashboard-widget__text"> @lang('Withdrawan') </span>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card custom--card">
                        <div class="card-header">
                            <h5 class="mb-0">@lang('Latest Transactions')</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table--responsive--lg">
                                <thead>
                                    <tr>
                                        <th>@lang('Trx')</th>
                                        <th>@lang('Transacted')</th>
                                        <th>@lang('Amount')</th>
                                        <th>@lang('Post Balance')</th>
                                        <th>@lang('Detail')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($transactions as $trx)
                                        <tr>
                                            <td>
                                                <div><strong>{{ $trx->trx }}</strong></div>
                                            </td>
                                            <td>
                                                <div>
                                                    {{ showDateTime($trx->created_at) }}<br>
                                                    <small class="text-muted">{{ diffForHumans($trx->created_at) }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <span class="fw-bold @if ($trx->trx_type == '+') text--success @else text--danger @endif">
                                                        {{ $trx->trx_type }} {{ showAmount($trx->amount) }}
                                                    </span>
                                                </div>
                                            </td>
                                            <td>
                                                <div>{{ showAmount($trx->post_balance) }}</div>
                                            </td>
                                            <td>
                                                <div>{{ __($trx->details) }}</div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">{{ __($emptyMessage) }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <div class="col-xxl-3 col-lg-12 ps-xxl-4">
            <div class="dashboard-sidebar">
                <div class="dashboard-sidebar__item">
                    <h5 class="dashboard-sidebar__title"> @lang('Suggest for you') </h5>
                    <div class="dashboard-sidebar__content">
                        @forelse ($campaigns as $campaign)
                            <div class="latest-item">
                                <div class="latest-item__thumb">
                                    <a href="{{ route('campaign.details', $campaign->slug) }}">
                                        <img src="{{ getImage(getFilePath('campaign') . '/' . 'thumb_' . $campaign->image, getFileThumb('campaign')) }}" class="fit-image" alt="img">
                                    </a>
                                </div>
                                <div class="latest-item__content">
                                    <h6 class="latest-item__title">
                                        <a href="{{ route('campaign.details', $campaign->slug) }}">
                                            {{ __($campaign->title) }}
                                        </a>
                                    </h6>
                                    <a href="{{ route('campaign.details', $campaign->slug) }}" class="btn btn-outline--white btn--sm">{{ showAmount($campaign->payout_per_conversion) }}</a>
                                </div>
                            </div>
                        @empty
                            <div class="latest-item">
                                <i class="las la-list-ul"></i>
                                <br>
                                @lang('No campaign found')
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($user->kv == Status::KYC_UNVERIFIED && $user->kyc_rejection_reason)
        <div class="modal fade custom--modal" id="kycRejectionReason">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">@lang('KYC Document Rejection Reason')</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>{{ $user->kyc_rejection_reason }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
