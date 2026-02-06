@extends($activeTemplate . 'layouts.frontend')
@section('content')
    <div class="blog-section mb-120 mt-60">
        <div class="container">
            <div class="row gy-4 justify-content-center">
                @include('Template::partials.blogs', ['blogs' => $blogs])
            </div>
            @if ($blogs->hasPages())
                <div class="mt-5">
                    {{ paginateLinks($blogs) }}
                </div>
            @endif
        </div>
    </div>


    @if (isset($sections->secs) && $sections->secs != null)
        @foreach (json_decode($sections->secs) as $sec)
            @include($activeTemplate . 'sections.' . $sec)
        @endforeach
    @endif
@endsection
