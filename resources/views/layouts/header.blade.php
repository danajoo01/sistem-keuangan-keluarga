<!--! ================================================================ !-->
<!--! [Start] Header !-->
<!--! ================================================================ !-->
<header class="nxl-header">
    <div class="header-wrapper">
        <div class="header-left d-flex align-items-center gap-4">
            <a href="javascript:void(0);" class="nxl-head-mobile-toggler" id="mobile-collapse">
                <div class="hamburger hamburger--arrowturn">
                    <div class="hamburger-box">
                        <div class="hamburger-inner"></div>
                    </div>
                </div>
            </a>
            <div class="nxl-navigation-toggle">
                <a href="javascript:void(0);" id="menu-mini-button">
                    <i class="feather-align-left"></i>
                </a>
                <a href="javascript:void(0);" id="menu-expend-button" style="display: none">
                    <i class="feather-arrow-right"></i>
                </a>
            </div>
        </div>

        <div class="header-right ms-auto">
            <div class="d-flex align-items-center">
                <div class="dropdown nxl-h-item">
                    <a href="javascript:void(0);" class="nxl-head-link position-relative" data-bs-toggle="dropdown" role="button" data-bs-auto-close="outside">
                        <i class="feather-bell"></i>
                        @if($unreadNotificationCount > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">{{ $unreadNotificationCount }}</span>
                        @endif
                    </a>
                    <div class="dropdown-menu dropdown-menu-end nxl-h-dropdown notification-dropdown" style="width: min(360px, calc(100vw - 24px));">
                        <div class="dropdown-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Notifikasi</h6>
                            <span class="fs-12 text-muted">{{ $unreadNotificationCount }} belum dibaca</span>
                        </div>
                        <div class="dropdown-divider"></div>
                        @forelse($headerNotifications as $notification)
                        <a href="{{ route('notifications.visit', $notification->id) }}" class="dropdown-item py-3 notification-item" style="display: block; white-space: normal;">
                            <div class="fw-semibold text-dark notification-text" style="display: block; white-space: normal; overflow-wrap: anywhere; word-break: break-word; line-height: 1.45;">{{ $notification->data['title'] ?? 'Notifikasi' }}</div>
                            <div class="fs-12 text-muted mt-1 notification-text" style="display: block; white-space: normal; overflow-wrap: anywhere; word-break: break-word; line-height: 1.45;">{{ $notification->data['message'] ?? '-' }}</div>
                            <div class="fs-11 text-muted mt-1">{{ $notification->created_at?->diffForHumans() }}</div>
                        </a>
                        @empty
                        <div class="px-3 py-4 text-center text-muted fs-12">Belum ada notifikasi baru.</div>
                        @endforelse
                    </div>
                </div>
                <div class="nxl-h-item d-none d-sm-flex">
                    <div class="full-screen-switcher">
                        <a href="javascript:void(0);" class="nxl-head-link me-0" onclick="$ ('body').fullScreenHelper('toggle');">
                            <i class="feather-maximize maximize"></i>
                            <i class="feather-minimize minimize"></i>
                        </a>
                    </div>
                </div>
                <div class="nxl-h-item dark-light-theme">
                    <a href="javascript:void(0);" class="nxl-head-link me-0 dark-button">
                        <i class="feather-moon"></i>
                    </a>
                    <a href="javascript:void(0);" class="nxl-head-link me-0 light-button" style="display: none">
                        <i class="feather-sun"></i>
                    </a>
                </div>
                <div class="dropdown nxl-h-item">
                    <a href="javascript:void(0);" data-bs-toggle="dropdown" role="button" data-bs-auto-close="outside">
                        <img src="{{ asset('assets/images/general/full-avatar.png') }}" alt="user-image" class="img-fluid user-avtar me-0" />
                    </a>
                    <div class="dropdown-menu dropdown-menu-end nxl-h-dropdown nxl-user-dropdown">
                        <div class="dropdown-header">
                            <div class="d-flex align-items-center">
                                <img src="{{ asset('assets/images/general/full-avatar.png') }}" alt="user-image" class="img-fluid user-avtar" />
                                <div>
                                    <h6 class="text-dark mb-0">{{ auth()->user()->name }}<span class="badge bg-soft-success text-success ms-1"><i class="feather-check-circle"></i></span></h6>
                                    <span class="fs-12 fw-medium text-muted">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="dropdown-divider"></div>
                        <a href="{{ route('profile.edit') }}" class="dropdown-item">
                            <i class="feather-user"></i>
                            <span>Profile</span>
                        </a>
                        <div class="dropdown-divider"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item border-0 bg-transparent w-100 text-start">
                                <i class="feather-log-out"></i>
                                <span>Logout</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
<!--! ================================================================ !-->
<!--! [End] Header !-->