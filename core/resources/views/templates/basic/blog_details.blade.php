@extends($activeTemplate . 'layouts.frontend')

@section('content')
    <section class="blog-detials mb-120 mt-60">
        <div class="container">
            <div class="row gy-5 justify-content-center">
                <div class="col-xl-9 col-lg-8">
                    <div class="blog-details">
                        <div class="blog-details__thumb">
                            <img src="{{ frontendImage('blog', $blog?->data_values?->image ?? null, '940x580') }}" class="fit-image" alt="Blog Image">
                            <div class="blog-item__date">
                                <h4 class="date-time">{{ showDateTime($blog->created_at, 'd') }}</h4>
                                <h6 class="month">{{ showDateTime($blog->created_at, 'M') }}</h6>
                            </div>
                        </div>
                        <div class="blog-details__content">
                            <span class="blog-details__subtitle">{{ __($blog?->data_values?->badge ?? 'Blog') }}</span>

                            <h4 class="my-3">{{ __($blog?->data_values?->title ?? '') }}</h4>

                            @php echo $blog?->data_values?->description ?? '' @endphp

                            <div class="blog-details__content-bottom">
                                <div class="blog-details__share d-flex align-items-center flex-wrap">
                                    <h5 class="social-share__title mb-0 me-sm-3 me-1 d-inline-block">@lang('Share')</h5>
                                    <ul class="social-list list-two">
                                        <li class="social-list__item">
                                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}"
                                               class="social-list__link flex-center" target="_blank">
                                                <i class="fab fa-facebook-f"></i>
                                            </a>
                                        </li>
                                        <li class="social-list__item">
                                            <a href="https://twitter.com/intent/tweet?text={{ urlencode($blog?->data_values?->title ?? '') }}&amp;url={{ urlencode(url()->current()) }}"
                                               class="social-list__link flex-center" target="_blank">
                                                <i class="fab fa-twitter"></i>
                                            </a>
                                        </li>
                                        <li class="social-list__item">
                                            <a href="https://pinterest.com/pin/create/bookmarklet/?media={{ frontendImage('blog', $blog?->data_values?->image ?? null, '940x580') }}&url={{ urlencode(url()->current()) }}"
                                               class="social-list__link flex-center" target="_blank">
                                                <i class="fab fa-pinterest-p"></i>
                                            </a>
                                        </li>
                                        <li class="social-list__item">
                                            <a href="http://www.linkedin.com/shareArticle?mini=true&amp;url={{ urlencode(url()->current()) }}"
                                               class="social-list__link flex-center" target="_blank">
                                                <i class="fab fa-linkedin-in"></i>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="fb-comments mt-4" data-href="{{ url()->current() }}" data-numposts="5"></div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-4">
                    <div class="blog-sidebar">
                        <div class="sidebar-item">
                            <h5 class="sidebar-item__title">@lang('Latest blogs')</h5>
                            <div class="sidebar-item__content">
                                @foreach ($recentBlogs as $recentBlog)
                                    <div class="latest-blog">
                                        <div class="latest-blog__thumb">
                                            <a href="{{ route('blog.details', $recentBlog?->slug) }}">
                                                <img src="{{ frontendImage('blog', 'thumb_' . $recentBlog?->data_values?->image ?? null, '940x580') }}" class="fit-image" alt="img">
                                            </a>
                                        </div>
                                        <div class="latest-blog__content">
                                            <h6 class="latest-blog__title">
                                                <a href="{{ route('blog.details', $recentBlog?->slug) }}">
                                                    {{ strLimit(__($recentBlog?->data_values?->title ?? ''), 50) }}
                                                </a>
                                            </h6>
                                            <span class="latest-blog__date fs-12">{{ showDateTime($recentBlog->created_at, 'd M Y') }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('fbComment')
    @php echo loadExtension('fb-comment') @endphp
@endpush
