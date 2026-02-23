@props(['campaign', 'loopIteration' => 1])
@php
    $link = route('campaign.details', $campaign->slug ?? '');
    $bgClass = $loopIteration % 3 == 0 ? 'card-success-bg' : ($loopIteration % 2 == 0 ? 'card-base-two-bg' : '');
@endphp

<div class="card-item {{ $bgClass }}">
    <a href="{{ $link }}" class="card-item__thumb">
        <img src="{{ getImage(getFilePath('campaign') . '/' . 'thumb_' . $campaign->image, getFileThumb('campaign')) }}" alt="@lang('img')">
    </a>
    <div class="card-item__content">
        <h5 class="card-item__title">
            <a href="{{ $link }}" class="card-item__title-link">
                {{ __($campaign->title) }}
            </a>
        </h5>
        <div class="card-item__button">
            <a href="{{ $link }}" class="btn-outline--white btn w-100">
                {{ showAmount($campaign->payout_per_conversion) }}
            </a>
        </div>
    </div>
</div>
