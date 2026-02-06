@extends($activeTemplate . 'layouts.master')
@section('content')
<div class="dashboard-inner">
    <div class="mb-4">
        <h3 class="mb-2">@lang('View Ad History')</h3>
        <p>@lang('Your ad viewing history and earnings')</p>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table--responsive--lg">
                            <thead>
                                <tr>
                                    <th>@lang('Date')</th>
                                    <th>@lang('Package')</th>
                                    <th>@lang('Ad URL')</th>
                                    <th>@lang('Watch Duration')</th>
                                    <th>@lang('Reward')</th>
                                    <th>@lang('Status')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($adViews as $adView)
                                    <tr>
                                        <td>
                                            {{ showDateTime($adView->viewed_at, 'd M Y, h:i A') }}
                                        </td>
                                        <td>
                                            {{ $adView->adPackageOrder->package->name ?? 'N/A' }}
                                        </td>
                                        <td>
                                            <a href="{{ $adView->ad_url }}" target="_blank" class="text-primary">
                                                {{ Str::limit($adView->ad_url, 30) }}
                                            </a>
                                        </td>
                                        <td>
                                            {{ $adView->watch_duration }} @lang('seconds')
                                        </td>
                                        <td>
                                            <span class="text-success">+{{ showAmount($adView->reward_amount) }} {{ $general->cur_text }}</span>
                                        </td>
                                        <td>
                                            @if($adView->is_completed)
                                                <span class="badge badge--success">@lang('Completed')</span>
                                            @else
                                                <span class="badge badge--warning">@lang('Incomplete')</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-center" colspan="6">@lang('No ad views found')</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($adViews->hasPages())
                        <div class="mt-3">
                            {{ paginateLinks($adViews) }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
