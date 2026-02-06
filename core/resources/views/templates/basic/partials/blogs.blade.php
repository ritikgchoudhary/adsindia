@php
    $classes = ['card-base-two-bg', 'card-success-bg'];
@endphp

@foreach ($blogs as $key => $blog)
    @php
        $class = $classes[$key % count($classes)];
    @endphp
    <div class="col-lg-4 col-md-6">
        <div class="blog-item {{ $class }}">
            <div class="blog-item__thumb">
                <a href="{{ route('blog.details', $blog?->slug ?? '') }}" class="blog-item__thumb-link">
                    <img src="{{ frontendImage('blog', 'thumb_' . $blog?->data_values?->image ?? '', '470x290') }}" class="fit-image" alt="img">
                </a>
                <div class="blog-item__date">
                    <h4 class="date-time">{{ showDateTime($blog->created_at, 'd') }}</h4>
                    <h6 class="month">{{ showDateTime($blog->created_at, 'M') }}</h6>
                </div>
            </div>
            <div class="blog-item__content">
                <span class="blog-item__badge">{{ __($blog?->data_values?->badge) }}</span>
                <h5 class="blog-item__title">
                    <a href="{{ route('blog.details', $blog?->slug) }}" class="blog-item__title-link">
                        {{ __($blog?->data_values?->title) }}
                    </a>
                </h5>
            </div>
        </div>
    </div>
@endforeach
