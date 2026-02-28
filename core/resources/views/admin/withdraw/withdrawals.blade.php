@extends('admin.layouts.app')

@section('panel')
    <div class="row justify-content-center">
        @if (request()->routeIs('admin.withdraw.data.all') || request()->routeIs('admin.withdraw.method'))
            <div class="col-12">
                @include('admin.withdraw.widget')
            </div>
        @endif
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body p-0">

                    <div class="table-responsive--sm table-responsive">
                        <table class="table table--light style--two">
                            <thead>
                                <tr>
                                    <th>@lang('Method | Transaction')</th>
                                    <th>@lang('Initiated')</th>
                                    <th>@lang('User')</th>
                                    <th>@lang('Type')</th>
                                    <th>@lang('Amount')</th>
                                    <th>@lang('Conversion')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Action')</th>

                                </tr>
                            </thead>
                            <tbody>
                                @forelse($withdrawals as $withdraw)
                                    @php
                                        $details = $withdraw->withdraw_information != null ? json_encode($withdraw->withdraw_information) : null;
                                    @endphp
                                    <tr @if($withdraw->is_priority == 1) style="background-color: rgba(239, 68, 68, 0.1);" @endif>
                                        <td>
                                            @if($withdraw->is_priority == 1)
                                                <span class="badge badge--danger mb-1" style="font-size: 10px;">PRIORITY PAYOUT</span><br>
                                            @endif
                                            <span class="fw-bold"><a href="{{ appendQuery('method', $withdraw?->method?->id) }}"> {{ __($withdraw?->method?->name) }}</a></span>
                                            <br>
                                            <small>{{ $withdraw->trx }}</small>
                                        </td>
                                        <td>
                                            {{ showDateTime($withdraw->created_at) }} <br> {{ diffForHumans($withdraw->created_at) }}
                                        </td>

                                        <td>
                                            @if ($withdraw->user_id)
                                                <span class="fw-bold">{{ $withdraw?->user?->fullname }}</span>
                                                <br>
                                                <span class="small"> <a href="{{ appendQuery('search', $withdraw?->user?->username) }}"><span>@</span>{{ $withdraw?->user?->username }}</a> </span>
                                            @else
                                                <span class="fw-bold">{{ $withdraw?->advertiser?->fullname }}</span>
                                                <br>
                                                <span class="small"> <a href="{{ appendQuery('search', $withdraw?->advertiser?->username) }}"><span>@</span>{{ $withdraw?->advertiser?->username }}</a> </span>
                                            @endif
                                        </td>

                                        <td>
                                            @if ($withdraw->user_id)
                                                <span class="fw-bold">@lang('User')</span>
                                            @else
                                                <span class="fw-bold">@lang('Advertiser')</span>
                                            @endif
                                        </td>

                                        <td>
                                            {{ showAmount($withdraw->amount) }} - <span class="text--danger" title="@lang('charge')">{{ showAmount($withdraw->charge) }} </span>
                                            <br>
                                            <strong title="@lang('Amount after charge')">
                                                {{ showAmount($withdraw->amount - $withdraw->charge) }}
                                            </strong>

                                        </td>

                                        <td>
                                            {{ showAmount(1) }} = {{ showAmount($withdraw->rate, currencyFormat: false) }} {{ __($withdraw->currency) }}
                                            <br>
                                            <strong>{{ showAmount($withdraw->final_amount, currencyFormat: false) }} {{ __($withdraw->currency) }}</strong>
                                        </td>

                                        <td>
                                            @php echo $withdraw->statusBadge @endphp
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.withdraw.data.details', $withdraw->id) }}" class="btn btn-sm btn-outline--primary ms-1">
                                                <i class="la la-desktop"></i> @lang('Details')
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                        <tr>
                                            <td class="text-muted text-center" colspan="100%">{{ __($emptyMessage) }}</td>
                                        </tr>
                                    @endforelse

                                </tbody>
                            </table><!-- table end -->
                        </div>
                    </div>
                    @if ($withdrawals->hasPages())
                        <div class="card-footer py-4">
                            {{ paginateLinks($withdrawals) }}
                        </div>
                    @endif
                </div><!-- card end -->
            </div>
        </div>
    @endsection




    @push('breadcrumb-plugins')
        <x-search-form dateSearch='yes' placeholder='Username / TRX' />
    @endpush
