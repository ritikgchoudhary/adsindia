@extends($activeTemplate . 'layouts.master')
@section('content')
<div class="dashboard-inner">
    <div class="mb-4">
        <h3 class="mb-2">@lang('View Ad & Earn')</h3>
        <p>@lang('Watch ads and earn rewards')</p>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="alert alert-info">
                        <h5>@lang('Package Information')</h5>
                        <p class="mb-1"><strong>@lang('Package'):</strong> {{ $activeOrder->package->name }}</p>
                        <p class="mb-1"><strong>@lang('Daily Limit'):</strong> {{ $activeOrder->package->daily_ad_limit }} @lang('ads')</p>
                        <p class="mb-1"><strong>@lang('Reward per Ad'):</strong> {{ showAmount($activeOrder->package->reward_per_ad) }} {{ $general->cur_text }}</p>
                        <p class="mb-1"><strong>@lang('Today Viewed'):</strong> {{ $todayViews }}/{{ $activeOrder->package->daily_ad_limit }}</p>
                        <p class="mb-1"><strong>@lang('Video Duration'):</strong> {{ $activeOrder->package->duration_seconds }} @lang('seconds')</p>
                    </div>

                    @if($todayViews >= $activeOrder->package->daily_ad_limit)
                        <div class="alert alert-warning">
                            <i class="las la-exclamation-triangle"></i> @lang('You have reached your daily ad viewing limit. Please come back tomorrow.')
                        </div>
                    @else
                        <div id="video-container" class="mb-4">
                            <div class="text-center">
                                <button class="btn btn-primary btn-lg" id="load-video-btn">
                                    <i class="las la-play"></i> @lang('Load Ad Video')
                                </button>
                            </div>
                        </div>

                        <div id="video-player-container" style="display: none;">
                            <video id="ad-video" controls width="100%" style="max-width: 800px; margin: 0 auto; display: block;">
                                <source id="video-source" src="" type="video/mp4">
                                @lang('Your browser does not support the video tag.')
                            </video>
                            <div class="text-center mt-3">
                                <p class="text-muted">@lang('Please watch the complete video to earn reward')</p>
                                <p id="watch-progress" class="text-info"></p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
    let currentVideo = null;
    let watchStartTime = null;
    let watchDuration = 0;
    let progressInterval = null;

    document.getElementById('load-video-btn')?.addEventListener('click', function() {
        loadVideo();
    });

    function loadVideo() {
        const btn = document.getElementById('load-video-btn');
        if (btn) btn.disabled = true;

        fetch('{{ route("user.ad.view.get") }}')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.videos && data.videos.length > 0) {
                    const video = data.videos[0];
                    const videoSource = document.getElementById('video-source');
                    const videoPlayer = document.getElementById('ad-video');
                    
                    if (video.sources && video.sources.length > 0) {
                        videoSource.src = video.sources[0].src || video.sources[0];
                        videoPlayer.load();
                        
                        document.getElementById('video-container').style.display = 'none';
                        document.getElementById('video-player-container').style.display = 'block';
                        
                        watchStartTime = Date.now();
                        startProgressTracking();
                        
                        videoPlayer.addEventListener('ended', function() {
                            completeAdView(video.sources[0].src || video.sources[0], data.duration);
                        });
                    }
                } else {
                    notify('error', data.message || 'Failed to load video');
                    if (btn) btn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                notify('error', 'Failed to load video');
                if (btn) btn.disabled = false;
            });
    }

    function startProgressTracking() {
        const videoPlayer = document.getElementById('ad-video');
        const progressElement = document.getElementById('watch-progress');
        
        progressInterval = setInterval(() => {
            if (videoPlayer && !videoPlayer.paused) {
                watchDuration = Math.floor(videoPlayer.currentTime);
                if (progressElement) {
                    progressElement.textContent = `Watched: ${watchDuration} seconds`;
                }
            }
        }, 1000);
    }

    function completeAdView(adUrl, requiredDuration) {
        clearInterval(progressInterval);
        
        const videoPlayer = document.getElementById('ad-video');
        const watchDuration = Math.floor(videoPlayer.currentTime || 0);
        
        fetch('{{ route("user.ad.view.complete") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                ad_url: adUrl,
                watch_duration: watchDuration
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                notify('success', data.message || 'Reward added successfully!');
                setTimeout(() => {
                    location.reload();
                }, 2000);
            } else {
                notify('error', data.message || 'Failed to complete ad view');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            notify('error', 'Failed to complete ad view');
        });
    }
</script>
@endpush
