@extends($activeTemplate . 'layouts.frontend')
@section('content')
    @php
        $bannedContent = getContent('user_banned.content', true);
    @endphp
    <div class="py-120">
        <div class="container">
            <div class="row justify-content-center align-items-center">
                <div class="col-lg-12 text-center">
                    <img src="{{ frontendImage('user_banned', $bannedContent?->data_values?->image, '700x400') }}" alt="@lang('image')" class="mb-4">
                    <h4 class="text--danger mb-2">{{ __($bannedContent?->data_values?->heading) }}</h4>
                    <p class="mb-4">{{ __($user->ban_reason) }} </p>
                </div>
            </div>
        </div>
    </div>
@endsection
