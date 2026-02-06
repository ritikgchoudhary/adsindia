@extends($activeTemplate . 'layouts.frontend')
@section('content')
    <div class="campaign-details-section mb-120 mt-60">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xl-9">
                    <div class="campaign-details">
                        <div class="campaign-details__thumb">
                            <img src="{{ getImage(getFilePath('campaign') . '/' . $campaign->image, getFileSize('campaign')) }}" alt="img">
                        </div>
                        <div class="campaign-details__content">
                            <div class="content-item flex-between gap-3">
                                <h3 class="campaign-details__title mb-0">
                                    {{ __($campaign->title) }}
                                </h3>

                                <div class="flex-shrink-0 campaign-details__price">
                                    <h3 class="text--base mb-0">{{ gs('cur_sym') }}{{ getAmount($campaign->payout_per_conversion) }}</h3>
                                    <p class="fs-14 text-white fw-semibold">@lang('Per Conversion')</p>
                                </div>
                            </div>

                            <div class="content-item">
                                @php echo $campaign->description; @endphp
                            </div>

                            <div class="campaign-details__info">
                                <table class="table">
                                    <tbody>
                                        <tr>
                                            <td> @lang('Allowed Traffic') </td>
                                            <td>
                                                <div class="flex-align gap-2">
                                                    @foreach ($campaign->trafficTypeName as $traffic)
                                                        <span class="text"> {{ __($traffic) }} </span>
                                                    @endforeach
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td> @lang('Start At') </td>
                                            <td>
                                                {{ showDateTime($campaign->starts_at) }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td> @lang('End At') </td>
                                            <td>
                                                {{ showDateTime($campaign->ends_at) }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td> @lang('Conversion Limit') </td>
                                            <td>
                                                {{ $campaign->conversion_limit }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="campaign-details__btn">
                            @if (auth()->check())
                                <button type="button" class="btn btn--base pill btn--lg getAffiliateLinkBtn" data-title="{{ __($campaign->title) }}" data-url="{{ $campaign->url }}?ref={{ $campaign->tracking_token }}_{{ encrypt(auth()->user()->username) }}"
                                        data-image-url="{{ asset($activeTemplateTrue . 'images/thumbs/cd-1.png') }}">
                                    @lang('Get Affiliate Link')
                                    <span class="btn-icon"> <i class="las la-arrow-right"></i> </span>
                                </button>
                            @else
                                <button type="button" class="btn btn--base pill btn--lg" data-bs-toggle="modal" data-bs-target="#loginPromptModal">
                                    @lang('Get Affiliate Link')
                                    <span class="btn-icon"> <i class="las la-arrow-right"></i> </span>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- If user not logged in -->
    <div class="modal custom--modal fade" id="loginPromptModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Login Required')</h5>
                    <span type="button" class="close" data-bs-dismiss="modal"><i class="las la-times"></i></span>
                </div>
                <div class="modal-body">
                    <p class="text-center mb-0">
                        @lang('Please log in to access your affiliate link and start promoting this campaign.')
                    </p>
                </div>
                <div class="modal-footer mt-4">
                    <button type="button" class="btn btn-dark btn--sm" data-bs-dismiss="modal">@lang('Close')</button>
                    <a href="{{ route('user.login') }}" class="btn btn--base btn--sm">@lang('Login')</a>
                </div>
            </div>
        </div>
    </div>

    @if (auth()->check())
        <x-share-modal />
    @endif
@endsection
