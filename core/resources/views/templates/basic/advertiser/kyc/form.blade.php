@extends($activeTemplate . 'layouts.master')
@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card custom--card">
                    <div class="card-body">
                        <form action="{{ route('advertiser.kyc.submit') }}" method="post" enctype="multipart/form-data">
                            @csrf

                            <x-viser-form identifier="act" identifierValue="advertiser_kyc" />

                            <button type="submit" class="btn btn--base w-100">@lang('Submit')</button>
                        </form>
                    </div>
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


@push('script')
    <script>
        (function($) {
            "use strict";
            $('input').removeClass('form-control');
        })(jQuery)
    </script>
@endpush
