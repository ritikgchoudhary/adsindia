@extends($activeTemplate . 'layouts.master')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-12">

            {{-- Filter Form --}}
            <div class="show-filter mb-3 text-end">
                <button type="button" class="btn btn--base showFilterBtn btn--sm">
                    <i class="las la-filter"></i> @lang('Filter')
                </button>
            </div>
            <div class="card custom--card responsive-filter-card mb-4">
                <div class="card-body">
                    <form>
                        <div class="d-flex flex-wrap gap-4">
                            <div class="flex-grow-1">
                                <label class="form--label">@lang('Transaction Number')</label>
                                <input type="search" name="search" value="{{ request()->search }}" class="form-control form--control">
                            </div>
                            <div class="flex-grow-1 select2-parent">
                                <label class="form--label d-block">@lang('Type')</label>
                                <select name="trx_type" class="form-select form--control select2" data-minimum-results-for-search="-1">
                                    <option value="">@lang('All')</option>
                                    <option value="+" @selected(request()->trx_type == '+')>@lang('Plus')</option>
                                    <option value="-" @selected(request()->trx_type == '-')>@lang('Minus')</option>
                                </select>
                            </div>
                            <div class="flex-grow-1 select2-parent">
                                <label class="form--label d-block">@lang('Remark')</label>
                                <select class="form-select form--control select2" data-minimum-results-for-search="-1" name="remark">
                                    <option value="">@lang('All')</option>
                                    @foreach ($remarks as $remark)
                                        <option value="{{ $remark->remark }}" @selected(request()->remark == $remark->remark)>
                                            {{ __(keyToTitle($remark->remark)) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex-grow-1 align-self-end">
                                <button class="btn btn--base w-100">
                                    <i class="las la-filter"></i> @lang('Filter')
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Transaction Table using Campaign Table Design --}}
            <div class="campaign-table">
                <div class="card custom--card">
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
                                        <td colspan="5" class="text-center text-muted">@lang($emptyMessage)</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($transactions->hasPages())
                        <div class="mt-3">
                            {{ paginateLinks($transactions) }}
                        </div>
                    @endif
                </div>

            </div>

        </div>
    </div>
@endsection
@push('style')
    <style>
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
