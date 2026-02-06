@extends($activeTemplate.'layouts.master')

@section('content')
<div class="dashboard-body">
    <h4 class="title">@lang('Referral Program')</h4>
    <div class="card custom--card">
        <div class="card-body">

            {{-- Referral Link Input --}}
            <form action="#">
                <div class="copy-input">
                    <input type="text" class="form--control referralURL"
                        value="{{ route('home') }}?reference={{ auth()->user()->username }}" readonly>
                    <span class="copy-icon copyBoard">
                        <i class="las la-copy"></i>
                    </span>
                </div>
            </form>

            {{-- Share Links --}}
            <div class="referral-share d-flex align-items-center flex-wrap mt-4">
                <h5 class="social-share__title mb-0 me-sm-3 me-1 d-inline-block">@lang('Share')</h5>
                <ul class="social-list list-two">
                    <li class="social-list__item">
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(route('home') . '?reference=' . auth()->user()->username) }}"
                           target="_blank" class="social-list__link flex-center">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                    </li>
                    <li class="social-list__item">
                        <a href="https://twitter.com/intent/tweet?url={{ urlencode(route('home') . '?reference=' . auth()->user()->username) }}"
                           target="_blank" class="social-list__link flex-center active">
                            <i class="fab fa-twitter"></i>
                        </a>
                    </li>
                    <li class="social-list__item">
                        <a href="https://www.linkedin.com/shareArticle?mini=true&url={{ urlencode(route('home') . '?reference=' . auth()->user()->username) }}"
                           target="_blank" class="social-list__link flex-center">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                    </li>
                    <li class="social-list__item">
                        <a href="https://www.instagram.com/" target="_blank"
                           class="social-list__link flex-center">
                            <i class="fab fa-instagram"></i>
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Referred By --}}
            @if(auth()->user()->referrer)
                <h6 class="text-white mt-4">@lang('You are referred by') {{ auth()->user()->referrer->fullname }}</h6>
            @endif

            {{-- Referral Tree --}}
            @if($user->allReferrals->count() > 0 && $maxLevel > 0)
            <div class="treeview-container mt-4">
                <ul class="treeview">
                    <li class="items-expanded">
                        {{ $user->fullname }} ( {{ $user->username }} )
                        @include($activeTemplate.'partials.under_tree',['user'=>$user,'layer'=>0,'isFirst'=>true])
                    </li>
                </ul>
            </div>
            @endif

        </div>
    </div>
</div>
@endsection

@push('style-lib')
<link href="{{ asset('assets/global/css/jquery.treeView.css') }}" rel="stylesheet" type="text/css">
@endpush

@push('style')
    <style>

    .copied::after {
        content: 'COPIED';
        position: absolute;
        right: 0;
        background-color: #{{ gs('base_color') }};
        color: #fff;
        padding: 4px 8px;
        font-size: 14px;
        border-radius: 5px 5px 0 5px;
        white-space: nowrap;
        z-index: 999;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }

    .copyBoard {
        position: relative;
    }

    .treeview,
    .treeview li {
        color: #ffffff !important;
    }

    .treeview li::marker {
        color: #ffffff;
    }

    .treeview li a {
        color: #ffffff !important;
    }
    </style>
@endpush

@push('script')
<script src="{{ asset('assets/global/js/jquery.treeView.js') }}"></script>
<script>
    (function($){
        "use strict";
        $('.treeview').treeView();

        $('.copyBoard').on('click', function () {
            const copyText = document.querySelector('.referralURL');
            copyText.select();
            copyText.setSelectionRange(0, 99999);
            document.execCommand('copy');
            copyText.blur();
            $(this).addClass('copied');
            setTimeout(() => $(this).removeClass('copied'), 1500);
        });
    })(jQuery);
</script>
@endpush
