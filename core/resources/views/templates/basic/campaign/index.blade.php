@extends($activeTemplate . 'layouts.frontend')
@section('content')
    <div class="campaign-section mb-120 mt-60">
        <div class="container">
            <div class="row gy-4 align-items-start justify-content-center campaign-list">
                <div class="col-xl-9 col-lg-8">
                    <div class="loader-wrapper">
                        <div class="loader"></div>
                    </div>
                    <div class="row gy-4 justify-content-center" id="campaigns">
                        @include($activeTemplate . 'partials.campaigns')
                    </div>
                </div>
                <div class="col-xl-3 col-lg-4">
                    <div class="campaign-sidebar">
                        <div class="sidebar-item">
                            <h5 class="sidebar-item__title">@lang('Searching for')</h5>
                            <form action="{{ route('campaigns') }}" class="search-form">
                                <input type="text" name="search" value="{{ request()->search }}" class="form--control" placeholder="@lang('Search..')">
                                <button class="search-form__icon" type="button">
                                    <i class="las la-search"></i>
                                </button>
                            </form>
                        </div>
                        <div class="sidebar-item">
                            <h5 class="sidebar-item__title">@lang('Categories')</h5>
                            <div class="sidebar-item__content">
                                <div class="sidebar-item__text">
                                    <div class="form--check">
                                        <input class="form-check-input sortCategory" type="checkbox" name="category" id="category-all" {{ isset($categoryId) ? '' : 'checked' }}>
                                        <label class="form-check-label" for="category-all">@lang('All') ({{ $categories->sum('campaigns_count') }})</label>
                                    </div>
                                </div>
                                @foreach ($categories as $category)
                                    <div class="sidebar-item__text">
                                        <div class="form--check">
                                            <input class="form-check-input sortCategory" type="checkbox" name="category" value="{{ $category->id }}" id="category-{{ $category->id }}" {{ isset($categoryId) ? ($categoryId == $category->id ? 'checked' : '') : '' }}>
                                            <label class="form-check-label" for="category-{{ $category->id }}">
                                                {{ __($category->name) }} ({{ $category->campaigns_count ?? 0 }})
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                                <button type="button" class="load-more-button">@lang('Load more')</button>
                            </div>
                        </div>

                        <div class="sidebar-item">
                            <h5 class="sidebar-item__title">@lang('Traffic Type')</h5>
                            <div class="sidebar-item__content">
                                <div class="sidebar-item__text">
                                    <div class="form--check">
                                        <input class="form-check-input sortTraffic" type="checkbox" name="traffic_type" id="traffic-all" checked>
                                        <label class="form-check-label" for="traffic-all">
                                            @lang('All')
                                        </label>
                                    </div>
                                </div>
                                @foreach ($traffics as $index => $traffic)
                                    <div class="sidebar-item__text">
                                        <div class="form--check">
                                            <input class="form-check-input sortTraffic" type="checkbox" name="traffic_type" value="{{ $traffic->id }}" id="traffic-{{ $index }}">
                                            <label class="form-check-label" for="traffic-{{ $index }}">
                                                {{ __($traffic->name) }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                                <button type="button" class="load-more-button">@lang('Load more')</button>
                            </div>
                        </div>

                        <div class="sidebar-item">
                            <h5 class="sidebar-item__title">@lang('Date Range')</h5>
                            <div class="sidebar-item__content">
                                <div class="sidebar-item__text">
                                    <div class="form--radio">
                                        <input class="form-check-input sortDate" type="radio" name="date" id="date-all" checked>
                                        <label class="form-check-label" for="date-all">
                                            @lang('All')
                                        </label>
                                    </div>
                                </div>
                                @foreach (['Today', 'Yesterday', 'Last 7 Days', 'Last 30 Days'] as $index => $date)
                                    <div class="sidebar-item__text">
                                        <div class="form--radio">
                                            <input class="form-check-input sortDate" type="radio" name="date" value="{{ $date }}" id="date-{{ $index }}">
                                            <label class="form-check-label" for="date-{{ $index }}">
                                                {{ __($date) }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                                <button type="button" class="load-more-button">@lang('Load more')</button>
                            </div>
                        </div>
                        <div class="sidebar-item">
                            <h5 class="sidebar-item__title">@lang('Sorting')</h5>
                            <div class="sidebar-item__content">
                                <div class="sidebar-item__text">
                                    <div class="form--radio">
                                        <input class="form-check-input sortCampaign" type="radio" name="sort" id="sort-all" checked>
                                        <label class="form-check-label" for="sort-all">
                                            @lang('All')
                                        </label>
                                    </div>
                                </div>
                                @foreach (['Most Recent', 'Highest Budget', 'Lowest Budget', 'Most conversions'] as $index => $sort)
                                    <div class="sidebar-item__text">
                                        <div class="form--radio">
                                            <input class="form-check-input sortCampaign" type="radio" value="{{ $sort }}" name="sort" id="sort-{{ $index }}">
                                            <label class="form-check-label" for="sort-{{ $index }}">
                                                {{ __($sort) }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


@push('script')
    <script>
        (function($) {
            "use strict";

            let page = null;
            $('.loader-wrapper').addClass('d-none');
            $('.sortTraffic, .sortCategory, .sortDate, .sortCampaign').on('change', function() {
                $('.loader-wrapper').removeClass('d-none');

                if ($('#category-all').is(':checked')) {
                    $("input[type='checkbox'][name='category']").not(this).prop('checked', false);
                }

                if ($("input[type='checkbox'][name='category']:checked").length == 0) {
                    $('#category-all').attr('checked', 'checked');
                }


                if ($('#traffic-all').is(':checked')) {
                    $("input[type='checkbox'][name='traffic_type']").not(this).prop('checked', false);
                }

                if ($("input[type='checkbox'][name='traffic_type']:checked").length == 0) {
                    $('#traffic-all').attr('checked', 'checked');
                }

                fetchCampaign();
            });

            $('.search-form__icon').on('click', function(e) {
                e.preventDefault();
                fetchCampaign();
            });


            function fetchCampaign() {
                let data = {};
                data.search = $('input[name="search"]').val();
                data.sort = $('input[name="sort"]:checked').val();
                data.category = [];
                data.traffic_type = [];
                data.date = $('input[name="date"]:checked').val();

                $('.sortCategory:checked').each(function() {
                    if ($(this).val()) {
                        data.category.push($(this).val());
                    }
                });

                $('.sortTraffic:checked').each(function() {
                    if ($(this).val()) {
                        data.traffic_type.push($(this).val());
                    }
                });


                let url = `{{ route('campaigns.filter') }}`;
                if (page) {
                    url = `{{ route('campaigns.filter') }}?page=${page}`;
                }

                $.ajax({
                    method: "GET",
                    url: url,
                    data: data,
                    success: function(response) {
                        $('#campaigns').html(response);
                        scrollHeight();
                    }
                }).done(function() {
                    $('.loader-wrapper').addClass('d-none')
                });
            }

            $(document).on('click', '.pagination a', function(event) {
                event.preventDefault();
                $('.loader-wrapper').removeClass('d-none');
                page = $(this).attr('href').split('page=')[1];
                fetchCampaign();
            });

            function scrollHeight() {
                const target = $('#campaigns');
                if (target.length) {
                    $('html, body').animate({
                        scrollTop: target.offset().top - 50
                    }, 500);
                }
            }

        })(jQuery)
    </script>
@endpush

@push('style')
    <style>
        body:not(:has(.d-none.loader-wrapper)) {
            overflow: hidden;
        }
    </style>
@endpush
