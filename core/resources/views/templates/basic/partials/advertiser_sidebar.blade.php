<div class="sidebar-menu flex-between">
    <div class="sidebar-menu__inner">
        <span class="sidebar-menu__close d-lg-none d-block"><i class="fas fa-times"></i></span>
        <div class="sidebar-logo">
            <a href="{{ route('home') }}" class="sidebar-logo__link">
                <img src="{{ siteLogo() }}" alt="Logo">
            </a>
        </div>
        <ul class="sidebar-menu-list">
            <li class="sidebar-menu-list__item {{ menuActive('advertiser.home') }}" role="presentation">
                <a href="{{ route('advertiser.home') }}" class="sidebar-menu-list__link">
                    <span class="icon"><i class="fa-solid fa-border-all"></i></span>
                    <span class="text">@lang('Dashboard')</span>
                </a>
            </li>
            <li class="sidebar-menu-list__item {{ menuActive('advertiser.campaign.*') }}" role="presentation">
                <a href="{{ route('advertiser.campaign.index') }}" class="sidebar-menu-list__link">
                    <span class="icon"><i class="fa-solid fa-bullhorn"></i></span>
                    <span class="text">@lang('Campaigns')</span>
                </a>
            </li>
            <li class="sidebar-menu-list__item {{ menuActive('advertiser.tracking.snippets') }}" role="presentation">
                <a href="{{ route('advertiser.tracking.snippets') }}" class="sidebar-menu-list__link">
                    <span class="icon"><i class="fa-solid fa-code"></i></span>
                    <span class="text">@lang('Tracking Snippets')</span>
                </a>
            </li>
            @php
                $depositRoutes = ['advertiser.deposit.index', 'advertiser.deposit.history'];
            @endphp
            <li class="sidebar-menu-list__item has-dropdown {{ menuActive($depositRoutes, 3) }}" role="presentation">
                <a href="javascript:void(0)" class="sidebar-menu-list__link">
                    <span class="icon"><i class="fas fa-wallet"></i></span>
                    <span class="text">@lang('Deposit')</span>
                </a>
                <div class="sidebar-submenu {{ menuActive($depositRoutes, 3) ? 'active' : '' }}">
                    <ul class="sidebar-submenu-list">
                        <li class="sidebar-submenu-list__item {{ menuActive('advertiser.deposit.index') }}">
                            <a href="{{ route('advertiser.deposit.index') }}" class="sidebar-submenu-list__link">
                                <span class="text">@lang('Deposit Money')</span>
                            </a>
                        </li>
                        <li class="sidebar-submenu-list__item {{ menuActive('advertiser.deposit.history') }}">
                            <a href="{{ route('advertiser.deposit.history') }}" class="sidebar-submenu-list__link">
                                <span class="text">@lang('Deposit Log')</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            @php
                $withdrawRoutes = ['advertiser.withdraw', 'advertiser.withdraw.history'];
            @endphp
            <li class="sidebar-menu-list__item has-dropdown {{ menuActive($withdrawRoutes, 3) }}" role="presentation">
                <a href="javascript:void(0)" class="sidebar-menu-list__link">
                    <span class="icon"><i class="fas fa-hand-holding-usd"></i></span>
                    <span class="text">@lang('Withdraw')</span>
                </a>
                <div class="sidebar-submenu {{ menuActive($withdrawRoutes, 3) ? 'active' : '' }}">
                    <ul class="sidebar-submenu-list">
                        <li class="sidebar-submenu-list__item {{ menuActive('advertiser.withdraw') }}">
                            <a href="{{ route('advertiser.withdraw') }}" class="sidebar-submenu-list__link">
                                <span class="text">@lang('Withdraw Money')</span>
                            </a>
                        </li>
                        <li class="sidebar-submenu-list__item {{ menuActive('advertiser.withdraw.history') }}">
                            <a href="{{ route('advertiser.withdraw.history') }}" class="sidebar-submenu-list__link">
                                <span class="text">@lang('Withdraw Log')</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            <li class="sidebar-menu-list__item {{ menuActive('advertiser.transactions') }}" role="presentation">
                <a href="{{ route('advertiser.transactions') }}" class="sidebar-menu-list__link">
                    <span class="icon"><i class="fas fa-random"></i></span>
                    <span class="text">@lang('Transactions')</span>
                </a>
            </li>
            @php
                $ticketRoutes = ['advertiser.ticket.open', 'advertiser.ticket.index', 'advertiser.ticket.view'];
            @endphp
            <li class="sidebar-menu-list__item has-dropdown {{ menuActive($ticketRoutes) }}" role="presentation">
                <a href="javascript:void(0)" class="sidebar-menu-list__link">
                    <span class="icon"><i class="fas fa-headset"></i></span>
                    <span class="text">@lang('Support Ticket')</span>
                </a>
                <div class="sidebar-submenu {{ menuActive($ticketRoutes, 3) ? 'active' : '' }}">
                    <ul class="sidebar-submenu-list">
                        <li class="sidebar-submenu-list__item {{ menuActive('advertiser.ticket.open') }}">
                            <a href="{{ route('advertiser.ticket.open') }}" class="sidebar-submenu-list__link">
                                <span class="text">@lang('Open New Ticket')</span>
                            </a>
                        </li>
                        <li class="sidebar-submenu-list__item {{ menuActive('advertiser.ticket.index') }}">
                            <a href="{{ route('advertiser.ticket.index') }}" class="sidebar-submenu-list__link">
                                <span class="text">@lang('My Tickets')</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="sidebar-menu-list__item {{ menuActive('advertiser.profile.setting') }}" role="presentation">
                <a href="{{ route('advertiser.profile.setting') }}" class="sidebar-menu-list__link">
                    <span class="icon"><i class="fa-solid fa-user"></i></span>
                    <span class="text">@lang('Profile Setting')</span>
                </a>
            </li>

            <li class="sidebar-menu-list__item {{ menuActive('advertiser.change.password') }}" role="presentation">
                <a href="{{ route('advertiser.change.password') }}" class="sidebar-menu-list__link">
                    <span class="icon"><i class="fa-solid fa-lock"></i></span>
                    <span class="text">@lang('Password Change')</span>
                </a>
            </li>

            <li class="sidebar-menu-list__item {{ menuActive('advertiser.twofactor') }}" role="presentation">
                <a href="{{ route('advertiser.twofactor') }}" class="sidebar-menu-list__link">
                    <span class="icon"><i class="fas fa-shield-alt"></i></span>
                    <span class="text">@lang('2FA Security')</span>
                </a>
            </li>

            <li class="sidebar-menu-list__item" role="presentation">
                <a href="{{ route('advertiser.logout') }}" class="sidebar-menu-list__link">
                    <span class="icon"><i class="fas fa-sign-out-alt"></i></span>
                    <span class="text">@lang('Logout')</span>
                </a>
            </li>
        </ul>
    </div>
</div>
