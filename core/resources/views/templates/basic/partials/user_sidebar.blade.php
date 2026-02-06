<div class="sidebar-menu flex-between">
    <div class="sidebar-menu__inner">
        <span class="sidebar-menu__close d-lg-none d-block"><i class="fas fa-times"></i></span>

        <div class="sidebar-logo">
            <a href="{{ route('home') }}" class="sidebar-logo__link">
                <img src="{{ siteLogo() }}" alt="Logo">
            </a>
        </div>
        <ul class="sidebar-menu-list">
            <li class="sidebar-menu-list__item {{ menuActive('user.home') }}" role="presentation">
                <a href="{{ route('user.home') }}" class="sidebar-menu-list__link">
                    <span class="icon"><i class="fa-solid fa-border-all"></i></span>
                    <span class="text">@lang('Dashboard')</span>
                </a>
            </li>
            <li class="sidebar-menu-list__item {{ menuActive('user.conversion.log') }}" role="presentation">
                <a href="{{ route('user.conversion.log') }}" class="sidebar-menu-list__link">
                    <span class="icon"><i class="fas fa-list"></i></span>
                    <span class="text">@lang('Conversion Log')</span>
                </a>
            </li>
            @php
                $withdrawRoutes = ['user.withdraw', 'user.withdraw.history'];
            @endphp
            <li class="sidebar-menu-list__item has-dropdown {{ menuActive($withdrawRoutes, 3) }}" role="presentation">
                <a href="javascript:void(0)" class="sidebar-menu-list__link">
                    <span class="icon"><i class="fas fa-hand-holding-usd"></i></span>
                    <span class="text">@lang('Withdraw')</span>
                </a>
                <div class="sidebar-submenu {{ menuActive($withdrawRoutes, 3) ? 'd-block' : '' }}">
                    <ul class="sidebar-submenu-list">
                        <li class="sidebar-submenu-list__item {{ menuActive('user.withdraw') }}">
                            <a href="{{ route('user.withdraw') }}" class="sidebar-submenu-list__link">
                                <span class="text">@lang('Withdraw Money')</span>
                            </a>
                        </li>
                        <li class="sidebar-submenu-list__item {{ menuActive('user.withdraw.history') }}">
                            <a href="{{ route('user.withdraw.history') }}" class="sidebar-submenu-list__link">
                                <span class="text">@lang('Withdraw Log')</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="sidebar-menu-list__item {{ menuActive('user.transactions') }}" role="presentation">
                <a href="{{ route('user.transactions') }}" class="sidebar-menu-list__link">
                    <span class="icon"><i class="fas fa-random"></i></span>
                    <span class="text">@lang('Transactions')</span>
                </a>
            </li>

            @php
                $ticketRoutes = ['ticket.open', 'ticket.index', 'ticket.view'];
            @endphp
            <li class="sidebar-menu-list__item has-dropdown {{ menuActive($ticketRoutes) }}" role="presentation">
                <a href="javascript:void(0)" class="sidebar-menu-list__link">
                    <span class="icon"><i class="fas fa-headset"></i></span>
                    <span class="text">@lang('Support Ticket')</span>
                </a>
                <div class="sidebar-submenu {{ menuActive($ticketRoutes, 3) ? 'd-block' : '' }}">
                    <ul class="sidebar-submenu-list">
                        <li class="sidebar-submenu-list__item {{ menuActive('ticket.open') }}">
                            <a href="{{ route('ticket.open') }}" class="sidebar-submenu-list__link">
                                <span class="text">@lang('Open New Ticket')</span>
                            </a>
                        </li>
                        <li class="sidebar-submenu-list__item {{ menuActive('ticket.index') }}">
                            <a href="{{ route('ticket.index') }}" class="sidebar-submenu-list__link">
                                <span class="text">@lang('My Tickets')</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="sidebar-menu-list__item {{ menuActive('user.profile.setting') }}" role="presentation">
                <a href="{{ route('user.profile.setting') }}" class="sidebar-menu-list__link">
                    <span class="icon"><i class="fa-solid fa-user"></i></span>
                    <span class="text">@lang('Profile Setting')</span>
                </a>
            </li>

            <li class="sidebar-menu-list__item {{ menuActive('user.change.password') }}" role="presentation">
                <a href="{{ route('user.change.password') }}" class="sidebar-menu-list__link">
                    <span class="icon"><i class="fa-solid fa-lock"></i></span>
                    <span class="text">@lang('Password Change')</span>
                </a>
            </li>

            <li class="sidebar-menu-list__item {{ menuActive('user.twofactor') }}" role="presentation">
                <a href="{{ route('user.twofactor') }}" class="sidebar-menu-list__link">
                    <span class="icon"><i class="fas fa-shield-alt"></i></span>
                    <span class="text">@lang('2FA Security')</span>
                </a>
            </li>

            <li class="sidebar-menu-list__item" role="presentation">
                <a href="{{ route('user.logout') }}" class="sidebar-menu-list__link">
                    <span class="icon"><i class="fas fa-sign-out-alt"></i></span>
                    <span class="text">@lang('Logout')</span>
                </a>
            </li>

        </ul>
    </div>
</div>
