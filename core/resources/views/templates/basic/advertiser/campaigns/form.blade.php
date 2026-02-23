@extends($activeTemplate . 'layouts.master')

@section('content')
    @php
        $trackingToken = $campaign->tracking_token ?? (string) Str::uuid();
    @endphp
    <div class="alert alert--danger mb-4" role="alert">
        <div class="alert__icon">
            <i class="fas fa-bullhorn"></i>
        </div>
        <div class="alert__content">
            <h4 class="alert__title">@lang('Campaign Approval Required')</h4>
            <p class="alert__desc">
                @lang('After submitting your campaign, it will remain in ')<strong>@lang('pending status')</strong> @lang('until reviewed and approved by an admin.')
                <br>
                <strong>@lang('Note'):</strong> @lang('The campaign budget will only be deducted from your balance once the campaign is approved.')
            </p>
            <p class="alert__desc">
                @lang('To avoid delays, please ensure you have sufficient balance. You can')
                <a href="{{ route('advertiser.deposit.index') }}" class="alert__link">@lang('deposit funds here')</a>.
            </p>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        @if (isset($campaign))
            <div class="d-flex gap-2">
                @php echo $campaign->statusBadge; @endphp
                @if ($campaign->is_paused)
                    @php echo $campaign->pauseBadge; @endphp
                @endif
            </div>
        @endif

        @if (isset($campaign))
            <div class="d-flex gap-2 flex-wrap">
                @if ($campaign->status != Status::CAMPAIGN_APPROVED && $campaign->status != Status::CAMPAIGN_COMPLETED)
                    <a href="{{ $campaign->url }}?ref={{ $campaign->tracking_token }}_testingConnectionToServer" class="btn btn--base" target="_blank">
                        <i class="las la-globe"></i> @lang('Test Connection')
                    </a>
                @endif

                @if ($campaign->status == Status::CAMPAIGN_APPROVED)
                    <button type="button"
                            class="btn {{ $campaign->is_paused ? 'btn--success' : 'btn--warning' }} confirmationBtn"
                            data-question="{{ $campaign->is_paused ? __('Are you sure you want to resume this campaign? The campaign will become visible to users again and conversions will resume.') : __('Are you sure you want to pause this campaign? The campaign will be hidden from users and conversions will stop.') }}"
                            data-action="{{ route('advertiser.campaign.toggle.pause', $campaign->id) }}">
                        <i class="las {{ $campaign->is_paused ? 'la-play-circle' : 'la-pause-circle' }}"></i>
                        {{ $campaign->is_paused ? __('Resume Campaign') : __('Pause Campaign') }}
                    </button>
                @endif
            </div>
        @endif
    </div>


    <form action="{{ route('advertiser.campaign.store', isset($campaign) ? $campaign->id : 0) }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="card custom--card">
            <div class="card-body">
                <div class="search-wrapper">
                    <div class="search-item">
                        <div class="search-item__left">
                            <p class="search-item__title">@lang('Campaign Image')</p>
                        </div>
                        <div class="search-item__right">
                            <div class="profile-item mb-2">
                                <div class="profile-item__thumb">
                                    <div class="file-upload">
                                        <label class="edit" for="campaign-cover"><i class="las la-camera"></i></label>
                                        <input type="file" name="image" class="form-control form--control image-uploader" id="campaign-cover" data-target="#campaignPreview" accept=".png,.jpg,.jpeg" hidden>
                                    </div>
                                    <div class="thumb">
                                        <img id="campaignPreview" src="{{ getImage(getFilePath('campaign') . '/' . $campaign?->image, getFileSize('campaign')) }}" alt="Campaign Cover">
                                    </div>
                                </div>
                            </div>
                            <p class="fs-14 text--white fw-medium mb-0"> @lang('Supported Files: .png, .jpg, .jpeg. Image will be resized into :size px', ['size' => getFileSize('campaign')])</p>
                        </div>
                    </div>

                    <div class="search-item">
                        <div class="search-item__left">
                            <p class="search-item__title">@lang('Campaign Title')</p>
                        </div>
                        <div class="search-item__right">
                            <input type="text" class="form--control" name="title" value="{{ old('title', $campaign->title ?? '') }}" required>
                        </div>
                    </div>

                    <div class="search-item">
                        <div class="search-item__left">
                            <p class="search-item__title">@lang('Campaign Description')</p>
                        </div>
                        <div class="search-item__right">
                            <textarea name="description" id="description" rows="5" class="form-control nicEdit">{{ old('description', $campaign->description ?? '') }}</textarea>
                        </div>
                    </div>

                    <div class="search-item">
                        <div class="search-item__left">
                            <p class="search-item__title">@lang('Landing Page URL')</p>
                        </div>
                        <div class="search-item__right">
                            <input type="url" class="form--control" name="url" value="{{ old('url', $campaign->url ?? '') }}" required>
                        </div>
                    </div>


                    <div class="search-item">
                        <div class="search-item__left">
                            <p class="search-item__title">@lang('Category')</p>
                        </div>
                        <div class="search-item__right">
                            <select name="category_id" class="form--control select2" required>
                                <option value="">@lang('Select Category')</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', $campaign->category_id ?? '') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="search-item">
                        <div class="search-item__left">
                            <p class="search-item__title">@lang('Traffic Types')</p>
                        </div>
                        <div class="search-item__right">
                            <select name="traffic_type_id[]" class="form--control select2" multiple="multiple" required>
                                @foreach ($traffics as $traffic)
                                    <option value="{{ $traffic->id }}" @selected(in_array($traffic->id, old('tags', $campaign->trafficTypeId ?? []) ?? []))>
                                        {{ $traffic->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="search-item">
                        <div class="search-item__left">
                            <p class="search-item__title">@lang('Start Date')</p>
                        </div>
                        <div class="search-item__right">
                            <input type="text" name="starts_at" class="form--control" value="{{ old('starts_at', isset($campaign->starts_at) ? showDateTime($campaign->starts_at, 'Y-m-d h:i:s') : '') }}" placeholder="@lang('Start Date')">
                        </div>
                    </div>

                    <div class="search-item">
                        <div class="search-item__left">
                            <p class="search-item__title">@lang('End Date')</p>
                        </div>
                        <div class="search-item__right">
                            <input type="text" name="ends_at" class="form--control" value="{{ old('ends_at', isset($campaign->ends_at) ? showDateTime($campaign->ends_at, 'Y-m-d h:i:s') : '') }}" placeholder="@lang('End Date')">

                        </div>
                    </div>

                    <div class="search-item">
                        <div class="search-item__left">
                            <p class="search-item__title">@lang('Conversion Limit')</p>
                        </div>
                        <div class="search-item__right">
                            <input type="number" class="form--control" name="conversion_limit" value="{{ old('conversion_limit', $campaign->conversion_limit ?? '') }}" @if (isset($campaign) && $campaign->payment_status == Status::PAID) readonly @endif>
                            <code><i class="las la-exclamation-triangle"></i> @lang('Once approved, the conversion limit cannot be edited. Please review carefully before submitting.')</code>
                        </div>
                    </div>

                    <div class="search-item">
                        <div class="search-item__left">
                            <p class="search-item__title">@lang('Affiliate Payout per Conversion')</p>
                        </div>
                        <div class="search-item__right">
                            <input type="number" step="0.01" class="form--control" name="payout_per_conversion" value="{{ old('payout_per_conversion', $campaign->payout_per_conversion ?? '') }}" required @if (isset($campaign) && $campaign->payment_status == Status::PAID) readonly @endif>
                            <code><i class="las la-exclamation-triangle"></i> @lang('Once approved, the payout per conversion cannot be edited. Please review carefully before submitting.')</code>
                        </div>
                    </div>

                    <div class="search-item">
                        <div class="search-item__left">
                            <p class="search-item__title">@lang('Admin Commission Per Conversion')</p>
                        </div>
                        <div class="search-item__right">
                            <div class="form-group">
                                <div class="input-group">
                                    <input type="number" step="0.01" class="form-control form--control" name="admin_commission" value="{{ getAmount(gs('system_affiliate_commission')) }}" readonly>
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="search-wrapper-btn mt-4">
                    <button class="btn btn--base btn--lg pill w-100" type="submit">@lang('Submit')</button>
                </div>
            </div>
        </div>
    </form>

    <x-confirmation-modal class="custom--modal" closeBtn="btn--danger btn--sm" submitBtn="btn--base btn--sm" />

@endsection

@push('style')
    <style>
        #tracking-snippet,
        #landing-snippet {
            user-select: all;
            white-space: pre-wrap;
            color: #ffff;
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

@push('script-lib')
    <script src="{{ asset('assets/global/js/nicEdit.js') }}"></script>
    <script src="{{ asset('assets/admin/js/moment.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/daterangepicker.min.js') }}"></script>
@endpush

@push('style-lib')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/admin/css/daterangepicker.css') }}">
@endpush


@push('script')
    <script>
        (function($) {
            "use strict";
            bkLib.onDomLoaded(function() {
                $(".nicEdit").each(function(index) {
                    $(this).attr("id", "nicEditor" + index);

                    new nicEditor({
                        fullPanel: false
                    }).panelInstance('nicEditor' + index, {
                        hasPanel: true
                    });
                    $('.nicEdit-main').parent('div').addClass('nicEdit-custom-main')
                });
            });

            $("#campaign-cover").on('change', function() {
                proPicURL(this);
            });

            function proPicURL(input) {
                if (input.files && input.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        const result = reader.result;
                        $("#campaignPreview").attr('src', result);
                    }
                    reader.readAsDataURL(input.files[0]);
                }
            }

            function calculateBudget() {
                const payout = parseFloat($('[name="payout_per_conversion"]').val()) || 0;
                const commission = parseFloat($('[name="admin_commission"]').val()) || 0;
                const limit = parseInt($('[name="conversion_limit"]').val()) || 0;
                const total = (payout + commission) * limit;
                $('#campaign_budget').val(total.toFixed(2));
            }

            $('[name="payout_per_conversion"], [name="admin_commission"], [name="conversion_limit"]').on('input', calculateBudget);
            calculateBudget();


            const startInput = $('[name="starts_at"]');
            const endInput = $('[name="ends_at"]');

            const existingStart = startInput.val() ? startInput.val() : moment();
            const existingEnd = endInput.val() ? moment(endInput.val()) : moment().add(1, 'hours');

            startInput.daterangepicker({
                singleDatePicker: true,
                timePicker: true,
                showDropdowns: true,
                startDate: existingStart,
                minDate: existingStart,
                locale: {
                    format: 'YYYY-MM-DD HH:mm'
                }
            });

            endInput.daterangepicker({
                singleDatePicker: true,
                timePicker: true,
                showDropdowns: true,
                startDate: existingEnd,
                minDate: moment(),
                locale: {
                    format: 'YYYY-MM-DD HH:mm'
                }
            });

            startInput.on('apply.daterangepicker', function(ev, picker) {
                const selectedStart = picker.startDate;

                endInput.data('daterangepicker').minDate = selectedStart;

                if (moment(endInput.val()).isBefore(selectedStart)) {
                    endInput.data('daterangepicker').setStartDate(selectedStart.clone().add(1, 'hours'));
                }
            });
        })(jQuery);
    </script>
@endpush

@push('style')
    <style>
        .alert__desc {
            font-size: 1rem;
        }

        .profile-item__thumb {
            width: 100px;
            height: 100px;
        }

        .profile-item__thumb .thumb {
            border-radius: 8px;
        }

        .profile-item .edit {
            width: 28px;
            height: 28px;
            right: -2px;
            bottom: -2px;
            border: 3px solid hsl(var(--black));
        }

        .nicEdit-panelContain {
            border-color: #333333 !important;
            border-radius: 8px 8px 0 0;
        }

        .nicEdit-custom-main {
            border-color: #333333 !important;
            border-radius: 0 0 8px 8px;
        }

        .nicEdit-main {
            outline: none !important;
            color: #fff;
        }

        .nicEdit-pane,
        .nicEdit-pane * {
            background-color: black !important;
            color: white !important;

            h6,
            h5,
            h4,
            h3,
            h2,
            h1 {
                font-size: 14px !important
            }
        }

        @media (max-width: 767px) {
            .search-item {
                margin-bottom: 16px;
                padding-bottom: 16px;
                border-bottom: 1px solid hsl(var(--white)/0.1);
            }

            .form--control {
                border-radius: 6px;
                padding: 12px 24px;
            }

            .select2-container--default .select2-selection--single {
                padding: 10px 24px !important;
                border-radius: 6px !important;
            }

            .select2-container--open .select2-selection.select2-selection--single,
            .select2-container--open .select2-selection.select2-selection--multiple {
                border-radius: 6px !important;
            }

            .search-item__title {
                color: hsl(var(--white));
                font-weight: 400;
                font-size: 0.875rem;
            }

            .dropdown-wrapper {
                display: none;
            }

            .select2-container--default .select2-selection--single .select2-selection__arrow {
                top: 23px !important;
            }

            .select2-container--open .select2-dropdown {
                margin: 0 !important;
            }
        }
    </style>
@endpush
