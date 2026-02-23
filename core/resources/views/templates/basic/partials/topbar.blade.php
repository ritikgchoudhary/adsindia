@php
    $authInfo = getAuthenticatedUserInfo();
    $prefix = $authInfo?->type === 'advertiser' ? 'advertiser' : 'user';
    $fullname = $authInfo?->user?->fullname ?? 'User';
    $imagePath = $authInfo?->type === 'advertiser' ? 'advertiserProfile' : 'userProfile';
    $imageFile = $authInfo?->user?->image;
@endphp

<div class="dashboard-header">
    <div class="dashboard-header__inner flex-between">
        <div class="dashboard-header__left">
            <div class="dashboard-body__bar d-lg-none d-block">
                <span class="dashboard-body__bar-icon"><i class="fas fa-bars"></i></span>
            </div>
            <div class="search-form-wrapper">
            </div>
        </div>
        <div class="user-info">
            <div class="user-info__right">

                <div class="user-info__button">
                    <p class="user-info__name">@lang('Hello'), {{ __($fullname) }}</p>
                    <div class="user-info__thumb">
                        <img src="{{ getImage(getFilePath($imagePath) . '/' . $imageFile, getFileSize($imagePath)) }}" alt="User">
                    </div>
                </div>
            </div>
            <ul class="user-info-dropdown">
                <li class="user-info-dropdown__item">
                    <a class="user-info-dropdown__link" href="{{ route($prefix . '.profile.setting') }}">
                        <span class="icon"><i class="far fa-user-circle"></i></span>
                        <span class="text">@lang('Profile Setting')</span>
                    </a>
                </li>

                <li class="user-info-dropdown__item">
                    <a class="user-info-dropdown__link" href="{{ route($prefix . '.change.password') }}">
                        <span class="icon"><i class="fas fa-key"></i></span>
                        <span class="text">@lang('Change Password')</span>
                    </a>
                </li>

                <li class="user-info-dropdown__item">
                    <a class="user-info-dropdown__link" href="{{ route($prefix . '.twofactor') }}">
                        <span class="icon"><i class="fas fa-shield-alt"></i></span>
                        <span class="text">@lang('2FA Security')</span>
                    </a>
                </li>

                <li class="user-info-dropdown__item">
                    <a class="user-info-dropdown__link" href="{{ route($prefix . '.logout') }}">
                        <span class="icon"><i class="fas fa-sign-out-alt"></i></span>
                        <span class="text">@lang('Logout')</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>
