@php
    $languages = \App\Models\Language::all();
    $currentLang = $languages->where('code', session('lang') ?? app()->getLocale())->first();
@endphp

<div class="custom--dropdown">
    <div class="custom--dropdown__selected dropdown-list__item">
        <div class="icon thumb me-1">
            <img src="{{ getImage(getFilePath('language') . '/' . $currentLang->image ?? '', getFileSize('language')) }}" alt="{{ $currentLang->name }}">
        </div>
        <span class="text">
            {{ __($currentLang->name ?? 'Language') }}
        </span>
    </div>
    <ul class="dropdown-list">
        @foreach ($languages as $lang)
            <li class="dropdown-list__item @if ($lang->code == $currentLang) active @endif" data-value="{{ $lang->code }}">
                <a href="{{ route('home') }}/change/{{ $lang->code }}" class="thumb">
                    <img src="{{ getImage(getFilePath('language') . '/' . $lang->image, getFileSize('language')) }}" alt="{{ $lang->name }}">
                    <span class="text">{{ __($lang->name) }}</span>
                </a>
            </li>
        @endforeach
    </ul>
</div>
