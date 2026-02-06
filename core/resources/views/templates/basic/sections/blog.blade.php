@php
    $blogContent = getContent('blog.content', true);
    $blogPosts = getContent('blog.element', false, 3);
@endphp

<section class="blog my-120">
    <div class="container">
        <div class="section-heading text-center">
            <span class="section-heading__subtitle">{{ __($blogContent?->data_values?->subheading ?? '') }}</span>
            <h3 class="section-heading__title">{{ __($blogContent?->data_values?->heading ?? '') }}</h3>
            <p class="section-heading__desc">{{ __($blogContent?->data_values?->description ?? '') }}</p>
        </div>
        <div class="row gy-4 justify-content-center">
            @include('Template::partials.blogs', ['blogs' => $blogPosts])
        </div>
    </div>
</section>
