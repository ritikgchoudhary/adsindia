@php
    $categoryContent = getContent('category.content', true);
    $categories = App\Models\Category::active()->whereHas('campaigns')->withCount('campaigns')->orderBy('campaigns_count')->get();
@endphp
<div class="category-section my-60">
    <div class="container">
        <div class="section-heading">
            <h3 class="section-heading__title">
                <span class="text--base">
                    {{ __($categoryContent?->data_values?->heading_base ?? '') }}
                </span>
                <span class="title-text">
                    {{ __($categoryContent?->data_values?->heading_text ?? '') }}
                </span>
            </h3>
        </div>
        <div class="row gy-4 justify-content-center">
            @foreach ($categories as $key => $category)
                <div class="col-xxl-2 col-lg-3 col-sm-4 col-6">
                    <a class="category-item" href="{{ route('campaigns', $category->id) }}">
                        <div class="category-item__thumb">
                            <img src="{{ getImage(getFilePath('category') . '/' . $category->image, getFileSize('category')) }}" alt="img">
                        </div>
                        <div class="category-item__content">
                            <h5 class="category-item__title">
                                {{ __($category->name) }}
                            </h5>
                            <span class="category-item__number"> ({{ $category->campaigns_count }}) </span>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</div>
