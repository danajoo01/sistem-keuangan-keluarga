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
                @can('dashboard')
                <li class="nxl-item">
                    <a href="{{ route('dashboard') }}" class="nxl-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <span class="nxl-micon"><i class="feather-airplay"></i></span>
                        <span class="nxl-mtext">Dashboard</span>
                    </a>
                </li>
                @endcan
                @can('profile')
                <li class="nxl-item">
                    <a href="{{ route('profile.edit') }}" class="nxl-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                        <span class="nxl-micon"><i class="feather-user"></i></span>
                        <span class="nxl-mtext">Profile</span>
                    </a>
                </li>
                @endcan
                @canany(['data-pemasukan', 'pengajuan-dana', 'approval-pengajuan', 'data-pengeluaran', 'approval-pengeluaran'])
                <li class="nxl-item nxl-hasmenu {{ request()->routeIs('keuangan.*') ? 'nxl-trigger' : '' }}">
                    <a href="javascript:void(0);" class="nxl-link {{ request()->routeIs('keuangan.*') ? 'active' : '' }}">
                        <span class="nxl-micon"><i class="feather-dollar-sign"></i></span>
                        <span class="nxl-mtext">Keuangan</span>
                        <span class="nxl-arrow"><i class="feather-chevron-down"></i></span>
                    </a>
                    <ul class="nxl-submenu {{ request()->routeIs('keuangan.*') ? 'submenu-open' : '' }}">
                        @can('data-pemasukan')
                        <li class="nxl-item">
                            <a href="{{ route('keuangan.pemasukan.index') }}" class="nxl-link {{ request()->routeIs('keuangan.pemasukan.*') ? 'active' : '' }}">
                                <span class="nxl-mtext">Data Pemasukan</span>
                            </a>
                        </li>
                        @endcan
                        @can('pengajuan-dana')
                        <li class="nxl-item">
                            <a href="{{ route('keuangan.pengajuan-dana.index') }}" class="nxl-link {{ request()->routeIs('keuangan.pengajuan-dana.*') ? 'active' : '' }}">
                                <span class="nxl-mtext">Pengajuan Dana</span>
                            </a>
                        </li>
                        @endcan
                        @can('approval-pengajuan')
                        <li class="nxl-item">
                            <a href="{{ route('keuangan.approval-pengajuan.index') }}" class="nxl-link {{ request()->routeIs('keuangan.approval-pengajuan.*') ? 'active' : '' }}">
                                <span class="nxl-mtext">Approval Pengajuan</span>
                            </a>
                        </li>
                        @endcan
                        @can('data-pengeluaran')
                        <li class="nxl-item">
                            <a href="{{ route('keuangan.pengeluaran.index') }}" class="nxl-link {{ request()->routeIs('keuangan.pengeluaran.*') ? 'active' : '' }}">
                                <span class="nxl-mtext">Data Pengeluaran</span>
                            </a>
                        </li>
                        @endcan
                        @can('approval-pengeluaran')
                        <li class="nxl-item">
                            <a href="{{ route('keuangan.approval-pengeluaran.index') }}" class="nxl-link {{ request()->routeIs('keuangan.approval-pengeluaran.*') ? 'active' : '' }}">
                                <span class="nxl-mtext">Approval Pengeluaran</span>
                            </a>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endcanany
                @can('master-data')
                <li class="nxl-item nxl-hasmenu {{ request()->routeIs('master-data.*') ? 'nxl-trigger' : '' }}">
                    <a href="javascript:void(0);" class="nxl-link {{ request()->routeIs('master-data.*') ? 'active' : '' }}">
                        <span class="nxl-micon"><i class="feather-database"></i></span>
                        <span class="nxl-mtext">Data Master</span>
                        <span class="nxl-arrow"><i class="feather-chevron-down"></i></span>
                    </a>
                    <ul class="nxl-submenu {{ request()->routeIs('master-data.*') ? 'submenu-open' : '' }}">
                        @can('users')
                        <li class="nxl-item">
                            <a href="{{ route('master-data.users.index') }}" class="nxl-link {{ request()->routeIs('master-data.users.*') ? 'active' : '' }}">
                                <span class="nxl-mtext">User</span>
                            </a>
                        </li>
                        @endcan
                        @can('role-akses')
                        <li class="nxl-item">
                            <a href="{{ route('master-data.role-access.index') }}" class="nxl-link {{ request()->routeIs('master-data.role-access.*') ? 'active' : '' }}">
                                <span class="nxl-mtext">Role Akses</span>
                            </a>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endcan
            </ul>
        </div>
    </div>
</nav>
<!--! ================================================================ !-->
<!--! [End] Navigation Menu !-->