@extends($activeTemplate . 'layouts.master')
@push('style')
<style>
    .dashboard .dashboard-widget .dashboard-widget__content { width: auto; padding-left: 0; }
    .dashboard .dashboard-widget a.stretched-link { z-index: 1; }
</style>
@endpush
@section('content')
    @php
        $kyc = getContent('kyc.content', true);
        $user = auth()->user();
    @endphp
    <div class="notice"></div>

    {{-- KYC Alerts --}}
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

    {{-- Welcome & Quick Stats --}}
    <div class="dashboard-overview mb-4">
        <p class="text-muted mb-0">@lang('Here’s what’s happening with your account')</p>
    </div>

    <div class="row gy-4">
        <div class="col-xxl-9 col-lg-12">
            {{-- Stats Cards --}}
            <div class="row g-3 mb-4">
                <div class="col-sm-6 col-xl-3">
                    <div class="dashboard-widget h-100">
                        <div class="d-flex align-items-center gap-3">
                            <div class="dashboard-widget__icon flex-center rounded-3" style="min-width: 52px; height: 52px; background: hsl(var(--base) / 0.12);">
                                <i class="las la-wallet text--base" style="font-size: 1.5rem;"></i>
                            </div>
                            <div class="dashboard-widget__content flex-grow-1">
                                <span class="dashboard-widget__text d-block small text-muted">@lang('Balance')</span>
                                <h3 class="dashboard-widget__number mb-0 fs-5">{{ gs('cur_sym') }}{{ getAmount($widget['balance']) }}</h3>
                            </div>
                        </div>
                        <a href="{{ route('user.withdraw') }}" class="stretched-link"></a>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="dashboard-widget h-100">
                        <div class="d-flex align-items-center gap-3">
                            <div class="dashboard-widget__icon flex-center rounded-3" style="min-width: 52px; height: 52px; background: rgba(34, 197, 94, 0.12);">
                                <i class="fas fa-link text-success" style="font-size: 1.25rem;"></i>
                            </div>
                            <div class="dashboard-widget__content flex-grow-1">
                                <span class="dashboard-widget__text d-block small text-muted">@lang('Affiliate Income')</span>
                                <h3 class="dashboard-widget__number mb-0 fs-5">{{ gs('cur_sym') }}{{ getAmount($widget['total_earning']) }}</h3>
                            </div>
                        </div>
                        <a href="{{ route('user.conversion.log') }}" class="stretched-link"></a>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="dashboard-widget h-100">
                        <div class="d-flex align-items-center gap-3">
                            <div class="dashboard-widget__icon flex-center rounded-3" style="min-width: 52px; height: 52px; background: rgba(59, 130, 246, 0.12);">
                                <i class="fas fa-video text-primary" style="font-size: 1.25rem;"></i>
                            </div>
                            <div class="dashboard-widget__content flex-grow-1">
                                <span class="dashboard-widget__text d-block small text-muted">@lang('Ads Income')</span>
                                <h3 class="dashboard-widget__number mb-0 fs-5">{{ gs('cur_sym') }}{{ getAmount($widget['ads_income'] ?? 0) }}</h3>
                            </div>
                        </div>
                        @if(\Illuminate\Support\Facades\Route::has('user.ad.view.index'))
                            <a href="{{ route('user.ad.view.index') }}" class="stretched-link"></a>
                        @endif
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="dashboard-widget h-100">
                        <div class="d-flex align-items-center gap-3">
                            <div class="dashboard-widget__icon flex-center rounded-3" style="min-width: 52px; height: 52px; background: rgba(245, 158, 11, 0.12);">
                                <i class="fas fa-hand-holding-usd text-warning" style="font-size: 1.25rem;"></i>
                            </div>
                            <div class="dashboard-widget__content flex-grow-1">
                                <span class="dashboard-widget__text d-block small text-muted">@lang('Withdrawn')</span>
                                <h3 class="dashboard-widget__number mb-0 fs-5">{{ gs('cur_sym') }}{{ getAmount($widget['total_withdraw']) }}</h3>
                            </div>
                        </div>
                        <a href="{{ route('user.withdraw.history') }}" class="stretched-link"></a>
                    </div>
                </div>
            </div>

            {{-- Latest Transactions --}}
            <div class="card custom--card">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <h5 class="mb-0">@lang('Latest Transactions')</h5>
                    <a href="{{ route('user.transactions') }}" class="btn btn--base btn--sm">@lang('View All')</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table--responsive--lg table-hover mb-0">
                            <thead class="bg--light">
                                <tr>
                                    <th>@lang('Trx')</th>
                                    <th>@lang('Date')</th>
                                    <th>@lang('Amount')</th>
                                    <th>@lang('Post Balance')</th>
                                    <th>@lang('Detail')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transactions as $trx)
                                    <tr>
                                        <td><code class="small">{{ $trx->trx }}</code></td>
                                        <td>
                                            <span class="d-block">{{ showDateTime($trx->created_at, 'd M Y') }}</span>
                                            <small class="text-muted">{{ diffForHumans($trx->created_at) }}</small>
                                        </td>
                                        <td>
                                            <span class="fw-bold @if ($trx->trx_type == '+') text--success @else text--danger @endif">
                                                {{ $trx->trx_type }}{{ gs('cur_sym') }}{{ getAmount($trx->amount) }}
                                            </span>
                                        </td>
                                        <td>{{ gs('cur_sym') }}{{ getAmount($trx->post_balance) }}</td>
                                        <td><span class="text-muted small">{{ __($trx->details) }}</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">
                                            <i class="las la-exchange-alt fa-2x mb-2 d-block opacity-50"></i>
                                            {{ __($emptyMessage) }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sidebar: Suggested Campaigns --}}
        <div class="col-xxl-3 col-lg-12">
            <div class="dashboard-sidebar">
                <div class="dashboard-sidebar__item card custom--card border-0">
                    <div class="card-body">
                        <h5 class="dashboard-sidebar__title mb-3">@lang('Suggest for you')</h5>
                        <div class="dashboard-sidebar__content">
                            @forelse ($campaigns as $campaign)
                                <a href="{{ route('campaign.details', $campaign->slug) }}" class="latest-item d-flex align-items-center gap-3 p-3 rounded-3 mb-2 text-decoration-none" style="background: var(--section-bg); transition: background 0.2s;">
                                    <div class="latest-item__thumb flex-shrink-0 rounded-2 overflow-hidden" style="width: 56px; height: 56px;">
                                        <img src="{{ getImage(getFilePath('campaign') . '/' . 'thumb_' . $campaign->image, getFileThumb('campaign')) }}" class="fit-image w-100 h-100" alt="{{ __($campaign->title) }}">
                                    </div>
                                    <div class="latest-item__content flex-grow-1 min-w-0">
                                        <h6 class="latest-item__title mb-1 text-dark">{{ Str::limit(__($campaign->title), 28) }}</h6>
                                        <span class="badge bg--base text-white">{{ gs('cur_sym') }}{{ showAmount($campaign->payout_per_conversion) }}</span>
                                    </div>
                                    <i class="las la-arrow-right text-muted"></i>
                                </a>
                            @empty
                                <div class="text-center py-4 text-muted small">
                                    <i class="las la-bullhorn fa-2x mb-2 d-block opacity-50"></i>
                                    @lang('No campaign found')
                                </div>
                            @endforelse
                        </div>
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
