@php
    $campaignSection = getContent('campaigns.content', true);
    $featuredCampaigns = \App\Models\Campaign::running()
        ->featured()
        ->whereHas('category', function ($q) {
            $q->active();
        })
        ->latest()
        ->take(8)
        ->get();
@endphp

<div class="campaign-section my-120">
    <div class="container">
        <div class="section-heading">
            <div class="left-thumb-wrapper">
                <div class="left-thumb"></div>
                <div class="border-shape"></div>
            </div>
            <div class="right-thumb-wrapper">
                <div class="right-thumb"></div>
                <div class="border-shape"></div>
            </div>
            <span class="section-heading__subtitle">
                {{ __($campaignSection?->data_values?->subtitle ?? '') }}
            </span>

            <h3 class="section-heading__title">
                {{ __($campaignSection?->data_values?->heading ?? '') }}
            </h3>

            <p class="section-heading__desc">
                {{ __($campaignSection?->data_values?->description ?? '') }}
            </p>
        </div>

        <div class="row gy-4 justify-content-center">
            @forelse ($featuredCampaigns as $campaign)
                <div class="col-xl-3 col-lg-4 col-sm-6">
                    <x-campaign-card :campaign="$campaign" :loop-iteration="$loop->iteration" />
                </div>
            @empty
                <div class="col-xl-3 col-lg-4 col-sm-6">
                    <div class="text-center">
                        <i class="las la-2x la-clipboard-list"></i>
                        <br>
                        @lang('No campaigns found yet!')
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>
