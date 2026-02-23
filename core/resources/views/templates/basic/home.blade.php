@extends($activeTemplate . 'layouts.frontend')
@section('content')

    @include($activeTemplate . 'sections.banner')

    @php
        $sectionList = !empty($sections?->secs) ? json_decode($sections->secs) : null;
        if (empty($sectionList) || !is_array($sectionList)) {
            $sectionList = ['about', 'category', 'campaigns', 'work_process', 'why_choose_us', 'benefit_section', 'counter_section', 'testimonials', 'cta_section', 'faq_section', 'blog', 'partner_section'];
        }
    @endphp
    @foreach ($sectionList as $sec)
        @if (view()->exists($activeTemplate . 'sections.' . $sec))
            @include($activeTemplate . 'sections.' . $sec)
        @endif
    @endforeach

@endsection
