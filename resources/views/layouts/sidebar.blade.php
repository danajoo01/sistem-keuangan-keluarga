<!--! ================================================================ !-->
<!--! [Start] Navigation Menu !-->
<!--! ================================================================ !-->
<nav class="nxl-navigation">
    <div class="navbar-wrapper">
        <div class="m-header">
            <a href="{{ route('dashboard') }}" class="b-brand">
                <img src="{{ asset('assets/images/logo-full-bu.png') }}" alt="logo" class="logo logo-lg" />
                <img src="{{ asset('assets/images/logo-full-bu.png') }}" alt="logo" class="logo logo-sm" />
            </a>
        </div>
        <div class="navbar-content">
            <ul class="nxl-navbar">
                <li class="nxl-item nxl-caption">
                    <label>Navigation</label>
                </li>
                <li class="nxl-item">
                    <a href="{{ route('dashboard') }}" class="nxl-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <span class="nxl-micon"><i class="feather-airplay"></i></span>
                        <span class="nxl-mtext">Dashboard</span>
                    </a>
                </li>
                <li class="nxl-item">
                    <a href="{{ route('profile.edit') }}" class="nxl-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                        <span class="nxl-micon"><i class="feather-user"></i></span>
                        <span class="nxl-mtext">Profile</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<!--! ================================================================ !-->
<!--! [End] Navigation Menu !-->